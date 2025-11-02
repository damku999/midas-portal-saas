# Multi-Tenancy Deployment Guide

**Project**: Midas Portal SaaS
**Version**: 1.0
**Last Updated**: 2025-11-02

---

## Prerequisites

### Server Requirements
- **PHP**: 8.2 or higher
- **MySQL**: 8.0 or higher
- **Web Server**: Nginx (recommended) or Apache
- **Composer**: Latest version
- **Node.js**: 18+ (for frontend assets)
- **SSL Certificate**: Wildcard certificate for *.yourdomain.com

### Server Resources (Recommended)
- **CPU**: 4+ cores
- **RAM**: 8GB minimum (16GB recommended)
- **Storage**: 100GB+ SSD
- **Network**: 1Gbps connection

---

## Step 1: Server Setup

### 1.1 Update System
```bash
sudo apt update && sudo apt upgrade -y
```

### 1.2 Install PHP 8.2 and Extensions
```bash
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring \
php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath \
php8.2-intl php8.2-redis
```

### 1.3 Install MySQL 8
```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

### 1.4 Install Nginx
```bash
sudo apt install -y nginx
```

### 1.5 Install Composer
```bash
curl -sS https://getcomposer.com/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

---

## Step 2: Application Deployment

### 2.1 Clone Repository
```bash
cd /var/www
sudo git clone https://github.com/yourusername/midas-portal.git
cd midas-portal
sudo chown -R www-data:www-data /var/www/midas-portal
```

### 2.2 Install Dependencies
```bash
composer install --optimize-autoloader --no-dev
npm install && npm run build
```

### 2.3 Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with production values:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://midastech.in

# Central Database
DB_CONNECTION=central
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=central
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

# Tenancy Configuration
TENANCY_DATABASE_PREFIX=tenant_
CENTRAL_DOMAINS=midastech.in,www.midastech.in

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 2.4 Set Permissions
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## Step 3: Database Setup

### 3.1 Create Central Database
```bash
mysql -u root -p
```

```sql
CREATE DATABASE central CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'midas_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON central.* TO 'midas_user'@'localhost';
GRANT CREATE ON *.* TO 'midas_user'@'localhost';  -- For tenant database creation
FLUSH PRIVILEGES;
EXIT;
```

### 3.2 Run Migrations
```bash
# Central database migrations
php artisan migrate --database=central --path=database/migrations/central

# Publish tenant migrations
php artisan tenants:migrate
```

### 3.3 Seed Central Data
```bash
php artisan db:seed --class=CentralAdminSeeder
```

**Default Credentials**:
- Email: admin@midastech.in
- Password: password (CHANGE IMMEDIATELY!)

---

## Step 4: Nginx Configuration

### 4.1 Create Nginx Configuration
```bash
sudo nano /etc/nginx/sites-available/midas-portal
```

```nginx
# Redirect HTTP to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name midastech.in *.midastech.in;
    return 301 https://$host$request_uri;
}

# Main domain (central admin)
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name midastech.in www.midastech.in;

    root /var/www/midas-portal/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /etc/ssl/certs/midastech.in.crt;
    ssl_certificate_key /etc/ssl/private/midastech.in.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Logging
    access_log /var/log/nginx/midas-portal-access.log;
    error_log /var/log/nginx/midas-portal-error.log;

    # PHP Processing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}

# Wildcard subdomain (tenant domains)
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name ~^(?<subdomain>[^.]+)\.midastech\.in$;

    root /var/www/midas-portal/public;
    index index.php;

    # SSL Configuration (wildcard certificate)
    ssl_certificate /etc/ssl/certs/wildcard.midastech.in.crt;
    ssl_certificate_key /etc/ssl/private/wildcard.midastech.in.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Logging (separate log for tenant requests)
    access_log /var/log/nginx/midas-portal-tenants-access.log;
    error_log /var/log/nginx/midas-portal-tenants-error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 4.2 Enable Site
```bash
sudo ln -s /etc/nginx/sites-available/midas-portal /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

## Step 5: SSL Certificate Setup

### 5.1 Using Let's Encrypt (Recommended)
```bash
sudo apt install -y certbot python3-certbot-nginx

# Main domain
sudo certbot --nginx -d midastech.in -d www.midastech.in

# Wildcard certificate
sudo certbot certonly --manual --preferred-challenges dns \
  -d *.midastech.in -d midastech.in
```

Follow DNS challenge instructions to verify domain ownership.

### 5.2 Auto-Renewal
```bash
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer
```

---

## Step 6: Queue Worker Setup

### 6.1 Create Supervisor Configuration
```bash
sudo nano /etc/supervisor/conf.d/midas-worker.conf
```

```ini
[program:midas-worker]
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

### 6.2 Start Supervisor
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start midas-worker:*
```

---

## Step 7: Scheduler Setup

### 7.1 Add Cron Job
```bash
sudo crontab -e -u www-data
```

Add:
```
* * * * * cd /var/www/midas-portal && php artisan schedule:run >> /dev/null 2>&1
```

