# Central Domain Specification - midastech.in

**Purpose**: Detailed specification for central domain with public website and administration panel
**Domain**: `midastech.in` (main domain)
**Admin Access**: `midastech.in/admin` (super administrators only)
**Production URL**: `https://midastech.in`
**Local URL**: `http://midastech.in.local:8000` (local development)

---

## Overview

The central domain `midastech.in` serves TWO primary purposes:

### 1. Public Website
- Marketing and informational website accessible to all visitors
- Product information, pricing, features, testimonials
- Sign-up and registration flows for new tenants
- Public contact forms and support information
- SEO-optimized landing pages

### 2. Central Admin Panel (`/admin` route)
- Super admin control panel for managing all tenants
- Subscription and billing management
- System-wide configuration
- Tenant provisioning and monitoring
- Completely separate authentication from tenant users and public visitors

---

## Access Control

### Authentication
- **Separate authentication system** from tenant users
- Uses `central` guard in Laravel
- Stored in `tenant_users` table in central database
- No shared authentication with tenant users

### Authorization Levels
1. **Super Admin** - Full access to all features
2. **Support Admin** - Can view and assist tenants (no billing access)
3. **Billing Admin** - Manages subscriptions and payments only

### Login Flow
```
1. User visits admin.midastech.in
2. Redirected to /login (central auth page)
3. Credentials checked against tenant_users table
4. If valid + is_super_admin = true → access granted
5. Session stored with central guard
6. Redirect to central dashboard
```

---

## Dashboard Features

### Main Dashboard (`/`)

**URL**: `https://midastech.in/admin/`

**Metrics Cards**:
- Total Tenants (active, trial, suspended, cancelled)
- Monthly Recurring Revenue (MRR)
- New Tenants This Month
- Churn Rate
- Total Users Across All Tenants
- Total Customers Across All Tenants
- System Health Status

**Charts**:
- Tenant Growth Trend (line chart - last 12 months)
- Revenue by Plan (pie chart)
- Tenant Status Distribution (donut chart)
- Active Users per Tenant (bar chart - top 10)

**Recent Activity**:
- Last 10 tenant registrations
- Recent subscription changes
- Failed payments
- Support tickets (if implemented)

**Quick Actions**:
- Create New Tenant
- View All Tenants
- Generate Revenue Report
- System Health Check

---

## Tenant Management

### Tenant List (`/tenants`)

**URL**: `https://midastech.in/admin/tenants`

**Features**:
- **Search**: By name, subdomain, email
- **Filters**: Status (active, trial, suspended, cancelled), Plan, Date range
- **Sort**: By name, created date, status, MRR
- **Export**: CSV/Excel export of tenant list

**Table Columns**:
| Column | Description | Actions |
|--------|-------------|---------|
| ID | Tenant ID | - |
| Name | Tenant company name | Link to details |
| Subdomain | {subdomain}.midastech.in | Open in new tab |
| Plan | Current pricing plan | - |
| Status | Active/Trial/Suspended/Cancelled | Status badge |
| Users | Count of users | - |
| Customers | Count of customers | - |
| Created | Registration date | - |
| Actions | Edit, Suspend, Delete, Impersonate | Dropdown |

**Bulk Actions**:
- Suspend Selected
- Activate Selected
- Change Plan
- Export Selected

### Create Tenant (`/tenants/create`)

**URL**: `https://midastech.in/admin/tenants/create`

**Form Fields**:

**Section 1: Company Information**
- Company Name (required)
- Subdomain (required, alphanumeric + hyphens, validated for uniqueness)
- Contact Email (required, for admin account)
- Contact Phone (optional)

**Section 2: Subscription**
- Select Plan (dropdown: Starter, Professional, Enterprise)
- Trial Period (checkbox, default 14 days)
- Trial End Date (if trial enabled)
- Initial Status (Trial, Active, Suspended)

**Section 3: Admin User**
- Admin First Name (required)
- Admin Last Name (required)
- Admin Email (required, becomes tenant admin login)
- Admin Password (auto-generated or manual)
- Send Welcome Email (checkbox, default checked)

**Section 4: Configuration (Optional)**
- Custom Branding Enabled
- Storage Limit Override (GB)
- User Limit Override
- Custom Features (checkboxes for beta features)

**Provisioning Process**:
1. Validate all inputs
2. Check subdomain availability
3. Create tenant record in central database
4. Create MySQL database: `tenant_{id}`
5. Run all tenant migrations on new database
6. Seed default data (roles, statuses, settings)
7. Create tenant admin user
8. Create subscription record
9. Send welcome email to admin
10. Show success message with login link

