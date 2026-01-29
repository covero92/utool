# Export Database Script for Windows
# Exports the suporte_hub database to SQL file for migration

$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$outputFile = "suporte_hub_export_$timestamp.sql"

Write-Host "=== Utool Database Export ===" -ForegroundColor Cyan
Write-Host ""

# Database connection details
$dbHost = "localhost"
$dbPort = "5432"
$dbName = "suporte_hub"
$dbUser = "postgres"

Write-Host "Database: $dbName" -ForegroundColor Gray
Write-Host "Host: $dbHost:$dbPort" -ForegroundColor Gray
Write-Host "Output: $outputFile" -ForegroundColor Gray
Write-Host ""

# Check if pg_dump is available
$pgDumpPath = Get-Command pg_dump -ErrorAction SilentlyContinue

if (-not $pgDumpPath) {
    Write-Host "ERROR: pg_dump not found in PATH" -ForegroundColor Red
    Write-Host "Please ensure PostgreSQL client tools are installed and in PATH" -ForegroundColor Yellow
    Write-Host "Common location: C:\Program Files\PostgreSQL\<version>\bin" -ForegroundColor Yellow
    exit 1
}

Write-Host "Starting export..." -ForegroundColor Green

# Set PGPASSWORD environment variable (will prompt if not set)
if (-not $env:PGPASSWORD) {
    $securePassword = Read-Host "Enter PostgreSQL password for user '$dbUser'" -AsSecureString
    $BSTR = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePassword)
    $env:PGPASSWORD = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($BSTR)
}

try {
    # Export database with schema and data
    & pg_dump -h $dbHost -p $dbPort -U $dbUser -d $dbName `
        --clean `
        --if-exists `
        --create `
        --encoding=UTF8 `
        --no-owner `
        --no-privileges `
        -f $outputFile

    if ($LASTEXITCODE -eq 0) {
        $fileSize = (Get-Item $outputFile).Length / 1KB
        Write-Host ""
        Write-Host "SUCCESS: Database exported successfully!" -ForegroundColor Green
        Write-Host "File: $outputFile" -ForegroundColor Cyan
        Write-Host "Size: $([math]::Round($fileSize, 2)) KB" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "Next steps:" -ForegroundColor Yellow
        Write-Host "1. Copy this file to your Linux server" -ForegroundColor Gray
        Write-Host "2. Run: psql -U postgres -f $outputFile" -ForegroundColor Gray
    } else {
        Write-Host "ERROR: Export failed with exit code $LASTEXITCODE" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "ERROR: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
} finally {
    # Clear password from environment
    Remove-Item Env:\PGPASSWORD -ErrorAction SilentlyContinue
}
