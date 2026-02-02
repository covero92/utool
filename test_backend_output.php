<?php
// Simulate the backend environment and request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['action'] = 'fetch_codes';
$_POST['type'] = 'nbs';

// Capture output
ob_start();
include 'c:\xampp\htdocs\utool\nfse-nacional.php';
$output = ob_get_clean();

echo "--- LOG CONTENT ---\n";
echo file_get_contents(__DIR__ . '/debug_fetch.log');
echo "\n--- RAW OUTPUT LENGTH ---\n";
echo strlen($output);
