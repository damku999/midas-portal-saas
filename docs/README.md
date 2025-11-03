# Midas Portal Documentation

**Laravel 10.x | Multi-Tenant SaaS Platform | stancl/tenancy v3.9.1**

---

## 📚 Documentation Files

| File | Description |
|------|-------------|
| **[ARCHITECTURE.md](ARCHITECTURE.md)** | Complete system architecture, multi-tenancy design, routing, authentication |
| **[FEATURES.md](FEATURES.md)** | Lead management, WhatsApp integration, protection system |
| **[DEPLOYMENT.md](DEPLOYMENT.md)** | Local setup, testing, production deployment, rollback procedures |
| **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** | Common issues, fixes, solutions |
| **[API_REFERENCE.md](API_REFERENCE.md)** | API endpoints reference |
| **README.md** | This file - documentation index |

---

## 🚀 Quick Start

### New Developers
1. Read **[ARCHITECTURE.md](ARCHITECTURE.md)** - Understand the system
2. Follow **[DEPLOYMENT.md](DEPLOYMENT.md)** - Set up locally
3. Check **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** when stuck

### DevOps Engineers
1. **[DEPLOYMENT.md](DEPLOYMENT.md)** - Production deployment
2. **[ARCHITECTURE.md](ARCHITECTURE.md)** - Infrastructure requirements
3. **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Emergency procedures

### Frontend Developers
1. **[ARCHITECTURE.md](ARCHITECTURE.md)** → Portal Structure section
2. **[API_REFERENCE.md](API_REFERENCE.md)** - API endpoints
3. **[FEATURES.md](FEATURES.md)** - Feature implementations

---

## 🏗️ System Overview

### Three-Portal Architecture

```
Central Domain (midastech.in)
├── Public Website (/)
└── Central Admin (/midas-admin/*)

Tenant Subdomains ({tenant}.midastech.in)
├── Staff Portal (/)
└── Customer Portal (/customer/*)
```

### Key Features

- **Complete Tenant Isolation** - Separate databases per tenant
- **Subdomain Routing** - Automatic tenant identification
- **Three Authentication Guards** - Central, staff, customer
- **Automated Provisioning** - New tenant ready in < 2 minutes

---

## 🧪 Local Testing

```bash
# Add to hosts file
127.0.0.1 midastech.testing.in
127.0.0.1 demo.midastech.testing.in

# Access URLs
http://midastech.testing.in:8085              # Public website
http://midastech.testing.in:8085/midas-admin  # Central admin
http://demo.midastech.testing.in:8085         # Tenant staff
http://demo.midastech.testing.in:8085/customer # Customer portal
```

---

## 🆘 Common Issues

### Quick Fixes

| Issue | Solution |
|-------|----------|
| 404 on routes | `php artisan optimize:clear` |
| Cache errors | Change `.env` → `CACHE_DRIVER=database` |
| Tenant creation fails | Check schema: first_name/last_name not name |
| Wrong login redirect | Verify domain in `config/tenancy.php` |

**Full troubleshooting**: See [TROUBLESHOOTING.md](TROUBLESHOOTING.md)

---

## 📝 Recent Changes

### Documentation Consolidation (Nov 3, 2025)

**Reduced from 35+ files → 6 files (83% reduction)**

- Merged all architecture, routing, multi-tenancy docs → **ARCHITECTURE.md**
- Merged all feature docs → **FEATURES.md**
- Merged deployment, testing, rollback → **DEPLOYMENT.md**
- Merged all fixes and issues → **TROUBLESHOOTING.md**

### Critical Fixes Applied (Nov 2-3, 2025)

1. ✅ Domain routing fix (TenantCouldNotBeIdentifiedOnDomainException)
2. ✅ Tenant creation schema fix (first_name/last_name vs name)
3. ✅ Cache tagging support (database driver)
4. ✅ Double modal popup fix
5. ✅ BelongsToTenant trait removed (49 models)

---

## 📞 Support

**Need Help?**
1. Check **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)**
2. Review **[ARCHITECTURE.md](ARCHITECTURE.md)**
3. Contact senior developers

**Report Issues**:
Include domain context, error messages, and steps to reproduce.

---

**Last Updated**: 2025-11-03
**Status**: Production Ready
**Documentation**: Complete & Consolidated
