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
   `ash
   unzip utool-deployment-*.zip -d /var/www/html/utool
   cd /var/www/html/utool
   `

2. **Run the setup script**:
   `ash
   chmod +x setup.sh
   ./setup.sh
   `

3. **Configure environment**:
   `ash
   nano .env
   # Edit database credentials and other settings
   `

4. **Import database**:
   `ash
   psql -U postgres -f suporte_hub_export_*.sql
   `

5. **Configure web server** (see docs/deployment-guide.md)

6. **Test access**:
   Open browser: http://your-server-ip/utool

## Database Export

The database export file (suporte_hub_export_*.sql) should be created separately
using the export-database.ps1 script on your Windows machine.

## Support

For issues or questions, refer to docs/deployment-guide.md

---
Package created: 20260202-173945