**Success Screen**:
```
✅ Tenant Created Successfully!

Company: ABC Insurance
Subdomain: abc.midastech.in
Admin Email: admin@abcinsurance.com
Status: Trial (expires: 2025-11-16)

[View Tenant Details] [Create Another Tenant] [Back to List]
```

### View Tenant (`/tenants/{id}`)

**URL**: `https://midastech.in/admin/tenants/123`

**Sections**:

**1. Tenant Overview**
- Company name, subdomain, status
- Plan details
- Created date, last activity
- Quick actions (Edit, Suspend, Delete, Impersonate)

**2. Statistics Dashboard**
- User count vs limit
- Customer count vs limit
- Leads this month vs limit
- Storage usage vs limit
- Progress bars showing usage percentages

**3. Subscription Details**
- Current plan
- Billing interval (monthly/yearly)
- Start date, renewal date
- Payment status
- Trial end date (if applicable)
- MRR contribution

**4. Usage Metrics**
- Users: X / Y limit
- Customers: X / Y limit
- Leads (this month): X / Y limit
- Storage: X GB / Y GB
- Database size
- Last backup date

**5. Activity Log**
- Tenant created
- Subscription changes
- Suspensions/activations
- Admin impersonations
- Failed payments
- Feature usage milestones

**6. Admin Users**
- List of users with admin role
- Email, last login
- Actions: Impersonate, Reset Password

**7. Technical Details**
- Database name
- Database size
- Number of tables
- Total records
- Last migration run
- Laravel version
- PHP version

### Edit Tenant (`/tenants/{id}/edit`)

**URL**: `https://midastech.in/admin/tenants/123/edit`

**Editable Fields**:
- Company Name
- Contact Email
- Contact Phone
- Status (dropdown)
- Custom limits overrides
- Custom features toggles
- Metadata (JSON field for custom data)

**Actions**:
- Save Changes
- Cancel
- View Tenant Details

### Tenant Actions

#### Suspend Tenant
**URL**: `POST /tenants/{id}/suspend`

**Process**:
1. Confirm suspension (modal with reason)
2. Update status to 'suspended'
3. Log suspension event
4. Send notification email to tenant admin
5. Tenant users cannot login (see suspension page)

**Suspension Page** (tenant side):
```
Your account has been suspended.

Reason: Payment failure / Terms violation / Other

Please contact support: support@midastech.in

[Contact Support Button]
```

#### Activate Tenant
**URL**: `POST /tenants/{id}/activate`

**Process**:
1. Confirm activation
2. Update status to 'active'
3. Log activation event
4. Send notification email
5. Tenant users can login normally

#### Delete Tenant
**URL**: `DELETE /tenants/{id}`

⚠️ **DANGEROUS OPERATION**

**Process**:
1. Show confirmation modal:
   ```
   ⚠️ DELETE TENANT

   You are about to permanently delete:
   - Company: ABC Insurance
   - Subdomain: abc.midastech.in
   - Database: tenant_123
   - All user data, customers, leads, etc.

   This action CANNOT be undone!

   Type "DELETE ABC" to confirm:
   [_________________]

   [Cancel] [DELETE PERMANENTLY]
   ```
2. Verify confirmation text matches
3. Create full database backup
4. Drop tenant database
5. Delete tenant record (soft delete)
6. Delete all tenant files from storage
7. Log deletion event
8. Send notification to stored admin email

**Soft Delete**:
- Tenant record kept for 30 days
- Database backed up and compressed
- Can be restored within 30 days
- After 30 days, permanently purged

#### Impersonate Tenant User
**URL**: `POST /tenants/{id}/users/{user_id}/impersonate`

**Process**:
1. Verify super admin permissions
2. Switch to tenant database context
3. Store impersonation data in session:
   ```php
   session([
       'impersonating' => true,
       'impersonator_id' => auth()->guard('central')->id(),
       'impersonator_name' => auth()->guard('central')->user()->name,
       'tenant_id' => $tenant->id,
       'original_url' => request()->url(),
   ]);
   ```
4. Login as tenant user (without password)
5. Log impersonation event in audit log
6. Redirect to tenant subdomain
7. Show impersonation banner at top:
   ```
   ⚠️ You are impersonating John Doe (john@abc.com) at ABC Insurance
   [Stop Impersonation] [Return to Central Admin]
   ```

