#!/bin/bash
# Quick Deployment Script for Utool Hub
# This script automates the deployment process on the Linux server

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${CYAN}=== Utool Hub Quick Deployment ===${NC}"
echo ""

# Configuration
DEPLOY_DIR="/var/www/html/utool"
WEB_USER="www-data"
DB_NAME="suporte_hub"

# Check if running as root
if [ "$EUID" -eq 0 ]; then 
   echo -e "${RED}ERROR: Do not run this script as root${NC}"
   echo "Run as your regular user. Sudo will be used when needed."
   exit 1
fi

# Check for deployment package
PACKAGE=$(ls -t utool-deployment-*.zip 2>/dev/null | head -1)
if [ -z "$PACKAGE" ]; then
    echo -e "${RED}ERROR: No deployment package found${NC}"
    echo "Please upload utool-deployment-*.zip to this directory"
    exit 1
fi

echo -e "${GREEN}Found package: $PACKAGE${NC}"

# Check for database export
DB_EXPORT=$(ls -t suporte_hub_export_*.sql 2>/dev/null | head -1)
if [ -z "$DB_EXPORT" ]; then
    echo -e "${YELLOW}WARNING: No database export found${NC}"
    echo "Database import will be skipped"
    SKIP_DB=true
else
    echo -e "${GREEN}Found database: $DB_EXPORT${NC}"
    SKIP_DB=false
fi

echo ""
read -p "Deploy to $DEPLOY_DIR? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Deployment cancelled"
    exit 0
fi

# Create deployment directory
echo -e "${CYAN}Creating deployment directory...${NC}"
sudo mkdir -p $DEPLOY_DIR
sudo chown $USER:$USER $DEPLOY_DIR

# Extract package
echo -e "${CYAN}Extracting package...${NC}"
unzip -q $PACKAGE -d $DEPLOY_DIR

# Run setup
cd $DEPLOY_DIR
if [ -f setup.sh ]; then
    echo -e "${CYAN}Running setup script...${NC}"
    chmod +x setup.sh
    ./setup.sh
else
    echo -e "${YELLOW}WARNING: setup.sh not found, skipping automated setup${NC}"
fi

# Configure environment
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        echo -e "${YELLOW}Created .env file. Please edit with your settings:${NC}"
        echo "  nano $DEPLOY_DIR/.env"
    fi
fi

# Import database
if [ "$SKIP_DB" = false ]; then
    echo ""
    read -p "Import database now? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${CYAN}Importing database...${NC}"
        
        # Check if database exists
        if sudo -u postgres psql -lqt | cut -d \| -f 1 | grep -qw $DB_NAME; then
            echo -e "${YELLOW}Database $DB_NAME already exists${NC}"
            read -p "Drop and recreate? (y/n) " -n 1 -r
            echo
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                sudo -u postgres dropdb $DB_NAME
                sudo -u postgres createdb $DB_NAME
            fi
        else
            sudo -u postgres createdb $DB_NAME
        fi
        
        # Import
        DB_PATH="$(dirname $PACKAGE)/$DB_EXPORT"
        sudo -u postgres psql -d $DB_NAME -f "$DB_PATH"
        echo -e "${GREEN}Database imported successfully${NC}"
    fi
fi

# Set permissions
echo -e "${CYAN}Setting permissions...${NC}"
sudo chown -R $WEB_USER:$WEB_USER $DEPLOY_DIR
sudo find $DEPLOY_DIR -type d -exec chmod 755 {} \;
sudo find $DEPLOY_DIR -type f -exec chmod 644 {} \;
sudo chmod -R 775 $DEPLOY_DIR/logs_uploaded $DEPLOY_DIR/uploads 2>/dev/null || true

echo ""
echo -e "${GREEN}=== Deployment Complete! ===${NC}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Edit configuration: sudo nano $DEPLOY_DIR/.env"
echo "2. Configure web server (Apache/Nginx)"
echo "3. Test access: http://your-server-ip/utool"
echo ""
echo -e "${CYAN}Web server configuration files:${NC}"
echo "  Apache: $DEPLOY_DIR/docs/apache-vhost.conf"
echo "  Nginx:  $DEPLOY_DIR/docs/nginx-server-block.conf"
echo ""
