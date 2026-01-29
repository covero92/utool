# Deploy Update Script
# Usage: .\deploy-update.ps1
# This script creates a new package and instructs how to push it to the server

$serverUser = "suporteitl"
$serverIp = "10.1.11.69"
$remotePath = "/tmp"

Write-Host "=== Utool Auto-Deployment ===" -ForegroundColor Cyan
Write-Host "1. Creating new deployment package..."
$startTime = Get-Date

Write-Host "=== Utool Auto-Deployment ===" -ForegroundColor Cyan
Write-Host "1. Creating new deployment package..."
.\create-deployment-package.ps1

$latestZip = Get-ChildItem -Filter "utool-deployment-*.zip" | Where-Object { $_.LastWriteTime -ge $startTime } | Sort-Object CreationTime -Descending | Select-Object -First 1

if (-not $latestZip) {
    Write-Host "Error: No NEW zip file found. Package creation likely failed." -ForegroundColor Red
    exit
}

if ($latestZip.Length -lt 1MB) {
     Write-Host "Error: Zip file is too small ($($latestZip.Length) bytes). Package creation failed." -ForegroundColor Red
     exit
}

Write-Host "2. Package created: $($latestZip.Name)" -ForegroundColor Green
Write-Host "3. Uploading to server (You may be asked for password: suporte@123)..." -ForegroundColor Yellow

# Using scp
& scp $latestZip.FullName "${serverUser}@${serverIp}:${remotePath}/"

if ($LASTEXITCODE -eq 0) {
    Write-Host "4. Upload success! Now installing..." -ForegroundColor Green
    
    $commands = @(
        "sudo mkdir -p /home/suporteitl/utool",
        "sudo unzip -o ${remotePath}/$($latestZip.Name) -d /home/suporteitl/utool",
        "cd /home/suporteitl/utool",
        "sudo chmod +x setup.sh",
        "sudo ./setup.sh",
        "sudo chown -R www-data:www-data /home/suporteitl/utool/data",
        "sudo chmod -R 777 /home/suporteitl/utool/data",
        "sudo touch /home/suporteitl/utool/data/fiscal_blog.json",
        "sudo chown www-data:www-data /home/suporteitl/utool/data/fiscal_blog.json",
        "sudo chmod 666 /home/suporteitl/utool/data/fiscal_blog.json",
        "ls -la /home/suporteitl/utool/data/",
        "rm ${remotePath}/$($latestZip.Name)"
    )
    
    $remoteCommand = $commands -join " && "
    
    Write-Host "Running remote setup commands..."
    & ssh "${serverUser}@${serverIp}" -t "$remoteCommand"
    
    Write-Host "=== Deployment Complete! ===" -ForegroundColor Cyan
} else {
    Write-Host "Upload failed. Please check connectivity or credentials." -ForegroundColor Red
}
