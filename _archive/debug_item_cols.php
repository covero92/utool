<?php
require 'includes/db_connection.php'; // Adjust path if needed

// Try to connect manually if include fails or doesn't set $dbconn
if (!isset($dbconn)) {
    session_start();
    if (isset($_SESSION['db_host'])) {
        $host = $_SESSION['db_host'];
        $port = $_SESSION['db_port'];
        $dbname = $_SESSION['db_name'];
        $user = $_SESSION['db_user'];
        $password = $_SESSION['db_pass'];
        $conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password";
        $dbconn = pg_connect($conn_string);
    }
}

if ($dbconn) {
    $res = pg_query($dbconn, "SELECT * FROM notafiscalitem LIMIT 1");
    if ($res) {
        $row = pg_fetch_assoc($res);
        file_put_contents('debug_item_cols.txt', print_r(array_keys($row), true));
        echo "Columns dumped to debug_item_cols.txt";
    } else {
        echo "Error querying notafiscalitem: " . pg_last_error($dbconn);
    }
} else {
    echo "Database connection failed.";
}
?>
