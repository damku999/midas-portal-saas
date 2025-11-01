# Midas Portal - Deployment Guide

## Environment Setup

### Prerequisites
- PHP 8.2+ with extensions: `mbstring`, `xml`, `curl`, `zip`, `gd`, `mysql`, `redis`
- Composer 2.x
- Node.js 18+ & NPM
- MySQL 8.0+
- Redis (recommended for production)
- Web server (Apache/Nginx)

### Installation Steps

#### 1. Clone Repository
```bash
git clone <repository-url> midas-portal
cd midas-portal
```

#### 2. Install Dependencies
```bash
# PHP dependencies
composer install --no-dev --optimize-autoloader

# JavaScript dependencies
npm install
npm run production
```

#### 3. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

#### 4. Configure Environment Variables

**Required Configuration**:
```env
# Application
APP_NAME="Midas Portal"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=midas_portal
DB_USERNAME=midas_user
DB_PASSWORD=secure_password_here

# Mail (SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=noreply@your-domain.com
MAIL_PASSWORD=smtp_password_here
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Security
CSP_ENABLED=true
CSP_REPORT_ONLY=false

# Cloudflare Turnstile (Bot Protection)
TURNSTILE_SITE_KEY=your_site_key
TURNSTILE_SECRET_KEY=your_secret_key
```

#### 5. Database Setup
```bash
# Run migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --class=UnifiedPermissionsSeeder --force
```

#### 6. Storage Permissions
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### 7. Optimize for Production
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache

# Optimize autoloader
composer dump-autoload --optimize
```

---

## Web Server Configuration

### Apache Configuration

**VirtualHost** (`/etc/apache2/sites-available/midas-portal.conf`):
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com

    # Redirect to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}$1 [R=301,L]
</VirtualHost>

<VirtualHost *:443>
    ServerName your-domain.com
    ServerAlias www.your-domain.com

    DocumentRoot /var/www/midas-portal/public

    <Directory /var/www/midas-portal/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/your-domain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/your-domain.com/privkey.pem

    # Security Headers (additional to Laravel middleware)
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

    # Logging
    ErrorLog ${APACHE_LOG_DIR}/midas-portal-error.log
    CustomLog ${APACHE_LOG_DIR}/midas-portal-access.log combined
</VirtualHost>
```

**Enable Required Modules**:
```bash
sudo a2enmod rewrite ssl headers
sudo a2ensite midas-portal.conf
sudo systemctl reload apache2
```

### Nginx Configuration

**Server Block** (`/etc/nginx/sites-available/midas-portal`):
```nginx
# Redirect HTTP to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

# HTTPS Server
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com www.your-domain.com;

    root /var/www/midas-portal/public;
    index index.php index.html;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Gzip Compression
    gzip on;
    gzip_types text/css application/javascript application/json;

    # Laravel Routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Logging
    access_log /var/log/nginx/midas-portal-access.log;
    error_log /var/log/nginx/midas-portal-error.log;
}
```

**Enable Site**:
```bash
sudo ln -s /etc/nginx/sites-available/midas-portal /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## SSL/TLS Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache  # For Apache
sudo apt install certbot python3-certbot-nginx   # For Nginx

# Obtain Certificate
sudo certbot --apache -d your-domain.com -d www.your-domain.com
# OR
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Auto-renewal (cron)
sudo certbot renew --dry-run
```

---

## Task Scheduling

**Cron Entry** (`crontab -e`):
```cron
# Laravel Scheduler
* * * * * cd /var/www/midas-portal && php artisan schedule:run >> /dev/null 2>&1

# Backup Database (Daily at 2 AM)
0 2 * * * cd /var/www/midas-portal && php artisan backup:run >> /var/log/midas-backup.log 2>&1
```

**Scheduled Tasks** (in `app/Console/Kernel.php`):
- Send renewal reminders (daily at 9 AM)
- Send birthday wishes (daily at 8 AM)
- Retry failed notifications (hourly)
- Archive old logs (weekly)

---

## Queue Workers

### Supervisor Configuration

**Config File** (`/etc/supervisor/conf.d/midas-portal-worker.conf`):
```ini
[program:midas-portal-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/midas-portal/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/midas-portal/storage/logs/worker.log
stopwaitsecs=3600
```

**Start Workers**:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start midas-portal-worker:*
```

---

## Monitoring & Health Checks

### Health Endpoints

```bash
# Basic Health Check
curl https://your-domain.com/health
# Response: {"status":"healthy","timestamp":"2025-11-01T10:00:00Z"}

# Detailed Health Check
curl https://your-domain.com/health/detailed
# Response: {database, cache, queue, disk_space, etc.}

# Liveness (for Kubernetes)
curl https://your-domain.com/health/liveness

# Readiness (for Load Balancers)
curl https://your-domain.com/health/readiness
```

### Log Monitoring

**View Logs**:
```bash
# Application Logs
tail -f storage/logs/laravel.log

# Web Server Logs
tail -f /var/log/apache2/midas-portal-error.log   # Apache
tail -f /var/log/nginx/midas-portal-error.log     # Nginx

