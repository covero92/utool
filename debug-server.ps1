# Debug Server Script
# Usage: .\debug-server.ps1

$serverUser = "suporteitl"
$serverIp = "10.1.15.204"

Write-Host "=== Utool Server Debug ===" -ForegroundColor Cyan
Write-Host "Connecting to $serverIp... (Enter password 'suporte@123' if asked)" -ForegroundColor Yellow

$commands = @(
    "echo '--- Checking /home/suporteitl/utool ---'",
    "ls -la /home/suporteitl/utool | head -n 5",
    "echo ''",
    "echo '--- Checking /var/www/html/utool ---'",
    "ls -la /var/www/html/utool | head -n 5",
    "echo ''",
    "echo '--- Apache Config Search ---'",
    "grep -r 'DocumentRoot' /etc/apache2/sites-enabled/ 2>/dev/null || echo 'Cannot read apache config'",
    "grep -r 'root' /etc/nginx/sites-enabled/ 2>/dev/null || echo 'Cannot read nginx config'"
)

$remoteCommand = $commands -join " && "

& ssh "${serverUser}@${serverIp}" -t "$remoteCommand"

Write-Host "=== Debug Complete ===" -ForegroundColor Cyan

$remoteCommand = $commands -join " && "

& ssh "${serverUser}@${serverIp}" -t "$remoteCommand"

Write-Host "=== Debug Complete ===" -ForegroundColor Cyan
