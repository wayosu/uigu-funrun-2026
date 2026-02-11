#!/bin/bash

# UIGU Fun Run - Deployment Script untuk Shared Hosting (Rumahweb)
# Script ini untuk deployment di shared hosting tanpa akses root/sudo

set -e

echo "================================================"
echo "UIGU Fun Run - Shared Hosting Deployment"
echo "================================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# # Step 1: Pull latest code (jika menggunakan git)
# echo -e "${YELLOW}[1/6] Pulling latest code...${NC}"
# if [ -d ".git" ]; then
#     git pull origin main
#     echo -e "${GREEN}✓ Code updated${NC}"
# else
#     echo -e "${YELLOW}⚠ Git not found, skipping...${NC}"
# fi
# echo ""

# # Step 2: Install/Update dependencies
# echo -e "${YELLOW}[2/6] Updating dependencies...${NC}"
# if command -v composer &> /dev/null; then
#     composer install --no-dev --optimize-autoloader --no-interaction
#     echo -e "${GREEN}✓ Dependencies updated${NC}"
# else
#     echo -e "${RED}✗ Composer not found. Install composer first.${NC}"
# fi
# echo ""

# Step 3: Clear all caches
echo -e "${YELLOW}[3/6] Clearing all caches...${NC}"
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan event:clear
php artisan clear-compiled

# Clear OPcache (for shared hosting)
if [ -f "public/.htaccess" ]; then
    # Touch .htaccess to reload PHP
    touch public/.htaccess
fi

echo -e "${GREEN}✓ All caches cleared${NC}"
echo ""

# Step 4: Run migrations
echo -e "${YELLOW}[4/6] Running migrations...${NC}"
php artisan migrate --force --no-interaction
echo -e "${GREEN}✓ Migrations completed${NC}"
echo ""

# Step 5: Re-optimize for production
echo -e "${YELLOW}[5/6] Optimizing for production...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
echo -e "${GREEN}✓ Optimization completed${NC}"
echo ""

# Step 6: Fix permissions
echo -e "${YELLOW}[6/6] Fixing permissions...${NC}"
chmod -R 755 storage bootstrap/cache
echo -e "${GREEN}✓ Permissions fixed${NC}"
echo ""

# Test homepage
echo -e "${YELLOW}Testing homepage...${NC}"
DOMAIN=$(php artisan tinker --execute="echo config('app.url');" 2>/dev/null || echo "http://localhost")
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "${DOMAIN}/" || echo "000")

if [ "$HTTP_STATUS" = "200" ]; then
    echo -e "${GREEN}✓ Homepage returns HTTP 200 OK${NC}"
elif [ "$HTTP_STATUS" = "000" ]; then
    echo -e "${YELLOW}⚠ Cannot test (curl not available or domain not accessible)${NC}"
else
    echo -e "${RED}✗ Homepage returns HTTP ${HTTP_STATUS}${NC}"
    echo "Check logs: tail -50 storage/logs/laravel.log"
fi
echo ""

echo "================================================"
echo -e "${GREEN}Deployment completed!${NC}"
echo "================================================"
echo ""
echo "Next steps:"
echo "1. Test in browser: ${DOMAIN}"
echo "2. If still error, check: storage/logs/laravel.log"
echo "3. Clear browser cache and test again"
echo ""
echo "Common issues on shared hosting:"
echo "- If 503: Contact hosting to restart PHP-FPM"
echo "- If 500: Check file permissions (755 for folders, 644 for files)"
echo "- If Vite error: Run 'npm run build' locally, then re-upload public/build/"
echo ""
