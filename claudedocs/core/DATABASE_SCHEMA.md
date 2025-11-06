# Database Schema Documentation

**Project**: Midas Insurance Portal
**Database Engine**: MySQL 8.4
**Total Tables**: 70+ tables
**Architecture**: Multi-Tenant (Database-Per-Tenant)
**Last Updated**: 2025-11-06

---

## Table of Contents

1. [Database Architecture Overview](#database-architecture-overview)
2. [Central Database Schema](#central-database-schema)
3. [Tenant Database Schema](#tenant-database-schema)
4. [Entity Relationship Diagrams](#entity-relationship-diagrams)
5. [Table Relationships](#table-relationships)
6. [Indexes and Performance](#indexes-and-performance)
7. [Data Migration Strategy](#data-migration-strategy)

---

## Database Architecture Overview

### Database Separation Model

The Midas Portal uses a **Database-Per-Tenant** architecture with two distinct database types:

```
┌─────────────────────────────────────────────────────────┐
│                   CENTRAL DATABASE                       │
│  Database: central                                       │
│  Purpose: Platform management, billing, tenant metadata │
│  Tables: 9 tables                                        │
│  Scope: Cross-tenant data                                │
└─────────────────────────────────────────────────────────┘
                           │
                           │ manages
                           ↓
┌─────────────────────────────────────────────────────────┐
│              TENANT DATABASES (Isolated)                 │
│  Databases: tenant_1, tenant_2, ..., tenant_N           │
│  Purpose: Business operations, customer data             │
│  Tables: 61+ tables per tenant                           │
│  Scope: Single tenant data (complete isolation)         │
└─────────────────────────────────────────────────────────┘
```

### Connection Configuration

**File**: `config/database.php`

```php
'connections' => [
    // Central database connection
    'central' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'database' => 'central',
        'username' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD'),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],

    // Tenant database connection (dynamically set)
    'tenant' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'database' => null, // Set dynamically: tenant_{id}
        'username' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD'),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
],
```

### Migration Organization

**Total Migrations**: 72 migrations

**Directory Structure**:
```
database/migrations/
├── central/           (11 migrations - Central database)
│   ├── 2019_09_15_000010_create_tenants_table.php
│   ├── 2019_09_15_000020_create_domains_table.php
│   ├── 2025_11_02_000001_create_plans_table.php
│   ├── 2025_11_02_000002_create_subscriptions_table.php
│   ├── 2025_11_02_000003_create_tenant_users_table.php
│   ├── 2025_11_02_000004_create_audit_logs_table.php
│   ├── 2025_11_03_152035_create_contact_submissions_table.php
│   ├── 2025_11_05_140330_update_billing_interval_enum_in_plans_table.php
│   ├── 2025_11_05_141822_add_payment_method_to_subscriptions_table.php
│   └── 2025_11_05_143507_create_payments_table.php
│
└── tenant/            (61 migrations - Tenant databases)
    ├── Core System (13 tables)
    ├── Customer Management (6 tables)
    ├── Insurance Policies (10 tables)
    ├── Claims Management (4 tables)
    ├── Lead Management (9 tables)
    ├── Security & Auth (8 tables)
    ├── Notifications (4 tables)
    └── Master Data (7 tables)
```

---

## Central Database Schema

**Database Name**: `central`
**Purpose**: Platform management, tenant metadata, billing, subscriptions
**Total Tables**: 9 tables

### Table Catalog

| Table | Purpose | Row Count (Est) | Storage Size (Est) |
|-------|---------|-----------------|-------------------|
| `tenants` | Tenant registry | 100-1000 | Small (< 1 MB) |
| `domains` | Subdomain mappings | 100-1000 | Small (< 1 MB) |
| `plans` | Pricing tiers | 3-10 | Tiny (< 100 KB) |
| `subscriptions` | Billing records | 100-1000 | Small (< 2 MB) |
| `payments` | Payment transactions | 1000-10000 | Medium (2-10 MB) |
| `tenant_users` | Central admin users | 5-50 | Tiny (< 1 MB) |
| `audit_logs` | Platform audit trail | 10000+ | Large (10+ MB) |
| `contact_submissions` | Website contact forms | 100-1000 | Small (< 1 MB) |
| `migrations` | Migration history | 72 | Tiny (< 10 KB) |

---

### 1. tenants

**Purpose**: Master registry of all tenants in the system

**Columns**:
```sql
CREATE TABLE tenants (
    id VARCHAR(255) PRIMARY KEY,           -- Unique tenant identifier (UUID)
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    data JSON NULL                         -- Flexible metadata storage
);
```

**Data Column Structure** (JSON):
```json
{
    "company_name": "Acme Insurance Brokers",
    "subdomain": "acme",
    "database_name": "tenant_acme_5abc123",
    "email": "admin@acme.com",
    "phone": "+919876543210",
    "status": "active",
    "settings": {
        "timezone": "Asia/Kolkata",
        "currency": "INR",
        "locale": "en_IN"
    }
}
```

**Indexes**:
- PRIMARY KEY (`id`)

**Relationships**:
- Has One: `subscription`
- Has Many: `domains`
- Has Many: `payments`
- Has Many: `audit_logs`

---

### 2. domains

**Purpose**: Map custom domains/subdomains to tenants

**Columns**:
```sql
CREATE TABLE domains (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    domain VARCHAR(255) UNIQUE NOT NULL,   -- e.g., "acme.midastech.in"
    tenant_id VARCHAR(255) NOT NULL,       -- Foreign key to tenants.id
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_domain (domain),
    INDEX idx_tenant_id (tenant_id)
);
```

**Example Data**:
```
id | domain                          | tenant_id
---+---------------------------------+------------
1  | acme.midastech.in              | tenant_1
2  | globeinsurance.midastech.in    | tenant_2
3  | customdomain.com               | tenant_3
```

**Relationships**:
- Belongs To: `tenant`

---

### 3. plans

**Purpose**: Subscription pricing tiers and feature limits

**Columns**:
```sql
CREATE TABLE plans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,            -- "Starter", "Professional", "Enterprise"
    slug VARCHAR(255) UNIQUE NOT NULL,     -- "starter", "professional", "enterprise"
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL,          -- Monthly price (e.g., 999.00)
    billing_interval ENUM('monthly', 'yearly') DEFAULT 'monthly',
    features JSON NULL,                    -- ["Feature 1", "Feature 2", ...]

    -- Limits
    max_users INT DEFAULT -1,              -- -1 = unlimited
    max_customers INT DEFAULT -1,
    max_leads_per_month INT DEFAULT -1,
    storage_limit_gb INT DEFAULT 5,

    -- Metadata
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    metadata JSON NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active)
);
```

**Example Data**:
```sql
INSERT INTO plans (name, slug, price, max_users, max_customers, max_leads_per_month) VALUES
('Starter', 'starter', 999.00, 5, 1000, 500),
('Professional', 'professional', 2999.00, 20, 10000, 5000),
('Enterprise', 'enterprise', 9999.00, -1, -1, -1);
```

**Features JSON Structure**:
```json
[
    "Customer Management",
    "Policy Tracking",
    "Quotation System",
    "Claims Management",
    "Email Notifications",
    "WhatsApp Integration",
    "Advanced Reports",
    "Multi-user Access",
    "API Access",
    "Priority Support"
]
```

**Relationships**:
- Has Many: `subscriptions`

---

### 4. subscriptions

**Purpose**: Track tenant subscriptions, billing status, trials

**Columns**:
```sql
CREATE TABLE subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id VARCHAR(255) NOT NULL,
    plan_id BIGINT UNSIGNED NOT NULL,

    -- Status
    status ENUM('trial', 'active', 'cancelled', 'expired', 'past_due', 'suspended') DEFAULT 'trial',

    -- Trial
    is_trial BOOLEAN DEFAULT TRUE,
    trial_ends_at TIMESTAMP NULL,

    -- Billing
    starts_at TIMESTAMP NULL,
    ends_at TIMESTAMP NULL,
    next_billing_date TIMESTAMP NULL,
    mrr DECIMAL(10,2) DEFAULT 0.00,        -- Monthly Recurring Revenue

    -- Payment Gateway
    payment_gateway VARCHAR(255) NULL,     -- "stripe", "razorpay", "manual"
    gateway_subscription_id VARCHAR(255) NULL,
    gateway_customer_id VARCHAR(255) NULL,
    payment_method JSON NULL,              -- {"type": "card", "last4": "1234", "brand": "visa"}

    -- Cancellation
    cancelled_at TIMESTAMP NULL,
    cancellation_reason VARCHAR(255) NULL,

    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE RESTRICT,

    INDEX idx_tenant_id (tenant_id),
    INDEX idx_status (status),
    INDEX idx_trial_ends_at (trial_ends_at),
    INDEX idx_next_billing_date (next_billing_date)
);
```

**Status Flow**:
```
trial → active → [past_due | cancelled | expired | suspended]
  ↓
active (if payment successful before trial_ends_at)
```

**Relationships**:
- Belongs To: `tenant`
- Belongs To: `plan`
- Has Many: `payments`

---

### 5. payments

**Purpose**: Payment transaction history for all tenants

**Columns**:
```sql
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id VARCHAR(255) NOT NULL,
    subscription_id BIGINT UNSIGNED NOT NULL,

    payment_gateway VARCHAR(255) NOT NULL, -- "razorpay", "stripe", "bank_transfer"
    gateway_payment_id VARCHAR(255) UNIQUE NULL,  -- "pay_xxx" (Razorpay), "pi_xxx" (Stripe)
    gateway_order_id VARCHAR(255) NULL,    -- "order_xxx" (Razorpay)

    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'INR',
    status ENUM('pending', 'processing', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    type ENUM('subscription', 'renewal', 'upgrade', 'addon') DEFAULT 'subscription',

    description TEXT NULL,
    gateway_response JSON NULL,            -- Full webhook payload
    metadata JSON NULL,

    paid_at TIMESTAMP NULL,
    failed_at TIMESTAMP NULL,
    refunded_at TIMESTAMP NULL,
    failure_reason TEXT NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE,

    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_subscription_status (subscription_id, status),
    INDEX idx_payment_gateway (payment_gateway),
    INDEX idx_created_at (created_at)
);
```

**Status Transitions**:
```
pending → processing → completed
         ↓             ↓
       failed       refunded
```

**Relationships**:
- Belongs To: `tenant`
- Belongs To: `subscription`

---

### 6. tenant_users

**Purpose**: Central admin user accounts (super admins, support, billing)

**Columns**:
```sql
CREATE TABLE tenant_users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,

    -- Role Flags
    is_super_admin BOOLEAN DEFAULT FALSE,
    is_support_admin BOOLEAN DEFAULT FALSE,
    is_billing_admin BOOLEAN DEFAULT FALSE,

    -- Contact
    phone VARCHAR(20) NULL,
    avatar TEXT NULL,

    -- Login Tracking
    last_login_at TIMESTAMP NULL,
    last_login_ip VARCHAR(45) NULL,

    -- Two-Factor Auth
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret TEXT NULL,
    two_factor_recovery_codes TEXT NULL,

    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    INDEX idx_email (email),
    INDEX idx_is_super_admin (is_super_admin),
    INDEX idx_is_active (is_active)
);
```

**Relationships**:
- Has Many: `audit_logs` (as performer)

---

### 7. audit_logs (Central)

**Purpose**: Audit trail for all central admin actions

**Columns**:
```sql
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_user_id BIGINT UNSIGNED NULL,   -- Who performed the action
    tenant_id VARCHAR(255) NULL,           -- Which tenant was affected

    action VARCHAR(255) NOT NULL,          -- "created", "updated", "deleted", "suspended"
    description TEXT NULL,
    details JSON NULL,                     -- {"old": {...}, "new": {...}}

    subject_type VARCHAR(255) NULL,        -- "Tenant", "Subscription", "Payment"
    subject_id VARCHAR(255) NULL,

    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_tenant_user_id (tenant_user_id),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_action (action),
    INDEX idx_subject (subject_type, subject_id),
    INDEX idx_created_at (created_at)
);
```

**Example Entries**:
```json
{
    "tenant_user_id": 1,
    "tenant_id": "tenant_1",
    "action": "suspended",
    "description": "Suspended tenant due to payment failure",
    "details": {
        "old_status": "active",
        "new_status": "suspended",
        "reason": "Payment failed 3 times"
    },
    "subject_type": "Tenant",
    "subject_id": "tenant_1"
}
```

**Relationships**:
- Belongs To: `tenant_user`
- Belongs To: `tenant`

---

### 8. contact_submissions

**Purpose**: Contact form submissions from public website

**Columns**:
```sql
CREATE TABLE contact_submissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    company VARCHAR(255) NULL,
    message TEXT NOT NULL,

    status ENUM('new', 'in_progress', 'resolved', 'spam') DEFAULT 'new',
    assigned_to BIGINT UNSIGNED NULL,      -- tenant_users.id
    notes TEXT NULL,

    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    referrer TEXT NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    INDEX idx_status (status),
    INDEX idx_email (email),
    INDEX idx_created_at (created_at)
);
```

**Relationships**:
- Belongs To: `tenant_user` (as assigned_to)

---

### Central Database ER Diagram

```
┌─────────────┐
│   tenants   │───┐
└─────────────┘   │
       │          │ 1:N
       │ 1:N      ↓
       │      ┌──────────┐
       │      │ domains  │
       │      └──────────┘
       │
       │ 1:1
       ↓
┌──────────────────┐
│  subscriptions   │
└──────────────────┘
       │ N:1
       ↓
  ┌─────────┐
  │  plans  │
  └─────────┘

┌──────────────────┐
│  subscriptions   │───┐
└──────────────────┘   │ 1:N
                       ↓
                  ┌──────────┐
                  │ payments │
                  └──────────┘

┌──────────────┐      ┌─────────────┐
│ tenant_users │─────→│ audit_logs  │
└──────────────┘ 1:N  └─────────────┘
                           │ N:1
                           ↓
                      ┌─────────────┐
                      │   tenants   │
                      └─────────────┘

┌─────────────────────────┐
│  contact_submissions    │
└─────────────────────────┘
```

---

## Tenant Database Schema

**Database Pattern**: `tenant_{id}` (e.g., `tenant_acme_5abc123`)
**Purpose**: Complete business operations for single tenant
**Total Tables**: 61+ tables per tenant
**Isolation**: Complete data isolation - no cross-tenant queries possible

### Table Categories

| Category | Tables | Purpose |
|----------|--------|---------|
| **Core System** | 13 | Users, roles, permissions, settings, sessions |
| **Customer Management** | 6 | Customers, family groups, types, devices |
| **Insurance Policies** | 10 | Policies, quotations, companies, types, addons |
| **Claims Management** | 4 | Claims, stages, documents, liability |
| **Lead Management** | 9 | Leads, activities, documents, WhatsApp campaigns |
| **Security & Auth** | 8 | 2FA, device tracking, trusted devices, security events |
| **Notifications** | 4 | Templates, logs, types, delivery tracking |
| **Master Data** | 7 | Branches, brokers, fuel types, policy types, etc. |

**Total**: 61 tables

---

### Core System Tables (13 tables)

#### 1. users

**Purpose**: Tenant staff/admin user accounts

**Columns**:
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,

    phone VARCHAR(20) NULL,
    avatar TEXT NULL,
    designation VARCHAR(255) NULL,
    employee_id VARCHAR(50) NULL,

    branch_id BIGINT UNSIGNED NULL,

    status BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP NULL,
    last_login_ip VARCHAR(45) NULL,

    -- Protection
    is_protected BOOLEAN DEFAULT FALSE,
    protected_reason VARCHAR(255) NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (branch_id) REFERENCES branches(id),
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_is_protected (is_protected)
);
```

**Relationships**:
- Belongs To: `branch`
- Has Many Roles (via `model_has_roles`)
- Has Many Permissions (via `model_has_permissions`)
- Has Many: `leads` (as assigned_to)
- Has Many: `audit_logs`

---

#### 2-4. Spatie Permission Tables

**roles**: Role definitions (Admin, Manager, Agent, etc.)
**permissions**: Permission definitions (customer-create, policy-view, etc.)
**model_has_roles**: User-Role pivot table
**model_has_permissions**: User-Permission pivot table
**role_has_permissions**: Role-Permission pivot table

Standard Spatie Laravel Permission package tables.

---

#### 5. app_settings

**Purpose**: Tenant-specific system configuration

**Columns**:
```sql
CREATE TABLE app_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key VARCHAR(255) UNIQUE NOT NULL,      -- e.g., "company_name", "email_signature"
    value TEXT NULL,
    type VARCHAR(50) DEFAULT 'string',     -- string, number, boolean, json
    category VARCHAR(100) NULL,            -- "general", "email", "notification", "security"
    description TEXT NULL,
    is_public BOOLEAN DEFAULT FALSE,       -- Can be accessed without auth
    is_editable BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_key (key),
    INDEX idx_category (category)
);
```

**Example Settings**:
```sql
INSERT INTO app_settings (key, value, category) VALUES
('company_name', 'Acme Insurance Brokers', 'general'),
('company_logo', '/storage/logos/acme.png', 'general'),
('email_from_address', 'no-reply@acme.com', 'email'),
('whatsapp_enabled', 'true', 'notification'),
('max_upload_size_mb', '10', 'general');
```

**Relationships**: None (key-value store)

---

### Customer Management Tables (6 tables)

#### 6. customers

**Purpose**: Customer master data

**Columns** (30+ columns):
```sql
CREATE TABLE customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_number VARCHAR(50) UNIQUE NOT NULL, -- Auto: CUST-202501-0001

    -- Personal Information
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    mobile_number VARCHAR(20) NOT NULL,
    alternate_mobile VARCHAR(20) NULL,
    date_of_birth DATE NULL,
    gender ENUM('male', 'female', 'other') NULL,

    -- Address
    address TEXT NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    pincode VARCHAR(10) NULL,
    country VARCHAR(100) DEFAULT 'India',

    -- Business Information
    customer_type_id BIGINT UNSIGNED NULL,
    company_name VARCHAR(255) NULL,
    gst_number VARCHAR(15) NULL,
    pan_number VARCHAR(10) NULL,
    aadhar_number VARCHAR(12) NULL,

    -- Family & Grouping
    family_group_id BIGINT UNSIGNED NULL,
    is_family_head BOOLEAN DEFAULT FALSE,

    -- Documents
    pan_card_file TEXT NULL,
    aadhar_card_file TEXT NULL,
    gst_certificate_file TEXT NULL,

    -- Special Dates
    anniversary_date DATE NULL,

    -- Lead Conversion
    converted_from_lead_id BIGINT UNSIGNED NULL,

    -- Authentication (for customer portal)
    password VARCHAR(255) NULL,
    email_verified_at TIMESTAMP NULL,

    -- Notification Preferences
    whatsapp_opt_in BOOLEAN DEFAULT TRUE,
    email_opt_in BOOLEAN DEFAULT TRUE,
    sms_opt_in BOOLEAN DEFAULT TRUE,

    -- Protection
    is_protected BOOLEAN DEFAULT FALSE,
    protected_reason VARCHAR(255) NULL,

    status BOOLEAN DEFAULT TRUE,
    remarks TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (customer_type_id) REFERENCES customer_types(id),
    FOREIGN KEY (family_group_id) REFERENCES family_groups(id),
    FOREIGN KEY (converted_from_lead_id) REFERENCES leads(id),

    INDEX idx_customer_number (customer_number),
    INDEX idx_email (email),
    INDEX idx_mobile_number (mobile_number),
    INDEX idx_status (status),
    INDEX idx_family_group_id (family_group_id),
    INDEX idx_is_protected (is_protected)
);
```

**Relationships**:
- Belongs To: `customer_type`
- Belongs To: `family_group`
- Belongs To: `lead` (as converted_from_lead_id)
- Has Many: `customer_insurances`
- Has Many: `quotations`
- Has Many: `claims`
- Has Many: `customer_devices`

---

#### 7. family_groups

**Purpose**: Group family members for shared policy viewing

**Columns**:
```sql
CREATE TABLE family_groups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_name VARCHAR(255) NOT NULL,
    primary_customer_id BIGINT UNSIGNED NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (primary_customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_primary_customer (primary_customer_id)
);
```

**Relationships**:
- Belongs To: `customer` (as primary_customer_id)
- Has Many: `customers`

---

#### 8. customer_types

**Purpose**: Customer categorization (Individual, Corporate, Broker, etc.)

**Columns**:
```sql
CREATE TABLE customer_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships**:
- Has Many: `customers`

---

### Insurance Policy Tables (10 tables)

#### 9. customer_insurances

**Purpose**: Insurance policy records

**Columns** (40+ columns):
```sql
CREATE TABLE customer_insurances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    policy_number VARCHAR(100) UNIQUE NOT NULL,

    -- Relationships
    customer_id BIGINT UNSIGNED NOT NULL,
    insurance_company_id BIGINT UNSIGNED NOT NULL,
    policy_type_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NULL,
    broker_id BIGINT UNSIGNED NULL,

    -- Policy Dates
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    issue_date DATE NULL,
    registration_date DATE NULL,

    -- Premium Details
    premium_amount DECIMAL(10,2) NOT NULL,
    premium_type_id BIGINT UNSIGNED NULL,
    gst_amount DECIMAL(10,2) NULL,
    net_premium DECIMAL(10,2) NOT NULL,

    -- Commission Breakdown
    own_commission DECIMAL(10,2) NULL,
    transfer_commission DECIMAL(10,2) NULL,
    reference_commission DECIMAL(10,2) NULL,
    relationship_manager_id BIGINT UNSIGNED NULL,
    reference_user_id BIGINT UNSIGNED NULL,

    -- Vehicle Details (for vehicle insurance)
    vehicle_number VARCHAR(20) NULL,
    make_model_variant VARCHAR(255) NULL,
    chassis_number VARCHAR(50) NULL,
    engine_number VARCHAR(50) NULL,
    fuel_type_id BIGINT UNSIGNED NULL,
    cubic_capacity INT NULL,
    seating_capacity INT NULL,
    manufacturing_year INT NULL,

    -- NCB & IDV
    previous_ncb DECIMAL(5,2) DEFAULT 0,
    current_ncb DECIMAL(5,2) DEFAULT 0,
    idv_amount DECIMAL(10,2) NULL,

    -- Documents
    policy_document TEXT NULL,
    rc_copy TEXT NULL,
    previous_policy_copy TEXT NULL,

    -- Renewal Tracking
    renewal_of_policy_id BIGINT UNSIGNED NULL,
    is_renewed BOOLEAN DEFAULT FALSE,
    renewed_to_policy_id BIGINT UNSIGNED NULL,

    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    remarks TEXT NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    -- Foreign Keys
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (insurance_company_id) REFERENCES insurance_companies(id),
    FOREIGN KEY (policy_type_id) REFERENCES policy_types(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (broker_id) REFERENCES brokers(id),
    FOREIGN KEY (premium_type_id) REFERENCES premium_types(id),
    FOREIGN KEY (fuel_type_id) REFERENCES fuel_types(id),
    FOREIGN KEY (relationship_manager_id) REFERENCES relationship_managers(id),
    FOREIGN KEY (reference_user_id) REFERENCES reference_users(id),
    FOREIGN KEY (renewal_of_policy_id) REFERENCES customer_insurances(id),
    FOREIGN KEY (renewed_to_policy_id) REFERENCES customer_insurances(id),

    -- Indexes
    INDEX idx_policy_number (policy_number),
    INDEX idx_customer_id (customer_id),
    INDEX idx_insurance_company_id (insurance_company_id),
    INDEX idx_policy_type_id (policy_type_id),
    INDEX idx_status (status),
    INDEX idx_end_date (end_date),
    INDEX idx_vehicle_number (vehicle_number)
);
```

**Relationships**:
- Belongs To: `customer`
- Belongs To: `insurance_company`
- Belongs To: `policy_type`
- Belongs To: `branch`
- Belongs To: `broker`
- Belongs To: `premium_type`
- Belongs To: `fuel_type`
- Belongs To: `relationship_manager`
- Belongs To: `reference_user`
- Belongs To: `customer_insurance` (as renewal_of_policy_id)
- Has Many: `claims`

---

#### 10-11. Quotation Tables

**quotations**: Quote requests from customers
**quotation_companies**: Multi-company quote comparison
**quotation_statuses**: Workflow statuses for quotations

---

### Lead Management Tables (9 tables)

#### 12. leads

**Purpose**: Lead capture and conversion tracking

**Columns** (30+ columns):
```sql
CREATE TABLE leads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_number VARCHAR(50) UNIQUE NOT NULL,   -- LD-202501-0001

    -- Personal Information
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    mobile_number VARCHAR(20) NOT NULL,
    alternate_mobile VARCHAR(20) NULL,

    -- Address
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    pincode VARCHAR(10) NULL,
    address TEXT NULL,

    -- Lead Details
    source_id BIGINT UNSIGNED NOT NULL,
    status_id BIGINT UNSIGNED NOT NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    product_interest VARCHAR(255) NULL,

    -- Assignment
    assigned_to BIGINT UNSIGNED NULL,          -- users.id
    relationship_manager_id BIGINT UNSIGNED NULL,
    reference_user_id BIGINT UNSIGNED NULL,

    -- Follow-up
    next_follow_up_date DATETIME NULL,
    follow_up_completed BOOLEAN DEFAULT FALSE,

    -- Conversion
    converted_to_customer_id BIGINT UNSIGNED NULL,
    converted_at TIMESTAMP NULL,
    is_lost BOOLEAN DEFAULT FALSE,
    lost_reason TEXT NULL,

    -- Protection
    is_protected BOOLEAN DEFAULT FALSE,
    protected_reason VARCHAR(255) NULL,

    remarks TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (source_id) REFERENCES lead_sources(id),
    FOREIGN KEY (status_id) REFERENCES lead_statuses(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (relationship_manager_id) REFERENCES relationship_managers(id),
    FOREIGN KEY (reference_user_id) REFERENCES reference_users(id),
    FOREIGN KEY (converted_to_customer_id) REFERENCES customers(id),

    INDEX idx_lead_number (lead_number),
    INDEX idx_email (email),
    INDEX idx_mobile_number (mobile_number),
    INDEX idx_source_id (source_id),
    INDEX idx_status_id (status_id),
    INDEX idx_priority (priority),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_next_follow_up_date (next_follow_up_date),
    INDEX idx_is_protected (is_protected)
);
```

**Relationships**:
- Belongs To: `lead_source`
- Belongs To: `lead_status`
- Belongs To: `user` (as assigned_to)
- Belongs To: `relationship_manager`
- Belongs To: `reference_user`
- Belongs To: `customer` (as converted_to_customer_id)
- Has Many: `lead_activities`
- Has Many: `lead_documents`
- Has Many: `lead_whatsapp_messages`

---

#### 13-20. Other Lead Tables

**lead_sources**: Lead origin tracking (Website, Referral, Walk-in, etc.)
**lead_statuses**: Workflow statuses (New, Contacted, Qualified, etc.)
**lead_activities**: Activity timeline (calls, meetings, emails)
**lead_documents**: Document attachments
**lead_whatsapp_messages**: WhatsApp message log
**lead_whatsapp_campaigns**: Bulk WhatsApp campaigns
**lead_whatsapp_campaign_leads**: Campaign-lead pivot
**lead_whatsapp_templates**: Reusable message templates

---

### Notification Tables (4 tables)

#### 21. notification_logs

**Purpose**: Track all notifications sent (Email, WhatsApp, SMS, Push)

**Columns**:
```sql
CREATE TABLE notification_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    notification_type_id BIGINT UNSIGNED NOT NULL,
    template_id BIGINT UNSIGNED NULL,

    recipient_type VARCHAR(255) NULL,         -- "Customer", "User"
    recipient_id BIGINT UNSIGNED NULL,
    recipient_email VARCHAR(255) NULL,
    recipient_phone VARCHAR(20) NULL,

    subject VARCHAR(255) NULL,
    content TEXT NULL,
    variables JSON NULL,                      -- Template variable values

    channel ENUM('email', 'whatsapp', 'sms', 'push') NOT NULL,
    status ENUM('pending', 'sent', 'failed', 'delivered', 'read') DEFAULT 'pending',

    sent_at TIMESTAMP NULL,
    failed_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    read_at TIMESTAMP NULL,

    error_message TEXT NULL,
    retry_count INT DEFAULT 0,
    next_retry_at TIMESTAMP NULL,

    gateway_message_id VARCHAR(255) NULL,     -- External ID from gateway
    gateway_response JSON NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (notification_type_id) REFERENCES notification_types(id),
    FOREIGN KEY (template_id) REFERENCES notification_templates(id),

    INDEX idx_recipient (recipient_type, recipient_id),
    INDEX idx_channel_status (channel, status),
    INDEX idx_sent_at (sent_at),
    INDEX idx_next_retry_at (next_retry_at)
);
```

**Relationships**:
- Belongs To: `notification_type`
- Belongs To: `notification_template`
- Has Many: `notification_delivery_tracking`

---

### Security & Auth Tables (8 tables)

#### 22. two_factor_auth

**Purpose**: TOTP two-factor authentication

**Columns**:
```sql
CREATE TABLE two_factor_auth (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_type VARCHAR(255) NOT NULL,          -- "User", "Customer"
    user_id BIGINT UNSIGNED NOT NULL,

    secret VARCHAR(255) NOT NULL,             -- Base32 encoded secret
    recovery_codes TEXT NULL,                 -- JSON array of backup codes

    enabled BOOLEAN DEFAULT FALSE,
    verified BOOLEAN DEFAULT FALSE,
    verified_at TIMESTAMP NULL,

    last_used_at TIMESTAMP NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_user (user_type, user_id),
    INDEX idx_enabled (enabled)
);
```

**Relationships**:
- Polymorphic Belongs To: `user` or `customer`

---

#### 23. device_tracking

**Purpose**: Track and fingerprint user devices

**Columns**:
```sql
CREATE TABLE device_tracking (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_type VARCHAR(255) NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,

    device_fingerprint VARCHAR(255) NOT NULL,
    device_name VARCHAR(255) NULL,
    device_type VARCHAR(50) NULL,             -- "mobile", "tablet", "desktop"

    browser VARCHAR(100) NULL,
    browser_version VARCHAR(50) NULL,
    os VARCHAR(100) NULL,
    os_version VARCHAR(50) NULL,

    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,

    is_trusted BOOLEAN DEFAULT FALSE,
    is_blocked BOOLEAN DEFAULT FALSE,

    first_seen_at TIMESTAMP NULL,
    last_seen_at TIMESTAMP NULL,
    login_count INT DEFAULT 0,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_user (user_type, user_id),
    INDEX idx_fingerprint (device_fingerprint),
    INDEX idx_is_trusted (is_trusted),
    INDEX idx_last_seen_at (last_seen_at)
);
```

**Relationships**:
- Polymorphic Belongs To: `user` or `customer`

---

#### 24-29. Other Security Tables

**two_factor_attempts**: Login attempt logging
**trusted_devices**: Explicitly trusted devices
**security_settings**: Per-user security configuration
**security_events**: Security incident logging
**customer_devices**: Customer-specific device tracking

---

### Master Data Tables (7 tables)

#### 30. branches

**Purpose**: Office/branch locations

**Columns**:
```sql
CREATE TABLE branches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,

    address TEXT NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    pincode VARCHAR(10) NULL,

    phone VARCHAR(20) NULL,
    email VARCHAR(255) NULL,

    manager_id BIGINT UNSIGNED NULL,

    -- Protection
    is_protected BOOLEAN DEFAULT FALSE,
    protected_reason VARCHAR(255) NULL,

    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (manager_id) REFERENCES users(id),
    INDEX idx_code (code),
    INDEX idx_is_active (is_active),
    INDEX idx_is_protected (is_protected)
);
```

**Relationships**:
- Has Many: `users`
- Has Many: `customer_insurances`
- Belongs To: `user` (as manager_id)

---

#### 31-36. Other Master Tables

**brokers**: External broker information
**insurance_companies**: Insurance provider master
**fuel_types**: Vehicle fuel types (Petrol, Diesel, CNG, Electric)
**policy_types**: Policy categories (Vehicle, Life, Health, Property)
**premium_types**: Premium payment frequencies (Monthly, Quarterly, Annual)
**relationship_managers**: RM master data
**reference_users**: Referral user tracking

---

### Tenant Database ER Diagram

```
┌──────────┐
│  users   │───┐
└──────────┘   │
               │ N:1
               ↓
          ┌──────────┐
          │ branches │
          └──────────┘

┌─────────────┐       ┌──────────────┐       ┌────────────────────┐
│   leads     │──────→│  customers   │──────→│ customer_insurances│
└─────────────┘ N:1   └──────────────┘ 1:N   └────────────────────┘
      │                      │                         │
      │ 1:N                  │ 1:N                     │ 1:N
      ↓                      ↓                         ↓
┌──────────────┐      ┌─────────────┐         ┌──────────┐
│lead_activities│      │ quotations  │         │  claims  │
└──────────────┘      └─────────────┘         └──────────┘

┌──────────────┐      ┌──────────────────┐
│   customers  │─────→│ family_groups    │
└──────────────┘ N:1  └──────────────────┘

┌──────────┐      ┌──────────────────┐
│  users   │─────→│ two_factor_auth  │
└──────────┘ 1:1  └──────────────────┘
```

---

## Entity Relationship Diagrams

### Cross-Database Relationships

```
CENTRAL DATABASE                      TENANT DATABASE
┌──────────────┐                      ┌──────────────┐
│   tenants    │                      │    users     │
│              │                      │              │
│ - id (PK)    │                      │ - id (PK)    │
│ - data:      │                      │ - email      │
│   {database} │  ╔══════════════════>│ - password   │
│              │  ║ Context Switch    │              │
└──────────────┘  ║                   └──────────────┘
       │          ║
       │ 1:1      ║ Database connection switches
       ↓          ║ when tenant identified by subdomain
┌──────────────┐  ║
│subscriptions │  ║
└──────────────┘  ║
                  ╚═══════════════════════════════════>

Legend:
════> Database Context Switch (via middleware)
───> Foreign Key Relationship
```

---

## Table Relationships

### Central Database Relationships

```sql
-- tenants → subscriptions (1:1)
subscriptions.tenant_id → tenants.id

-- tenants → domains (1:N)
domains.tenant_id → tenants.id

-- subscriptions → plans (N:1)
subscriptions.plan_id → plans.id

-- subscriptions → payments (1:N)
payments.subscription_id → subscriptions.id

-- payments → tenants (N:1)
payments.tenant_id → tenants.id

-- audit_logs → tenant_users (N:1)
audit_logs.tenant_user_id → tenant_users.id

-- audit_logs → tenants (N:1)
audit_logs.tenant_id → tenants.id
```

### Tenant Database Key Relationships

```sql
-- customers → customer_insurances (1:N)
customer_insurances.customer_id → customers.id

-- customers → family_groups (N:1)
customers.family_group_id → family_groups.id

-- customers → leads (1:1 conversion)
customers.converted_from_lead_id → leads.id

-- leads → customers (1:1 conversion)
leads.converted_to_customer_id → customers.id

-- leads → users (N:1 assignment)
leads.assigned_to → users.id

-- customer_insurances → claims (1:N)
claims.customer_insurance_id → customer_insurances.id

-- users → branches (N:1)
users.branch_id → branches.id

-- customer_insurances → insurance_companies (N:1)
customer_insurances.insurance_company_id → insurance_companies.id
```

---

## Indexes and Performance

### Central Database Indexes

**High-Performance Queries**:
```sql
-- Find tenant by subdomain (most frequent query)
SELECT * FROM domains WHERE domain = 'acme.midastech.in';
-- Uses: idx_domain (UNIQUE)

-- Check subscription status
SELECT status FROM subscriptions WHERE tenant_id = 'tenant_1';
-- Uses: idx_tenant_id

-- Find expiring trials
SELECT * FROM subscriptions
WHERE trial_ends_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 3 DAY);
-- Uses: idx_trial_ends_at

-- Payment history by tenant
SELECT * FROM payments
WHERE tenant_id = 'tenant_1' AND status = 'completed'
ORDER BY created_at DESC;
-- Uses: idx_tenant_status (composite)
```

### Tenant Database Indexes

**High-Performance Queries**:
```sql
-- Find customer by mobile
SELECT * FROM customers WHERE mobile_number = '9876543210';
-- Uses: idx_mobile_number

-- Expiring policies (daily cron job)
SELECT * FROM customer_insurances
WHERE end_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)
AND status = 'active';
-- Uses: idx_end_date + idx_status

-- Lead follow-ups due today
SELECT * FROM leads
WHERE next_follow_up_date <= NOW()
AND follow_up_completed = FALSE
AND assigned_to = 5;
-- Uses: idx_next_follow_up_date + idx_assigned_to

-- User login
SELECT * FROM users WHERE email = 'user@example.com' AND status = TRUE;
-- Uses: idx_email (UNIQUE)
```

### Index Strategy

**Composite Indexes**:
```sql
-- Common query patterns benefit from composite indexes
INDEX (tenant_id, status)           -- payments, audit_logs
INDEX (customer_id, end_date)       -- customer_insurances
INDEX (assigned_to, status_id)      -- leads
INDEX (channel, status)             -- notification_logs
```

**Covering Indexes**:
```sql
-- Include frequently selected columns in index
CREATE INDEX idx_subscription_status_covering
ON subscriptions (tenant_id, status, trial_ends_at, next_billing_date);
-- Allows query to be satisfied entirely from index
```

---

## Data Migration Strategy

### Central Database Migration

**Order**: MUST run in exact order
```bash
php artisan migrate --path=database/migrations/central --database=central
```

**Files**:
1. `create_tenants_table.php`
2. `create_domains_table.php`
3. `create_plans_table.php`
4. `create_subscriptions_table.php`
5. `create_tenant_users_table.php`
6. `create_audit_logs_table.php`
7. `create_contact_submissions_table.php`
8. `create_payments_table.php`
9. Update migrations (billing_interval, payment_method, etc.)

### Tenant Database Migration

**Automatic**: Runs when tenant is created via `TenantCreationService`

```php
// In TenantCreationService.php
$tenant->run(function () {
    Artisan::call('migrate', [
        '--database' => 'tenant',
        '--path' => 'database/migrations/tenant',
        '--force' => true,
    ]);
});
```

**Order**: Automatically handled by timestamp prefixes (2025_10_08_000001, etc.)

### Seeding Strategy

**Central Database**:
```bash
php artisan db:seed --class=PlanSeeder --database=central
```

**Tenant Database** (per tenant):
```php
$tenant->run(function () {
    Artisan::call('db:seed', [
        '--class' => 'TenantDatabaseSeeder',
        '--force' => true,
    ]);
});
```

**Seeded Data**:
- Lead sources (10 defaults)
- Lead statuses (6 workflow states)
- Notification types
- Customer types
- Policy types
- Fuel types
- Premium types
- Quotation statuses
- Commission types
- Default roles (Admin, Manager, Agent)
- Default permissions (100+ permissions)

---

## Summary

### Database Statistics

| Metric | Central | Per Tenant | Notes |
|--------|---------|------------|-------|
| **Tables** | 9 | 61 | Total: 70+ across all databases |
| **Migrations** | 11 | 61 | Total: 72 migrations |
| **Indexes** | 25+ | 150+ | Performance-optimized |
| **Foreign Keys** | 8 | 50+ | Data integrity enforced |
| **Estimated Size** | 10-50 MB | 100 MB - 1 GB | Varies by tenant activity |

### Data Isolation Guarantees

✅ **Complete Database Separation**: Each tenant has dedicated MySQL database
✅ **No Cross-Tenant Queries**: Impossible to query another tenant's data
✅ **Independent Scaling**: Each tenant database can be optimized separately
✅ **Backup Isolation**: Per-tenant backup and restore capability
✅ **Migration Isolation**: Tenant migrations don't affect central or other tenants

### Performance Considerations

- **Connection Pooling**: Configured for 20+ concurrent tenant connections
- **Query Optimization**: Strategic indexes on high-frequency query columns
- **Data Archival**: Soft deletes with automatic purging after 90 days
- **Cache Strategy**: Redis cache per tenant for frequently accessed data
- **Read Replicas**: Central database supports read replica for reporting

---

**Document Version**: 1.0
**Last Updated**: 2025-11-06
**Related Documentation**:
- [ARCHITECTURE.md](../ARCHITECTURE.md)
- [MULTI_PORTAL_ARCHITECTURE.md](MULTI_PORTAL_ARCHITECTURE.md)
- [SERVICE_LAYER.md](SERVICE_LAYER.md) (pending)
