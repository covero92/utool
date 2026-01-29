#!/bin/bash

# setup-utool.sh
# Run this on your Ubuntu Server to prepare for 'utool'

echo -e "\033[36m[Utool Setup] Starting installation...\033[0m"

# 1. Update Packages
echo "[Utool Setup] Updating system packages..."
sudo apt-get update

# 2. Install PHP and Extensions
# Essential for this project: pgsql, xml, mbstring, zip
echo "[Utool Setup] Installing PHP and extensions..."
sudo apt-get install -y php php-cli php-fpm php-pgsql php-xml php-mbstring php-zip php-curl unzip

# 3. Install Composer (if not found)
if ! command -v composer &> /dev/null; then
    echo "[Utool Setup] Composer not found. Installing..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
else
    echo "[Utool Setup] Composer is already installed."
fi

# 4. Install Project Dependencies
echo "[Utool Setup] Installing PHP dependencies via Composer..."
# --no-dev for production, -o for optimized autoloader
composer install --no-dev -o

# 5. Directory Permissions
# Assuming running user owns files, but web server (www-data) needs write access to uploads/logs
echo "[Utool Setup] Configuring permissions..."
sudo chown -R www-data:www-data logs_uploaded uploads font
sudo chmod -R 775 logs_uploaded uploads font

# 6. Database Check (Optional info)
echo "[Utool Setup] Please ensure PostgreSQL is running and the database 'suporte_hub' exists."
echo "You can check DB connection by running: php setup_auth_db.php"

echo -e "\033[32m[Utool Setup] Installation Complete!\033[0m"
echo "If using Apache/Nginx, ensure the document root points to this directory."
