# Midas Portal - Project Documentation

**Version:** 1.0.0
**Last Updated:** 2025-11-03
**Laravel Version:** 10.x
**Multi-Tenancy:** stancl/tenancy v3.9.1

---

## 📚 Documentation Index

### 🏗️ Multi-Tenancy Architecture

**Location:** `docs/multi-tenancy/`

| Document | Purpose | Audience |
|----------|---------|----------|
| [MULTI_TENANCY_PLAN.md](multi-tenancy/MULTI_TENANCY_PLAN.md) | Complete multi-tenancy architecture overview | All developers |
| [IMPLEMENTATION_COMPLETE.md](multi-tenancy/IMPLEMENTATION_COMPLETE.md) ⭐ | Implementation completion summary | All team members |
| [FINAL_COMPLETION_SUMMARY.md](multi-tenancy/FINAL_COMPLETION_SUMMARY.md) | Final implementation summary | All stakeholders |
| [CENTRAL_DOMAIN_SPEC.md](multi-tenancy/CENTRAL_DOMAIN_SPEC.md) | Central domain specifications | Backend developers |
| [LOCAL_TESTING_GUIDE.md](multi-tenancy/LOCAL_TESTING_GUIDE.md) | Local development testing guide | All developers |
| [DEPLOYMENT_GUIDE.md](multi-tenancy/DEPLOYMENT_GUIDE.md) | Production deployment guide | DevOps, senior developers |
| [ROLLBACK_GUIDE.md](multi-tenancy/ROLLBACK_GUIDE.md) | Emergency rollback procedures | DevOps, senior developers |

### 🛣️ Routing System

**Location:** `docs/routing/`

| Document | Purpose | Audience |
|----------|---------|----------|
| [ROUTING_ARCHITECTURE.md](routing/ROUTING_ARCHITECTURE.md) | Complete routing architecture | All developers |
| [DOMAIN_ROUTING_GUIDE.md](routing/DOMAIN_ROUTING_GUIDE.md) | Domain-based routing guide | Backend developers |
| [MIDDLEWARE_GUIDE.md](routing/MIDDLEWARE_GUIDE.md) | Middleware configuration guide | Backend developers |
| [CRITICAL_FIX_DOMAIN_ROUTING.md](routing/CRITICAL_FIX_DOMAIN_ROUTING.md) 🔥 | CRITICAL: Domain routing fix | All developers |
| [ROUTING_FIXES_2025-11-02.md](routing/ROUTING_FIXES_2025-11-02.md) | Other routing fixes applied | All developers |
| [TROUBLESHOOTING.md](routing/TROUBLESHOOTING.md) | Common routing issues & solutions | All developers |

### 🚀 Features & Implementations

**Location:** `docs/features/`

| Document | Purpose | Audience |
|----------|---------|----------|
| [LEAD_MANAGEMENT_COMPLETE.md](features/LEAD_MANAGEMENT_COMPLETE.md) | Lead management system | All developers |
| [LEAD_MANAGEMENT_PLAN.md](features/LEAD_MANAGEMENT_PLAN.md) | Lead management architecture | Backend developers |
| [LEAD_MANAGEMENT_QUICKSTART.md](features/LEAD_MANAGEMENT_QUICKSTART.md) | Lead management quick start | All developers |
| [WHATSAPP_LEAD_IMPLEMENTATION.md](features/WHATSAPP_LEAD_IMPLEMENTATION.md) | WhatsApp integration | Backend developers |
| [WHATSAPP_USER_GUIDE.md](features/WHATSAPP_USER_GUIDE.md) | WhatsApp user guide | End users, support team |
| [PROTECTION_SYSTEM_IMPLEMENTATION.md](features/PROTECTION_SYSTEM_IMPLEMENTATION.md) | Data protection system | All developers |
| [PROTECTION_QUICK_START.md](features/PROTECTION_QUICK_START.md) | Protection quick start | All developers |

### 🛠️ Reference Documentation

**Location:** `docs/`

| Document | Purpose | Audience |
|----------|---------|----------|
| [API_REFERENCE.md](API_REFERENCE.md) | API endpoints reference | Backend developers, frontend developers |
| [DOCUMENTATION_INDEX.md](DOCUMENTATION_INDEX.md) | Complete documentation index | All team members |
| [PROJECT_INDEX.md](PROJECT_INDEX.md) | Complete project structure | All developers |
| [TENANT_CREATION_FIX_SUMMARY.md](TENANT_CREATION_FIX_SUMMARY.md) | Tenant creation bug fixes | Backend developers |
| [CLEANUP_REPORT_2025-11-03.md](CLEANUP_REPORT_2025-11-03.md) | Project cleanup report | All developers |

### 📦 Archived Documentation

**Location:** `docs/archive/`

Historical documentation including session summaries, planning docs, and fix summaries.

---

## 🚀 Quick Start Guides

### For New Developers

1. Read [MULTI_TENANCY_PLAN.md](multi-tenancy/MULTI_TENANCY_PLAN.md) - Understand the architecture
2. Read [ROUTING_ARCHITECTURE.md](routing/ROUTING_ARCHITECTURE.md) - Understand routing
3. Follow [LOCAL_TESTING_GUIDE.md](multi-tenancy/LOCAL_TESTING_GUIDE.md) - Set up local environment
4. Check [TROUBLESHOOTING.md](routing/TROUBLESHOOTING.md) when issues arise

### For Frontend Developers

1. [MULTI_TENANCY_PLAN.md](multi-tenancy/MULTI_TENANCY_PLAN.md) - Section: "Frontend Implications"
2. [DOMAIN_ROUTING_GUIDE.md](routing/DOMAIN_ROUTING_GUIDE.md) - Section: "URL Generation"
3. [LOCAL_TESTING_GUIDE.md](multi-tenancy/LOCAL_TESTING_GUIDE.md) - Testing different portals

