# Multi-Tenancy Deployment Guide

**Project**: Midas Portal SaaS
**Last Updated**: 2025-11-03

---

## 1. Local Development Setup

### Prerequisites
- PHP 8.2+, MySQL 8.0+, Composer, Node.js 18+
- XAMPP/WAMP for Windows or LAMP for Linux

### 1.1 Database Setup
```bash
# Create central database
mysql -u root -p
CREATE DATABASE central CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'midas_user'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON central.* TO 'midas_user'@'localhost';
GRANT CREATE ON *.* TO 'midas_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 1.2 Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Configure `.env`:
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=central
DB_HOST=127.0.0.1
DB_DATABASE=central
DB_USERNAME=midas_user
DB_PASSWORD=password

TENANCY_DATABASE_PREFIX=tenant_
CENTRAL_DOMAINS=midastech.in,localhost,127.0.0.1
```

### 1.3 Install Dependencies
```bash
composer install
npm install && npm run build
```

### 1.4 Run Migrations
```bash
# Central database
php artisan migrate --database=central --path=database/migrations/central

# Seed data
php artisan db:seed --class=CentralAdminSeeder
```

**Default Credentials**: admin@midastech.in / password

### 1.5 Configure Local Hosts
Edit hosts file (`C:\Windows\System32\drivers\etc\hosts` or `/etc/hosts`):
```
127.0.0.1 midastech.in.local
127.0.0.1 tenant1.midastech.in.local
127.0.0.1 tenant2.midastech.in.local
```

### 1.6 Start Development Server
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

---

## 2. Testing Guide

### 2.1 Test Central Admin
1. Access: http://127.0.0.1:8000/admin/login
2. Login: admin@midastech.in / password
3. Verify dashboard metrics display correctly

### 2.2 Create Test Tenant
1. Click "Create New Tenant"
2. Fill form:
   - Company: Test Company 1
   - Subdomain: tenant1
   - Email: admin@tenant1.com
   - Plan: Professional
   - Trial: 14 days
3. Verify tenant database created

### 2.3 Test Tenant Access
1. Access: http://tenant1.midastech.in.local:8000
2. Login with tenant credentials
3. Verify default data (statuses, sources, notification types)

### 2.4 Test Data Isolation
1. Create customer in tenant1
2. Create second tenant (tenant2)
3. Verify tenant2 cannot see tenant1 data
4. Confirm separate databases in MySQL

### 2.5 Test Subscription & Billing
1. Navigate to tenant subscription page
2. Verify plan details, trial status, usage metrics
3. Test plan limits enforcement
4. Verify MRR calculation in central admin

### 2.6 Test Error Handling
- Invalid subdomain: Should show "Tenant Not Found"
- Suspended tenant: Should show "Account Suspended"
- Expired trial: Should prompt for upgrade

### 2.7 Testing Checklist
- [ ] Central admin login works
- [ ] Can create/edit/suspend tenants
- [ ] Tenant database auto-created
- [ ] Tenant isolation verified
- [ ] Subscription tracking works
- [ ] Usage metrics accurate
- [ ] Error pages display correctly
- [ ] Audit logs recorded

---

## 3. Production Deployment

### 3.1 Server Requirements
- CPU: 4+ cores, RAM: 8GB+, Storage: 100GB SSD
- PHP 8.2, MySQL 8.0, Nginx, Redis
- SSL: Wildcard certificate for *.yourdomain.com

### 3.2 Server Setup
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring \
php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-redis

# Install MySQL
sudo apt install -y mysql-server
sudo mysql_secure_installation

# Install Nginx & Redis
sudo apt install -y nginx redis-server

# Install Composer
curl -sS https://getcomposer.com/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 3.3 Deploy Application
```bash
cd /var/www
sudo git clone https://github.com/yourusername/midas-portal.git
cd midas-portal
sudo chown -R www-data:www-data /var/www/midas-portal

composer install --optimize-autoloader --no-dev
npm install && npm run build

cp .env.example .env
php artisan key:generate
```

Configure production `.env`:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://midastech.in

DB_CONNECTION=central
DB_HOST=127.0.0.1
DB_DATABASE=central
DB_USERNAME=midas_user
DB_PASSWORD=secure_password

