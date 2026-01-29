# Deploy Script for Windows
# Zips the current folder excluding unnecessary files

$timestamp = Get-Date -Format "yyyyMMdd-HHmm"
$zipName = "utool-deploy-$timestamp.zip"
$sourceDir = Get-Location
$exclude = @(
    "vendor", 
    "node_modules", 
    ".git", 
    ".vscode", 
    "_archive", 
    "*.zip", 
    "*.tar.gz",
    "deploy-utool.ps1"
)

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

Write-Host "Zipping the following items:" -ForegroundColor Gray
$items.Name

# Compress only the filtered items
Compress-Archive -Path $items.FullName -DestinationPath $zipName -CompressionLevel Optimal -Force

Write-Host "Done! File created: $zipName" -ForegroundColor Green
Write-Host "You can now upload this file to your Linux server."
