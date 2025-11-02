# Multi-Tenant SaaS Conversion Plan - Midas Insurance Portal

**Project**: Convert Midas Portal to Multi-Tenant SaaS Platform
**Branch**: `feature/multi-tenancy`
**Start Date**: 2025-11-02
**Estimated Duration**: 37-51 hours (5-7 days)
**Laravel Version**: 10.49.1
**PHP Version**: 8.2.12

---

## 🎯 Executive Summary

Transform the Midas Insurance Portal from a single-tenant application to a **multi-tenant SaaS platform** with:
- **Separate databases per tenant** (complete data isolation)
- **Subdomain-based routing** (`client1.midastech.in`, `client2.midastech.in`)
- **Central admin panel** (`admin.midastech.in`) for tenant management
- **Billing and subscription system** with multiple pricing tiers
- **Scalable architecture** supporting unlimited tenants

---

## 🏗️ Architecture Overview

### Current State
- **Database**: Single MySQL database (`u430606517_parth2`)
- **Users**: All users in one database
- **Data**: No isolation between different organizations
- **Domain**: Single domain deployment
- **Models**: 45+ Eloquent models

### Target State
```
Central Domain (midastech.in)
├── Public Website (/, /pricing, /features, /contact, etc.)
├── Central Admin Panel (/admin/*)
├── Central Database (tenant metadata, billing, subscriptions)
├── Super Admin Panel
└── Tenant Management

Tenant Domains (*.midastech.in)
├── Tenant Database 1 (tenant_1) → tenant1.midastech.in
├── Tenant Database 2 (tenant_2) → tenant2.midastech.in
└── Tenant Database N (tenant_N) → tenantN.midastech.in
    ├── Users (isolated)
    ├── Customers (isolated)
    ├── Leads (isolated)
    ├── Claims (isolated)
    └── All operational data (isolated)
```

---

## 📦 Database Structure

### Central Database (`central`)
**Purpose**: Manage tenant metadata and billing

**Tables**:
- `tenants` - Tenant information (id, name, subdomain, database_name, plan_id, status)
- `plans` - Pricing tiers (Starter, Professional, Enterprise)
- `subscriptions` - Billing records per tenant
- `tenant_users` - Super admin accounts (cross-tenant access)
- `migrations` - Central migration tracking

### Tenant Databases (`tenant_{id}`)
**Purpose**: Isolated operational data per tenant

**Tables** (60+ tables):
- All current application tables
- Complete data isolation
- Independent user management
- Separate roles and permissions
- Tenant-specific settings

---

## 🔑 Key Features

### 1. Subdomain-Based Routing
- **Main Domain**: `midastech.in` - Public website + Central admin at `/admin`
- **Tenant Subdomains**: Each tenant gets unique subdomain: `{tenant}.midastech.in`
- Automatic tenant identification from URL
- Handles invalid/suspended tenants gracefully

### 2. Complete Data Isolation
- Separate database per tenant
- No shared data between tenants
- Independent file storage per tenant
- Tenant-scoped sessions and cache

### 3. Central Management System
**Super Admin Features**:
- Create and provision new tenants (auto-database creation)
- Manage tenant subscriptions and billing
- Suspend/activate/delete tenants
- Monitor tenant usage and health
- Impersonate tenant users for support
- Global system configuration

### 4. Billing & Subscription System
**Pricing Tiers**:
- **Starter**: 5 users, 1,000 customers, 500 leads/month
- **Professional**: 20 users, 10,000 customers, 5,000 leads/month
- **Enterprise**: Unlimited users, unlimited data, custom features

**Features**:
- Trial periods (14/30 days)
- Usage limit enforcement
- Auto-suspend on payment failure
- Payment gateway integration (Stripe/Razorpay)
- Invoice generation