**Impersonation Banner** (tenant side):
```html
<div class="alert alert-warning" style="position: fixed; top: 0; width: 100%; z-index: 9999;">
    ⚠️ <strong>IMPERSONATION MODE</strong> -
    You are viewing as: John Doe (john@abc.com) |
    Tenant: ABC Insurance |
    <a href="/impersonate/stop">Stop Impersonation</a>
</div>
```

**Stop Impersonation**:
```
1. User clicks "Stop Impersonation"
2. Log out from tenant session
3. Restore central admin session
4. Redirect back to central admin
5. Log impersonation end event
```

---

## Plan Management

### Plans List (`/plans`)

**URL**: `https://midastech.in/admin/plans`

**Default Plans**:

| Plan | Price | Interval | Users | Customers | Leads/Month | Storage | Features |
|------|-------|----------|-------|-----------|-------------|---------|----------|
| Starter | $999 | Monthly | 5 | 1,000 | 500 | 5 GB | Basic |
| Professional | $2,999 | Monthly | 20 | 10,000 | 5,000 | 50 GB | Advanced |
| Enterprise | $9,999 | Monthly | Unlimited | Unlimited | Unlimited | 500 GB | All + API |

**Features**:
- Create new plan
- Edit existing plan
- Archive plan (hide from new signups)
- View tenants on each plan

### Create/Edit Plan

**Form Fields**:
- Plan Name (e.g., "Starter", "Professional")
- Slug (auto-generated from name)
- Description
- Price (decimal)
- Billing Interval (monthly/yearly)
- Features (multi-select or textarea, JSON)
- Limits:
  - Max Users (-1 for unlimited)
  - Max Customers (-1 for unlimited)
  - Max Leads per Month (-1 for unlimited)
  - Storage Limit (GB)
- Status (active/inactive)
- Sort Order (for display)

---

## Subscription Management

### Subscriptions List (`/subscriptions`)

**URL**: `https://midastech.in/admin/subscriptions`

**Table Columns**:
- Tenant Name
- Plan
- Status (active, cancelled, expired, past_due)
- Start Date
- End Date (if applicable)
- Next Billing Date
- MRR
- Payment Gateway (Stripe, Razorpay, Manual)
- Actions

**Filters**:
- Status
- Plan
- Payment Gateway
- Date Range

**Actions per Subscription**:
- View Details
- Change Plan
- Cancel Subscription
- Extend Trial
- Manual Payment Entry

### Subscription Details (`/subscriptions/{id}`)

**Sections**:
1. **Overview**: Tenant, plan, status, dates
2. **Billing History**: All invoices and payments
3. **Usage**: Current vs plan limits
4. **Payment Method**: Card details (last 4 digits)
5. **Actions**: Cancel, change plan, refund

---

## Billing & Revenue

### Revenue Dashboard (`/billing/revenue`)

**URL**: `https://midastech.in/admin/billing/revenue`

**Metrics**:
- Total MRR (Monthly Recurring Revenue)
- Total ARR (Annual Recurring Revenue)
- Average Revenue Per Tenant (ARPT)
- Churn Rate
- Net New MRR

**Charts**:
- MRR Growth (line chart, 12 months)
- Revenue by Plan (pie chart)
- New vs Churned MRR (bar chart)
- Revenue Forecast (line chart with prediction)

**Tables**:
- Top 10 Tenants by Revenue
- Recent Upgrades
- Recent Downgrades
- Cancelled Subscriptions

### Invoices (`/billing/invoices`)

**URL**: `https://midastech.in/admin/billing/invoices`

**Features**:
- List all invoices across all tenants
- Filter by status (paid, pending, failed)
- Filter by date range
- Export to CSV/PDF
- Bulk actions (send reminder, mark as paid)

**Table Columns**:
- Invoice #
- Tenant
- Amount
- Status
- Due Date
- Paid Date
- Actions (View, Download PDF, Send Reminder)

### Failed Payments (`/billing/failed-payments`)

**URL**: `https://midastech.in/admin/billing/failed-payments`

**Features**:
- List all failed payment attempts
- Retry payment
- Contact tenant
- Suspend tenant (if multiple failures)

---

## System Administration

### Global Settings (`/settings`)

**URL**: `https://midastech.in/admin/settings`

**Sections**:

**1. Application Settings**
- Site Name
- Support Email
- Support Phone
- Default Timezone
- Default Currency

**2. Email Configuration**
- SMTP Settings
- From Email
- From Name
- Email Templates (welcome, suspension, invoice, etc.)

**3. Payment Gateway**
- Stripe API Keys
- Razorpay API Keys
- Payment Webhooks URL
- Default Gateway