### For DevOps Engineers

1. [DEPLOYMENT_GUIDE.md](multi-tenancy/DEPLOYMENT_GUIDE.md) - Complete deployment process
2. [ROLLBACK_GUIDE.md](multi-tenancy/ROLLBACK_GUIDE.md) - Emergency procedures
3. [MULTI_TENANCY_PLAN.md](multi-tenancy/MULTI_TENANCY_PLAN.md) - Section: "Database Architecture"

---

## 🏛️ System Architecture Overview

### Three-Portal System

The Midas Portal implements a sophisticated multi-tenant architecture with three distinct portals:

```
┌─────────────────────────────────────────────────────────────┐
│                    DOMAIN STRUCTURE                          │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  Central Domain: midastech.testing.in                       │
│  ├─ Public Website (/, /features, /pricing, /about)        │
│  └─ Central Admin (/midas-admin/*)                          │
│                                                              │
│  Tenant Subdomains: {tenant}.midastech.testing.in          │
│  ├─ Staff Portal (/)                                        │
│  └─ Customer Portal (/customer/*)                           │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### Route Files

| File | Purpose | Domain | Guard |
|------|---------|--------|-------|
| `routes/public.php` | Public marketing website | Central only | None |
| `routes/central.php` | Central admin panel | Central only | `central` |
| `routes/web.php` | Tenant staff portal | Tenant only | `web` |
| `routes/customer.php` | Customer portal | Tenant only | `customer` |

### Middleware Stack

| Middleware | Purpose | Applied To |
|------------|---------|------------|
| `universal` | Initialize tenancy by domain | Tenant routes |
| `tenant` | Block central domain access | Tenant routes |
| `central.only` | Block tenant subdomain access | Central routes |
| `central.auth` | Central admin authentication | Central admin routes |
| `customer.auth` | Customer authentication | Customer portal routes |

---

## 📊 Database Architecture

### Central Database
- Stores tenant configurations
- Subscription management
- Central admin users
- **Not accessible from tenant context**

### Tenant Databases
- Each tenant has isolated database
- Contains all tenant-specific data (customers, policies, etc.)
- Database name format: `tenant_{tenant_id}`
- **Not accessible from central context**

---

## 🔐 Authentication System

### Three Separate Guards

1. **Central Guard** (`central`)
   - For central admin panel users
   - Database: `central`
   - Table: `central_admins`

2. **Web Guard** (default)
   - For tenant staff users
   - Database: Tenant-specific
   - Table: `users`

3. **Customer Guard** (`customer`)
   - For insurance customers
   - Database: Tenant-specific
   - Table: `customers`

---

## 🧪 Testing

### Local Testing Domains

```bash
# Add to C:\Windows\System32\drivers\etc\hosts (Windows)
# or /etc/hosts (Linux/Mac)

127.0.0.1 midastech.testing.in
127.0.0.1 demo.midastech.testing.in
127.0.0.1 test.midastech.testing.in
```

### Access URLs

- **Public Website:** http://midastech.testing.in:8085
- **Central Admin:** http://midastech.testing.in:8085/midas-admin
- **Tenant Staff:** http://demo.midastech.testing.in:8085
- **Customer Portal:** http://demo.midastech.testing.in:8085/customer

---

## 🆘 Common Issues

### Issue: 404 on Central Domain

**Solution:** Check middleware configuration in `app/Http/Kernel.php`
```php
'central.only' => \App\Http\Middleware\PreventAccessFromTenantDomains::class,
```

### Issue: Tenant Routes Accessible from Central Domain

**Solution:** Verify RouteServiceProvider loads public routes BEFORE tenant routes

### Issue: Route Not Found After Multi-Tenancy

**Solution:**
```bash
php artisan route:clear
php artisan route:cache
php artisan config:clear
```

**Full troubleshooting:** See [TROUBLESHOOTING.md](routing/TROUBLESHOOTING.md)

---

## 📝 Recent Changes (2025-11-03)

### Project Cleanup Completed ✅

**Actions Taken:**
- Consolidated documentation from `claudedocs/` to `docs/`
- Archived planning and session docs to `docs/archive/`
- Organized feature docs in `docs/features/`
- Cleaned up failed tenant databases
- Updated documentation structure

**Details:** See [CLEANUP_REPORT_2025-11-03.md](CLEANUP_REPORT_2025-11-03.md)

### Critical Bug Fixes Applied (2025-11-02)

1. **Tenant Creation Schema Fix** - Fixed user table column mismatch (first_name/last_name vs name)
2. **Double Modal Popup Fix** - Resolved delete tenant button triggering two modals
3. **Domain Routing Fix** - Fixed TenantCouldNotBeIdentifiedOnDomainException
4. **Cache Tagging Fix** - Switched to database cache driver for multi-tenancy support

**Details:** [TENANT_CREATION_FIX_SUMMARY.md](TENANT_CREATION_FIX_SUMMARY.md)

---

## 🤝 Contributing

### Before Making Changes

1. Read relevant documentation
2. Understand multi-tenancy implications
3. Test on both central and tenant domains
4. Update documentation if needed

### Documentation Standards

- Keep docs up-to-date with code changes
- Use clear, concise language
- Include code examples where helpful
- Update this index when adding new docs

---

## 📞 Support

For questions or issues:

1. Check [TROUBLESHOOTING.md](routing/TROUBLESHOOTING.md)
2. Review relevant architecture docs
3. Contact senior developers
4. Create detailed bug reports with domain context

---

## 📜 License

Proprietary - Midas Insurance Portal
© 2024-2025 All Rights Reserved
