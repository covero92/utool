# Create Deployment Package for Utool Hub
# This script creates a clean deployment package excluding development files

$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$packageName = "utool-deployment-$timestamp.zip"
$tempDir = "utool-temp-$timestamp"

Write-Host "=== Utool Deployment Package Creator ===" -ForegroundColor Cyan
Write-Host ""

# Files and directories to exclude
$exclude = @(
    "vendor",
    "node_modules",
    ".git",
    ".vscode",
    ".idea",
    "_archive",
    "font",
    "*.zip",
    "*.tar.gz",
    "*.sql",
    "*.log",
    "create-deployment-package.ps1",
    "export-database.ps1",
    "utool-temp-*",
    "data/*.json",
    "data/*.db",
    "includes/debug_log.txt",
    "uploads",
    "release-notes.xlsx",
    "release-notes.pdf",
    "*.log"
)

Write-Host "Creating temporary directory..." -ForegroundColor Gray
New-Item -ItemType Directory -Path $tempDir -Force | Out-Null

Write-Host "Copying files (excluding development files)..." -ForegroundColor Gray

# Get all items excluding the unwanted ones
$items = Get-ChildItem -Path . | Where-Object { 
    $name = $_.Name
    $isExcluded = $false
    foreach ($pattern in $exclude) {
        if ($name -like $pattern) {
            $isExcluded = $true
            break
        }
    }
    -not $isExcluded
}

# Copy items to temp directory
foreach ($item in $items) {
    Copy-Item -Path $item.FullName -Destination $tempDir -Recurse -Force
    Write-Host "  + $($item.Name)" -ForegroundColor DarkGray
}

# Cleanup: Remove excluded files that might have been copied via recursion
Write-Host "Cleaning up excluded files from package..." -ForegroundColor Gray
$cleanupPatterns = @(
    "data/*.json", 
    "data/*.db", 
    "includes/debug_log.txt",
    "uploads",
    "release-notes.xlsx",
    "release-notes.pdf",
    "*.log", 
    "*.zip", 
    "*.tar.gz"
)

foreach ($pattern in $cleanupPatterns) {
    if (Test-Path "$tempDir/$pattern") {
        Remove-Item "$tempDir/$pattern" -Force -Recurse -ErrorAction SilentlyContinue
        Write-Host "  - Removed $pattern" -ForegroundColor Yellow
    }
}

# Create setup script for Linux server
$setupScript = @"
#!/bin/bash
# Utool Hub Setup Script for Linux Server
# Run this script after extracting the deployment package

set -e

echo "=== Utool Hub Setup ==="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Check if running as root
if [ "`$EUID" -eq 0 ]; then 
   echo -e "`${RED}ERROR: Do not run this script as root`${NC}"
   echo "Run as your regular user. Sudo will be used when needed."
   exit 1
fi

echo -e "`${CYAN}Step 1: Updating system packages...`${NC}"
sudo apt-get update

echo -e "`${CYAN}Step 2: Installing PHP and extensions...`${NC}"
sudo apt-get install -y php php-cli php-fpm php-pgsql php-xml php-mbstring php-zip php-curl unzip

echo -e "`${CYAN}Step 3: Checking Composer...`${NC}"
if ! command -v composer &> /dev/null; then
    echo "Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
else
    echo "Composer already installed."
fi

echo -e "`${CYAN}Step 4: Installing PHP dependencies...`${NC}"
composer install --no-dev --optimize-autoloader

echo -e "`${CYAN}Step 5: Setting up directories...`${NC}"
mkdir -p logs_uploaded uploads data
sudo chown -R www-data:www-data logs_uploaded uploads data
sudo chmod -R 777 logs_uploaded uploads data
mkdir -p uploads/blog
sudo chmod -R 777 uploads/blog
sudo touch data/release_notes.json
sudo chmod 666 data/release_notes.json
sudo touch data/fiscal_blog.json
sudo chmod 666 data/fiscal_blog.json

echo -e "`${CYAN}Step 6: Environment configuration...`${NC}"
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        echo -e "`${YELLOW}Created .env file from .env.example`${NC}"
        echo -e "`${YELLOW}Please edit .env with your database credentials`${NC}"
    else
        echo -e "`${RED}WARNING: No .env.example found`${NC}"
    fi
else
    echo ".env file already exists."
fi

echo ""
echo -e "`${GREEN}=== Setup Complete! ===`${NC}"
echo ""
echo -e "`${YELLOW}Next steps:`${NC}"
echo "1. Edit .env file with your database credentials"
echo "2. Import database: psql -U postgres -f suporte_hub_export_*.sql"
echo "3. Configure web server (Apache/Nginx) to point to this directory"
echo "4. Test access: http://your-server-ip/utool"
echo ""
"@



# Ensure Unix line endings (LF) for Linux script
$setupScript = $setupScript -replace "`r`n", "`n"
$setupPath = Join-Path (Get-Location).Path "$tempDir\setup.sh"
[System.IO.File]::WriteAllText($setupPath, $setupScript)

# Create README for deployment
$readme = @"
# Utool Hub Deployment Package

This package contains all files needed to deploy the Utool Hub to a Linux server.

## Contents

- Application files (PHP, HTML, CSS, JS)
- Configuration templates (.env.example)
- Setup script (setup.sh)
- Documentation

## Prerequisites

- Linux server (Ubuntu 20.04+ recommended)
- PostgreSQL 12+
- Apache or Nginx web server
- PHP 8.0+

## Deployment Steps

1. **Extract this package** to your web server directory:
   ```bash
   unzip utool-deployment-*.zip -d /var/www/html/utool
   cd /var/www/html/utool
   ```

2. **Run the setup script**:
   ```bash
   chmod +x setup.sh
   ./setup.sh
   ```

3. **Configure environment**:
   ```bash
   nano .env
   # Edit database credentials and other settings
   ```

4. **Import database**:
   ```bash
   psql -U postgres -f suporte_hub_export_*.sql
   ```

5. **Configure web server** (see docs/deployment-guide.md)

6. **Test access**:
   Open browser: http://your-server-ip/utool

## Database Export

The database export file (suporte_hub_export_*.sql) should be created separately
using the export-database.ps1 script on your Windows machine.

## Support

For issues or questions, refer to docs/deployment-guide.md

---
Package created: $timestamp
"@

$readme | Out-File -FilePath "$tempDir\README.md" -Encoding UTF8

Write-Host ""
Write-Host "Creating deployment package..." -ForegroundColor Green

# Wait a bit for file handles to settle
Start-Sleep -Seconds 2

# Create the zip file
Compress-Archive -Path "$tempDir\*" -DestinationPath $packageName -CompressionLevel Optimal -Force

# Clean up temp directory
Remove-Item -Path $tempDir -Recurse -Force

$packageSize = (Get-Item $packageName).Length / 1MB

Write-Host ""
Write-Host "SUCCESS: Deployment package created!" -ForegroundColor Green
Write-Host "File: $packageName" -ForegroundColor Cyan
Write-Host "Size: $([math]::Round($packageSize, 2)) MB" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Export database using: .\export-database.ps1" -ForegroundColor Gray
Write-Host "2. Copy both files to your Linux server" -ForegroundColor Gray
Write-Host "3. Extract and run setup.sh on the server" -ForegroundColor Gray
Write-Host ""
