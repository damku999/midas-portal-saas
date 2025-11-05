# Midas Portal - API Reference

> **Complete API endpoint documentation for the Midas Portal Insurance Management System**
>
> **Last Updated**: 2025-11-02
> **Laravel Version**: 10.49.1
> **Total Routes**: 340+

---

## Table of Contents

1. [Authentication Endpoints](#authentication-endpoints)
2. [Customer Management](#customer-management)
3. [Insurance Policy Management](#insurance-policy-management)
4. [Quotation Management](#quotation-management)
5. [Claims Management](#claims-management)
6. [Lead Management](#lead-management)
7. [Notification Management](#notification-management)
8. [Family Group Management](#family-group-management)
9. [Security & Device Management](#security--device-management)
10. [Customer Portal Endpoints](#customer-portal-endpoints)
11. [Master Data Management](#master-data-management)
12. [Reports & Analytics](#reports--analytics)
13. [Health & Monitoring](#health--monitoring)
14. [User & Role Management](#user--role-management)

---

## Authentication Endpoints

### Admin Authentication

#### Login
```http
POST /login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}

Response 200:
{
  "success": true,
  "redirect": "/dashboard"
}
```

#### Two-Factor Authentication Setup
```http
GET /2fa/setup
Response: QR code for authenticator app

POST /2fa/verify
{
  "code": "123456"
}
```

#### Logout
```http
POST /logout
Response: Redirect to login page
```

### Customer Authentication

#### Customer Login
```http
POST /customer/login
Content-Type: application/json

{
  "email": "customer@example.com",
  "password": "password"
}

Response 200:
{
  "success": true,
  "redirect": "/customer/dashboard"
}
```

#### Customer Logout
```http
POST /customer/logout
Response: Redirect to customer login
```

#### Password Reset
```http
POST /customer/forgot-password
{
  "email": "customer@example.com"
}

POST /customer/reset-password
{
  "token": "reset_token",
  "email": "customer@example.com",
  "password": "new_password",
  "password_confirmation": "new_password"
}
```

---

## Customer Management

### List Customers
```http
GET /customers
Query Parameters:
  - page (int): Pagination page number
  - per_page (int): Results per page
  - search (string): Search by name, email, mobile
  - customer_type_id (int): Filter by customer type
  - status (string): Filter by status

Response 200:
{
  "data": [
    {
      "id": 1,
      "customer_number": "CUST-202501-0001",
      "first_name": "John",
      "last_name": "Doe",
      "email": "john@example.com",
      "mobile_number": "9876543210",
      "customer_type": "Individual",
      "status": "active"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 150,
    "per_page": 25
  }
}
```

### Create Customer
```http
POST /customers/store
Content-Type: application/json

{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "mobile_number": "9876543210",
  "date_of_birth": "1990-01-15",
  "gender": "male",
  "customer_type_id": 1,
  "address": "123 Main St",
  "city": "Mumbai",
  "state": "Maharashtra",
  "pincode": "400001",
  "pan_number": "ABCDE1234F",
  "aadhar_number": "123456789012",
  "gst_number": "22AAAAA0000A1Z5",
  "family_group_id": null
}

Response 200:
{
  "success": true,
  "message": "Customer created successfully",
  "customer_id": 123
}
```

### View Customer Details
```http
GET /customers/show/{id}

Response 200:
{
  "id": 1,
  "customer_number": "CUST-202501-0001",
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "mobile_number": "9876543210",
  "policies": [...],
  "quotations": [...],
  "claims": [...],
  "family_members": [...]
}
```

### Update Customer
```http
POST /customers/update/{id}
Content-Type: application/json

{
  "first_name": "John",
  "last_name": "Doe",
  "mobile_number": "9876543210",
  ...
}

Response 200:
{
  "success": true,
  "message": "Customer updated successfully"
}
```

### Delete Customer
```http
DELETE /customers/destroy/{id}

Response 200:
{
  "success": true,
  "message": "Customer deleted successfully"
}
```

### Export Customers
```http
GET /customers/export
Query Parameters:
  - format (string): excel, csv, pdf
  - filters (object): Same as list filters

Response: Excel/CSV/PDF file download
```

---

## Insurance Policy Management

### List Policies
```http
GET /customer_insurances
Query Parameters:
  - customer_id (int): Filter by customer
  - policy_type_id (int): Filter by policy type
  - insurance_company_id (int): Filter by company
  - status (string): active, expired, cancelled
  - expiry_from (date): Expiry date range start
  - expiry_to (date): Expiry date range end

Response 200:
{
  "data": [
    {
      "id": 1,
      "policy_number": "POL-2025-0001",
      "customer_name": "John Doe",
      "policy_type": "Vehicle Insurance",
      "insurance_company": "HDFC ERGO",
      "premium_amount": 15000,
      "start_date": "2025-01-01",
      "end_date": "2026-01-01",
      "status": "active"
    }
  ],
  "meta": {...}
}
```

### Create Policy
```http
POST /customer_insurances/store
Content-Type: multipart/form-data

{
  "customer_id": 1,
  "policy_type_id": 1,
  "insurance_company_id": 5,
  "branch_id": 2,
  "policy_number": "POL123456789",
  "premium_amount": 15000,
  "premium_type_id": 1,
  "gst_amount": 2700,
  "net_premium": 17700,
  "own_commission": 1500,
  "transfer_commission": 500,
  "reference_commission": 200,
  "start_date": "2025-01-01",
  "end_date": "2026-01-01",
  "vehicle_number": "MH01AB1234",
  "make_model_variant": "Maruti Swift VXi",
  "fuel_type_id": 1,
  "registration_date": "2020-01-15",
  "policy_document": (file),
  "rc_copy": (file)
}

Response 200:
{
  "success": true,
  "message": "Policy created successfully",
  "policy_id": 456
}
```

### View Policy Details
```http
GET /customer_insurances/show/{id}

Response 200:
{
  "id": 1,
  "policy_number": "POL-2025-0001",
  "customer": {...},
  "policy_type": {...},
  "insurance_company": {...},
  "premium_details": {...},
  "commission_breakdown": {...},
  "vehicle_details": {...},
  "documents": [...]
}
```

### Renew Policy
```http
GET /customer_insurances/renew/{id}
Response: Pre-filled renewal form

POST /customer_insurances/store
{
  "renewal_of_policy_id": 123,
  ...
}
```

### Send Policy via WhatsApp
```http
POST /customer_insurances/sendWADocument
Content-Type: application/json

{
  "customer_insurance_id": 123,
  "document_type": "policy_document"
}

Response 200:
{
  "success": true,
  "message": "Document sent via WhatsApp successfully"
}
```

---

## Quotation Management

### List Quotations
```http
GET /quotations
Query Parameters:
  - customer_id (int): Filter by customer
  - status (string): draft, sent, approved, rejected
  - date_from (date): Date range start
  - date_to (date): Date range end

Response 200:
{
  "data": [
    {
      "id": 1,
      "quotation_number": "QUOT-2025-0001",
      "customer_name": "John Doe",
      "vehicle_number": "MH01AB1234",
      "status": "sent",
      "total_companies": 5,
      "created_at": "2025-01-15"
    }
  ]
}
```

### Create Quotation
```http
POST /quotations/store
Content-Type: application/json

{
  "customer_id": 1,
  "vehicle_number": "MH01AB1234",
  "make_model_variant": "Maruti Swift VXi",
  "registration_date": "2020-01-15",
  "fuel_type_id": 1,
  "previous_policy_expiry": "2025-02-01",
  "previous_ncb": 20,
  "idv_amount": 450000,
  "selected_addon_covers": [1, 3, 5],
  "coverage_type": "comprehensive"
}

Response 200:
{
  "success": true,
  "quotation_id": 789,
  "message": "Quotation created successfully"
}
```

### Generate Multi-Company Quotes
```http
POST /quotations/generate-quotes/{quotation_id}
Content-Type: application/json

{
  "insurance_companies": [1, 2, 3, 5, 7],
  "idv_amount": 450000,
  "base_premium": 8500,
  "addon_covers": {...}
}

Response 200:
{
  "success": true,
  "quotes_generated": 5,
  "quotes": [
    {
      "company": "HDFC ERGO",
      "premium": 9500,
      "idv": 450000,
      "addons": [...]
    }
  ]
}
```

### Download Quotation PDF
```http
GET /quotations/download-pdf/{id}
Response: PDF file download
```

### Send Quotation via WhatsApp
```http
POST /quotations/send-whatsapp/{id}

Response 200:
{
  "success": true,
  "message": "Quotation sent via WhatsApp successfully"
}
```

---

## Claims Management

### List Claims
```http
GET /claims
Query Parameters:
  - customer_id (int): Filter by customer
  - policy_id (int): Filter by policy
  - status (string): registered, under_review, approved, rejected, settled
  - claim_type (string): cashless, reimbursement

Response 200:
{
  "data": [
    {
      "id": 1,
      "claim_number": "CLM-2025-0001",
      "customer_name": "John Doe",
      "policy_number": "POL-2025-0001",
      "claim_amount": 50000,
      "status": "under_review",
      "incident_date": "2025-01-10"
    }
  ]
}
```

### Create Claim
```http
POST /claims/store
Content-Type: multipart/form-data

{
  "customer_insurance_id": 123,
  "claim_amount": 50000,
  "incident_date": "2025-01-10",
  "incident_description": "Vehicle accident on highway",
  "claim_type": "reimbursement",
  "documents": [
    (file: FIR copy),
    (file: Damage photos),
    (file: Repair estimate)
  ]
}

Response 200:
{
  "success": true,
  "claim_id": 345,
  "claim_number": "CLM-2025-0345"
}
```

### Update Claim Status
```http
POST /claims/update-status/{id}
Content-Type: application/json

{
  "status": "approved",
  "approved_amount": 45000,
  "remarks": "Approved after assessment"
}

Response 200:
{
  "success": true,
  "message": "Claim status updated successfully"
}
```

---

## Lead Management

### List Leads
```http
GET /leads
Query Parameters:
  - status_id (int): Filter by lead status
  - source_id (int): Filter by lead source
  - assigned_to (int): Filter by assigned user
  - priority (string): low, medium, high
  - follow_up_date (date): Filter by follow-up date
  - converted (boolean): Filter converted leads

Response 200:
{
  "data": [
    {
      "id": 1,
      "lead_number": "LD-202501-0001",
      "name": "John Doe",
      "mobile_number": "9876543210",
      "email": "john@example.com",
      "source": "Website",
      "status": "New",
      "priority": "high",
      "assigned_to": "Agent Name",
      "next_follow_up_date": "2025-01-20"
    }
  ],
  "meta": {...}
}
```

### Create Lead
```http
POST /leads/store
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "mobile_number": "9876543210",
  "alternate_mobile": "9876543211",
  "city": "Mumbai",
  "state": "Maharashtra",
  "pincode": "400001",
  "address": "123 Main St",
  "date_of_birth": "1990-01-15",
  "occupation": "Software Engineer",
  "source_id": 1,
  "product_interest": "Vehicle Insurance",
  "status_id": 1,
  "priority": "high",
  "assigned_to": 5,
  "relationship_manager_id": 2,
  "reference_user_id": 3,
  "next_follow_up_date": "2025-01-20",
  "remarks": "Interested in comprehensive vehicle insurance"
}

Response 200:
{
  "success": true,
  "lead_id": 567,
  "lead_number": "LD-202501-0567"
}
```

### View Lead Details
```http
GET /leads/show/{id}

Response 200:
{
  "id": 1,
  "lead_number": "LD-202501-0001",
  "name": "John Doe",
  "contact_details": {...},
  "source": {...},
  "status": {...},
  "assigned_user": {...},
  "activities": [...],
  "documents": [...],
  "quotations": [...],
  "conversion_info": null
}
```

### Update Lead Status
```http
POST /leads/{id}/status
Content-Type: application/json

{
  "status_id": 3,
  "remarks": "Customer showed interest, sent quotation"
}

Response 200:
{
  "success": true,
  "message": "Lead status updated successfully"
}
```

### Assign Lead
```http
POST /leads/{id}/assign
Content-Type: application/json

{
  "assigned_to": 8
}

Response 200:
{
  "success": true,
  "message": "Lead assigned successfully"
}
```

### Convert Lead to Customer
```http
POST /leads/{id}/convert-auto

Response 200:
{
  "success": true,
  "customer_id": 234,
  "customer_number": "CUST-202501-0234",
  "message": "Lead converted to customer successfully"
}
```

### Bulk Convert Leads
```http
POST /leads/bulk-convert
Content-Type: application/json

{
  "lead_ids": [1, 5, 12, 23, 45]
}

Response 200:
{
  "success": true,
  "converted_count": 5,
  "failed_count": 0,
  "customers": [...]
}
```

### Add Lead Activity
```http
POST /leads/{id}/activity
Content-Type: application/json

{
  "activity_type": "call",
  "subject": "Follow-up call",
  "description": "Discussed insurance options",
  "outcome": "Interested in comprehensive plan",
  "next_action": "Send quotation",
  "scheduled_at": "2025-01-20 10:00:00"
}

Response 200:
{
  "success": true,
  "activity_id": 89
}
```

### Upload Lead Document
```http
POST /leads/{id}/document
Content-Type: multipart/form-data

{
  "document_type": "ID Proof",
  "file": (file)
}

Response 200:
{
  "success": true,
  "document_id": 45
}
```

### Lead Dashboard Analytics
```http
GET /leads/dashboard

Response 200:
{
  "statistics": {
    "total_leads": 1250,
    "new_leads": 45,
    "active_leads": 320,
    "converted_leads": 680,
    "lost_leads": 205,
    "conversion_rate": 54.4
  },
  "myLeads": 25,
  "myFollowUps": 8,
  "myOverdue": 3,
  "recentActivities": [...],
  "upcomingActivities": [...],
  "sourceWiseLeads": {...},
  "statusWiseLeads": {...}
}
```

### Export Leads
```http
GET /leads/export
Query Parameters:
  - format (string): excel, csv
  - filters (object): Same as list filters

Response: Excel/CSV file download
```

---

## Notification Management

### List Notification Templates
```http
GET /notification-templates
Response 200:
{
  "data": [
    {
      "id": 1,
      "name": "Policy Expiry Reminder",
      "notification_type": "email",
      "template_content": "Dear {{customer_name}}...",
      "variables": ["customer_name", "policy_number", "expiry_date"],
      "is_active": true
    }
  ]
}
```

### Send Test Notification
```http
POST /notification-templates/send-test
Content-Type: application/json

{
  "template_id": 5,
  "recipient": "test@example.com",
  "test_data": {
    "customer_name": "John Doe",
    "policy_number": "POL-2025-0001"
  }
}

Response 200:
{
  "success": true,
  "message": "Test notification sent successfully"
}
```

### Notification Logs
```http
GET /admin/notification-logs
Query Parameters:
  - notification_type_id (int): Filter by type
  - status (string): sent, failed, pending
  - customer_id (int): Filter by customer
  - date_from (date): Date range start
  - date_to (date): Date range end

Response 200:
{
  "data": [
    {
      "id": 1,
      "notification_type": "Email",
      "recipient": "john@example.com",
      "subject": "Policy Expiry Reminder",
      "status": "sent",
      "sent_at": "2025-01-15 10:30:00",
      "delivery_status": "delivered"
    }
  ]
}
```

### Resend Failed Notification
```http
POST /admin/notification-logs/{id}/resend

Response 200:
{
  "success": true,
  "message": "Notification queued for resend"
}
```

---

## Family Group Management

### List Family Groups
```http
GET /family-groups

Response 200:
{
  "data": [
    {
      "id": 1,
      "group_name": "Doe Family",
      "primary_customer": "John Doe",
      "total_members": 4,
      "created_at": "2025-01-01"
    }
  ]
}
```

### Create Family Group
```http
POST /family-groups/store
Content-Type: application/json

{
  "group_name": "Doe Family",
  "primary_customer_id": 1,
  "member_customer_ids": [2, 3, 4]
}

Response 200:
{
  "success": true,
  "family_group_id": 12
}
```

---

## Security & Device Management

### List Customer Devices
```http
GET /customer-devices
Query Parameters:
  - customer_id (int): Filter by customer
  - is_trusted (boolean): Filter trusted devices

Response 200:
{
  "data": [
    {
      "id": 1,
      "customer_name": "John Doe",
      "device_fingerprint": "abc123...",
      "device_type": "mobile",
      "browser": "Chrome 120",
      "os": "Android 14",
      "is_trusted": true,
      "last_used_at": "2025-01-15 14:30:00"
    }
  ]
}
```

### Revoke Device Trust
```http
POST /customer-devices/{id}/revoke

Response 200:
{
  "success": true,
  "message": "Device trust revoked"
}
```

### Audit Logs
```http
GET /audit-logs
Query Parameters:
  - user_id (int): Filter by user
  - action (string): Filter by action type
  - model_type (string): Filter by model
  - date_from (date): Date range start
  - date_to (date): Date range end

Response 200:
{
  "data": [
    {
      "id": 1,
      "user": "Admin User",
      "action": "created",
      "model": "Customer",
      "model_id": 123,
      "changes": {...},
      "ip_address": "192.168.1.1",
      "created_at": "2025-01-15 10:00:00"
    }
  ]
}
```

---

## Customer Portal Endpoints

### Customer Dashboard
```http
GET /customer/dashboard
Headers: Authorization: Bearer {token}

Response 200:
{
  "customer": {...},
  "active_policies": 3,
  "expiring_soon": 1,
  "pending_renewals": 1,
  "total_claims": 2,
  "recentPolicies": [...],
  "notifications": [...]
}
```

### Customer Policies
```http
GET /customer/my-policies
Headers: Authorization: Bearer {token}

Response 200:
{
  "data": [
    {
      "policy_number": "POL-2025-0001",
      "policy_type": "Vehicle Insurance",
      "insurance_company": "HDFC ERGO",
      "vehicle_number": "MH01AB1234",
      "start_date": "2025-01-01",
      "end_date": "2026-01-01",
      "status": "active",
      "documents": [...]
    }
  ]
}
```

### Customer Claims
```http
GET /customer/my-claims
Headers: Authorization: Bearer {token}

Response 200:
{
  "data": [
    {
      "claim_number": "CLM-2025-0001",
      "policy_number": "POL-2025-0001",
      "claim_amount": 50000,
      "status": "under_review",
      "incident_date": "2025-01-10",
      "documents": [...]
    }
  ]
}
```

### Download Policy Document
```http
GET /customer/download-policy/{policy_id}
Headers: Authorization: Bearer {token}

Response: PDF file download
```

---

## Master Data Management

### Branches
```http
GET /branches - List all branches
POST /branches/store - Create branch
PUT /branches/update/{id} - Update branch
DELETE /branches/destroy/{id} - Delete branch
```

### Insurance Companies
```http
GET /insurance-companies - List companies
POST /insurance-companies/store - Create company
PUT /insurance-companies/update/{id} - Update company
DELETE /insurance-companies/destroy/{id} - Delete company
```

### Lead Sources
```http
GET /lead-sources - List lead sources
POST /lead-sources/store - Create source
PUT /lead-sources/update/{id} - Update source
DELETE /lead-sources/destroy/{id} - Delete source
```

### Lead Statuses
```http
GET /lead-statuses - List lead statuses
POST /lead-statuses/store - Create status
PUT /lead-statuses/update/{id} - Update status
DELETE /lead-statuses/destroy/{id} - Delete status
```

---

## Reports & Analytics

### Business Reports Dashboard
```http
GET /business-reports/dashboard

Response 200:
{
  "total_premium": 12500000,
  "total_commission": 1250000,
  "policy_count": 450,
  "customer_count": 380,
  "active_policies": 420,
  "expiring_this_month": 35,
  "premiumByMonth": {...},
  "policyTypeWise": {...},
  "companyWise": {...}
}
```

### Commission Report
```http
GET /business-reports/commission
Query Parameters:
  - date_from (date): Start date
  - date_to (date): End date
  - branch_id (int): Filter by branch
  - user_id (int): Filter by user

Response 200:
{
  "total_commission": 125000,
  "own_commission": 100000,
  "transfer_commission": 20000,
  "reference_commission": 5000,
  "details": [...]
}
```

### Export Report
```http
GET /business-reports/export
Query Parameters:
  - report_type (string): premium, commission, policy
  - format (string): excel, pdf
  - filters (object): Date range and other filters

Response: Excel/PDF file download
```

---

## Health & Monitoring

### Health Check
```http
GET /health

Response 200:
{
  "status": "healthy",
  "database": "connected",
  "cache": "connected",
  "queue": "running",
  "storage": "writable"
}
```

### System Status
```http
GET /system/status
Requires: System Admin permission

Response 200:
{
  "app_version": "1.0.0",
  "laravel_version": "10.49.1",
  "php_version": "8.2.12",
  "database_version": "MySQL 8.4",
  "total_users": 45,
  "total_customers": 1250,
  "total_policies": 3400,
  "disk_usage": "45%",
  "memory_usage": "512 MB"
}
```

---

## User & Role Management

### List Users
```http
GET /users
Requires: user-list permission

Response 200:
{
  "data": [
    {
      "id": 1,
      "first_name": "John",
      "last_name": "Admin",
      "email": "admin@example.com",
      "role": "Admin",
      "status": true,
      "last_login": "2025-01-15 14:30:00"
    }
  ]
}
```

### Create User
```http
POST /users/store
Content-Type: application/json
Requires: user-create permission

{
  "first_name": "Jane",
  "last_name": "Manager",
  "email": "jane@example.com",
  "password": "password123",
  "role_id": 2,
  "status": true
}

Response 200:
{
  "success": true,
  "user_id": 23
}
```

### Assign Role
```http
POST /users/{id}/assign-role
Content-Type: application/json

{
  "role_id": 3
}

Response 200:
{
  "success": true,
  "message": "Role assigned successfully"
}
```

---

## Error Responses

### Common Error Codes

#### 400 Bad Request
```json
{
  "error": "Bad Request",
  "message": "Invalid input data",
  "errors": {
    "email": ["The email field is required."],
    "mobile_number": ["The mobile number format is invalid."]
  }
}
```

#### 401 Unauthorized
```json
{
  "error": "Unauthorized",
  "message": "Authentication required"
}
```

#### 403 Forbidden
```json
{
  "error": "Forbidden",
  "message": "You do not have permission to access this resource"
}
```

#### 404 Not Found
```json
{
  "error": "Not Found",
  "message": "Resource not found"
}
```

#### 422 Unprocessable Entity
```json
{
  "error": "Validation Error",
  "message": "The given data was invalid",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

#### 500 Internal Server Error
```json
{
  "error": "Internal Server Error",
  "message": "An unexpected error occurred"
}
```

---

## Rate Limiting

All API endpoints are rate limited to prevent abuse:

- **Authenticated Users**: 60 requests per minute
- **Guest Users**: 10 requests per minute
- **Sensitive Endpoints** (login, password reset): 5 requests per minute

Rate limit headers:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1642345678
```

---

## Pagination

List endpoints support pagination with the following parameters:

```
GET /customers?page=2&per_page=25
```

Response includes pagination metadata:
```json
{
  "data": [...],
  "meta": {
    "current_page": 2,
    "from": 26,
    "to": 50,
    "total": 150,
    "per_page": 25,
    "last_page": 6
  },
  "links": {
    "first": "/customers?page=1",
    "last": "/customers?page=6",
    "prev": "/customers?page=1",
    "next": "/customers?page=3"
  }
}
```

---

## Date/Time Format

All dates and timestamps use ISO 8601 format:

- **Date**: `YYYY-MM-DD` (e.g., `2025-01-15`)
- **DateTime**: `YYYY-MM-DD HH:MM:SS` (e.g., `2025-01-15 14:30:00`)
- **Timezone**: Asia/Kolkata (IST)

---

## API Versioning

Current API version: **v1** (default, no version prefix required)

Future versions will use URL prefixing:
```
/api/v2/customers
```

---

## Authentication

### Admin Panel
Uses Laravel session-based authentication with CSRF protection.

Include CSRF token in all POST/PUT/DELETE requests:
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

JavaScript:
```javascript
headers: {
  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
}
```

### Customer Portal
Uses Laravel Sanctum for API token authentication.

Include token in requests:
```
Authorization: Bearer {token}
```

---

## Support

For API support and questions:
- **Email**: support@webmonks.in
- **Documentation**: [Full Project Documentation](PROJECT_INDEX.md)
- **Development Team**: WebMonks Development Team

---

**Last Updated**: 2025-11-02
**Document Version**: 1.0
**API Version**: v1
