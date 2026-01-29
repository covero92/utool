<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/portal_helpers.php';

echo "<h1>Backend Save Test</h1>";

$portal = new SupportPortal();

// 1. Test Read
$notices = $portal->getNotices();
echo "Current Notices Count: " . count($notices) . "<br>";

// 2. Test Write
$newNotice = [
    'id' => 'test_' . uniqid(),
    'title' => 'Backend Test ' . date('H:i:s'),
    'description' => 'This is a test notice generated directly by PHP.',
    'type' => 'success',
    'date' => date('Y-m-d H:i:s')
];

array_unshift($notices, $newNotice);

echo "Attempting to save new count: " . count($notices) . "<br>";

try {
    $portal->updateConfig('notices', $notices);
    echo "<span style='color:green'>Save executed without exception.</span><br>";
} catch (Exception $e) {
    echo "<span style='color:red'>Exception: " . $e->getMessage() . "</span><br>";
}

// 3. Verify
$portal2 = new SupportPortal();
$notices2 = $portal2->getNotices();
echo "Reloaded Notices Count: " . count($notices2) . "<br>";

if (count($notices2) > count($notices) - 1) {
    echo "<h2 style='color:green'>SUCCESS: Backend write works!</h2>";
} else {
    echo "<h2 style='color:red'>FAILURE: Data was not persisted.</h2>";
}
?>
