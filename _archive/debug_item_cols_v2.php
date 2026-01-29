<?php
require_once 'db_connection.php';

$tableName = 'notafiscalitem';
$query = "SELECT column_name, data_type 
          FROM information_schema.columns 
          WHERE table_name = '$tableName' 
          ORDER BY ordinal_position";

$result = pg_query($dbconn, $query);

if (!$result) {
    die("Error fetching columns: " . pg_last_error($dbconn));
}

$columns = [];
while ($row = pg_fetch_assoc($result)) {
    $columns[] = $row['column_name'] . " (" . $row['data_type'] . ")";
}

file_put_contents('debug_item_cols_output.txt', implode("\n", $columns));
echo "Columns dumped to debug_item_cols_output.txt";
?>
