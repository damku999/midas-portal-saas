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

### 2.6 Test Payment Gateway Integration
1. Configure test payment gateway credentials (see Section 6)
2. Test subscription creation with payment
3. Verify webhook handling for payment success/failure
4. Test trial-to-paid conversion flow
5. Verify payment records in central database

### 2.7 Test Error Handling
- Invalid subdomain: Should show "Tenant Not Found"
- Suspended tenant: Should show "Account Suspended"
- Expired trial: Should prompt for upgrade

### 2.8 Testing Checklist
- [ ] Central admin login works
- [ ] Can create/edit/suspend tenants
- [ ] Tenant database auto-created
- [ ] Tenant isolation verified
- [ ] Subscription tracking works
- [ ] Usage metrics accurate
- [ ] Payment gateway integration works
- [ ] Webhook endpoints responding
- [ ] Trial conversion tested
- [ ] Error pages display correctly
- [ ] Audit logs recorded
- [ ] Email notifications sent
- [ ] WhatsApp notifications working (if configured)

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

## 6. Payment Gateway Configuration

### 6.1 Razorpay Setup (Primary Gateway)

#### Create Razorpay Account
1. Visit https://razorpay.com and create account
2. Complete KYC verification for production access
3. Navigate to Settings → API Keys

#### Test Mode Configuration
```env
# .env configuration
RAZORPAY_KEY=rzp_test_XXXXXXXXXXXX
RAZORPAY_SECRET=YYYYYYYYYYYYYYYY
RAZORPAY_WEBHOOK_SECRET=whsec_ZZZZZZZZZZZ
RAZORPAY_ENABLED=true
```

#### Production Mode Configuration
```env
# .env configuration
RAZORPAY_KEY=rzp_live_XXXXXXXXXXXX
RAZORPAY_SECRET=YYYYYYYYYYYYYYYY
RAZORPAY_WEBHOOK_SECRET=whsec_ZZZZZZZZZZZ
RAZORPAY_ENABLED=true
```

#### Configure Razorpay Dashboard
1. **Webhook Setup** (Settings → Webhooks):
   - Webhook URL: `https://yourdomain.com/webhooks/razorpay`
   - Events to subscribe:
     - `payment.captured`
     - `payment.failed`
     - `subscription.charged`
     - `subscription.cancelled`
     - `subscription.paused`
     - `subscription.resumed`
     - `refund.created`
   - Save webhook secret to `RAZORPAY_WEBHOOK_SECRET`

2. **Payment Methods** (Settings → Configuration):
   - Enable: Cards, UPI, Net Banking, Wallets
   - Set currency: INR
   - Configure settlement preferences

3. **Branding** (Settings → Branding):
   - Upload logo (recommended: 256x256 px)
   - Set brand color
   - Add support contact details

#### Test Razorpay Integration
```bash
# Test payment creation
php artisan tinker
>>> $payment = app(\App\Services\PaymentService::class);
>>> $order = $payment->createOrder(1000, 'INR', ['tenant_id' => 1]);
>>> print_r($order);

# Test webhook locally with ngrok
ngrok http 8000
# Update Razorpay webhook URL to: https://YOUR_SUBDOMAIN.ngrok.io/webhooks/razorpay
```

**Test Cards** (Test Mode Only):
- Success: `4111 1111 1111 1111` (any CVV, future date)
- Failure: `4000 0000 0000 0002`
- 3D Secure: `5267 3181 8797 5449`

### 6.2 Stripe Setup (Alternative Gateway)

#### Create Stripe Account
1. Visit https://stripe.com and create account
2. Complete business verification
3. Navigate to Developers → API Keys

#### Configuration
```env
# .env configuration
STRIPE_KEY=pk_test_XXXXXXXXXXXX
STRIPE_SECRET=sk_test_YYYYYYYYYYYY
STRIPE_WEBHOOK_SECRET=whsec_ZZZZZZZZZZZ
STRIPE_ENABLED=false  # Set to true to enable
```

#### Configure Stripe Dashboard
1. **Webhook Setup** (Developers → Webhooks):
   - Endpoint URL: `https://yourdomain.com/webhooks/stripe`
   - Events:
     - `payment_intent.succeeded`
     - `payment_intent.payment_failed`
     - `customer.subscription.created`
     - `customer.subscription.updated`
     - `customer.subscription.deleted`
     - `charge.refunded`

2. **Product Catalog**:
   - Create products matching your plans
   - Set pricing (monthly/yearly)
   - Configure billing intervals

#### Test Stripe Integration
**Test Cards**:
- Success: `4242 4242 4242 4242`
- Decline: `4000 0000 0000 0002`
- 3D Secure: `4000 0025 0000 3155`

