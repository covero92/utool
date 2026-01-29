# Fix Permissions Script
$serverUser = "suporteitl"
$serverIp = "10.1.11.69"

Write-Host "=== Fixing Permissions on Server ===" -ForegroundColor Cyan
Write-Host "Target: /home/suporteitl/utool/data"

$commands = @(
    "echo 'Applying permissions...'",
    "sudo chown -R www-data:www-data /home/suporteitl/utool/data /home/suporteitl/utool/uploads /home/suporteitl/utool/logs_uploaded",
    "sudo chmod -R 775 /home/suporteitl/utool/data /home/suporteitl/utool/uploads /home/suporteitl/utool/logs_uploaded",
    "sudo chmod 664 /home/suporteitl/utool/data/release_notes.json",
    "ls -la /home/suporteitl/utool/data/release_notes.json"
)

$remoteCommand = $commands -join " && "

& ssh "${serverUser}@${serverIp}" -t "$remoteCommand"

Write-Host "Done! Please check the Debug Info on the browser." -ForegroundColor Green