**4. WhatsApp Configuration** (if enabled)
- WhatsApp API Key
- Default Message Templates
- Rate Limits

**5. Storage Settings**
- Default Storage Limit (GB)
- File Upload Max Size
- Allowed File Types

**6. Security**
- Force HTTPS
- Session Timeout
- 2FA Enforced for Tenants
- Password Policy

**7. Feature Flags** (global toggles)
- Enable WhatsApp Module
- Enable Claims Module
- Enable Quotations Module
- Enable Lead Conversion

### Audit Logs (`/audit-logs`)

**URL**: `https://midastech.in/admin/audit-logs`

**Logged Events**:
- Tenant created/edited/suspended/deleted
- Subscription changes
- Payment events
- Admin impersonations
- System config changes
- Failed login attempts

**Table Columns**:
- Timestamp
- Admin User
- Action
- Tenant (if applicable)
- Details
- IP Address

**Filters**:
- Date Range
- Admin User
- Action Type
- Tenant

### System Health (`/system/health`)

**URL**: `https://midastech.in/admin/system/health`

**Checks**:
- ✅ Application Status (up/down)
- ✅ Central Database (connected/size/performance)
- ✅ Tenant Database Connections (test all)
- ✅ Queue Status (jobs pending/failed)
- ✅ Redis Cache (if enabled)
- ✅ File Storage (disk space)
- ✅ Email Service (test connection)
- ✅ Payment Gateway (API status)

**Alerts**:
- Database connections > 90%
- Disk space < 10% free
- Failed jobs > 100
- Central database slow queries

---

## API Endpoints (for central admin)

### Authentication
```
POST /api/central/login
POST /api/central/logout
GET  /api/central/me
```

### Tenants
```
GET    /api/central/tenants
POST   /api/central/tenants
GET    /api/central/tenants/{id}
PUT    /api/central/tenants/{id}
DELETE /api/central/tenants/{id}
POST   /api/central/tenants/{id}/suspend
POST   /api/central/tenants/{id}/activate
```

### Subscriptions
```
GET    /api/central/subscriptions
GET    /api/central/subscriptions/{id}
PUT    /api/central/subscriptions/{id}
POST   /api/central/subscriptions/{id}/cancel
```

### Analytics
```
GET /api/central/analytics/revenue
GET /api/central/analytics/tenants
GET /api/central/analytics/churn
```

---

## Security Measures

### Central Admin Protection
1. **IP Whitelist** (optional): Restrict central admin to specific IPs
2. **2FA Required**: All super admins must enable 2FA
3. **Session Timeout**: 30 minutes of inactivity
4. **Audit Logging**: All actions logged
5. **Rate Limiting**: Strict rate limits on central endpoints
6. **HTTPS Only**: No HTTP access allowed

### Data Protection
1. **Encrypted Backups**: All database backups encrypted
2. **Access Logs**: Who accessed what tenant data
3. **Impersonation Logs**: Every impersonation event logged
4. **Role-Based Access**: Different admin levels with specific permissions

---

## Technical Implementation

### Database Connection
```php
// config/database.php
'central' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'database' => 'central',
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
    // ... other settings
],
```

### Auth Guard
```php
// config/auth.php
'guards' => [
    'central' => [
        'driver' => 'session',
        'provider' => 'central_users',
    ],
],

'providers' => [
    'central_users' => [
        'driver' => 'eloquent',
        'model' => App\Models\Central\TenantUser::class,
    ],
],
```

### Middleware
```php
// app/Http/Middleware/CentralAuth.php
public function handle($request, Closure $next)
{
    if (!Auth::guard('central')->check()) {
        return redirect()->route('central.login');
    }

    if (!Auth::guard('central')->user()->is_super_admin) {
        abort(403, 'Unauthorized');
    }

    return $next($request);
}
```

---

## User Roles & Permissions

| Role | Tenants | Subscriptions | Billing | Settings | Impersonate | Audit Logs |
|------|---------|---------------|---------|----------|-------------|------------|
| Super Admin | ✅ Full | ✅ Full | ✅ Full | ✅ Full | ✅ Yes | ✅ View All |
| Support Admin | ✅ View/Edit | ❌ View Only | ❌ No | ❌ View Only | ✅ Yes | ✅ View Own |
| Billing Admin | ✅ View Only | ✅ Full | ✅ Full | ❌ No | ❌ No | ✅ View Billing |

---

**Document Version**: 1.0
**Last Updated**: 2025-11-02