### 6.3 Webhook Security

#### Verify Webhook Signatures
Both Razorpay and Stripe webhooks are automatically verified in:
- `app/Http/Controllers/WebhookController.php`
- Middleware: `VerifyWebhookSignature`

#### IP Whitelisting (Optional)
Add to `.env`:
```env
RAZORPAY_WEBHOOK_IPS=13.232.146.66,13.234.74.26,13.234.74.27
STRIPE_WEBHOOK_IPS=3.18.12.63,3.130.192.231,13.235.14.237
```

Configure in `config/payment.php`:
```php
'webhook_ip_whitelist' => [
    'razorpay' => explode(',', env('RAZORPAY_WEBHOOK_IPS', '')),
    'stripe' => explode(',', env('STRIPE_WEBHOOK_IPS', '')),
],
```

#### Webhook Retry Configuration
```env
# Automatic retry settings
WEBHOOK_RETRY_ATTEMPTS=3
WEBHOOK_RETRY_DELAY=300  # 5 minutes in seconds
```

### 6.4 Testing Webhooks Locally

#### Using ngrok
```bash
# Start local server
php artisan serve --host=0.0.0.0 --port=8000

# In new terminal, start ngrok
ngrok http 8000

# Copy ngrok URL (e.g., https://abc123.ngrok.io)
# Update webhook URLs in Razorpay/Stripe dashboards
```

#### Manual Webhook Testing
```bash
# Test Razorpay webhook
curl -X POST https://yourdomain.com/webhooks/razorpay \
  -H "Content-Type: application/json" \
  -H "X-Razorpay-Signature: SIGNATURE_HERE" \
  -d '{
    "event": "payment.captured",
    "payload": {
      "payment": {
        "entity": {
          "id": "pay_test123",
          "amount": 100000,
          "status": "captured"
        }
      }
    }
  }'
```

#### Monitor Webhook Logs
```bash
# Application logs
tail -f storage/logs/laravel.log | grep webhook

# Database webhook log
php artisan tinker
>>> \App\Models\Central\Payment::latest()->first();
```

### 6.5 Subscription Deployment Configuration

#### Configure Subscription Plans
Update plans in central database:
```sql
-- Verify plans exist
SELECT * FROM plans;

-- Update Razorpay plan IDs (from Razorpay dashboard)
UPDATE plans SET razorpay_plan_id = 'plan_XXXX' WHERE slug = 'starter';
UPDATE plans SET razorpay_plan_id = 'plan_YYYY' WHERE slug = 'professional';
UPDATE plans SET razorpay_plan_id = 'plan_ZZZZ' WHERE slug = 'enterprise';

-- Update Stripe price IDs (from Stripe dashboard)
UPDATE plans SET stripe_price_id = 'price_XXXX' WHERE slug = 'starter';
UPDATE plans SET stripe_price_id = 'price_YYYY' WHERE slug = 'professional';
UPDATE plans SET stripe_price_id = 'price_ZZZZ' WHERE slug = 'enterprise';
```

#### Trial Configuration
```env
# Default trial period (days)
DEFAULT_TRIAL_DAYS=14

# Trial grace period after expiry (days)
TRIAL_GRACE_PERIOD=3

# Enable/disable auto-trial for new signups
AUTO_TRIAL_ENABLED=true
```

#### Automated Trial Processing
Configure scheduler to run trial checks:
```bash
# Verify scheduler is running
php artisan schedule:list

# Expected output should include:
# subscriptions:process-trials ... Daily at 00:00
```

Manual trial processing:
```bash
php artisan subscriptions:process-trials
```

### 6.6 Payment Gateway Monitoring

#### Health Checks
```bash
# Check gateway connectivity
php artisan payment:test-gateway razorpay
php artisan payment:test-gateway stripe

# Verify webhook endpoints
curl -I https://yourdomain.com/webhooks/razorpay
curl -I https://yourdomain.com/webhooks/stripe
```

#### Monitor Payment Failures
```sql
-- Recent failed payments
SELECT * FROM payments
WHERE status = 'failed'
ORDER BY created_at DESC
LIMIT 20;

-- Payment failure rate (last 30 days)
SELECT
    DATE(created_at) as date,
    COUNT(*) as total,
    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
    ROUND(SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as failure_rate
FROM payments
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(created_at)
ORDER BY date DESC;
```

#### Gateway Failover Strategy
```php
// config/payment.php
'failover' => [
    'enabled' => env('PAYMENT_FAILOVER_ENABLED', true),
    'primary_gateway' => 'razorpay',
    'fallback_gateway' => 'stripe',
    'retry_failed_after' => 300, // 5 minutes
],
```

