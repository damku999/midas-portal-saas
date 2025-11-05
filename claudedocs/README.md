# Midas Portal Documentation

Complete technical documentation for the Midas Portal multi-tenant insurance management system.

---

## 📚 Documentation Structure

### **Core Documentation**

| Document | Description |
|----------|-------------|
| [ARCHITECTURE.md](ARCHITECTURE.md) | System architecture, design patterns, and technical decisions |
| [API_REFERENCE.md](API_REFERENCE.md) | Complete API endpoint documentation (340+ routes) |
| [FEATURES.md](FEATURES.md) | Feature specifications and implementation details |

### **🚀 Setup & Configuration**

| Document | Description |
|----------|-------------|
| [setup/LOCAL_TENANT_ACCESS_GUIDE.md](setup/LOCAL_TENANT_ACCESS_GUIDE.md) | Local development setup for multi-tenancy |
| [setup/NGROK_QUICK_START.md](setup/NGROK_QUICK_START.md) | Webhook testing with ngrok for payment gateways |

### **💳 Features & Integrations**

| Document | Description |
|----------|-------------|
| [features/PAYMENT_GATEWAY_INTEGRATION.md](features/PAYMENT_GATEWAY_INTEGRATION.md) | Razorpay & Stripe payment integration guide |
| [features/TRIAL_CONVERSION_SYSTEM.md](features/TRIAL_CONVERSION_SYSTEM.md) | Complete trial management: email reminders, auto-conversion, payment integration |
| [features/FILE_STORAGE_MULTI_TENANCY.md](features/FILE_STORAGE_MULTI_TENANCY.md) | Tenant-isolated file storage with usage tracking |

### **⚙️ Operations & Deployment**

| Document | Description |
|----------|-------------|
| [operations/DEPLOYMENT.md](operations/DEPLOYMENT.md) | Production deployment checklist and procedures |
| [operations/TROUBLESHOOTING.md](operations/TROUBLESHOOTING.md) | Common issues and solutions |
| [operations/MULTI_TENANCY_FIXES.md](operations/MULTI_TENANCY_FIXES.md) | Multi-tenancy bug fixes and improvements |

### **🧪 Testing & Verification**

| Document | Description |
|----------|-------------|
| [testing/EMAIL_VERIFICATION_TESTING.md](testing/EMAIL_VERIFICATION_TESTING.md) | Email system testing procedures |
| [testing/FILE_UPLOAD_ACCESSIBILITY_TEST.md](testing/FILE_UPLOAD_ACCESSIBILITY_TEST.md) | File upload testing and validation |

---

## 🎯 Quick Navigation

### **For New Developers**
1. Start with [ARCHITECTURE.md](ARCHITECTURE.md) to understand the system
2. Review [setup/LOCAL_TENANT_ACCESS_GUIDE.md](setup/LOCAL_TENANT_ACCESS_GUIDE.md) for local setup
3. Check [API_REFERENCE.md](API_REFERENCE.md) for available endpoints
4. Read [FEATURES.md](FEATURES.md) for feature specifications

### **For DevOps/Deployment**
1. [operations/DEPLOYMENT.md](operations/DEPLOYMENT.md) - Deployment procedures
2. [operations/TROUBLESHOOTING.md](operations/TROUBLESHOOTING.md) - Common issues
3. [features/PAYMENT_GATEWAY_INTEGRATION.md](features/PAYMENT_GATEWAY_INTEGRATION.md) - Payment setup

### **For QA/Testing**
1. [testing/EMAIL_VERIFICATION_TESTING.md](testing/EMAIL_VERIFICATION_TESTING.md)
2. [testing/FILE_UPLOAD_ACCESSIBILITY_TEST.md](testing/FILE_UPLOAD_ACCESSIBILITY_TEST.md)
3. [API_REFERENCE.md](API_REFERENCE.md) - API testing reference

### **For Payment Integration**
1. [features/PAYMENT_GATEWAY_INTEGRATION.md](features/PAYMENT_GATEWAY_INTEGRATION.md) - Complete integration guide
2. [features/TRIAL_CONVERSION_SYSTEM.md](features/TRIAL_CONVERSION_SYSTEM.md) - Trial & subscription management
3. [setup/NGROK_QUICK_START.md](setup/NGROK_QUICK_START.md) - Local webhook testing