---

## Step 8: Redis Setup

### 8.1 Install Redis
```bash
sudo apt install -y redis-server
```

### 8.2 Configure Redis
```bash
sudo nano /etc/redis/redis.conf
```

Set:
```
maxmemory 2gb
maxmemory-policy allkeys-lru
```

```bash
sudo systemctl restart redis-server
```

---

## Step 9: Optimization

### 9.1 Laravel Optimizations
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer dump-autoload --optimize
```

### 9.2 PHP-FPM Tuning
```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

Adjust:
```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

```bash
sudo systemctl restart php8.2-fpm
```

---

## Step 10: Backup Strategy

### 10.1 Database Backup Script
Create `/var/www/midas-portal/scripts/backup-db.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/midas-portal"
DATE=$(date +%Y%m%d_%H%M%S)

# Backup central database
mysqldump -u midas_user -p'password' central > "$BACKUP_DIR/central_$DATE.sql"

# Backup all tenant databases
mysql -u midas_user -p'password' -e "SHOW DATABASES" | grep "^tenant_" | while read db; do
    mysqldump -u midas_user -p'password' "$db" > "$BACKUP_DIR/${db}_$DATE.sql"
done

# Compress backups
gzip "$BACKUP_DIR"/*_$DATE.sql

# Delete backups older than 30 days
find "$BACKUP_DIR" -name "*.sql.gz" -mtime +30 -delete
```

### 10.2 Automate Backups
```bash
sudo chmod +x /var/www/midas-portal/scripts/backup-db.sh
sudo crontab -e
```

Add:
```
0 2 * * * /var/www/midas-portal/scripts/backup-db.sh
```

---

## Step 11: Monitoring

### 11.1 Laravel Telescope (Optional - Dev Only)
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

### 11.2 Health Check Endpoints
Available at:
- `/health` - Basic health check
- `/health/detailed` - Detailed system health
- `/health/liveness` - K8s liveness probe
- `/health/readiness` - K8s readiness probe

---

## Step 12: Security Checklist

- [ ] Change default admin password
- [ ] Configure firewall (UFW)
- [ ] Enable fail2ban
- [ ] Set up regular security updates
- [ ] Configure database user with least privileges
- [ ] Enable application firewall (ModSecurity)
- [ ] Set up intrusion detection (OSSEC)
- [ ] Configure backup encryption
- [ ] Enable 2FA for admin accounts
- [ ] Review Laravel security best practices

---

## Step 13: Post-Deployment

### 13.1 Create First Tenant
1. Login to central admin: https://midastech.in/admin
2. Navigate to "Tenants" → "Create New"
3. Fill in tenant details
4. System will automatically:
   - Create tenant database
   - Run migrations
   - Seed default data
   - Create admin user

### 13.2 Test Tenant Access
1. Access tenant subdomain: https://tenant1.midastech.in
2. Login with tenant admin credentials
3. Verify all features working

### 13.3 Monitor Logs
```bash
# Application logs
tail -f storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/midas-portal-error.log

# Queue worker logs
tail -f storage/logs/worker.log
```

---

## Troubleshooting

### Issue: Subdomain not working
- Check DNS records (wildcard A record)
- Verify Nginx configuration
- Check SSL certificate includes wildcard
- Review `/var/log/nginx/error.log`

### Issue: Database permission errors
```bash
# Grant tenant database creation permission
mysql -u root -p
GRANT CREATE ON *.* TO 'midas_user'@'localhost';
FLUSH PRIVILEGES;
```

### Issue: Queue not processing
```bash
# Check supervisor status
sudo supervisorctl status

# Restart workers
sudo supervisorctl restart midas-worker:*
```

### Issue: Cache issues
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Rollback Procedure

### If deployment fails:
```bash
# Restore database from backup
gunzip /var/backups/midas-portal/central_YYYYMMDD_HHMMSS.sql.gz
mysql -u midas_user -p central < /var/backups/midas-portal/central_YYYYMMDD_HHMMSS.sql

# Revert code
git checkout previous_stable_tag
composer install --no-dev
php artisan migrate:rollback
```

---

## Maintenance Mode

### Enable maintenance mode:
```bash
php artisan down --secret="maintenance-bypass-token"
```

Access via: `https://midastech.in/maintenance-bypass-token`

### Disable maintenance mode:
```bash
php artisan up
```

---

## Performance Benchmarks

Expected performance on recommended hardware:
- **Response Time**: <200ms (95th percentile)
- **Concurrent Users**: 1000+
- **Database Queries**: <50ms average
- **Tenant Isolation**: 100% (separate databases)

---

## Support

For deployment assistance:
- Email: support@midastech.in
- Documentation: /docs/multi-tenancy/
- GitHub Issues: https://github.com/yourusername/midas-portal/issues

---

**Deployment Checklist**: See `DEPLOYMENT_CHECKLIST.md`
**Technical Documentation**: See `TECHNICAL_DOCUMENTATION.md`