# Worker Logs
tail -f storage/logs/worker.log
```

**Log Viewer (Web UI)**:
```
https://your-domain.com/webmonks-log-viewer
```

---

## Backup Strategy

### Database Backup

**Manual Backup**:
```bash
# Create backup
mysqldump -u midas_user -p midas_portal > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore backup
mysql -u midas_user -p midas_portal < backup_20251101_120000.sql
```

**Automated Backup** (using Spatie Laravel Backup):
```bash
composer require spatie/laravel-backup

# Configure in config/backup.php
php artisan backup:run
```

### File Backup

**Backup Storage Directory**:
```bash
# Create archive
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/app/

# Sync to remote server
rsync -avz storage/app/ user@backup-server:/backups/midas-portal/
```

---

## Security Hardening

### File Permissions
```bash
# Application files
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Storage and cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Protect sensitive files
chmod 600 .env
chmod 600 storage/oauth-*.key
```

### Environment Security
```bash
# Disable directory listing
# Already configured in .htaccess and web server config

# Hide Laravel version
# Remove X-Powered-By header in middleware

# Enable HTTPS only
# Configured in TrustProxies middleware
```

### Database Security
```sql
-- Create dedicated database user with limited privileges
CREATE USER 'midas_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON midas_portal.* TO 'midas_user'@'localhost';
FLUSH PRIVILEGES;

-- Disable LOAD DATA INFILE (if not needed)
SET GLOBAL local_infile=0;
```

---

## Performance Optimization

### PHP-FPM Configuration

**File**: `/etc/php/8.2/fpm/pool.d/www.conf`
```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500

; Increase memory limit
php_admin_value[memory_limit] = 256M

; OPcache
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### MySQL Optimization

**File**: `/etc/mysql/my.cnf`
```ini
[mysqld]
# InnoDB Settings
innodb_buffer_pool_size = 2G
innodb_log_file_size = 512M
innodb_flush_method = O_DIRECT

# Query Cache (if < MySQL 8.0)
query_cache_type = 1
query_cache_size = 128M

# Max Connections
max_connections = 200

# Slow Query Log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
```

### Redis Configuration

**File**: `/etc/redis/redis.conf`
```ini
maxmemory 512mb
maxmemory-policy allkeys-lru

# Persistence
save 900 1
save 300 10
save 60 10000
```

---

## Zero-Downtime Deployment

### Deployment Script

**File**: `deploy.sh`
```bash
#!/bin/bash
set -e

echo "Starting deployment..."

# Pull latest code
git pull origin main

# Install/update dependencies
composer install --no-dev --optimize-autoloader --no-interaction
npm install
npm run production

# Run migrations (safe for production)
php artisan migrate --force

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
sudo supervisorctl restart midas-portal-worker:*

# Restart PHP-FPM
sudo systemctl reload php8.2-fpm

echo "Deployment completed successfully!"
```

**Make Executable**:
```bash
chmod +x deploy.sh
./deploy.sh
```

---

## Rollback Procedure

```bash
# 1. Revert to previous Git commit
git revert HEAD
git push origin main

# 2. Rollback database migrations (if needed)
php artisan migrate:rollback --step=1

# 3. Restore from backup
mysql -u midas_user -p midas_portal < backup_YYYYMMDD_HHMMSS.sql

# 4. Clear caches
php artisan cache:clear
php artisan config:clear

# 5. Restart services
sudo supervisorctl restart midas-portal-worker:*
sudo systemctl reload php8.2-fpm
```

---

## Troubleshooting

### Common Issues

**1. 500 Internal Server Error**
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check web server logs
tail -f /var/log/apache2/error.log  # Apache
tail -f /var/log/nginx/error.log    # Nginx

# Check file permissions
ls -la storage/ bootstrap/cache/
```

**2. Database Connection Error**
```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check MySQL service
sudo systemctl status mysql

# Verify credentials in .env
grep DB_ .env
```

**3. Queue Not Processing**
```bash
# Check queue workers
sudo supervisorctl status midas-portal-worker:*

# Check Redis connection
redis-cli ping

# View failed jobs
php artisan queue:failed
php artisan queue:retry all
```

**4. Email Not Sending**
```bash
# Test SMTP connection
php artisan tinker
>>> Mail::raw('Test', fn($msg) => $msg->to('test@example.com')->subject('Test'));

# Check mail logs
grep -r "Failed to send" storage/logs/
```

---

## Production Checklist

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Generate strong `APP_KEY`
- [ ] Configure SSL/TLS certificate
- [ ] Enable security headers (`CSP_ENABLED=true`)
- [ ] Configure Cloudflare Turnstile
- [ ] Set up database backups (automated)
- [ ] Configure queue workers with Supervisor
- [ ] Set up cron for Laravel Scheduler
- [ ] Enable Redis for caching and sessions
- [ ] Configure file upload limits
- [ ] Test all notification channels (Email, WhatsApp, SMS)
- [ ] Enable monitoring and health checks
- [ ] Set up error tracking (optional: Sentry)
- [ ] Document server credentials securely
- [ ] Test disaster recovery procedure

---

**Next**: See `DEVELOPER_GUIDE.md` for local development setup