### 6.7 Subscription Checklist

**Pre-Production**:
- [ ] Razorpay/Stripe account verified and activated
- [ ] Production API keys configured in `.env`
- [ ] Webhook URLs configured in gateway dashboards
- [ ] Webhook signature verification tested
- [ ] Plans synced between database and gateway
- [ ] Test payments completed successfully
- [ ] Refund process tested
- [ ] Trial conversion flow validated

**Production**:
- [ ] Live payment processed successfully
- [ ] Webhooks receiving and processing correctly
- [ ] Email notifications sending on payment events
- [ ] Subscription status updating correctly
- [ ] Usage tracking functioning
- [ ] Trial expiry automation working
- [ ] Payment failure retry logic active
- [ ] Dashboard metrics displaying accurately

---

## 7. Multi-Portal Configuration

### 7.1 Portal Routing Setup

#### Domain Configuration
```env
# config/tenancy-domains.php
SUPPORTED_DOMAINS=midastech.in,midastech.testing.in,localhost:8085
CENTRAL_DOMAIN=midastech.in
PUBLIC_WEBSITE_DOMAIN=www.midastech.in
```

#### Nginx Configuration for Multi-Portal
Create `/etc/nginx/sites-available/midas-portal`:
```nginx
# Public Website (www.midastech.in)
server {
    listen 80;
    server_name www.midastech.in;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name www.midastech.in;

    ssl_certificate /etc/letsencrypt/live/midastech.in/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/midastech.in/privkey.pem;

    root /var/www/midas-portal/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

# Central Admin Portal (midastech.in)
server {
    listen 80;
    server_name midastech.in;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name midastech.in;

    ssl_certificate /etc/letsencrypt/live/midastech.in/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/midastech.in/privkey.pem;

    root /var/www/midas-portal/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

# Tenant Portals (*.midastech.in)
server {
    listen 80;
    server_name *.midastech.in;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name *.midastech.in;

    ssl_certificate /etc/letsencrypt/live/midastech.in/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/midastech.in/privkey.pem;

    root /var/www/midas-portal/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Enable and restart:
```bash
sudo ln -s /etc/nginx/sites-available/midas-portal /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 7.2 Portal Access Testing

#### Public Website Portal
```bash
# Test homepage
curl -I https://www.midastech.in

# Expected routes:
# GET / - Homepage
# GET /features - Features page
# GET /pricing - Pricing page
# GET /about - About page
# POST /contact - Contact form
```

#### Central Admin Portal
```bash
# Test admin login
curl -I https://midastech.in/admin/login

# Expected routes:
# GET /admin/login - Admin login
# GET /admin/dashboard - Admin dashboard
# GET /admin/tenants - Tenant management
# GET /admin/plans - Plan management
```

#### Tenant Staff Portal
```bash
# Test tenant access
curl -I https://tenant1.midastech.in/login

# Expected routes:
# GET /login - Staff login
# GET /dashboard - Staff dashboard
# GET /customers - Customer management
# GET /policies - Policy management
```

#### Customer Portal
```bash
# Test customer portal
curl -I https://tenant1.midastech.in/customer/login

# Expected routes:
# GET /customer/login - Customer login
# GET /customer/dashboard - Customer dashboard
# GET /customer/policies - Customer policies view
```

### 7.3 Multi-Portal Checklist
- [ ] All four portals accessible via correct domains
- [ ] Portal routing logic working (subdomain detection)
- [ ] Authentication isolated per portal
- [ ] Session management separated
- [ ] Middleware protecting correct routes
- [ ] Public website accessible without authentication
- [ ] Central admin requires admin authentication
- [ ] Tenant portals isolated by subdomain
- [ ] Customer portal requires customer authentication

---

## Support

**Email**: support@midastech.in
**Documentation**: /docs/multi-tenancy/
**Issues**: GitHub repository issues section

---

## Related Documentation

- [SUBSCRIPTION_MANAGEMENT.md](../features/SUBSCRIPTION_MANAGEMENT.md) - Subscription system details
- [PAYMENT_GATEWAY_INTEGRATION.md](../features/PAYMENT_GATEWAY_INTEGRATION.md) - Payment integration guide
- [MULTI_PORTAL_ARCHITECTURE.md](../core/MULTI_PORTAL_ARCHITECTURE.md) - Multi-portal system architecture
- [ENVIRONMENT_CONFIGURATION.md](../setup/ENVIRONMENT_CONFIGURATION.md) - Complete environment setup
- [TROUBLESHOOTING.md](./TROUBLESHOOTING.md) - Common deployment issues
