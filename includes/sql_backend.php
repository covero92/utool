<?php
session_start();

// Disable error reporting for cleaner JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

/*
 * SQL Backend Handler
 * Actions: test_connect, execute_query, list_dbs, list_tables, get_table_details
 */

$action = $_POST['action'] ?? '';

// Helper to send JSON response
function jsonResponse($success, $data = [], $message = '') {
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $message]);
    exit;
}

// Get connection string from session or POST
function getConnectionString() {
    if (isset($_POST['host'])) {
        // Establishing new connection
        $host = $_POST['host'];
        $port = $_POST['port'] ?? '5432';
        $user = $_POST['user'];
        $pass = $_POST['password'];
        $dbname = !empty($_POST['dbname']) ? $_POST['dbname'] : 'postgres';
        
        return "host=$host port=$port dbname=$dbname user=$user password=$pass";
    } elseif (isset($_SESSION['sql_conn'])) {
        // Reusing session connection
        // We actully store creds in session to reconnect, as resources can't be serialized
        $creds = $_SESSION['sql_conn'];
        return "host={$creds['host']} port={$creds['port']} dbname={$creds['dbname']} user={$creds['user']} password={$creds['password']}";
    }
    return null;
}

// Action: Connect / Test Connection
if ($action === 'connect') {
    $connStr = getConnectionString();
    if (!$connStr) jsonResponse(false, [], 'Dados de conexão ausentes.');

    $conn = pg_connect($connStr);
    
    if ($conn) {
        // Store credentials in session on success
        if (isset($_POST['host'])) {
            $_SESSION['sql_conn'] = [
                'host' => $_POST['host'],
                'port' => $_POST['port'] ?? '5432',
                'user' => $_POST['user'],
                'password' => $_POST['password'],
                'dbname' => !empty($_POST['dbname']) ? $_POST['dbname'] : 'postgres'
            ];
        }
        jsonResponse(true, ['meta' => $_SESSION['sql_conn']], 'Conexão realizada com sucesso!');
    } else {
        jsonResponse(false, [], 'Falha ao conectar: ' . pg_last_error());
    }
}

// Action: Disconnect
if ($action === 'disconnect') {
    unset($_SESSION['sql_conn']);
    jsonResponse(true, [], 'Desconectado.');
}

// Middleware: Check connection for subsequent actions
$connStr = getConnectionString();
if (!$connStr) {
    jsonResponse(false, [], 'Não conectado.');
}
$conn = pg_connect($connStr);
if (!$conn) {
    jsonResponse(false, [], 'Perda de conexão. Reconecte.');
}

// Action: List Databases
if ($action === 'list_dbs') {
    // Modified query to get name AND size
    $query = "
        SELECT datname, 
               pg_database_size(datname) as size_bytes,
               pg_size_pretty(pg_database_size(datname)) as size_pretty
        FROM pg_database 
        WHERE datistemplate = false 
        ORDER BY datname;
    ";
    $result = pg_query($conn, $query);
    
    if (!$result) {
        jsonResponse(false, [], pg_last_error($conn));
    }
    
    $dbs = [];
    $totalSize = 0;
    
    while ($row = pg_fetch_assoc($result)) {
        $size = floatval($row['size_bytes']);
        $totalSize += $size;
        
        $dbs[] = [
            'name' => $row['datname'],
            'size_bytes' => $size,
            'size_pretty' => $row['size_pretty']
        ];
    }
    
    // Calculate total size pretty
    // Since we don't have pg_size_pretty for a raw number in PHP easily available without query, 
    // let's do a quick query or just format it in JS? 
    // Easier to ask Postgres to format the sum if possible, but we summed in PHP.
    // Let's just do a simple PHP formatter or helper query. 
    // Actually, let's run a separate query for total just to be clean or use a helper.
    // Simpler: PHP formatting for total.
    
    function formatBytes($bytes, $precision = 2) { 
        $units = array('B', 'kB', 'MB', 'GB', 'TB'); 
        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
        $bytes /= pow(1024, $pow); 
        return round($bytes, $precision) . ' ' . $units[$pow]; 
    } 

    $stats = [
        'total_count' => count($dbs),
        'total_size_bytes' => $totalSize,
        'total_size_pretty' => formatBytes($totalSize)
    ];
    
    // Include current connection info
    jsonResponse(true, ['dbs' => $dbs, 'stats' => $stats, 'meta' => $_SESSION['sql_conn'] ?? null]);
}

// Action: Change Database (Update Session)
if ($action === 'change_db') {
    $newDb = $_POST['new_dbname'];
    $newDb = $_POST['new_dbname'];
    if ($newDb) {
        $_SESSION['sql_conn']['dbname'] = $newDb;
        jsonResponse(true, ['meta' => $_SESSION['sql_conn']], "Alterado para $newDb");
    }
    jsonResponse(false, [], 'Nome da base inválido.');
}

// Action: List Tables (public schema only for now)
if ($action === 'list_tables') {
    // List tables and views
    $query = "
        SELECT table_name, table_type 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name;
    ";
    $result = pg_query($conn, $query);
    
    if (!$result) {
        jsonResponse(false, [], pg_last_error($conn));
    }
    
    $tables = [];
    while ($row = pg_fetch_assoc($result)) {
        $tables[] = [
            'name' => $row['table_name'],
            'type' => $row['table_type'] == 'VIEW' ? 'view' : 'table'
        ];
    }
    
    jsonResponse(true, $tables);
}

// Action: Execute Query
if ($action === 'execute') {
    $sql = $_POST['sql'];
    if (empty($sql)) jsonResponse(false, [], 'SQL vazio.');

    $startTime = microtime(true);
    $result = @pg_query($conn, $sql);
    $duration = round((microtime(true) - $startTime) * 1000, 2); // ms

    if (!$result) {
        jsonResponse(false, [], pg_last_error($conn));
    }

    $affected = pg_affected_rows($result);
    $rows = [];
    $columns = [];

    // Only fetch data for SELECT
    if (pg_num_fields($result) > 0) {
        $numFields = pg_num_fields($result);
        for ($i = 0; $i < $numFields; $i++) {
            $columns[] = pg_field_name($result, $i);
        }

        // Limit return size for safety (e.g. 2000 rows)
        $rowCount = 0;
        while ($row = pg_fetch_assoc($result)) {
            if ($rowCount > 2000) break; 
            $rows[] = $row;
            $rowCount++;
        }
    }

    jsonResponse(true, [
        'columns' => $columns,
        'rows' => $rows,
        'duration' => $duration,
        'affected' => $affected
    ]);
}

// Action: Describe Table (Get Schema)
if ($action === 'describe_table') {
    $table = $_POST['table'];
    if (!$table) jsonResponse(false, [], 'Tabela não informada.');
    
    // Fetch columns metadata
    $sql = "SELECT column_name, data_type, character_maximum_length, is_nullable, column_default 
            FROM information_schema.columns 
            WHERE table_name = $1 
            ORDER BY ordinal_position;";
            
    $result = pg_query_params($conn, $sql, [$table]);
    
    if ($result) {
        $rows = pg_fetch_all($result) ?: [];
        jsonResponse(true, $rows);
    } else {
        jsonResponse(false, [], pg_last_error($conn));
    }
}

jsonResponse(false, [], 'Ação desconhecida.');?>
