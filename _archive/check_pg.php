<?php
echo "PDO Drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";
echo "PgSQL Extension: " . (extension_loaded('pgsql') ? 'Yes' : 'No') . "\n";
?>
