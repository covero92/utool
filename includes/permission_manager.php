<?php
// includes/permission_manager.php
require_once 'db_connection.php';

class PermissionManager {
    private $pdo;
    private $rolePermissions = [];

    public function __construct() {
        $this->pdo = getDBConnection();
    }

    /**
     * Checks if a role can view a specific card.
     * Default to TRUE if no rule is defined, unless otherwise specified.
     */
    public function canView($cardSlug, $roleId) {
        if (!$this->pdo) return true; // Fail open if DB error? Or fail closed? Letting fail open for continuity.
        
        // Super admin bypass check should happen upstream or here. 
        // Assuming admin users usually have bypass, but this function checks specific DB rules.
        
        // Check cache first to avoid slamming DB in loops?
        // For now, simple query. Optimization later.

        $stmt = $this->pdo->prepare("SELECT can_view FROM card_permissions WHERE card_slug = :slug AND role_id = :role");
        $stmt->execute([':slug' => $cardSlug, ':role' => $roleId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return (bool)$result['can_view'];
        }

        // Default Policy: VISIBLE unless explicitly hidden
        return true; 
    }

    /**
     * Set permission for a card and role
     */
    public function setPermission($cardSlug, $roleId, $canView) {
        if (!$this->pdo) return false;

        // Upsert logic (PostgreSQL)
        $sql = "
            INSERT INTO card_permissions (card_slug, role_id, can_view)
            VALUES (:slug, :role, :view)
            ON CONFLICT (card_slug, role_id) 
            DO UPDATE SET can_view = EXCLUDED.can_view;
        ";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':slug' => $cardSlug,
            ':role' => $roleId,
            ':view' => $canView ? 'true' : 'false'
        ]);
    }

    public function getAllPermissions() {
        if (!$this->pdo) return [];
        $stmt = $this->pdo->query("SELECT * FROM card_permissions");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getPDO() {
        return $this->pdo;
    }
}
?>
