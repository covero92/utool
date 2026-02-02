<?php
session_start();
require_once 'portal_helpers.php';
require_once 'portal_auth.php'; // Includes db_connection.php

$action = $_POST['portal_action'] ?? ($_POST['action'] ?? '');
$portal = new SupportPortal();
$auth = new PortalAuth();

// --- PUBLIC ACTIONS (No Auth Required) ---

if ($action === 'login') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    if ($auth->login($user, $pass)) {
        header("Location: ../index.php");
    } else {
        $_SESSION['login_error_flag'] = true; // To reopen modal
        header("Location: ../index.php");
    }
    exit;
}

if ($action === 'register') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $name = $_POST['full_name'] ?? '';
    
    $result = $auth->register($user, $pass, $name);
    
    if ($result['success']) {
        $_SESSION['login_success_msg'] = $result['message'];
    } else {
        $_SESSION['register_error'] = $result['message'];
        $_SESSION['register_error_flag'] = true;
    }
    header("Location: ../index.php");
    exit;
}

if ($action === 'logout') {
    $auth->logout();
    header("Location: ../index.php");
    exit;
}

// --- AUTHENTICATED ACTIONS ---

// Require Login for any other action
if (!isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Authenticated User Actions
if ($action === 'change_password') {
    $currentPass = $_POST['current_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $userId = $_SESSION['user_id'] ?? null;
    
    if (!$userId || !$currentPass || !$newPass) {
        echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
        exit;
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $userExp = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($userExp && password_verify($currentPass, $userExp['password_hash'])) {
        $newHash = password_hash($newPass, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
        $update->execute([':hash' => $newHash, ':id' => $userId]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Senha atual incorreta']);
    }
    exit;
}

// --- SUPPORT / ADMIN ACTIONS ---
// (Notices, Weather, etc - Support can edit)
if (isSupport() || isAdmin()) {
    
    if ($action === 'update_weather') {
        $city = $_POST['city'] ?? '';
        $lat = $_POST['lat'] ?? '';
        $lon = $_POST['lon'] ?? '';
        
        if ($city && $lat && $lon) {
            $portal->updateConfig('weather', ['city' => $city, 'lat' => (float)$lat, 'lon' => (float)$lon]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing fields']);
        }
        exit;
    }

    if ($action === 'save_notice') {
        $id = $_POST['id'] ?? '';
        $title = $_POST['title'] ?? 'Aviso';
        $type = $_POST['type'] ?? 'info';
        $desc = $_POST['description'] ?? '';
        $team = $_POST['team'] ?? 'Suporte (geral)';
        
        if ($desc) {
            $notices = $portal->getNotices();
            
            if ($id) {
                // Update existing
                foreach ($notices as &$n) {
                    if ($n['id'] === $id) {
                        $n['title'] = $title;
                        $n['type'] = $type;
                        $n['description'] = $desc;
                        $n['team'] = $team;
                         // Keep original date or update? Usually keep original creation date.
                        break;
                    }
                }
            } else {
                // Create new
                $newNotice = [
                    'id' => uniqid('notice_'),
                    'type' => $type,
                    'title' => $title,
                    'description' => $desc,
                    'team' => $team,
                    'date' => date('Y-m-d H:i:s'),
                    'author' => getCurrentUser()
                ];
                array_unshift($notices, $newNotice); // Prepend to top
            }
            
            $portal->updateConfig('notices', $notices);
            echo json_encode(['success' => true]);
        } else {
             echo json_encode(['success' => false, 'message' => 'Descrição obrigatória']);
        }
        exit;
    }

    // --- PPR ACTIONS ---
    if ($action === 'get_ppr_data') {
        $year = $_POST['year'] ?? date('Y');
        
        $pdo = getDBConnection();
        // Fetch values
        $stmt = $pdo->prepare("
            SELECT m.key, v.month, v.value 
            FROM ppr_values v
            JOIN ppr_metrics m ON v.metric_id = m.id
            WHERE v.year = :year
        ");
        $stmt->execute([':year' => (int)$year]);
        $rows = $stmt->fetchAll();
        
        // Transform to { 'metric_key': { '1': 'value', '2': 'value' } }
        $data = [];
        foreach($rows as $r) {
            $data[$r['key']][$r['month']] = $r['value'];
        }
        
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    if ($action === 'save_ppr_data') {
        $year = $_POST['year'] ?? date('Y');
        $entriesRaw = $_POST['entries'] ?? '';
        $entries = json_decode($entriesRaw, true);

        if (!is_array($entries)) {
            echo json_encode(['success' => false, 'message' => 'Invalid data format']);
            exit;
        }

        $pdo = getDBConnection();
        $currentUser = getCurrentUser() ?? 'System';
        $currentUserId = $_SESSION['user_id'] ?? null;

        try {
            $pdo->beginTransaction();
            
            // Get Metrics for THIS YEAR
            $stmt = $pdo->prepare("SELECT key, id FROM ppr_metrics WHERE year = :year");
            $stmt->execute([':year' => (int)$year]);
            $metrics = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // [key => id]
            
            // Prepare statements
            $checkOld = $pdo->prepare("SELECT value FROM ppr_values WHERE metric_id = :mid AND month = :month AND year = :year");
            $upsert = $pdo->prepare("
                INSERT INTO ppr_values (metric_id, year, month, value, updated_at)
                VALUES (:mid, :year, :month, :val, NOW())
                ON CONFLICT (metric_id, year, month) 
                DO UPDATE SET value = :val, updated_at = NOW()
            ");

            $audit = $pdo->prepare("INSERT INTO ppr_audit_log (user_id, user_name, action, entity_type, entity_id, old_value, new_value, details, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

            foreach ($entries as $e) {
                $key = $e['key'];
                $month = (int)$e['month'];
                $val = trim($e['value']);
                
                if (!isset($metrics[$key])) {
                     file_put_contents('debug_save.txt', "Metric $key not found for Year $year\n", FILE_APPEND);
                     continue; 
                }
                $mid = $metrics[$key];
                
                // Check change
                $checkOld->execute([':mid' => $mid, ':month' => $month, ':year' => (int)$year]);
                $oldVal = $checkOld->fetchColumn();
                $oldVal = ($oldVal === false) ? '' : $oldVal;

                file_put_contents('debug_save.txt', "Checking $key ($mid): Old='$oldVal', New='$val'\n", FILE_APPEND);

                if ($oldVal !== $val) {
                    // Execute Update
                    $upsert->execute([
                        ':mid' => $mid,
                        ':year' => (int)$year,
                        ':month' => $month,
                        ':val' => $val
                    ]);
                    
                    // Log Audit
                    $details = "Updated $key ($month/$year)";
                    $audit->execute([$currentUserId, $currentUser, 'UPDATE', 'ppr_value', $mid, $oldVal, $val, $details]);
                    file_put_contents('debug_save.txt', "Updated and Audited\n", FILE_APPEND);
                }
            }
            
            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $ex) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
        }
        exit;
    }

    if ($action === 'get_ppr_audit') {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT * FROM ppr_audit_log ORDER BY created_at DESC LIMIT 100");
        $logs = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $logs]);
        exit;
    }

    if ($action === 'get_ppr_history') {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT v.year, m.key, v.month, v.value 
            FROM ppr_values v
            JOIN ppr_metrics m ON v.metric_id = m.id
            ORDER BY v.year, m.key, v.month
        ");
        $stmt->execute();
        $rows = $stmt->fetchAll();
        
        $history = [];
        foreach($rows as $r) {
            $history[$r['year']][$r['key']][$r['month']] = $r['value'];
        }
        echo json_encode(['success' => true, 'data' => $history]);
        exit;
    }

    if ($action === 'get_metrics_config') {
        $year = $_POST['year'] ?? date('Y');
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM ppr_metrics WHERE year = :year ORDER BY okr_group, key");
        $stmt->execute([':year' => (int)$year]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Transform to Structure: [{id: 'okr1', metrics: [...]}, ...]
        $groups = [];
        foreach($rows as $r) {
            $gid = $r['okr_group']; // e.g. 'okr1'
            if (!isset($groups[$gid])) {
                $groups[$gid] = ['id' => $gid, 'metrics' => []];
            }
            $groups[$gid]['metrics'][] = [
                'key' => $r['key'],
                'name' => $r['name'],
                'type' => $r['type'],
                'desc' => $r['target_description'], // Use full description from DB
                'target' => $r['target_value']
            ];
        }
        echo json_encode(['success' => true, 'data' => array_values($groups)]);
        exit;
    }

    if ($action === 'save_metric_attribute') {
        if (!isAdmin()) {
             echo json_encode(['success' => false, 'message' => 'Unauthorized']);
             exit;
        }
        $year = $_POST['year'];
        $key = $_POST['key'];
        $col = $_POST['column']; // e.g. 'target_description'
        $val = $_POST['value'];
        
        $allowedCols = ['name', 'target_description', 'type', 'target_value'];
        if (!in_array($col, $allowedCols)) {
             echo json_encode(['success' => false, 'message' => 'Column not allowed']);
             exit;
        }

        $pdo = getDBConnection();
        try {
            $stmt = $pdo->prepare("UPDATE ppr_metrics SET $col = :val WHERE key = :key AND year = :year");
            $stmt->execute([':val' => $val, ':key' => $key, ':year' => $year]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

}
    // --- ADMIN / LEADER ACTIONS ---
    
    // Update Version (Admin specific usually? Or System Config?)
    if ($action === 'update_version') {
        if (!hasCapability('system_config')) { echo json_encode(['success'=>false, 'message'=>'Unauthorized']); exit; }
        
        $version = $_POST['version'] ?? '';
        $date = $_POST['date'] ?? '';
        if ($version && $date) {
            $portal->updateConfig('latest_version', ['version' => $version, 'date' => $date]);
            echo json_encode(['success' => true]);
        }
        exit;
    }

    // Toggle Card Visibility (Edit Tools capability)
    if ($action === 'toggle_card') {
        if (!hasCapability('edit_tools')) { echo json_encode(['success'=>false, 'message'=>'Unauthorized']); exit; }
        
        $cardId = $_POST['card_id'] ?? '';
        if ($cardId) {
            $newState = $portal->toggleBlockToken($cardId);
            echo json_encode(['success' => true, 'blocked' => $newState]);
        }
        exit;
    }
    
    // Delete Notice (Support/Admin)
    if ($action === 'delete_notice') {
        if (!isSupport() && !isAdmin()) { echo json_encode(['success'=>false, 'message'=>'Unauthorized']); exit; }

        $id = $_POST['notice_id'] ?? '';
        $notices = $portal->getNotices();
        $notices = array_filter($notices, function($n) use ($id) { return ($n['id'] ?? '') !== $id; });
        $portal->updateConfig('notices', array_values($notices));
        echo json_encode(['success' => true]);
        exit;
    }

    // --- ROLE MANAGEMENT (manage_roles) ---
    if ($action === 'admin_save_role') {
        if (!hasCapability('manage_roles')) { echo json_encode(['success'=>false, 'message'=>'Unauthorized: Missing manage_roles']); exit; }

        $id = $_POST['id'] ?? '';
        $name = $_POST['name'] ?? '';
        $desc = $_POST['description'] ?? '';
        // Decode capabilities array
        $caps = json_decode($_POST['capabilities'] ?? '[]', true);
        if (!is_array($caps)) $caps = [];
        $capsJson = json_encode($caps);
        
        if (!$name) { echo json_encode(['success' => false, 'message' => 'Nome obrigatório']); exit; }
        
        $pdo = getDBConnection();
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE roles SET name = ?, description = ?, capabilities = ? WHERE id = ?");
            try {
                $stmt->execute([$name, $desc, $capsJson, $id]);
                echo json_encode(['success' => true]);
            } catch (Exception $e) { echo json_encode(['success' => false, 'message' => 'Erro ao atualizar (nome duplicado?)']); }
        } else {
            // Create
            $stmt = $pdo->prepare("INSERT INTO roles (name, description, capabilities) VALUES (?, ?, ?)");
            try {
                $stmt->execute([$name, $desc, $capsJson]);
                echo json_encode(['success' => true]);
            } catch (Exception $e) { echo json_encode(['success' => false, 'message' => 'Erro ao criar (nome duplicado?)']); }
        }
        exit;
    }

    if ($action === 'admin_delete_role') {
        if (!hasCapability('manage_roles')) { echo json_encode(['success'=>false, 'message'=>'Unauthorized']); exit; }

        $id = $_POST['id'] ?? '';
        $pdo = getDBConnection();
        
        // Check if system
        $check = $pdo->prepare("SELECT is_system FROM roles WHERE id = ?");
        $check->execute([$id]);
        $role = $check->fetch();
        if ($role && $role['is_system']) {
            echo json_encode(['success' => false, 'message' => 'Permissões de sistema não podem ser excluídas']);
            exit;
        }

        // Check columns
        $usersLinked = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role_id = ?");
        $usersLinked->execute([$id]);
        if ($usersLinked->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Existem usuários com esta permissão. Remova-os antes de excluir.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }

    // --- USER MANAGEMENT (manage_users) ---
    if ($action === 'admin_update_user_role_id') {
        if (!hasCapability('manage_users')) { echo json_encode(['success'=>false, 'message'=>'Unauthorized']); exit; }

        $targetUserId = $_POST['user_id'] ?? '';
        $newRoleId = $_POST['role_id'] ?? '';
        
        // Security: Prevent editing admins if not System Config
        // Check target user role
        $pdo = getDBConnection();
        $target = $pdo->prepare("SELECT r.name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
        $target->execute([$targetUserId]);
        $tRole = $target->fetchColumn();
        
        if ($tRole && stripos($tRole, 'admin') !== false && !hasCapability('system_config')) {
             echo json_encode(['success'=>false, 'message'=>'Você não pode modificar um Administrador.']); exit; 
        }

        $stmt = $pdo->prepare("UPDATE users SET role_id = :role WHERE id = :id");
        $stmt->execute([':role' => $newRoleId, ':id' => $targetUserId]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'admin_update_user_role') {
         // Legacy - only admin
         if (!hasCapability('manage_users')) { echo json_encode(['success'=>false, 'message'=>'Unauthorized']); exit; }
         
         // ... (Logic removed for brevity if not needed, or simplified)
         // Assuming legacy support not critical for new UI, but lets keep it simple
        $targetUserId = $_POST['user_id'] ?? '';
        $newRole = $_POST['role'] ?? '';
        $pdo = getDBConnection();
        $map = $pdo->prepare("SELECT id FROM roles WHERE name ILIKE ?");
        $map->execute([$newRole]);
        $rid = $map->fetchColumn();
        if ($rid) {
            $stmt = $pdo->prepare("UPDATE users SET role_id = :role WHERE id = :id");
            $stmt->execute([':role' => $rid, ':id' => $targetUserId]);
             echo json_encode(['success' => true]);
        } else {
             echo json_encode(['success' => false, 'message' => 'Role not found']);
        }
        exit;
    }

    if ($action === 'admin_toggle_user_status') {
        if (!hasCapability('manage_users')) { echo json_encode(['success'=>false, 'message'=>'Unauthorized']); exit; }

        $targetUserId = $_POST['user_id'] ?? '';
        $currentStatus = $_POST['current_status'] ?? '';
        
        // Security: Prevent blocking admins if not System Config
        $pdo = getDBConnection();
        $target = $pdo->prepare("SELECT r.name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
        $target->execute([$targetUserId]);
        $tRole = $target->fetchColumn();
        
        if ($tRole && stripos($tRole, 'admin') !== false && !hasCapability('system_config')) {
             echo json_encode(['success'=>false, 'message'=>'Você não pode bloquear um Administrador.']); exit; 
        }

        $newStatus = ($currentStatus === 'active') ? 'blocked' : 'active';
        $stmt = $pdo->prepare("UPDATE users SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $newStatus, ':id' => $targetUserId]);
        echo json_encode(['success' => true]);
        exit;
    }
    
    if ($action === 'admin_reset_password') {
        if (!hasCapability('manage_users')) { echo json_encode(['success'=>false, 'message'=>'Unauthorized']); exit; }

        $targetUserId = $_POST['user_id'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        
        if ($targetUserId && $newPass) {
             $pdo = getDBConnection();
             
            // Security: Prevent reset admin pass if not System Config
            $target = $pdo->prepare("SELECT r.name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
            $target->execute([$targetUserId]);
            $tRole = $target->fetchColumn();
            
            if ($tRole && stripos($tRole, 'admin') !== false && !hasCapability('system_config')) {
                 echo json_encode(['success'=>false, 'message'=>'Você não pode resetar a senha de um Administrador.']); exit; 
            }

             $hash = password_hash($newPass, PASSWORD_DEFAULT);
             $stmt = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
             $stmt->execute([':hash' => $hash, ':id' => $targetUserId]);
             echo json_encode(['success' => true]);
        } else {
             echo json_encode(['success' => false, 'message' => 'Missing data']);
        }
        exit;
    }

    if ($action === 'update_db_config') {
        if (!isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $host = $_POST['host'] ?? 'localhost';
        $port = $_POST['port'] ?? '5432';
        $dbname = $_POST['dbname'] ?? 'suporte_hub';
        $user = $_POST['user'] ?? 'postgres';
        $pass = $_POST['password'] ?? 'postgres';

        $content = "<?php\n// includes/db_connection.php\n\nfunction getDBConnection() {\n    \$host = '$host';\n    \$port = '$port';\n    \$dbname = '$dbname';\n    \$user = '$user';\n    \$password = '$pass';\n\n    try {\n        \$dsn = \"pgsql:host=\$host;port=\$port;dbname=\$dbname\";\n        \$pdo = new PDO(\$dsn, \$user, \$password, [\n            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC\n        ]);\n        return \$pdo;\n    } catch (PDOException \$e) {\n        // Log error instead of displaying freely in production\n        error_log(\"DB Connection Error: \" . \$e->getMessage());\n        return null;\n    }\n}\n?>";
        
        if(file_put_contents('includes/db_connection.php', $content)) {
            echo json_encode(['success' => true]);
        } else {
             echo json_encode(['success' => false, 'message' => 'Write failed']);
        }
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action or permission']);
?>
