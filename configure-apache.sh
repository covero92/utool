#!/bin/bash
# Configure Apache for Utool
# Run this script on the server after extracting the deployment package

set -e

UTOOL_DIR="/utool"
APACHE_CONF="/etc/apache2/sites-available/utool.conf"

echo "=== Configuring Apache for Utool ==="

# Create Apache configuration
sudo tee $APACHE_CONF > /dev/null <<'EOF'
<VirtualHost *:80>
    ServerName utool.intelidata.local
    DocumentRoot /utool
    
    <Directory /utool>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        # PHP Configuration
        <FilesMatch \.php$>
            SetHandler "proxy:unix:/var/run/php/php-fpm.sock|fcgi://localhost"
        </FilesMatch>
    </Directory>
    
    # Deny access to sensitive files
    <FilesMatch "^\.">
        Require all denied
    </FilesMatch>
    
    <FilesMatch "\.env$">
        Require all denied
    </FilesMatch>
    
    ErrorLog ${APACHE_LOG_DIR}/utool-error.log
    CustomLog ${APACHE_LOG_DIR}/utool-access.log combined
</VirtualHost>
EOF

echo "✓ Apache configuration created"

# Enable site and modules
echo "Enabling site and required modules..."
sudo a2ensite utool.conf
sudo a2enmod rewrite proxy_fcgi headers

# Reload Apache
echo "Reloading Apache..."
sudo systemctl reload apache2

echo ""
echo "=== Configuration Complete! ==="
echo ""
echo "Access Utool at: http://YOUR_SERVER_IP/utool"
echo ""
echo "Next steps:"
echo "1. Edit /utool/.env with database credentials"
echo "2. Test access in browser"
