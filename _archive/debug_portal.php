<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Portal Config</h1>";

$path = __DIR__ . '/data/portal_config.json';
echo "Path: " . $path . "<br>";

if (!file_exists($path)) {
    echo "<span style='color:red'>File does not exist!</span><br>";
    // Try creating it
    echo "Attempting to create...<br>";
    $initialData = [
        "weather" => ["city" => "Brusque", "lat" => -27.1177, "lon" => -48.9103],
        "latest_version" => ["version" => "1.0.0", "date" => date('d/m/Y')],
        "notices" => [],
        "meetings" => [],
        "blocked_cards" => []
    ];
    $res = file_put_contents($path, json_encode($initialData, JSON_PRETTY_PRINT));
    if ($res === false) {
        echo "<span style='color:red'>Failed to create file. Permission denied?</span><br>";
        exit;
    } else {
        echo "<span style='color:green'>File created successfully.</span><br>";
    }
} else {
    echo "<span style='color:green'>File exists.</span><br>";
}

if (!is_writable($path)) {
    echo "<span style='color:red'>File is NOT writable!</span><br>";
} else {
    echo "<span style='color:green'>File is writable.</span><br>";
}

$content = file_get_contents($path);
echo "Content length: " . strlen($content) . "<br>";

$json = json_decode($content, true);
if ($json === null) {
    echo "<span style='color:red'>JSON Decode Failed: " . json_last_error_msg() . "</span><br>";
    echo "<pre>" . htmlspecialchars($content) . "</pre>";
} else {
    echo "<span style='color:green'>JSON is valid.</span><br>";
    echo "Keys: " . implode(', ', array_keys($json)) . "<br>";
    
    // Test Write
    $json['debug_timestamp'] = date('Y-m-d H:i:s');
    $res = file_put_contents($path, json_encode($json, JSON_PRETTY_PRINT));
    if ($res === false) {
         echo "<span style='color:red'>Write Test Failed!</span><br>";
    } else {
         echo "<span style='color:green'>Write Test Success! Updated debug_timestamp.</span><br>";
    }
}
?>
