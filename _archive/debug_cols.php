<?php
require 'db_connection.php';

echo "--- Columns for unidademedida ---\n";
$result = pg_query($dbconn, "SELECT * FROM unidademedida LIMIT 1");
if ($result) {
    $row = pg_fetch_assoc($result);
    print_r(array_keys($row));
} else {
    echo "Error: " . pg_last_error($dbconn) . "\n";
}

echo "\n--- Columns for notafiscal (entrega) ---\n";
$result = pg_query($dbconn, "SELECT * FROM notafiscal LIMIT 1");
if ($result) {
    $row = pg_fetch_assoc($result);
    $keys = array_keys($row);
    $entrega_cols = array_filter($keys, function($k) { return strpos($k, 'entrega') !== false; });
    print_r($entrega_cols);
} else {
    echo "Error: " . pg_last_error($dbconn) . "\n";
}
?>
