<?php
// includes/portal_auth.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_connection.php';

// Capability Definitions
// Defines what each role can do
const ROLE_CAPABILITIES = [
    'administrador' => ['bypass_auth', 'manage_users', 'manage_roles', 'system_config', 'edit_tools', 'view_restricted', 'access_admin_panel'],
    'admin'         => ['bypass_auth', 'manage_users', 'manage_roles', 'system_config', 'edit_tools', 'view_restricted', 'access_admin_panel'], 
    'líder suporte' => ['bypass_auth', 'manage_users', 'view_restricted', 'access_admin_panel'],
    'lider suporte' => ['bypass_auth', 'manage_users', 'view_restricted', 'access_admin_panel'], // Normalized
    'suporte'       => ['bypass_auth', 'view_restricted'],
    'support'       => ['bypass_auth', 'view_restricted'],
    'usuário'       => [],
    'user'          => []
];

// Helper to check capabilities
function hasCapability($cap) {
    if (!isset($_SESSION['user_capabilities'])) return false;
    
    // SuperAdmin fallback (hardcoded for safety)
    if (isset($_SESSION['user_role']) && (strtolower($_SESSION['user_role']) === 'administrador' || strtolower($_SESSION['user_role']) === 'admin')) {
        return true; 
    }

    $caps = $_SESSION['user_capabilities'] ?? [];
    return in_array($cap, $caps);
}

// Keep existing helpers for backward compatibility
function hasRole($role) {
    if (!isset($_SESSION['user_role'])) return false;
    $userRole = strtolower($_SESSION['user_role']);
    return $userRole === strtolower($role);
}

function isAdmin() {
    return hasCapability('system_config'); 
}

function isSupport() {
    return hasCapability('view_restricted'); 
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    return isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;
}

class PortalAuth {
    private $pdo;

    public function __construct() {
        $this->pdo = getDBConnection();
    }

    public function getPDO() {
        return $this->pdo;
    }

    public function login($username, $password) {
        if (!$this->pdo) {
            $_SESSION['login_error'] = "Erro de conexão com o banco.";
            return false;
        }

        // Updated query to join with roles
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.username, u.password_hash, u.full_name, u.status, u.role_id, r.name as role_name, r.capabilities, u.profile_image, u.nickname, u.bio
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.username = :user
        ");
        $stmt->execute([':user' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['status'] !== 'active') {
                $_SESSION['login_error'] = "Sua conta está " . ($user['status'] === 'pending' ? 'aguardando aprovação' : 'bloqueada') . ".";
                return false;
            }

            // Success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role_id'] = $user['role_id'];
            $_SESSION['user_role'] = strtolower($user['role_name'] ?? 'user'); 
            $_SESSION['user_role_label'] = $user['role_name'] ?? 'Usuário';
            
            // New Profile Fields
            $_SESSION['user_image'] = $user['profile_image'] ?? null;
            $_SESSION['user_nickname'] = $user['nickname'] ?? null;
            $_SESSION['user_bio'] = $user['bio'] ?? null;
            
            // Load Capabilities
            $caps = json_decode($user['capabilities'] ?? '[]', true);
            if (!is_array($caps)) $caps = [];
            $_SESSION['user_capabilities'] = $caps;
            
            $_SESSION['logged_in'] = true;

            return true;
        }

        $_SESSION['login_error'] = "Usuário ou senha incorretos.";
        return false;
    }

    public function register($username, $password, $fullName) {
        if (!$this->pdo) return ['success' => false, 'message' => "Erro de banco."];

        // Check exists
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :user");
        $stmt->execute([':user' => $username]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => "Usuário já existe."];
        }

        $passHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Default: role=user, status=pending (Requires Admin Approval as per request? "só um admin pode dar a permissao... por padrão todos são usuários até um admin liberar")
        // Interpretation: They can register, but are "Pending" or standard "User"?
        // Request: "qualquer usuário pode se cadastrar, mas só um admin pode dar a permissao de suporte/usuário, por padrão todos são usuários até um admin liberar"
        // This implies they start as 'user' (maybe active?) but to get anything HIGHER requires admin. Or maybe they are BLOCKED until admin approves?
        // Let's set status = 'active' but role = 'user' (ReadOnly). Admin can promote.
        
        $role = 'user';
        $status = 'active'; 

        try {
            $insert = $this->pdo->prepare("INSERT INTO users (username, password_hash, full_name, role, status) VALUES (:user, :pass, :name, :role, :status)");
            $insert->execute([
                ':user' => $username,
                ':pass' => $passHash,
                ':name' => $fullName,
                ':role' => $role,
                ':status' => $status
            ]);
            return ['success' => true, 'message' => "Cadastro realizado! Faça login."];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => "Erro ao cadastrar: " . $e->getMessage()];

        }
    }

    public function updateProfile($userId, $data) {
        $params = [':id' => $userId];

        if (array_key_exists('full_name', $data)) {
            $fields[] = "full_name = :full_name";
            $params[':full_name'] = $data['full_name'];
            $_SESSION['user_name'] = $data['full_name']; // Update session
        }
        if (array_key_exists('nickname', $data)) {
            $fields[] = "nickname = :nickname";
            $params[':nickname'] = $data['nickname'];
            $_SESSION['user_nickname'] = $data['nickname'];
        }
        if (array_key_exists('bio', $data)) {
            $fields[] = "bio = :bio";
            $params[':bio'] = $data['bio'];
            $_SESSION['user_bio'] = $data['bio'];
        }
        if (array_key_exists('profile_image', $data)) {
            $fields[] = "profile_image = :profile_image";
            $params[':profile_image'] = $data['profile_image'];
            $_SESSION['user_image'] = $data['profile_image'];
        }
        // Password change handling
        if (!empty($data['password'])) {
            $fields[] = "password_hash = :password";
            $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (empty($fields)) {
            return ['success' => true, 'message' => "Nada para atualizar."];
        }

        try {
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return ['success' => true, 'message' => "Perfil atualizado!"];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => "Erro ao atualizar: " . $e->getMessage()];
        }
    }

    public function logout() {
        session_destroy();
    }

    public function updateLastSeen($userId) {
        if (!$this->pdo) return;
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET last_seen = NOW() WHERE id = :id");
            $stmt->execute([':id' => $userId]);
        } catch (PDOException $e) {
            // checking quietly
        }
    }

    public function getOnlineUsers() {
        if (!$this->pdo) return [];
        try {
            // Get users seen in last 5 minutes
            $stmt = $this->pdo->query("SELECT username, full_name, role FROM users WHERE last_seen > NOW() - INTERVAL '5 minutes' ORDER BY full_name ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
             return [];
        }
    }
}
?>
