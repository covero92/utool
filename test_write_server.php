<?php
header('Content-Type: text/plain');

$file = __DIR__ . '/data/release_notes.json';
$debugFile = __DIR__ . '/data/debug_write_test.txt';

echo "--- Persistence Test ---\n";
echo "File: $file\n";
echo "Permissions (data dir): " . substr(sprintf('%o', fileperms(__DIR__ . '/data')), -4) . "\n";
echo "Permissions (json file): " . (file_exists($file) ? substr(sprintf('%o', fileperms($file)), -4) : 'Not found') . "\n";
echo "Owner/Group: " . posix_getpwuid(fileowner(__DIR__ . '/data'))['name'] . "\n";
echo "Script Owner: " . get_current_user() . "\n";
echo "Process User (whoami): " . exec('whoami') . "\n";

// 1. Read
$content = file_get_contents($file);
$data = json_decode($content, true);

if (!$data) {
    echo "ERROR: Failed to read/decode JSON. content length: " . strlen($content) . "\n";
    exit;
}

echo "Current count: " . count($data) . "\n";
echo "First version: " . ($data[0]['version'] ?? 'N/A') . "\n";

// 2. Modify
$testId = uniqid('test_');
$newEntry = [
    'id' => $testId,
    'version' => '0.0.0-TEST',
    'date' => date('Y-m-d'),
    'title' => 'Persistence Test ' . date('H:i:s'),
    'description' => 'Temporary test entry.',
    'tags' => ['test'],
    'notes' => []
];

// Prepend
array_unshift($data, $newEntry);

// 3. Save
echo "Attempting to save...\n";
$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$result = file_put_contents($file, $json);

if ($result === false) {
    echo "ERROR: file_put_contents returned FALSE.\n";
    echo "Last Error: " . print_r(error_get_last(), true) . "\n";
} else {
    echo "Success: Wrote $result bytes.\n";
}

// 4. Verification Check (Immediate)
clearstatcache();
$newContent = file_get_contents($file);
$newData = json_decode($newContent, true);

if ($newData[0]['id'] === $testId) {
    echo "VERIFICATION PASSED: New entry found in file.\n";
    
    // Cleanup
    echo "Cleaning up test entry...\n";
    array_shift($newData);
    file_put_contents($file, json_encode($newData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "Cleanup complete.\n";
} else {
    echo "VERIFICATION FAILED: New entry NOT found in file.\n";
    echo "First entry ID: " . ($newData[0]['id'] ?? 'N/A') . "\n";
}
?>
