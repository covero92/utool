<?php
require_once 'includes/db_connection.php';

$pdo = getDBConnection();

try {
    // Add column if not exists
    $pdo->exec("ALTER TABLE roles ADD COLUMN IF NOT EXISTS capabilities TEXT");
    echo "Column 'capabilities' checked/added.\n";

    // Seed initial values based on names
    $roles = $pdo->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC);

    $defaults = [
        'administrador' => ['bypass_auth', 'manage_users', 'manage_roles', 'system_config', 'edit_tools', 'view_restricted', 'access_admin_panel'],
        'admin'         => ['bypass_auth', 'manage_users', 'manage_roles', 'system_config', 'edit_tools', 'view_restricted', 'access_admin_panel'],
        'líder suporte' => ['bypass_auth', 'manage_users', 'view_restricted', 'access_admin_panel'],
        'suporte'       => ['bypass_auth', 'view_restricted'],
        'usuário'       => []
    ];

    foreach ($roles as $r) {
        $nameLower = strtolower($r['name']);
        $caps = [];
        
        // Find match
        foreach ($defaults as $key => $val) {
            if (stripos($nameLower, $key) !== false) {
                $caps = $val;
                break;
            }
        }
        
        $json = json_encode($caps);
        $update = $pdo->prepare("UPDATE roles SET capabilities = ? WHERE id = ?");
        $update->execute([$json, $r['id']]);
        echo "Updated role '{$r['name']}' with " . count($caps) . " capabilities.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
