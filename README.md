# Insurance Admin Panel

> Comprehensive insurance management system built with Laravel 10 for managing customers, policies, claims, quotations, and more.

[![Laravel](https://img.shields.io/badge/Laravel-10.49-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2.12-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Documentation](#documentation)
- [Key Features](#key-features)
- [Security](#security)
- [Development](#development)
- [Contributing](#contributing)
- [Support](#support)

---

## 🎯 Overview

**Insurance Admin Panel** is a full-featured Laravel application designed for insurance agencies to manage their entire operations including:

- 👥 Customer & Family Group Management
- 📋 Insurance Policy Management
- 💰 Claims Processing
- 📊 Quotation Generation
- 💼 Broker & RM Management
- 📧 Automated Notifications (Email & WhatsApp)
- 📈 Reports & Exports
- 🔐 Multi-Guard Authentication (Admin + Customer Portal)
- 🛡️ Two-Factor Authentication (2FA)

---

## ✨ Features

### Core Modules
- **Customer Management** - Complete customer lifecycle with family grouping
- **Policy Management** - Multi-type insurance policies (Motor, Health, Life, Fire, Marine)
- **Claims Processing** - End-to-end claim tracking and management
- **Quotation System** - Professional quotation generation with PDF export
- **Broker Management** - Broker relationships and commission tracking
- **Reference Users** - Customer referral system
- **Relationship Managers** - RM assignment and tracking

### Advanced Features
- **🔧 App Settings Infrastructure** - Database-driven configuration with encryption
- **📊 Generic Export System** - Professional Excel exports with styling
- **📱 WhatsApp Integration** - Automated notifications and reminders
- **📧 Email Notifications** - Customizable email templates
- **🎂 Birthday Wishes** - Automated birthday greetings
- **⏰ Renewal Reminders** - Multi-day reminder system (30/15/7/1 days)
- **🔐 Two-Factor Authentication** - Enhanced security with 2FA
- **📱 Device Trust Management** - Remember trusted devices
- **🔍 Activity Logging** - Comprehensive audit trail
- **📄 PDF Generation** - Quotations, policies, and reports

### Security Features
- Multi-guard authentication (Admin + Customer)
- Two-factor authentication with recovery codes
- Encrypted sensitive settings (WhatsApp tokens, SMTP credentials)
- Role-based access control (via Spatie Laravel Permission)
- Activity logging (via Spatie Laravel Activitylog)
- CSRF protection
- Session management

---

## 🛠️ Tech Stack

### Backend
- **Framework**: Laravel 10.49
- **PHP**: 8.2.12
- **Database**: MySQL 8.0+
- **Authentication**: Laravel Sanctum + Multi-Guard

### Frontend
- **UI Framework**: Laravel UI (Bootstrap)
- **JavaScript**: Vanilla JS + jQuery
- **CSS Framework**: Bootstrap 5
- **Icons**: Font Awesome

### Key Packages
| Package                          | Purpose                           |
| -------------------------------- | --------------------------------- |
| `maatwebsite/excel`              | Excel export/import functionality |
| `barryvdh/laravel-dompdf`        | PDF generation                    |
| `simplesoftwareio/simple-qrcode` | QR code generation for 2FA        |
| `pragmarx/google2fa-laravel`     | Two-factor authentication         |
| `spatie/laravel-permission`      | Role & permission management      |
| `spatie/laravel-activitylog`     | Activity logging                  |
| `guzzlehttp/guzzle`              | HTTP client for WhatsApp API      |
| `opcodesio/log-viewer`           | Log viewing interface             |

---

## 📦 Requirements

- PHP >= 8.1
- Composer
- MySQL >= 8.0 or MariaDB >= 10.3
- Node.js & NPM (for asset compilation)
- Apache/Nginx web server

**Recommended:**
- PHP 8.2+
- MySQL 8.0+
- Composer 2.x

---

## 🚀 Installation

### 1. Clone Repository
```bash
git clone <repository-url> insurance-midas-portal
cd insurance-midas-portal
```

### 2. Install Dependencies
```bash
# PHP dependencies
composer install

# JavaScript dependencies
npm install
```

### 3. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Configuration
Edit `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=admin_panel
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Database Migration & Seeding
```bash
# Run migrations
php artisan migrate

# Seed database with initial data
php artisan db:seed

# Seed app settings
php artisan db:seed --class=AppSettingsSeeder
```

### 6. Build Assets
```bash
npm run dev
# or for production
npm run build
```

### 7. Storage Link
```bash
php artisan storage:link
```

### 8. Start Development Server
```bash
php artisan serve
```

Application will be available at: `http://localhost:8000`

---

## ⚙️ Configuration

### App Settings System

The application uses a database-driven configuration system. Key settings can be managed via:

```php
use App\Services\AppSettingService;

// Get setting
$timezone = AppSettingService::get('app_timezone');

// Update setting
AppSettingService::set('app_name', 'My Insurance Co');

// Encrypted setting
AppSettingService::setEncrypted('whatsapp_auth_token', 'token-here');
```

**Available Categories:**
- Application (9 settings) - App name, timezone, locale, currency, date/time formats
- WhatsApp (3 settings) - API credentials and configuration
- Mail (8 settings) - SMTP configuration
- Notifications (4 settings) - Notification preferences

See [APP_SETTINGS_DOCUMENTATION.md](APP_SETTINGS_DOCUMENTATION.md) for complete details.

### WhatsApp Configuration

Update in database via Tinker:
```bash
php artisan tinker
>>> AppSettingService::set('whatsapp_sender_id', '919727793123');
>>> AppSettingService::setEncrypted('whatsapp_auth_token', 'your-token');
>>> AppSettingService::set('whatsapp_base_url', 'https://api.botmastersender.com/api/v1/');
```

### Mail Configuration

Configure SMTP in App Settings or update `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
```

---

## 📚 Documentation

### 📖 Complete Documentation (20+ Files)
All detailed documentation is located in the `/claudedocs/` directory.

#### 🎯 Quick Start Guides
| Document                                                            | Description                                         | Best For           |
| ------------------------------------------------------------------- | --------------------------------------------------- | ------------------ |
| [**DOCUMENTATION_INDEX.md**](claudedocs/DOCUMENTATION_INDEX.md)     | 📇 **Master Index** - Find any documentation quickly | Everyone           |
| [**PROJECT_DOCUMENTATION.md**](claudedocs/PROJECT_DOCUMENTATION.md) | 📘 **Master Reference** - Complete system overview   | New developers     |
| [**MODULES.md**](claudedocs/MODULES.md)                             | 📦 **All Modules** - 25+ modules reference           | Module development |

#### 🗂️ Core Documentation
| Category            | Documents Available                                                                                                                                                       |
| ------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **System**          | [SYSTEM_ARCHITECTURE.md](claudedocs/SYSTEM_ARCHITECTURE.md) - Complete architecture                                                                                       |
| **Database**        | [DATABASE_DOCUMENTATION.md](claudedocs/DATABASE_DOCUMENTATION.md), [SEEDERS_GUIDE.md](claudedocs/SEEDERS_GUIDE.md), [SEEDERS_ANALYSIS.md](claudedocs/SEEDERS_ANALYSIS.md) |
| **Customer Portal** | [CUSTOMER_PORTAL_GUIDE.md](claudedocs/CUSTOMER_PORTAL_GUIDE.md), [CUSTOMER_PORTAL_QUICK_REFERENCE.md](claudedocs/CUSTOMER_PORTAL_QUICK_REFERENCE.md)                      |
| **Infrastructure**  | [APP_SETTINGS_DOCUMENTATION.md](claudedocs/APP_SETTINGS_DOCUMENTATION.md), [IMPLEMENTATION_GUIDE.md](claudedocs/IMPLEMENTATION_GUIDE.md)                                  |
| **Operations**      | [BACKGROUND_JOBS.md](claudedocs/BACKGROUND_JOBS.md), [DEPLOYMENT_SUMMARY.md](claudedocs/DEPLOYMENT_SUMMARY.md)                                                            |

> 💡 **Tip**: Start with [DOCUMENTATION_INDEX.md](claudedocs/DOCUMENTATION_INDEX.md) to navigate all 20+ documentation files, or go directly to [PROJECT_DOCUMENTATION.md](claudedocs/PROJECT_DOCUMENTATION.md) for a complete system overview.

---

## 🎨 Key Features

### 1. Customer Management
- Individual & family group management
- Customer portal for policy viewing
- Reference user tracking
- Activity logging

### 2. Policy Management
- Multiple policy types (Motor, Health, Life, Fire, Marine)
- Addon covers support
- Premium calculation
- Automatic renewal tracking

### 3. Claims Processing
- Claim creation & tracking
- Status management
- Document uploads
- Notification system

### 4. Quotation System
- Professional quotation generation
- PDF export with branding
- Email quotations to customers
- Conversion tracking

### 5. Export System
Professional Excel exports with:
- Blue header styling
- Alternating row colors
- Auto-sized columns
- Relationship mapping (names, not IDs)
- Filter support (search, date range, status)

**Usage:**
```php
// In any controller with ExportableTrait
use App\Traits\ExportableTrait;

class CustomerController extends Controller {
    use ExportableTrait;
}

// Export URLs
/customers/export
/customers/export?search=john
/customers/export?start_date=2025-01-01&end_date=2025-12-31
```

### 6. Notification System
- Email notifications for key events
- WhatsApp notifications via Botmaster API
- Renewal reminders (configurable days: 30, 15, 7, 1)
- Birthday wishes
- Claim updates
- Policy expiry alerts

### 7. Two-Factor Authentication
- QR code-based 2FA setup
- Recovery codes (10 codes)
- Trusted device management
- Multi-guard support (Admin + Customer)
- Session-based guard detection

---

## 🔐 Security

### Authentication
- Multi-guard system (Admin + Customer portals)
- Two-factor authentication
- Encrypted passwords (bcrypt)
- Session management
- CSRF protection

### Data Protection
- Encrypted sensitive settings (WhatsApp tokens, SMTP credentials)
- AES-256-CBC encryption via Laravel Crypt
- Activity logging for audit trails
- Role-based access control

### Best Practices
- Never commit `.env` file
- Rotate `APP_KEY` carefully (breaks encrypted data)
- Use strong passwords for admin accounts
- Enable 2FA for all admin users
- Regular database backups
- Keep dependencies updated

---

## 👨‍💻 Development

### Project Structure
```
midas-portal/
├── app/
│   ├── Console/Commands/      # Artisan commands (renewal reminders, birthday wishes)
│   ├── Exports/               # Export classes
│   ├── Helpers/               # Helper functions
│   ├── Http/Controllers/      # Controllers
│   ├── Models/                # Eloquent models
│   ├── Providers/             # Service providers
│   ├── Services/              # Business logic services
│   └── Traits/                # Reusable traits
├── database/
│   ├── migrations/            # Database migrations
│   ├── seeders/               # Database seeders
│   └── sql/                   # SQL deployment files
├── resources/
│   └── views/                 # Blade templates
├── public/                    # Public assets
├── routes/                    # Route definitions
└── storage/                   # File storage & logs
```

### Running Tests
```bash
php artisan test
```

### Code Style
Follow PSR-12 coding standards:
```bash
# Install PHP Code Sniffer
composer require --dev squizlabs/php_codesniffer

# Check code style
./vendor/bin/phpcs

# Fix code style
./vendor/bin/phpcbf
```

### Database Migrations
```bash
# Create migration
php artisan make:migration create_table_name

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Reset database
php artisan migrate:fresh --seed
```

### Artisan Commands
```bash
# Generate migration from existing database
php artisan generate:migrations

# IDE Helper (autocomplete)
php artisan ide-helper:generate
php artisan ide-helper:models

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

### Commit Message Guidelines
- Use present tense ("Add feature" not "Added feature")
- Use imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit first line to 72 characters
- Reference issues and pull requests

---

## 📞 Support

For support and questions:
- 📧 Email: support@insuranceadmin.com
- 📖 Documentation: See `/docs` directory
- 🐛 Issues: GitHub Issues

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- Laravel Framework
- Spatie Packages
- Maatwebsite Excel
- DomPDF
- All open-source contributors

---

## 📊 Project Status

**Version**: 1.0.0
**Status**: ✅ Production Ready
**Last Updated**: 2025-10-06

### Recent Updates
- ✅ App Settings Infrastructure (24 settings, 100% usage)
- ✅ Generic Export System
- ✅ Two-Factor Authentication with multi-guard support
- ✅ Birthday Wishes Feature
- ✅ Encrypted Settings for Sensitive Data
- ✅ Comprehensive Documentation

### Upcoming Features
- [ ] Multi-currency support
- [ ] Advanced reporting dashboard
- [ ] API for mobile app integration
- [ ] Multi-tenancy (SaaS mode)
- [ ] Payment gateway integration

---

**Built with ❤️ using Laravel**