TENANCY_DATABASE_PREFIX=tenant_
CENTRAL_DOMAINS=midastech.in,www.midastech.in

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 3.4 Set Permissions
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 3.5 SSL Setup (Let's Encrypt)
```bash
sudo apt install -y certbot python3-certbot-nginx

# Main domain
sudo certbot --nginx -d midastech.in -d www.midastech.in

# Wildcard certificate
sudo certbot certonly --manual --preferred-challenges dns \
  -d *.midastech.in -d midastech.in

# Auto-renewal
sudo systemctl enable certbot.timer
```

### 3.6 Queue Worker Setup
```bash
sudo nano /etc/supervisor/conf.d/midas-worker.conf
```

```ini
[program:midas-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/midas-portal/artisan queue:work redis --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/midas-portal/storage/logs/worker.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start midas-worker:*
```

### 3.7 Scheduler Setup
```bash
sudo crontab -e -u www-data
```
Add:
```
* * * * * cd /var/www/midas-portal && php artisan schedule:run >> /dev/null 2>&1
```

### 3.8 Optimization
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer dump-autoload --optimize
```

### 3.9 Security Checklist
- [ ] Change default admin password
- [ ] Configure firewall (UFW)
- [ ] Enable fail2ban
- [ ] Database least privileges
- [ ] Enable 2FA for admin
- [ ] Review security headers in Nginx
- [ ] Set up backup encryption

---

## 4. Rollback Procedures

### 4.1 Emergency Full Rollback
```bash
# Enable maintenance mode
php artisan down --secret="bypass-token"

# Restore database from backup
gunzip /var/backups/midas-portal/backup_YYYYMMDD_HHMMSS.sql.gz
mysql -u midas_user -p central < /var/backups/midas-portal/backup_YYYYMMDD_HHMMSS.sql

# Revert code to stable version
git checkout main
composer install --no-dev

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Restart services
sudo systemctl restart php8.2-fpm nginx redis-server

# Disable maintenance mode
php artisan up
```

**Estimated Time**: 15-30 minutes

### 4.2 Database Backup Strategy
Create `/var/www/midas-portal/scripts/backup-db.sh`:
```bash
#!/bin/bash
BACKUP_DIR="/var/backups/midas-portal"
DATE=$(date +%Y%m%d_%H%M%S)

# Backup central
mysqldump -u midas_user -pPassword central | gzip > "$BACKUP_DIR/central_$DATE.sql.gz"

# Backup all tenants
mysql -u midas_user -pPassword -e "SHOW DATABASES" | grep "^tenant_" | while read db; do
    mysqldump -u midas_user -pPassword "$db" | gzip > "$BACKUP_DIR/${db}_$DATE.sql.gz"
done

# Clean old backups (30 days)
find "$BACKUP_DIR" -name "*.sql.gz" -mtime +30 -delete
```

Automate:
```bash
sudo chmod +x /var/www/midas-portal/scripts/backup-db.sh
sudo crontab -e
# Add: 0 2 * * * /var/www/midas-portal/scripts/backup-db.sh
```

### 4.3 Partial Rollback (Specific Components)

**Rollback Tenant Creation**:
```bash
# Drop tenant database
mysql -u midas_user -p -e "DROP DATABASE tenant_<uuid>;"

# Remove tenant record from central
mysql -u midas_user -p central -e "DELETE FROM tenants WHERE id='<tenant_id>';"
```

**Rollback Configuration Changes**:
```bash
git checkout config/tenancy.php
php artisan config:clear
sudo systemctl restart php8.2-fpm
```

### 4.4 Post-Rollback Verification
```bash
# Check application status
php artisan config:cache
php artisan route:list
php artisan migrate:status

# Monitor logs
tail -f storage/logs/laravel.log
tail -f /var/log/nginx/error.log

# Verify services
sudo systemctl status nginx php8.2-fpm mysql redis-server supervisor
```

### 4.5 Rollback Checklist
- [ ] Application loads without errors
- [ ] Users can login
- [ ] Database queries functional
- [ ] All routes accessible
- [ ] No fatal errors in logs
- [ ] Services running properly
- [ ] Caches cleared
- [ ] Dependencies correct

---

## 5. Monitoring & Maintenance

### Health Check Endpoints
- `/health` - Basic check
- `/health/detailed` - System health
- `/health/liveness` - K8s liveness
- `/health/readiness` - K8s readiness

### Log Monitoring
```bash
# Application
tail -f storage/logs/laravel.log

# Nginx
tail -f /var/log/nginx/midas-portal-error.log

# Workers
tail -f storage/logs/worker.log
```

### Maintenance Mode
```bash
# Enable
php artisan down --secret="bypass-token"

# Access via: https://midastech.in/bypass-token

# Disable
php artisan up
```

---

## Support

**Email**: support@midastech.in
**Documentation**: /docs/multi-tenancy/
**Issues**: GitHub repository issues section
