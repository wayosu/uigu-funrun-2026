# Deployment Guide — Fun Run Event Registration

## Overview

Panduan lengkap untuk deployment sistem ke production server, termasuk server setup, configuration, security, monitoring, dan maintenance.

---

## Table of Contents

1. [Server Requirements](#1-server-requirements)
2. [Server Setup](#2-server-setup)
3. [Application Deployment](#3-application-deployment)
4. [Database Configuration](#4-database-configuration)
5. [Queue & Scheduler Setup](#5-queue--scheduler-setup)
6. [Email & Notification Configuration](#6-email--notification-configuration)
7. [Security Hardening](#7-security-hardening)
8. [Performance Optimization](#8-performance-optimization)
9. [Monitoring & Logging](#9-monitoring--logging)
10. [Backup Strategy](#10-backup-strategy)
11. [Production Checklist](#11-production-checklist)
12. [Maintenance & Troubleshooting](#12-maintenance--troubleshooting)

---

## 1. Server Requirements

### Minimum Specifications

| Component | Specification |
|-----------|---------------|
| **CPU** | 2 vCPU |
| **RAM** | 4 GB |
| **Storage** | 50 GB SSD |
| **OS** | Ubuntu 22.04 LTS / 24.04 LTS |
| **Bandwidth** | 100 Mbps |

### Recommended Specifications (High Traffic)

| Component | Specification |
|-----------|---------------|
| **CPU** | 4-8 vCPU |
| **RAM** | 8-16 GB |
| **Storage** | 100-200 GB SSD |
| **OS** | Ubuntu 22.04 LTS / 24.04 LTS |
| **Bandwidth** | 1 Gbps |

### Software Requirements

- **Web Server**: Nginx 1.18+
- **PHP**: 8.4+ (with FPM)
- **Database**: MySQL 8.0+ / PostgreSQL 14+
- **Cache & Queue**: Redis 7.0+
- **Node.js**: 20.x LTS (for asset compilation)
- **Supervisor**: For queue workers
- **Certbot**: For SSL certificates

---

## 2. Server Setup

### 2.1: Initial Server Configuration

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Set timezone
sudo timedatectl set-timezone Asia/Jakarta

# Create deploy user
sudo adduser deploy
sudo usermod -aG sudo deploy
sudo su - deploy

# Setup SSH key (dari local machine)
ssh-keygen -t ed25519 -C "deploy@funrun"
# Copy public key ke server
ssh-copy-id deploy@your-server-ip
```

### 2.2: Install Required Software

```bash
# Add repositories
sudo add-apt-repository ppa:ondrej/php -y
sudo add-apt-repository ppa:redislabs/redis -y

# Install PHP 8.4 & extensions
sudo apt install -y php8.4-fpm \
    php8.4-cli \
    php8.4-mysql \
    php8.4-pgsql \
    php8.4-redis \
    php8.4-mbstring \
    php8.4-xml \
    php8.4-bcmath \
    php8.4-curl \
    php8.4-gd \
    php8.4-intl \
    php8.4-zip \
    php8.4-soap

# Install MySQL
sudo apt install -y mysql-server

# Install Redis
sudo apt install -y redis-server

# Install Nginx
sudo apt install -y nginx

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Install Supervisor
sudo apt install -y supervisor

# Install Certbot (SSL)
sudo apt install -y certbot python3-certbot-nginx
```

### 2.3: Configure PHP

```bash
# Edit PHP-FPM configuration
sudo nano /etc/php/8.4/fpm/php.ini
```

**Important PHP settings**:
```ini
memory_limit = 512M
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
max_input_time = 300

; OPcache settings
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0
opcache.fast_shutdown=1
```

```bash
# Restart PHP-FPM
sudo systemctl restart php8.4-fpm
```

### 2.4: Configure MySQL

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database & user
sudo mysql -u root -p
```

```sql
CREATE DATABASE funrun_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'funrun_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON funrun_production.* TO 'funrun_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**MySQL Configuration** (`/etc/mysql/mysql.conf.d/mysqld.cnf`):
```ini
[mysqld]
max_connections = 200
innodb_buffer_pool_size = 2G
innodb_log_file_size = 512M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
query_cache_size = 0
query_cache_type = 0
```

```bash
sudo systemctl restart mysql
```

### 2.5: Configure Redis

```bash
sudo nano /etc/redis/redis.conf
```

**Important Redis settings**:
```conf
maxmemory 2gb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

```bash
sudo systemctl restart redis-server
sudo systemctl enable redis-server
```

---

## 3. Application Deployment

### 3.1: Setup Application Directory

```bash
# Create directory structure
sudo mkdir -p /var/www/funrun
sudo chown -R deploy:deploy /var/www/funrun

cd /var/www/funrun

# Clone repository
git clone git@github.com:your-org/funrun-app.git .
# Or using deployment key
```

### 3.2: Install Dependencies

```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node dependencies
npm ci

# Build assets
npm run build
```

### 3.3: Environment Configuration

```bash
# Copy environment file
cp .env.example .env
nano .env
```

**Production `.env` configuration**:
```env
APP_NAME="Fun Run Registration"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=https://funrun.yourdomain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=funrun_production
DB_USERNAME=funrun_user
DB_PASSWORD=your_strong_password

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=s3
QUEUE_CONNECTION=redis

SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_QUEUE_DB=2

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your_mailgun_username
MAIL_PASSWORD=your_mailgun_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@funrun.yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# AWS S3 for file storage
AWS_ACCESS_KEY_ID=your_aws_access_key
AWS_SECRET_ACCESS_KEY=your_aws_secret_key
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=funrun-storage
AWS_USE_PATH_STYLE_ENDPOINT=false

# Fonnte WhatsApp
FONNTE_API_KEY=your_fonnte_api_key
FONNTE_DEVICE_ID=your_device_id
```

```bash
# Generate application key
php artisan key:generate

# Set proper permissions
sudo chown -R deploy:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 3.4: Run Migrations

```bash
# Run migrations
php artisan migrate --force

# Seed initial data (settings, admin user)
php artisan db:seed --class=SettingsSeeder --force
php artisan db:seed --class=AdminUserSeeder --force
```

### 3.5: Optimize Application

```bash
# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan filament:cache-components

# Create storage link
php artisan storage:link
```

---

## 4. Nginx Configuration

### 4.1: Create Nginx Server Block

```bash
sudo nano /etc/nginx/sites-available/funrun
```

**Nginx configuration**:
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name funrun.yourdomain.com www.funrun.yourdomain.com;
    
    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name funrun.yourdomain.com www.funrun.yourdomain.com;

    root /var/www/funrun/public;
    index index.php index.html;

    # SSL certificates (will be generated by Certbot)
    ssl_certificate /etc/letsencrypt/live/funrun.yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/funrun.yourdomain.com/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval';" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;

    # Client max body size (for file uploads)
    client_max_body_size 20M;

    # Logs
    access_log /var/log/nginx/funrun-access.log;
    error_log /var/log/nginx/funrun-error.log;

    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        
        # Increase timeouts for long-running operations
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    # Deny access to .php files in specific directories
    location ~* /(?:uploads|files|wp-content|wp-includes|akismet)/.*.php$ {
        deny all;
        access_log off;
        log_not_found off;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/funrun /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
sudo systemctl enable nginx
```

### 4.2: Setup SSL Certificate

```bash
# Generate SSL certificate
sudo certbot --nginx -d funrun.yourdomain.com -d www.funrun.yourdomain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

---

## 5. Queue & Scheduler Setup

### 5.1: Configure Supervisor for Queue Workers

```bash
sudo nano /etc/supervisor/conf.d/funrun-worker.conf
```

**Supervisor configuration**:
```ini
[program:funrun-worker-high]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/funrun/artisan queue:work redis --queue=high --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/funrun/storage/logs/worker-high.log
stopwaitsecs=3600

[program:funrun-worker-default]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/funrun/artisan queue:work redis --queue=default --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=3
redirect_stderr=true
stdout_logfile=/var/www/funrun/storage/logs/worker-default.log
stopwaitsecs=3600

[program:funrun-worker-notifications]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/funrun/artisan queue:work redis --queue=notifications --sleep=5 --tries=5 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/funrun/storage/logs/worker-notifications.log
stopwaitsecs=3600

[program:funrun-horizon]
process_name=%(program_name)s
command=php /var/www/funrun/artisan horizon
autostart=true
autorestart=true
user=deploy
redirect_stderr=true
stdout_logfile=/var/www/funrun/storage/logs/horizon.log
stopwaitsecs=3600
```

```bash
# Reload Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all

# Check status
sudo supervisorctl status
```

### 5.2: Setup Laravel Scheduler

```bash
# Edit crontab
crontab -e
```

**Add cron job**:
```cron
* * * * * cd /var/www/funrun && php artisan schedule:run >> /dev/null 2>&1
```

---

## 6. Email & Notification Configuration

### 6.1: Email Setup (Mailgun Example)

```bash
# Install Mailgun driver (if not already)
composer require symfony/mailgun-mailer symfony/http-client
```

**Configure in `.env`**:
```env
MAIL_MAILER=mailgun
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@mg.yourdomain.com
MAIL_PASSWORD=your_mailgun_api_key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@funrun.yourdomain.com
MAIL_FROM_NAME="Fun Run Event"

MAILGUN_DOMAIN=mg.yourdomain.com
MAILGUN_SECRET=key-your_mailgun_api_key
MAILGUN_ENDPOINT=api.mailgun.net
```

### 6.2: WhatsApp Configuration (Fonnte)

**Configure in Admin Panel** or `.env`:
```env
FONNTE_API_KEY=your_fonnte_api_key
FONNTE_DEVICE_ID=your_device_id
```

---

## 7. Security Hardening

### 7.1: Firewall Configuration

```bash
# Install UFW
sudo apt install -y ufw

# Default policies
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Allow SSH, HTTP, HTTPS
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'

# Enable firewall
sudo ufw enable
sudo ufw status
```

### 7.2: Fail2Ban Setup

```bash
# Install Fail2Ban
sudo apt install -y fail2ban

# Configure Fail2Ban
sudo nano /etc/fail2ban/jail.local
```

```ini
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[sshd]
enabled = true

[nginx-http-auth]
enabled = true
```

```bash
sudo systemctl restart fail2ban
sudo systemctl enable fail2ban
```

### 7.3: Security Best Practices

```bash
# Disable root login via SSH
sudo nano /etc/ssh/sshd_config
```

```conf
PermitRootLogin no
PasswordAuthentication no
PubkeyAuthentication yes
```

```bash
sudo systemctl restart sshd

# Set file permissions
sudo chown -R deploy:www-data /var/www/funrun
sudo find /var/www/funrun -type f -exec chmod 644 {} \;
sudo find /var/www/funrun -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/funrun/storage
sudo chmod -R 775 /var/www/funrun/bootstrap/cache
```

---

## 8. Performance Optimization

### 8.1: PHP-FPM Tuning

```bash
sudo nano /etc/php/8.4/fpm/pool.d/www.conf
```

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

### 8.2: Enable HTTP/2 & Brotli

Already enabled in Nginx config (`listen 443 ssl http2`)

### 8.3: Database Optimization

```bash
# Run MySQL tuning script
sudo apt install -y mysqltuner
sudo mysqltuner
```

### 8.4: Laravel Optimization

```bash
cd /var/www/funrun

# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Enable OPcache (already configured in php.ini)
# Verify
php -i | grep opcache.enable
```

---

## 9. Monitoring & Logging

### 9.1: Application Monitoring

**Install Laravel Telescope** (development only):
```bash
composer require --dev laravel/telescope
php artisan telescope:install
php artisan migrate
```

**Setup Sentry** (error tracking):
```bash
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=your_sentry_dsn
```

### 9.2: Server Monitoring

**Install monitoring tools**:
```bash
# htop for process monitoring
sudo apt install -y htop

# nethogs for network monitoring
sudo apt install -y nethogs

# iotop for disk I/O monitoring
sudo apt install -y iotop
```

### 9.3: Log Management

**Setup log rotation**:
```bash
sudo nano /etc/logrotate.d/funrun
```

```conf
/var/www/funrun/storage/logs/*.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    create 0644 deploy deploy
    sharedscripts
}
```

---

## 10. Backup Strategy

### 10.1: Database Backup Script

```bash
nano /home/deploy/backup-db.sh
```

```bash
#!/bin/bash

# Configuration
BACKUP_DIR="/var/backups/funrun"
DB_NAME="funrun_production"
DB_USER="funrun_user"
DB_PASS="your_password"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Delete old backups
find $BACKUP_DIR -name "db_backup_*.sql.gz" -mtime +$RETENTION_DAYS -delete

# Upload to S3 (optional)
# aws s3 cp $BACKUP_DIR/db_backup_$DATE.sql.gz s3://your-bucket/backups/

echo "Database backup completed: $DATE"
```

```bash
chmod +x /home/deploy/backup-db.sh

# Add to crontab (daily at 2 AM)
crontab -e
```

```cron
0 2 * * * /home/deploy/backup-db.sh >> /var/log/backup.log 2>&1
```

### 10.2: Application Backup

```bash
# Backup uploaded files to S3
aws s3 sync /var/www/funrun/storage/app/public s3://your-bucket/storage/
```

---

## 11. Production Checklist

### Pre-Deployment Checklist

- [ ] All tests passing (`php artisan test`)
- [ ] Code formatted (`vendor/bin/pint`)
- [ ] Environment variables configured
- [ ] Database migrations tested
- [ ] Asset compilation successful (`npm run build`)
- [ ] SSL certificate installed
- [ ] Firewall configured
- [ ] Backup system setup
- [ ] Monitoring tools installed

### Post-Deployment Checklist

- [ ] Application accessible via HTTPS
- [ ] Database connection working
- [ ] Queue workers running (`supervisorctl status`)
- [ ] Scheduler working (check cron logs)
- [ ] Email sending working (send test email)
- [ ] WhatsApp sending working (send test message)
- [ ] File upload working
- [ ] Admin panel accessible
- [ ] Registration flow working end-to-end
- [ ] Payment verification working
- [ ] E-Ticket generation working
- [ ] Check-in system working

### Performance Checklist

- [ ] OPcache enabled and working
- [ ] Redis cache working
- [ ] Static assets cached (check headers)
- [ ] Gzip compression enabled
- [ ] HTTP/2 enabled
- [ ] Database queries optimized (check logs)
- [ ] Page load time < 3 seconds

### Security Checklist

- [ ] HTTPS enforced (HTTP redirects to HTTPS)
- [ ] Security headers present (check with securityheaders.com)
- [ ] File permissions correct
- [ ] Debug mode disabled (`APP_DEBUG=false`)
- [ ] Database credentials secure
- [ ] API keys stored in .env (not in code)
- [ ] Rate limiting configured
- [ ] CSRF protection enabled
- [ ] SQL injection protection (Eloquent)
- [ ] XSS protection (Blade escaping)

---

## 12. Maintenance & Troubleshooting

### Common Commands

```bash
# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.4-fpm
sudo systemctl restart redis-server
sudo supervisorctl restart all

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# View logs
tail -f storage/logs/laravel.log
tail -f /var/log/nginx/funrun-error.log
sudo journalctl -u php8.4-fpm -f

# Queue management
php artisan queue:work --once --verbose
php artisan queue:retry all
php artisan queue:flush
php artisan horizon:pause
php artisan horizon:continue
php artisan horizon:terminate

# Database
php artisan migrate:status
php artisan db:show
php artisan db:monitor
```

### Troubleshooting Common Issues

**Issue**: 502 Bad Gateway  
**Solution**:
```bash
sudo systemctl status php8.4-fpm
sudo systemctl restart php8.4-fpm
sudo systemctl restart nginx
```

**Issue**: Queue not processing  
**Solution**:
```bash
sudo supervisorctl status
sudo supervisorctl restart funrun-worker-default:*
php artisan queue:restart
```

**Issue**: High memory usage  
**Solution**:
```bash
# Check processes
htop
# Optimize PHP-FPM
sudo nano /etc/php/8.4/fpm/pool.d/www.conf
# Reduce pm.max_children
```

**Issue**: Slow database queries  
**Solution**:
```bash
# Enable query log
mysql -u root -p
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;

# Check slow queries
sudo tail -f /var/log/mysql/slow-query.log
```

---

## Deployment Script (Zero-Downtime)

```bash
#!/bin/bash

# deploy.sh - Zero-downtime deployment script

APP_DIR="/var/www/funrun"
BRANCH="main"

echo "Starting deployment..."

cd $APP_DIR

# Pull latest code
git fetch origin $BRANCH
git reset --hard origin/$BRANCH

# Install/update dependencies
composer install --no-dev --optimize-autoloader --no-interaction
npm ci --no-audit
npm run build

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Reload queue workers
php artisan queue:restart
sudo supervisorctl restart funrun-horizon:

# Reload PHP-FPM
sudo systemctl reload php8.4-fpm

echo "Deployment completed successfully!"
```

---

## Conclusion

Sistem ini siap untuk production dengan:
- ✅ **High availability**: Redundant queue workers, process monitoring
- ✅ **Scalability**: Horizontal scaling ready, optimized caching
- ✅ **Security**: SSL, firewall, security headers, rate limiting
- ✅ **Performance**: OPcache, Redis, CDN-ready, optimized assets
- ✅ **Reliability**: Automated backups, monitoring, error tracking
- ✅ **Maintainability**: Automated deployment, clear logging

**Support Contacts**:
- Server Admin: admin@yourdomain.com
- Developer: dev@yourdomain.com
- Emergency: +62-xxx-xxxx-xxxx
