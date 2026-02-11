#!/bin/bash

# UIGU Fun Run - Quick Deployment Script
# This script deploys the latest code and fixes 503/500 errors

set -e

echo "================================================"
echo "UIGU Fun Run - Quick Deployment"
echo "================================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running as correct user
if [ "$EUID" -eq 0 ]; then
   echo -e "${RED}ERROR: Do not run this script as root${NC}"
   echo "Run as the web application user (e.g., www-data or your deployment user)"
   exit 1
fi

# Step 1: Pull latest code
echo -e "${YELLOW}[1/7] Pulling latest code...${NC}"
git pull origin main
echo -e "${GREEN}✓ Code updated${NC}"
echo ""

# Step 2: Install/Update dependencies
echo -e "${YELLOW}[2/7] Updating dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction
echo -e "${GREEN}✓ Dependencies updated${NC}"
echo ""

# Step 3: Clear all caches
echo -e "${YELLOW}[3/7] Clearing all caches...${NC}"
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan event:clear
php artisan clear-compiled
echo -e "${GREEN}✓ All caches cleared${NC}"
echo ""

# Step 4: Run migrations
echo -e "${YELLOW}[4/7] Running migrations...${NC}"
php artisan migrate --force --no-interaction
echo -e "${GREEN}✓ Migrations completed${NC}"
echo ""

# Step 5: Re-optimize for production
echo -e "${YELLOW}[5/7] Optimizing for production...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
echo -e "${GREEN}✓ Optimization completed${NC}"
echo ""

# Step 6: Restart PHP-FPM
echo -e "${YELLOW}[6/7] Restarting PHP-FPM...${NC}"
echo "You may need to enter password for sudo:"
sudo systemctl restart php8.4-fpm
echo -e "${GREEN}✓ PHP-FPM restarted${NC}"
echo ""

# Step 7: Restart Nginx
echo -e "${YELLOW}[7/7] Restarting Nginx...${NC}"
sudo systemctl restart nginx
echo -e "${GREEN}✓ Nginx restarted${NC}"
echo ""

# Verify services
echo -e "${YELLOW}Verifying services...${NC}"
if sudo systemctl is-active --quiet php8.4-fpm; then
    echo -e "${GREEN}✓ PHP-FPM is running${NC}"
else
    echo -e "${RED}✗ PHP-FPM is not running${NC}"
fi

if sudo systemctl is-active --quiet nginx; then
    echo -e "${GREEN}✓ Nginx is running${NC}"
else
    echo -e "${RED}✗ Nginx is not running${NC}"
fi
echo ""

# Test homepage
echo -e "${YELLOW}Testing homepage...${NC}"
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/)
if [ "$HTTP_STATUS" = "200" ]; then
    echo -e "${GREEN}✓ Homepage returns HTTP 200 OK${NC}"
else
    echo -e "${RED}✗ Homepage returns HTTP ${HTTP_STATUS}${NC}"
    echo "Check logs: tail -f storage/logs/laravel.log"
fi
echo ""

echo "================================================"
echo -e "${GREEN}Deployment completed!${NC}"
echo "================================================"
echo ""
echo "Next steps:"
echo "1. Test in browser: http://uigu-funrun.com/"
echo "2. Monitor logs: tail -f storage/logs/laravel.log"
echo ""