---

## 📦 System Overview

### **Technology Stack**
- **Backend**: Laravel 10.49.1 (PHP 8.2.12)
- **Database**: MySQL 8.4 with Redis cache
- **Multi-Tenancy**: Stancl Tenancy Package
- **Payments**: Razorpay (primary), Stripe (ready)
- **Frontend**: Bootstrap 5.3.2, jQuery 3.7.1
- **Testing**: Pest PHP 2.36, Playwright

### **Key Features**
- 🏢 Full multi-tenancy with isolated databases
- 💳 Subscription management with payment gateways
- 📋 Customer & policy management
- 💰 Quotation system with PDF generation
- 🔔 Claims processing workflow
- 📧 Multi-channel notifications
- 🔐 Advanced security (2FA, audit logs)

---

## 🔍 Finding Documentation

### **By Topic**

**Multi-Tenancy**:
- [ARCHITECTURE.md](ARCHITECTURE.md) - Multi-tenant architecture
- [setup/LOCAL_TENANT_ACCESS_GUIDE.md](setup/LOCAL_TENANT_ACCESS_GUIDE.md) - Local tenant access
- [features/FILE_STORAGE_MULTI_TENANCY.md](features/FILE_STORAGE_MULTI_TENANCY.md) - Tenant file storage
- [operations/MULTI_TENANCY_FIXES.md](operations/MULTI_TENANCY_FIXES.md) - Bug fixes

**Payments & Subscriptions**:
- [features/PAYMENT_GATEWAY_INTEGRATION.md](features/PAYMENT_GATEWAY_INTEGRATION.md) - Payment gateway setup
- [features/TRIAL_CONVERSION_SYSTEM.md](features/TRIAL_CONVERSION_SYSTEM.md) - Complete trial management & automation

**Development**:
- [setup/LOCAL_TENANT_ACCESS_GUIDE.md](setup/LOCAL_TENANT_ACCESS_GUIDE.md) - Local setup
- [setup/NGROK_QUICK_START.md](setup/NGROK_QUICK_START.md) - Webhook testing
- [API_REFERENCE.md](API_REFERENCE.md) - API documentation

**Operations**:
- [operations/DEPLOYMENT.md](operations/DEPLOYMENT.md) - Deployment guide
- [operations/TROUBLESHOOTING.md](operations/TROUBLESHOOTING.md) - Issue resolution
- [operations/NEXT_STEPS_AND_RECOMMENDATIONS.md](operations/NEXT_STEPS_AND_RECOMMENDATIONS.md) - Future work

---

## 📝 Documentation Standards

### **File Naming**
- Use `SCREAMING_SNAKE_CASE.md` for documentation files
- Group related docs in subdirectories
- Keep filenames descriptive and concise

### **Content Structure**
- Start with clear title and overview
- Use markdown headers (H2-H4) for sections
- Include code examples where applicable
- Add table of contents for long documents
- Cross-reference related documentation

### **Maintenance**
- Update documentation when features change
- Mark outdated sections clearly
- Remove obsolete documentation
- Keep examples current with codebase

---

## 🆘 Getting Help

**Can't find what you need?**
1. Check the [main README.md](../README.md) in project root
2. Search across all documentation files
3. Review [API_REFERENCE.md](API_REFERENCE.md) for endpoint details
4. Check [operations/TROUBLESHOOTING.md](operations/TROUBLESHOOTING.md) for common issues

**For specific topics**:
- **Architecture questions**: [ARCHITECTURE.md](ARCHITECTURE.md)
- **API usage**: [API_REFERENCE.md](API_REFERENCE.md)
- **Deployment issues**: [operations/DEPLOYMENT.md](operations/DEPLOYMENT.md)
- **Payment integration**: [features/PAYMENT_GATEWAY_INTEGRATION.md](features/PAYMENT_GATEWAY_INTEGRATION.md)

---

**Last Updated**: 2025-11-05
**Documentation Version**: 2.0
**System Version**: Laravel 10.49.1