### 5. Tenant Provisioning
**Automated Process**:
1. Create tenant record in central database
2. Generate unique subdomain and database name
3. Create MySQL database for tenant
4. Run all tenant migrations
5. Seed default data (roles, statuses, settings)
6. Generate tenant admin credentials
7. Send welcome email
8. **Duration**: < 2 minutes per tenant

---

## 📋 Implementation Phases

### Phase 1: Package Installation & Setup (2-3 hours)
- Install `stancl/tenancy` package
- Publish configuration files
- Configure database connections
- Update environment variables

**Key Files**:
- `composer.json`
- `config/tenancy.php`
- `config/database.php`
- `.env`

### Phase 2: Central Management System (4-6 hours)
- Create central database migrations
- Build Tenant, Plan, Subscription models
- Create central admin controllers
- Build tenant management UI
- Implement tenant provisioning logic

**Key Files**:
- `database/migrations/central/`
- `app/Models/Central/`
- `app/Http/Controllers/Central/`
- `routes/central.php`
- `resources/views/central/`

### Phase 3: Database Architecture Refactoring (6-8 hours)
- Classify tables as central vs tenant
- Update all models for tenant awareness
- Configure tenant-specific storage
- Create separate migration directories
- Test model isolation

**Key Files**:
- All `app/Models/` files (45+ models)
- `database/migrations/tenant/`
- `config/filesystems.php`

### Phase 4: Subdomain Routing & Middleware (4-5 hours)
- Configure subdomain identification
- Wrap routes with tenancy middleware
- Handle invalid tenants
- Update URL generation
- Local testing setup

**Key Files**:
- `routes/web.php`
- `app/Http/Middleware/`
- `config/tenancy.php`
- `.env`

### Phase 5: Authentication & Authorization (3-4 hours)
- Update login for tenant context
- Make Spatie permissions tenant-scoped
- Create super admin authentication
- Implement tenant user impersonation
- Update password reset flow

**Key Files**:
- `app/Http/Controllers/Auth/`
- `app/Models/Central/TenantUser.php`
- Database seeders for roles

### Phase 6: Data Migration (3-4 hours)
⚠️ **CRITICAL: Full backup required before starting**

- Backup current database
- Create migration command
- Migrate existing data to first tenant
- Create default tenant seeders
- Verify data integrity

**Key Files**:
- `app/Console/Commands/MigrateToTenancy.php`
- `database/seeders/Tenant/`

### Phase 7: Billing & Subscription System (6-8 hours)
- Create pricing plans
- Implement usage tracking
- Build limit enforcement middleware
- Integrate payment gateway
- Build billing dashboard

**Key Files**:
- `app/Services/UsageTrackingService.php`
- `app/Services/PaymentGatewayService.php`
- `app/Http/Middleware/EnforcePlanLimits.php`

### Phase 8: Testing & QA (4-6 hours)
- Unit tests for tenant isolation
- Feature tests for subdomain routing
- Security testing (cross-tenant access attempts)
- Performance testing with multiple tenants
- Bug fixes

**Key Files**:
- `tests/Feature/Tenancy/`
- `tests/Unit/Tenancy/`

### Phase 9: Deployment Configuration (3-4 hours)
- Server configuration (Nginx/Apache)
- Wildcard SSL setup
- Database optimization
- Deployment automation script
- Monitoring setup

**Key Files**:
- `deployment/nginx-config.conf`
- `deployment/deploy.sh`
- `deployment/ssl-setup.sh`

### Phase 10: Documentation (2-3 hours)
- Technical architecture documentation
- Administrator user guide
- Deployment guide
- API documentation

**Key Files**:
- `docs/ARCHITECTURE.md`
- `docs/ADMIN_GUIDE.md`
- `docs/DEPLOYMENT.md`

---

## 🛠️ Technical Requirements

### Required Packages
```json
{
  "stancl/tenancy": "^3.8",
  "stripe/stripe-php": "^13.0",
  "razorpay/razorpay": "^2.9"
}
```

