<?php
$host = $_SESSION['db_host'] ?? 'localhost';
$port = $_SESSION['db_port'] ?? '5432';
$dbname = $_SESSION['db_name'] ?? 'unico';
$user = $_SESSION['db_user'] ?? 'postgres';
$password = $_SESSION['db_pass'] ?? 'postgres';

$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password";
$dbconn = pg_connect($conn_string);

if (!$dbconn) {
    die("Connection failed");
}

$tables = ['notafiscal', 'notafiscalitem'];

foreach ($tables as $table) {
    file_put_contents('db_columns.txt', "TABLE: $table\n", FILE_APPEND);
    $sql = "SELECT column_name, data_type FROM information_schema.columns WHERE table_name = '$table' ORDER BY column_name";
    $result = pg_query($dbconn, $sql);
    
    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            file_put_contents('db_columns.txt', "  - " . $row['column_name'] . " (" . $row['data_type'] . ")\n", FILE_APPEND);
        }
    }
    file_put_contents('db_columns.txt', "\n", FILE_APPEND);
}
?>
