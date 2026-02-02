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
if [ "$EUID" -eq 0 ]; then 
   echo -e "${RED}ERROR: Do not run this script as root${NC}"
   echo "Run as your regular user. Sudo will be used when needed."
   exit 1
fi

echo -e "${CYAN}Step 1: Updating system packages...${NC}"
sudo apt-get update

echo -e "${CYAN}Step 2: Installing PHP and extensions...${NC}"
sudo apt-get install -y php php-cli php-fpm php-pgsql php-xml php-mbstring php-zip php-curl unzip

echo -e "${CYAN}Step 3: Checking Composer...${NC}"
if ! command -v composer &> /dev/null; then
    echo "Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
else
    echo "Composer already installed."
fi

echo -e "${CYAN}Step 4: Installing PHP dependencies...${NC}"
composer install --no-dev --optimize-autoloader

echo -e "${CYAN}Step 5: Setting up directories...${NC}"
mkdir -p logs_uploaded uploads data
sudo chown -R www-data:www-data logs_uploaded uploads data
sudo chmod -R 777 logs_uploaded uploads data
mkdir -p uploads/blog
sudo chmod -R 777 uploads/blog
sudo touch data/release_notes.json
sudo chmod 666 data/release_notes.json
sudo touch data/fiscal_blog.json
sudo chmod 666 data/fiscal_blog.json

echo -e "${CYAN}Step 6: Environment configuration...${NC}"
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        echo -e "${YELLOW}Created .env file from .env.example${NC}"
        echo -e "${YELLOW}Please edit .env with your database credentials${NC}"
    else
        echo -e "${RED}WARNING: No .env.example found${NC}"
    fi
else
    echo ".env file already exists."
fi

echo ""
echo -e "${GREEN}=== Setup Complete! ===${NC}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Edit .env file with your database credentials"
echo "2. Import database: psql -U postgres -f suporte_hub_export_*.sql"
echo "3. Configure web server (Apache/Nginx) to point to this directory"
echo "4. Test access: http://your-server-ip/utool"
echo ""