### Infrastructure Requirements
1. **DNS**: Wildcard subdomain (`*.midastech.in`)
2. **SSL**: Wildcard SSL certificate (Let's Encrypt)
3. **Database**: MySQL 8.0+ with 20+ connections per tenant
4. **Server**: 4GB+ RAM, multi-core CPU
5. **Storage**: SSD with sufficient space for tenant isolation

### Environment Variables
```env
# Multi-Tenancy Configuration
TENANCY_DATABASE=central
TENANT_DATABASE_PREFIX=tenant_
TENANT_SUBDOMAIN_ENABLED=true
APP_DOMAIN=midastech.in
CENTRAL_DOMAIN=midastech.in
CENTRAL_ADMIN_PATH=/admin

# Billing
STRIPE_KEY=
STRIPE_SECRET=
RAZORPAY_KEY_ID=
RAZORPAY_KEY_SECRET=
```

---

## 🚨 Critical Considerations

### Data Security
- Complete isolation between tenants
- No cross-tenant queries possible
- Separate file storage per tenant
- Tenant-scoped sessions and cache
- Encrypted sensitive data

### Performance
- Database connection pooling
- Redis cache per tenant (optional)
- Query optimization for tenant context
- Indexed subdomain lookups
- Lazy loading tenant data

### Scalability
- Support for 100+ tenants on single server
- Horizontal scaling via multiple app servers
- Database read replicas per tenant (advanced)
- CDN for static assets
- Queue workers per tenant (optional)

### Backup & Recovery
- Automated daily backups per tenant database
- Point-in-time recovery capability
- Central database backup
- File storage backups
- Disaster recovery plan

---

## ⚠️ Risks & Mitigation

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| Data loss during migration | Critical | Low | Complete backup, staging test, rollback plan |
| Performance degradation | High | Medium | Connection pooling, caching, query optimization |
| Cross-tenant data leak | Critical | Low | Comprehensive security testing, middleware validation |
| Subdomain routing issues | Medium | Low | Fallback mechanisms, clear error pages |
| Increased server costs | Medium | High | Efficient resource allocation, usage monitoring |
| Complex deployment | Medium | Medium | Automated deployment scripts, documentation |

---

## 📊 Success Metrics

### Technical KPIs
- ✅ Multiple tenants operate independently
- ✅ Zero cross-tenant data access possible
- ✅ Subdomain routing works 100% of the time
- ✅ Tenant provisioning < 2 minutes
- ✅ No performance degradation vs single-tenant
- ✅ < 1% error rate in tenant operations

### Business KPIs
- ✅ Can onboard 10+ tenants per day
- ✅ 99.9% uptime per tenant
- ✅ Billing system processes payments automatically
- ✅ Support tickets reduced via impersonation feature
- ✅ Documentation covers all admin operations

---

## 🚀 Post-Launch Roadmap

### Phase 2 Enhancements
1. **Tenant Customization**
   - Custom branding (logo, colors, themes)
   - White-label domains (`insurance.clientdomain.com`)
   - Custom email templates

2. **Advanced Features**
   - Multi-region deployment
   - Tenant-specific API keys
   - Advanced analytics and reporting
   - Tenant marketplace (add-ons)

3. **Enterprise Features**
   - SSO integration (SAML, OAuth)
   - Custom data retention policies
   - Advanced security controls
   - Dedicated database servers

4. **AI/ML Capabilities**
   - Tenant-specific ML models
   - Predictive analytics per tenant
   - Automated insights

---

## 📞 Support & Maintenance

### Ongoing Tasks
- Monitor tenant database sizes
- Optimize slow queries per tenant
- Review usage patterns
- Update pricing plans based on usage
- Security patches and updates
- Performance tuning

### Monitoring Alerts
- Tenant database > 90% full
- Failed tenant provisioning
- Payment failures
- High error rates per tenant
- Unusual cross-tenant activity attempts

---

**Document Version**: 1.0
**Last Updated**: 2025-11-02
**Status**: Ready for Implementation
