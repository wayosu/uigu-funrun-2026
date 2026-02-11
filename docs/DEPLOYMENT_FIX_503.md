# Fix 503/500 Error di Production - Step by Step

## Problem

Homepage masih error 503 (Chrome) atau 500 (Firefox) di production meskipun sudah ada fix yang menghapus query berat.

## Root Cause

Code terbaru belum ter-deploy dengan benar ke production atau cache belum di-clear.

## Deployment Steps (PENTING - IKUTI URUTAN INI)

### Step 1: Backup Database (Opsional tapi Recommended)

```bash
cd /path/to/production

# Backup database
php artisan db:backup
# atau manual mysqldump
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Step 2: Pull Latest Code

```bash
# Pastikan di branch yang benar
git status

# Pull latest changes
git pull origin main

# Verify perubahan sudah masuk
git log --oneline -5
```

### Step 3: Composer Dependencies (Jika Ada Update)

```bash
# Update composer dependencies
composer install --no-dev --optimize-autoloader

# Atau jika sudah di-run sebelumnya, skip step ini
```

### Step 4: Clear ALL Cache (CRITICAL!)

```bash
# Clear semua cache dalam 1 command
php artisan optimize:clear

# Atau clear satu per satu untuk memastikan:
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan event:clear

# Clear compiled files
php artisan clear-compiled
```

### Step 5: Run Migration (Jika Ada)

```bash
php artisan migrate --force
```

### Step 6: Re-optimize untuk Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Step 7: Restart Services

```bash
# Restart PHP-FPM
sudo systemctl restart php8.4-fpm

# Restart Nginx
sudo systemctl restart nginx

# Jika menggunakan Supervisor untuk Queue
sudo supervisorctl restart all

# Atau jika menggunakan Horizon
php artisan horizon:terminate
```

### Step 8: Verify Deployment

```bash
# Check PHP-FPM status
sudo systemctl status php8.4-fpm

# Check Nginx status
sudo systemctl status nginx

# Test artisan commands
php artisan --version
php artisan route:list | grep "welcome"
```

## Verification & Testing

### 1. Test dari Server (Internal)

```bash
# Test homepage dari server
curl -I http://localhost/
# atau
curl -I http://127.0.0.1/

# Harusnya return:
# HTTP/1.1 200 OK
```

### 2. Check Logs

```bash
# Monitor error log (buka terminal terpisah)
tail -f storage/logs/laravel.log

# Check PHP-FPM error log
sudo tail -f /var/log/php8.4-fpm.log

# Check Nginx error log
sudo tail -f /var/log/nginx/error.log
```

### 3. Test dari Browser

1. Buka browser dalam **Incognito/Private Mode**
2. Akses: `http://uigu-funrun.com/`
3. Buka DevTools (F12) -> Network tab
4. Hard refresh: `Ctrl + Shift + R`
5. Check response:
   - Status code harus `200 OK`
   - Tidak ada error di Console

## Troubleshooting

### Jika Masih 503/500 Setelah Deployment

#### 1. Check File Permissions

```bash
# Pastikan Laravel bisa menulis ke storage dan cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### 2. Check PHP-FPM Pool Configuration

```bash
# Edit PHP-FPM pool config
sudo nano /etc/php/8.4/fpm/pool.d/www.conf

# Pastikan:
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500

# Restart after change
sudo systemctl restart php8.4-fpm
```

#### 3. Check Memory Limit

```bash
# Check PHP memory limit
php -i | grep memory_limit

# Seharusnya minimal 256M untuk production
# Edit jika perlu:
sudo nano /etc/php/8.4/fpm/php.ini

# Set:
memory_limit = 256M

# Restart PHP-FPM
sudo systemctl restart php8.4-fpm
```

#### 4. Check Disk Space

```bash
# Check disk usage
df -h

# Check storage folder size
du -sh storage/*

# Clear old logs if needed
cd storage/logs
ls -lh
# Backup then remove old logs
mv laravel.log laravel.log.bak
touch laravel.log
chmod 664 laravel.log
chown www-data:www-data laravel.log
```

#### 5. Verify Code Changes Applied

```bash
# Check WelcomeController content
cat app/Http/Controllers/WelcomeController.php | grep -A 5 "public function index"

# Seharusnya TIDAK ada:
# - ValidateAvailableSlotsAction
# - getAvailableSlots
```

#### 6. Test Specific Route

```bash
# Test route directly
php artisan route:list | grep "/"

# Test tinker
php artisan tinker

# Di tinker, jalankan:
>>> $controller = app(\App\Http\Controllers\WelcomeController::class);
>>> $controller->index();
>>> exit
```

#### 7. Check Nginx Configuration

```bash
# Test nginx config
sudo nginx -t

# Check site config
sudo nano /etc/nginx/sites-available/uigu-funrun.com

# Pastikan:
location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
    fastcgi_read_timeout 300;  # Increase if needed
}
```

#### 8. Enable Debug Mode Temporarily (HATI-HATI!)

```bash
# ONLY for debugging, disable immediately after
nano .env

# Change:
APP_DEBUG=true

# Clear config cache
php artisan config:clear

# Test di browser, lihat error message detail
# IMMEDIATELY set back to false:
APP_DEBUG=false
php artisan config:cache
```

### Jika Error "Class not found" atau "Method not found"

```bash
# Regenerate autoload files
composer dump-autoload

# Clear various caches
php artisan optimize:clear

# Restart PHP-FPM
sudo systemctl restart php8.4-fpm
```

### Jika Error Database Connection

```bash
# Check .env database config
cat .env | grep DB_

# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

## Quick Reset (Last Resort)

Jika semua gagal, reset total:

```bash
cd /path/to/production

# 1. Clear everything
php artisan optimize:clear
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/cache/*
rm -rf storage/framework/views/*

# 2. Reinstall
composer install --no-dev --optimize-autoloader
composer dump-autoload

# 3. Re-cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 4. Restart everything
sudo systemctl restart php8.4-fpm
sudo systemctl restart nginx
```

## Success Indicators

Deployment berhasil jika:

✅ HTTP status code = `200 OK` (bukan 503/500)
✅ Homepage load < 2 detik
✅ Tidak ada error di browser console
✅ Tidak ada error di `storage/logs/laravel.log`
✅ PHP-FPM dan Nginx status = `active (running)`

## Prevention

Untuk deployment berikutnya, buat script automation:

```bash
#!/bin/bash
# File: deploy.sh

echo "Starting deployment..."

# Pull code
git pull origin main

# Clear cache
php artisan optimize:clear

# Migrate
php artisan migrate --force

# Re-optimize
php artisan optimize

# Restart services
sudo systemctl restart php8.4-fpm
sudo systemctl restart nginx

echo "Deployment completed!"
```

Jalankan dengan: `bash deploy.sh`

## Contact Support Checklist

Jika masih error setelah semua langkah di atas, prepare informasi berikut:

1. Output dari: `php artisan --version`
2. Output dari: `php -v`
3. Output dari: `tail -50 storage/logs/laravel.log`
4. Output dari: `sudo tail -50 /var/log/php8.4-fpm.log`
5. Output dari: `sudo tail -50 /var/log/nginx/error.log`
6. Screenshot error di browser
7. Output dari: `cat .env | grep -v PASSWORD | grep -v SECRET`
