# API Validation Documentation
**Laravel Admin Panel - Insurance Management System**

Version: 1.0.0
Last Updated: 2025-10-07
Base URL: `/api` (future implementation)

---

## Table of Contents

1. [Authentication & Authorization](#authentication--authorization)
2. [Common Patterns](#common-patterns)
3. [Error Response Format](#error-response-format)
4. [Data Types & Formats](#data-types--formats)
5. [Module Documentation](#module-documentation)
   - [Branches](#1-branches)
   - [Brokers](#2-brokers)
   - [Reference Users](#3-reference-users)
   - [Insurance Companies](#4-insurance-companies)
   - [Addon Covers](#5-addon-covers)
   - [Customers](#6-customers)
   - [Customer Insurances](#7-customer-insurances-policies)
   - [Claims](#8-claims)
   - [Fuel Types](#9-fuel-types)
   - [Policy Types](#10-policy-types)
   - [Premium Types](#11-premium-types)
   - [Relationship Managers](#12-relationship-managers)
   - [Quotations](#13-quotations)
   - [Users/Admins](#14-users-admins)
   - [Roles](#15-roles)
   - [Permissions](#16-permissions)
   - [App Settings](#17-app-settings)
   - [Notification Templates](#18-notification-templates)
   - [Family Groups](#19-family-groups)

---

## Authentication & Authorization

### Authentication Methods
- **Bearer Token**: `Authorization: Bearer {token}`
- **Session-based**: Laravel Sanctum tokens

### Permission Requirements
Each endpoint requires specific permissions:
- **List**: `{module}-list`
- **Create**: `{module}-create`
- **Edit**: `{module}-edit`
- **Delete**: `{module}-delete`

Example: `branch-list`, `customer-create`, `user-edit`

---

## Common Patterns

### Status Values
All modules use integer status:
- `1` = Active
- `0` = Inactive

### Unique Constraints
- **Branch**: `name` (unique)
- **Broker**: `email` (unique if provided)
- **Customer**: `email` (unique)
- **User**: `email` (unique)

### Foreign Key Validation
All relationships validated with `exists:table,id`:
```
customer_id: exists:customers,id
branch_id: exists:branches,id
insurance_company_id: exists:insurance_companies,id
```

---

## Error Response Format

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "mobile_number": ["The mobile number must be exactly 10 digits."]
  }
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Resource not found"
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "Internal server error",
  "error": "Detailed error message"
}
```

---

## Data Types & Formats

### Date Formats
- **UI Format**: `DD/MM/YYYY` (e.g., `07/10/2025`)
- **Database Format**: `YYYY-MM-DD` (e.g., `2025-10-07`)
- **ISO 8601**: `YYYY-MM-DDTHH:mm:ssZ` (response timestamps)

### Numeric Fields
- **Decimal Precision**: 2 decimal places
- **Currency**: INR (₹)
- **Percentages**: 0-100 (decimal allowed)

### File Uploads
- **Max Size**: 1024 KB (1 MB)
- **Accepted Types**: `application/pdf`, `image/jpeg`, `image/png`
- **Format**: `multipart/form-data`

### Indian Mobile Number
- **Format**: 10 digits
- **Validation**: `numeric|digits:10`
- **Example**: `9876543210`

---

## Module Documentation

## 1. Branches

### POST /api/branches

**Description**: Create a new branch

**Request Body**:
```json
{
  "name": "string",
  "email": "string|nullable",
  "mobile_number": "string|nullable"
}
```

**Validation Rules**:

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| name | string | Yes | required\|string\|max:255\|unique:branches,name | Branch name (must be unique) |
| email | string | No | nullable\|email\|max:255 | Contact email address |
| mobile_number | string | No | nullable\|string\|max:15 | Contact phone number |

**Example Request**:
```json
{
  "name": "MUMBAI BRANCH",
  "email": "mumbai@company.com",
  "mobile_number": "9876543210"
}
```

**Success Response (201)**:
```json
{
  "success": true,
  "message": "Branch created successfully",
  "data": {
    "id": 1,
    "name": "MUMBAI BRANCH",
    "email": "mumbai@company.com",
    "mobile_number": "9876543210",
    "status": 1,
    "created_at": "2025-10-07T12:00:00Z",
    "updated_at": "2025-10-07T12:00:00Z"
  }
}
```

**Error Response (422)**:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "email": ["The email must be a valid email address."]
  }
}
```

---

### PUT /api/branches/{id}

**Description**: Update an existing branch

**Request Body**:
```json
{
  "name": "string",
  "email": "string|nullable",
  "mobile_number": "string|nullable"
}
```

**Validation Rules**:

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| name | string | Yes | required\|string\|max:255\|unique:branches,name,{id} | Branch name (unique, excluding current record) |
| email | string | No | nullable\|email\|max:255 | Contact email address |
| mobile_number | string | No | nullable\|string\|max:15 | Contact phone number |

**Example Request**:
```json
{
  "name": "MUMBAI BRANCH - UPDATED",
  "email": "mumbai.updated@company.com",
  "mobile_number": "9876543211"
}
```

**Success Response (200)**:
```json
{
  "success": true,
  "message": "Branch updated successfully",
  "data": {
    "id": 1,
    "name": "MUMBAI BRANCH - UPDATED",
    "email": "mumbai.updated@company.com",
    "mobile_number": "9876543211",
    "status": 1,
    "updated_at": "2025-10-07T13:00:00Z"
  }
}
```

---

### GET /api/branches

**Description**: List all branches with pagination

**Query Parameters**:

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| page | integer | No | Page number (default: 1) |
| per_page | integer | No | Items per page (default: 10) |
| search | string | No | Search by name |
| status | integer | No | Filter by status (0 or 1) |

**Success Response (200)**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "MUMBAI BRANCH",
      "email": "mumbai@company.com",
      "mobile_number": "9876543210",
      "status": 1,
      "created_at": "2025-10-07T12:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 1,
    "last_page": 1
  }
}
```

---

### PATCH /api/branches/{id}/status

**Description**: Update branch status

**Request Body**:
```json
{
  "status": 1
}
```

**Validation Rules**:

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| status | integer | Yes | required\|in:0,1 | Status: 0 (Inactive) or 1 (Active) |

---

## 2. Brokers

### POST /api/brokers

**Description**: Create a new broker

**Request Body**:
```json
{
  "name": "string",
  "email": "string|nullable",
  "mobile_number": "string|nullable"
}
```

**Validation Rules**:

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| name | string | Yes | required\|string\|max:255 | Broker name |
| email | string | No | nullable\|email\|max:255 | Broker email address |
| mobile_number | string | No | nullable\|numeric\|digits:10 | Indian mobile number (10 digits) |

**Example Request**:
```json
{
  "name": "ICICI Lombard Agent",
  "email": "agent@icicilombard.com",
  "mobile_number": "9876543210"
}
```

**Success Response (201)**:
```json
{
  "success": true,
  "message": "Broker created successfully",
  "data": {
    "id": 1,
    "name": "ICICI Lombard Agent",
    "email": "agent@icicilombard.com",
    "mobile_number": "9876543210",
    "status": 1,
    "created_at": "2025-10-07T12:00:00Z"
  }
}
```

**Custom Error Messages**:
```json
{
  "name.required": "Broker name is required.",
  "name.max": "Broker name cannot exceed 255 characters.",
  "email.email": "Please provide a valid email address.",
  "mobile_number.digits": "Mobile number must be exactly 10 digits."
}
```

---

### PUT /api/brokers/{id}

**Description**: Update an existing broker

**Validation Rules**: Same as POST

---

## 3. Reference Users

**Description**: Reference users are individuals who refer customers to the insurance business.

### POST /api/reference-users

**Request Body**:
```json
{
  "name": "string",
  "email": "string|nullable",
  "mobile_number": "string|nullable"
}
```

**Validation Rules**:

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| name | string | Yes | required\|string\|max:255 | Reference user name |
| email | string | No | nullable\|email\|max:255 | Email address |
| mobile_number | string | No | nullable\|numeric\|digits:10 | Indian mobile number |

---

## 4. Insurance Companies

### POST /api/insurance-companies

**Description**: Create a new insurance company

**Request Body**:
```json
{
  "name": "string",
  "email": "string|nullable",
  "mobile_number": "string|nullable"
}
```

**Validation Rules**:

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| name | string | Yes | required\|string\|max:255 | Insurance company name |
| email | string | No | nullable\|email\|max:255 | Company email |
| mobile_number | string | No | nullable\|string\|max:15 | Contact number |

**Example Request**:
```json
{
  "name": "ICICI Lombard General Insurance",
  "email": "support@icicilombard.com",
  "mobile_number": "1800-266-7766"
}
```

**Success Response (201)**:
```json
{
  "success": true,
  "message": "Insurance Company created successfully",
  "data": {
    "id": 1,
    "name": "ICICI Lombard General Insurance",
    "email": "support@icicilombard.com",
    "mobile_number": "1800-266-7766",
    "status": 1,
    "created_at": "2025-10-07T12:00:00Z"
  }
}
```

---

## 5. Addon Covers

**Description**: Additional coverage options for insurance policies (e.g., Zero Depreciation, Engine Protection)

### POST /api/addon-covers

**Request Body**:
```json
{
  "name": "string",
  "description": "string|nullable",
  "is_vehicle": "integer",
  "status": "integer"
}
```

**Validation Rules**:

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| name | string | Yes | required\|string\|max:255 | Addon cover name |
| description | string | No | nullable\|string\|max:1000 | Description of the addon |
| is_vehicle | integer | Yes | required\|in:0,1 | 1 for vehicle insurance, 0 for other types |
| status | integer | Yes | required\|in:0,1 | 1 = Active, 0 = Inactive |

**Example Request**:
```json
{
  "name": "Zero Depreciation Cover",
  "description": "Covers full claim amount without depreciation",
  "is_vehicle": 1,
  "status": 1
}
```

---

## 6. Customers

### POST /api/customers

**Description**: Create a new customer (Retail or Corporate)

**Request Body**:
```json
{
  "name": "string",
  "email": "string",
  "mobile_number": "string",
  "status": "integer",
  "type": "string",
  "date_of_birth": "string|nullable",
  "wedding_anniversary_date": "string|nullable",
  "engagement_anniversary_date": "string|nullable",
  "pan_card_number": "string|nullable",
  "pan_card_path": "file|nullable",
  "aadhar_card_number": "string|nullable",
  "aadhar_card_path": "file|nullable",
  "gst_number": "string|nullable",
  "gst_path": "file|nullable"
}
```

**Validation Rules**:

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| name | string | Yes | required\|string\|max:255 | Customer full name |
| email | string | Yes | required\|email\|max:255\|unique:customers,email | Unique email address |
| mobile_number | string | Yes | required\|numeric\|digits:10 | Indian mobile number (10 digits) |
| status | integer | Yes | required\|numeric\|in:0,1 | Account status: 1 = Active, 0 = Inactive |
| type | string | Yes | required\|in:Retail,Corporate | Customer type |
| date_of_birth | date | No | nullable\|date | Format: DD/MM/YYYY or YYYY-MM-DD |
| wedding_anniversary_date | date | No | nullable\|date | Format: DD/MM/YYYY or YYYY-MM-DD |
| engagement_anniversary_date | date | No | nullable\|date | Format: DD/MM/YYYY or YYYY-MM-DD |
| pan_card_number | string | Conditional | required_if:type,Retail\|nullable\|string\|max:10 | PAN card number (required for Retail) |
| pan_card_path | file | No | nullable\|file\|max:1024\|mimetypes:application/pdf,image/jpeg,image/png | PAN card document (max 1MB) |
| aadhar_card_number | string | Conditional | required_if:type,Retail\|nullable\|string\|max:12 | Aadhar card number (required for Retail) |
| aadhar_card_path | file | No | nullable\|file\|max:1024\|mimetypes:application/pdf,image/jpeg,image/png | Aadhar card document (max 1MB) |
| gst_number | string | Conditional | required_if:type,Corporate\|nullable\|string\|max:15 | GST number (required for Corporate) |
| gst_path | file | No | nullable\|file\|max:1024\|mimetypes:application/pdf,image/jpeg,image/png | GST certificate (max 1MB) |

**Example Request (Retail Customer)**:
```json
{
  "name": "Rajesh Kumar",
  "email": "rajesh.kumar@gmail.com",
  "mobile_number": "9876543210",
  "status": 1,
  "type": "Retail",
  "date_of_birth": "15/06/1985",
  "wedding_anniversary_date": "12/11/2010",
  "pan_card_number": "ABCDE1234F",
  "aadhar_card_number": "123456789012"
}
```

**Example Request (Corporate Customer)**:
```json
{
  "name": "ABC Technologies Pvt Ltd",
  "email": "info@abctech.com",
  "mobile_number": "9876543210",
  "status": 1,
  "type": "Corporate",
  "gst_number": "27AABCU9603R1Z5"
}
```

**Success Response (201)**:
```json
{
  "success": true,
  "message": "Customer created successfully",
  "data": {
    "id": 1,
    "name": "Rajesh Kumar",
    "email": "rajesh.kumar@gmail.com",
    "mobile_number": "9876543210",
    "status": 1,
    "type": "Retail",
    "date_of_birth": "1985-06-15",
    "wedding_anniversary_date": "2010-11-12",
    "pan_card_number": "ABCDE1234F",
    "aadhar_card_number": "123456789012",
    "pan_card_path": "storage/customers/1/pan_card.pdf",
    "aadhar_card_path": "storage/customers/1/aadhar_card.pdf",
    "created_at": "2025-10-07T12:00:00Z"
  }
}
```

**Custom Error Messages**:
```json
{
  "name.required": "Customer name is required.",
  "email.required": "Email address is required.",
  "email.unique": "This email address is already taken.",
  "mobile_number.digits": "Mobile number must be exactly 10 digits.",
  "type.in": "Customer type must be either Retail or Corporate.",
  "pan_card_number.required_if": "PAN card number is required for Retail customers.",
  "aadhar_card_number.required_if": "Aadhar card number is required for Retail customers.",
  "gst_number.required_if": "GST number is required for Corporate customers."
}
```

---

### PUT /api/customers/{id}

**Description**: Update an existing customer

**Validation Rules**: Same as POST, except:
- `email`: `required|email|max:255|unique:customers,email,{id}`

---

## 7. Customer Insurances (Policies)

### POST /api/customer-insurances

**Description**: Create a new insurance policy for a customer

**Note**: This is the most complex module with 48+ fields covering vehicle, health, and life insurance policies.

**Request Body**:
```json
{
  "customer_id": "integer",
  "branch_id": "integer",
  "broker_id": "integer",
  "relationship_manager_id": "integer",
  "insurance_company_id": "integer",
  "policy_type_id": "integer",
  "premium_type_id": "integer",
  "fuel_type_id": "integer|nullable",
  "policy_no": "string",
  "issue_date": "string",
  "start_date": "string",
  "expired_date": "string",
  "tp_expiry_date": "string|nullable",
  "maturity_date": "string|nullable",
  "registration_no": "string|nullable",
  "rto": "string|nullable",
  "make_model": "string|nullable",
  "net_premium": "number|nullable",
  "premium_amount": "number|nullable",
  "gst": "number|nullable",
  "final_premium_with_gst": "number",
  "od_premium": "number|nullable",
  "tp_premium": "number|nullable",
  "cgst1": "number",
  "sgst1": "number",
  "cgst2": "number|nullable",
  "sgst2": "number|nullable",
  "mode_of_payment": "string|nullable",
  "cheque_no": "string|nullable",
  "commission_on": "string|nullable",
  "my_commission_percentage": "number|nullable",
  "my_commission_amount": "number|nullable",
  "transfer_commission_percentage": "number|nullable",
  "transfer_commission_amount": "number|nullable",
  "reference_commission_percentage": "number|nullable",
  "reference_commission_amount": "number|nullable",
  "actual_earnings": "number|nullable",
  "ncb_percentage": "number|nullable",
  "gross_vehicle_weight": "number|nullable",
  "mfg_year": "number|nullable",
  "plan_name": "string|nullable",
  "premium_paying_term": "string|nullable",
  "policy_term": "string|nullable",
  "sum_insured": "string|nullable",
  "pension_amount_yearly": "string|nullable",
  "approx_maturity_amount": "string|nullable",
  "life_insurance_payment_mode": "string|nullable",
  "remarks": "string|nullable",
  "policy_document_path": "file|nullable"
}
```

**Validation Rules**:

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| customer_id | integer | Yes | required\|exists:customers,id | Customer ID |
| branch_id | integer | Yes | required\|exists:branches,id | Branch ID |
| broker_id | integer | Yes | required\|exists:brokers,id | Broker ID |
| relationship_manager_id | integer | Yes | required\|exists:relationship_managers,id | RM ID |
| insurance_company_id | integer | Yes | required\|exists:insurance_companies,id | Insurance Company ID |
| policy_type_id | integer | Yes | required\|exists:policy_types,id | Policy Type ID |
| fuel_type_id | integer | No | nullable\|exists:fuel_types,id | Fuel Type ID (for vehicle insurance) |
| premium_type_id | integer | Yes | required\|exists:premium_types,id | Premium Type ID |
| issue_date | date | Yes | required\|date_format:d/m/Y | Policy issue date (DD/MM/YYYY) |
| expired_date | date | Yes | required\|date_format:d/m/Y | Policy expiry date (DD/MM/YYYY) |
| start_date | date | Yes | required\|date_format:d/m/Y | Policy start date (DD/MM/YYYY) |
| tp_expiry_date | date | No | nullable\|date_format:d/m/Y | Third party expiry date |
| maturity_date | date | No | nullable\|date_format:d/m/Y | Maturity date (for life insurance) |
| policy_no | string | Yes | required\|string | Policy number |
| net_premium | decimal | No | nullable\|numeric\|min:0 | Net premium amount |
| premium_amount | decimal | No | nullable\|numeric\|min:0 | Premium amount |
| gst | decimal | No | nullable\|numeric\|min:0 | GST amount |
| final_premium_with_gst | decimal | Yes | required\|numeric\|min:0 | Final premium including GST |
| mode_of_payment | string | No | nullable\|string | Payment mode (Cash/Cheque/Online) |
| cheque_no | string | No | nullable\|string | Cheque number if applicable |
| rto | string | No | nullable\|string | RTO code |
| registration_no | string | No | nullable\|string | Vehicle registration number |
| make_model | string | No | nullable\|string | Vehicle make and model |
| od_premium | decimal | No | nullable\|numeric\|min:0 | Own Damage premium |
| tp_premium | decimal | No | nullable\|numeric\|min:0 | Third Party premium |
| cgst1 | decimal | Yes | required\|numeric\|min:0 | CGST amount |
| sgst1 | decimal | Yes | required\|numeric\|min:0 | SGST amount |
| cgst2 | decimal | No | nullable\|numeric\|min:0 | Additional CGST |
| sgst2 | decimal | No | nullable\|numeric\|min:0 | Additional SGST |
| commission_on | string | No | nullable\|in:net_premium,od_premium,tp_premium | Commission calculation base |
| my_commission_percentage | decimal | No | nullable\|numeric | Commission percentage |
| my_commission_amount | decimal | No | nullable\|numeric | Commission amount |
| transfer_commission_percentage | decimal | No | nullable\|numeric | Transfer commission % |
| transfer_commission_amount | decimal | No | nullable\|numeric | Transfer commission amount |
| reference_commission_percentage | decimal | No | nullable\|numeric | Reference commission % |
| reference_commission_amount | decimal | No | nullable\|numeric | Reference commission amount |
| actual_earnings | decimal | No | nullable\|numeric | Actual earnings |
| ncb_percentage | decimal | No | nullable\|numeric | No Claim Bonus percentage |
| gross_vehicle_weight | decimal | No | nullable\|numeric | Vehicle weight |
| mfg_year | integer | No | nullable\|numeric | Manufacturing year |
| plan_name | string | No | nullable\|string | Insurance plan name |
| premium_paying_term | string | No | nullable\|string | Premium payment term |
| policy_term | string | No | nullable\|string | Policy term duration |
| sum_insured | string | No | nullable\|string | Sum insured amount |
| pension_amount_yearly | string | No | nullable\|string | Yearly pension amount |
| approx_maturity_amount | string | No | nullable\|string | Approximate maturity amount |
| life_insurance_payment_mode | string | No | nullable\|string | Payment mode for life insurance |
| remarks | string | No | nullable\|string | Additional remarks |
| policy_document_path | file | No | nullable\|file\|max:10240 | Policy document (max 10MB) |

**Example Request (Vehicle Insurance)**:
```json
{
  "customer_id": 1,
  "branch_id": 1,
  "broker_id": 1,
  "relationship_manager_id": 1,
  "insurance_company_id": 1,
  "policy_type_id": 1,
  "premium_type_id": 1,
  "fuel_type_id": 1,
  "policy_no": "ICICI/2025/ABC123",
  "issue_date": "07/10/2025",
  "start_date": "07/10/2025",
  "expired_date": "06/10/2026",
  "registration_no": "MH02AB1234",
  "rto": "MH02",
  "make_model": "Maruti Suzuki Swift VXI",
  "net_premium": 15000.00,
  "od_premium": 12000.00,
  "tp_premium": 3000.00,
  "gst": 2700.00,
  "cgst1": 1350.00,
  "sgst1": 1350.00,
  "final_premium_with_gst": 17700.00,
  "mode_of_payment": "Online",
  "ncb_percentage": 20.00,
  "mfg_year": 2020,
  "commission_on": "net_premium",
  "my_commission_percentage": 10.00,
  "my_commission_amount": 1500.00
}
```

**Success Response (201)**:
```json
{
  "success": true,
  "message": "Customer Insurance created successfully",
  "data": {
    "id": 1,
    "customer_id": 1,
    "policy_no": "ICICI/2025/ABC123",
    "registration_no": "MH02AB1234",
    "premium_amount": 15000.00,
    "final_premium_with_gst": 17700.00,
    "start_date": "2025-10-07",
    "expired_date": "2026-10-06",
    "status": 1,
    "created_at": "2025-10-07T12:00:00Z"
  }
}
```

---

## 8. Claims

### POST /api/claims

**Description**: Create a new insurance claim (Health or Vehicle)

**Request Body**:
```json
{
  "customer_insurance_id": "integer",
  "insurance_type": "string",
  "incident_date": "string",
  "description": "string|nullable",
  "whatsapp_number": "string|nullable",
  "send_email_notifications": "boolean",
  "status": "boolean"
}
```

**Validation Rules**:

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| customer_insurance_id | integer | Yes | required\|exists:customer_insurances,id | Policy/Insurance ID |
| insurance_type | string | Yes | required\|in:Health,Vehicle | Type of insurance |
| incident_date | date | Yes | required\|date\|before_or_equal:today | Date of incident (cannot be future) |
| description | string | No | nullable\|string\|max:1000 | Claim description |
| whatsapp_number | string | No | nullable\|string\|regex:/^[+]?[0-9\s\-\(\)]+$/\|max:15 | WhatsApp number for updates |
| send_email_notifications | boolean | No | boolean | Enable email notifications |
| status | boolean | Yes | required\|boolean | Claim status (Active/Inactive) |

**Example Request**:
```json
{
  "customer_insurance_id": 1,
  "insurance_type": "Vehicle",
  "incident_date": "05/10/2025",
  "description": "Front bumper damage due to minor accident",
  "whatsapp_number": "9876543210",
  "send_email_notifications": true,
  "status": true
}
```

**Success Response (201)**:
```json
{
  "success": true,
  "message": "Claim created successfully. Claim Number: CLM-2025-0001",
  "data": {
    "id": 1,
    "claim_number": "CLM-2025-0001",
    "customer_insurance_id": 1,
    "insurance_type": "Vehicle",
    "incident_date": "2025-10-05",
    "description": "Front bumper damage due to minor accident",
    "whatsapp_number": "9876543210",
    "send_email_notifications": true,
    "status": true,
    "created_at": "2025-10-07T12:00:00Z"
  }
}
```

**Custom Error Messages**:
```json
{
  "customer_insurance_id.required": "Please select a policy/insurance.",
  "customer_insurance_id.exists": "Selected policy/insurance is invalid.",
  "insurance_type.in": "Insurance type must be either Health or Vehicle.",
  "incident_date.before_or_equal": "Incident date cannot be in the future.",
  "whatsapp_number.regex": "Please enter a valid WhatsApp number."
}
```

---

## 9. Fuel Types

**Description**: Fuel types for vehicle insurance (Petrol, Diesel, CNG, Electric, Hybrid)

### POST /api/fuel-types

**Request Body**:
```json
{
  "name": "string",
  "status": "integer"
}
```

**Validation Rules**:

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| name | string | Yes | required\|string\|max:255 | Fuel type name |
| status | integer | Yes | required\|in:0,1 | Status: 1 = Active, 0 = Inactive |

**Example Values**:
- Petrol
- Diesel
- CNG
- Electric
- Hybrid

---

## 10. Policy Types

**Description**: Types of insurance policies (Comprehensive, Third Party, Own Damage, etc.)

### POST /api/policy-types

**Request Body**:
```json
{
  "name": "string",
  "status": "integer"
}
```

**Validation Rules**:

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| name | string | Yes | required\|string\|max:255 | Policy type name |
| status | integer | Yes | required\|in:0,1 | Status: 1 = Active, 0 = Inactive |

**Example Values**:
- Comprehensive
- Third Party
- Own Damage
- Standalone OD
- Standalone TP

---

## 11. Premium Types

**Description**: Premium categories for different insurance types

### POST /api/premium-types

**Request Body**:
```json
{
  "name": "string",
  "is_vehicle": "integer",
  "is_life_insurance_policies": "integer",
  "status": "integer"
}
```

**Validation Rules**:

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| name | string | Yes | required\|string\|max:255 | Premium type name |
| is_vehicle | integer | Yes | required\|in:0,1 | 1 for vehicle insurance, 0 otherwise |
| is_life_insurance_policies | integer | Yes | required\|in:0,1 | 1 for life insurance, 0 otherwise |
| status | integer | Yes | required\|in:0,1 | Status: 1 = Active, 0 = Inactive |

**Example Values**:
- Two Wheeler (is_vehicle: 1)
- Four Wheeler (is_vehicle: 1)
- Health Insurance (is_vehicle: 0)
- Term Insurance (is_vehicle: 0, is_life_insurance_policies: 1)

---

## 12. Relationship Managers

**Description**: Relationship managers assigned to customers

### POST /api/relationship-managers

**Request Body**:
```json
{
  "name": "string",
  "email": "string|nullable",
  "mobile_number": "string|nullable"
}
```

**Validation Rules**:

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| name | string | Yes | required\|string\|max:255 | RM name |
| email | string | No | nullable\|email\|max:255 | Email address |
| mobile_number | string | No | nullable\|numeric\|digits:10 | Indian mobile number |

---

## 13. Quotations

### POST /api/quotations

**Description**: Create a vehicle insurance quotation with multiple company quotes

**Request Body**:
```json
{
  "customer_id": "integer",
  "vehicle_number": "string|nullable",
  "make_model_variant": "string",
  "rto_location": "string",
  "manufacturing_year": "integer",
  "cubic_capacity_kw": "integer",
  "seating_capacity": "integer",
  "fuel_type": "string",
  "ncb_percentage": "number|nullable",
  "whatsapp_number": "string|nullable",
  "notes": "string|nullable",
  "policy_type": "string|nullable",
  "policy_tenure_years": "integer|nullable",
  "idv_vehicle": "number|nullable",
  "companies": "array|nullable"
}
```

**Validation Rules**:

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| customer_id | integer | Yes | required\|exists:customers,id | Customer ID |
| vehicle_number | string | No | nullable\|string\|max:20 | Vehicle registration number |
| make_model_variant | string | Yes | required\|string\|max:255 | Vehicle details |
| rto_location | string | Yes | required\|string\|max:255 | RTO location |
| manufacturing_year | integer | Yes | required\|integer\|min:1980\|max:{current_year+1} | Manufacturing year |
| cubic_capacity_kw | integer | Yes | required\|integer\|min:1 | Engine capacity in CC or KW |
| seating_capacity | integer | Yes | required\|integer\|min:1\|max:50 | Number of seats |
| fuel_type | string | Yes | required\|in:Petrol,Diesel,CNG,Electric,Hybrid | Fuel type |
| ncb_percentage | decimal | No | nullable\|numeric\|min:0\|max:50 | No Claim Bonus percentage |
| whatsapp_number | string | No | nullable\|string\|regex:/^[6-9]\d{9}$/ | Indian mobile number |
| notes | string | No | nullable\|string\|max:1000 | Additional notes |
| policy_type | string | No | nullable\|in:Comprehensive,Own Damage,Third Party | Policy type |
| policy_tenure_years | integer | No | nullable\|integer\|in:1,2,3 | Policy duration in years |
| idv_vehicle | decimal | No | nullable\|numeric\|min:10000\|max:10000000 | Insured Declared Value |
| companies | array | No | nullable\|array | Array of company quotes |
| companies.*.insurance_company_id | integer | Yes | required_with:companies\|exists:insurance_companies,id | Insurance company ID |
| companies.*.quote_number | string | No | nullable\|string\|max:255 | Quote reference number |
| companies.*.policy_type | string | Yes | required_with:companies\|in:Comprehensive,Own Damage,Third Party | Policy type for this company |
| companies.*.policy_tenure_years | integer | Yes | required_with:companies\|integer\|in:1,2,3 | Policy duration |
| companies.*.idv_vehicle | decimal | Yes | required_with:companies\|numeric\|min:10000\|max:10000000 | Vehicle IDV |
| companies.*.basic_od_premium | decimal | Yes | required_with:companies\|numeric\|min:0 | OD premium |
| companies.*.tp_premium | decimal | Yes | required_with:companies\|numeric\|min:0 | TP premium |
| companies.*.total_addon_premium | decimal | No | nullable\|numeric\|min:0 | Total addon premium |
| companies.*.net_premium | decimal | No | nullable\|numeric\|min:0 | Net premium |
| companies.*.sgst_amount | decimal | No | nullable\|numeric\|min:0 | SGST amount |
| companies.*.cgst_amount | decimal | No | nullable\|numeric\|min:0 | CGST amount |
| companies.*.final_premium | decimal | No | nullable\|numeric\|min:0 | Final premium including GST |
| companies.*.is_recommended | boolean | No | nullable\|boolean | Mark as recommended |
| companies.*.recommendation_note | string | No | nullable\|string\|max:500 | Recommendation reason |

**Example Request**:
```json
{
  "customer_id": 1,
  "vehicle_number": "MH02AB1234",
  "make_model_variant": "Maruti Suzuki Swift VXI",
  "rto_location": "Mumbai (MH-02)",
  "manufacturing_year": 2020,
  "cubic_capacity_kw": 1197,
  "seating_capacity": 5,
  "fuel_type": "Petrol",
  "ncb_percentage": 20.00,
  "whatsapp_number": "9876543210",
  "companies": [
    {
      "insurance_company_id": 1,
      "quote_number": "ICICI-Q-2025-001",
      "policy_type": "Comprehensive",
      "policy_tenure_years": 1,
      "idv_vehicle": 500000,
      "basic_od_premium": 12000,
      "tp_premium": 3000,
      "total_addon_premium": 2000,
      "net_premium": 17000,
      "sgst_amount": 1530,
      "cgst_amount": 1530,
      "final_premium": 20060,
      "is_recommended": true,
      "recommendation_note": "Best value for money"
    },
    {
      "insurance_company_id": 2,
      "quote_number": "HDFC-Q-2025-002",
      "policy_type": "Comprehensive",
      "policy_tenure_years": 1,
      "idv_vehicle": 500000,
      "basic_od_premium": 13000,
      "tp_premium": 3000,
      "total_addon_premium": 2500,
      "net_premium": 18500,
      "sgst_amount": 1665,
      "cgst_amount": 1665,
      "final_premium": 21830
    }
  ]
}
```

**Success Response (201)**:
```json
{
  "success": true,
  "message": "Quotation created successfully. Generating quotes from multiple companies...",
  "data": {
    "id": 1,
    "customer_id": 1,
    "vehicle_number": "MH02AB1234",
    "make_model_variant": "Maruti Suzuki Swift VXI",
    "companies_count": 2,
    "created_at": "2025-10-07T12:00:00Z"
  }
}
```

---

## 14. Users (Admins)

### POST /api/users

**Description**: Create a new admin user

**Request Body**:
```json
{
  "first_name": "string",
  "last_name": "string|nullable",
  "email": "string",
  "mobile_number": "string|nullable",
  "role_id": "integer",
  "new_password": "string",
  "status": "integer"
}
```

**Validation Rules**:

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| first_name | string | Yes | required\|string\|max:255 | First name |
| last_name | string | No | nullable\|string\|max:255 | Last name |
| email | string | Yes | required\|email\|max:255\|unique:users,email | Unique email address |
| mobile_number | string | No | nullable\|numeric\|digits:10 | Indian mobile number |
| role_id | integer | Yes | required\|exists:roles,id | Role ID |
| new_password | string | Yes | required\|string\|min:8\|max:16\|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/ | Strong password |
| status | integer | Yes | required\|in:0,1 | Status: 1 = Active, 0 = Inactive |

**Password Requirements**:
- Minimum 8 characters, maximum 16 characters
- At least one uppercase letter
- At least one lowercase letter
- At least one number
- At least one special character (@$!%*?&)

**Example Request**:
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@company.com",
  "mobile_number": "9876543210",
  "role_id": 2,
  "new_password": "SecureP@ss123",
  "status": 1
}
```

**Success Response (201)**:
```json
{
  "success": true,
  "message": "User created successfully",
  "data": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@company.com",
    "mobile_number": "9876543210",
    "role": "Manager",
    "status": 1,
    "created_at": "2025-10-07T12:00:00Z"
  }
}
```

**Custom Error Messages**:
```json
{
  "new_password.regex": "The new password format is invalid. It must contain at least one number, one special character, one uppercase letter, one lowercase letter, and be between 8 and 16 characters long."
}
```

---

### PUT /api/users/{id}

**Description**: Update an existing user

**Validation Rules**: Same as POST, except:
- `email`: `required|email|max:255|unique:users,email,{id}`
- `new_password`: Optional (only validate if provided)

---

## 15. Roles

**Description**: User roles for permission management (Admin, Manager, Employee, etc.)

### POST /api/roles

**Request Body**:
```json
{
  "name": "string",
  "guard_name": "string|nullable"
}
```

**Validation Rules**:

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| name | string | Yes | required\|string\|max:255\|unique:roles,name | Unique role name |
| guard_name | string | No | nullable\|string\|max:255 | Guard name (default: web) |

**Example Request**:
```json
{
  "name": "Branch Manager",
  "guard_name": "web"
}
```

---

## 16. Permissions

**Description**: Granular permissions for role-based access control

### POST /api/permissions

**Request Body**:
```json
{
  "name": "string",
  "guard_name": "string|nullable"
}
```

**Validation Rules**:

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| name | string | Yes | required\|string\|max:255\|unique:permissions,name | Unique permission name |
| guard_name | string | No | nullable\|string\|max:255 | Guard name (default: web) |

**Permission Naming Convention**:
- `{module}-list` (e.g., `customer-list`)
- `{module}-create` (e.g., `customer-create`)
- `{module}-edit` (e.g., `customer-edit`)
- `{module}-delete` (e.g., `customer-delete`)

---

## 17. App Settings

**Description**: Application-wide configuration settings (80+ settings across 8 categories)

### GET /api/app-settings

**Description**: Get all application settings

**Response**:
```json
{
  "success": true,
  "data": {
    "company": {
      "company_name": "Parth Rawal Insurance Services",
      "company_address": "Mumbai, Maharashtra",
      "company_phone": "+91 97277 93123",
      "company_email": "info@parthrawal.in"
    },
    "application": {
      "date_format": "d/m/Y",
      "time_format": "H:i",
      "timezone": "Asia/Kolkata",
      "pagination_default": 15
    },
    "notifications": {
      "send_email_notifications": true,
      "send_whatsapp_notifications": true,
      "send_birthday_wishes": true
    }
  }
}
```

### PUT /api/app-settings/{key}

**Description**: Update a specific setting

**Request Body**:
```json
{
  "value": "mixed"
}
```

**Validation Rules**:

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| value | mixed | Yes | required | Setting value (string, number, boolean, or JSON) |

**Setting Categories**:
1. Company Information (10 settings)
2. Application Settings (15 settings)
3. Email Configuration (8 settings)
4. WhatsApp Configuration (6 settings)
5. Notification Preferences (12 settings)
6. Business Rules (8 settings)
7. Document Management (6 settings)
8. Security Settings (5 settings)

---

## 18. Notification Templates

**Description**: Email and WhatsApp notification templates with variable placeholders

### POST /api/notification-templates

**Request Body**:
```json
{
  "notification_type_id": "integer",
  "channel": "string",
  "subject": "string|nullable",
  "body": "string",
  "variables": "array",
  "status": "integer"
}
```

**Validation Rules**:

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| notification_type_id | integer | Yes | required\|exists:notification_types,id | Notification type ID |
| channel | string | Yes | required\|in:email,whatsapp,both | Delivery channel |
| subject | string | Conditional | required_if:channel,email,both\|nullable\|string\|max:255 | Email subject (required for email) |
| body | string | Yes | required\|string | Template body with placeholders |
| variables | array | Yes | required\|array | Available template variables |
| status | integer | Yes | required\|in:0,1 | Status: 1 = Active, 0 = Inactive |

**Available Variables** (examples):
- `{customer_name}`
- `{policy_number}`
- `{expiry_date}`
- `{premium_amount}`
- `{company_name}`
- `{advisor_name}`

**Example Request**:
```json
{
  "notification_type_id": 1,
  "channel": "both",
  "subject": "Policy Renewal Reminder - {policy_number}",
  "body": "Dear {customer_name},\n\nYour policy {policy_number} is expiring on {expiry_date}. Premium amount: {premium_amount}\n\nRegards,\n{advisor_name}",
  "variables": ["customer_name", "policy_number", "expiry_date", "premium_amount", "advisor_name"],
  "status": 1
}
```

**Success Response (201)**:
```json
{
  "success": true,
  "message": "Notification Template created successfully",
  "data": {
    "id": 1,
    "notification_type": "Renewal Reminder - 30 Days",
    "channel": "both",
    "subject": "Policy Renewal Reminder - {policy_number}",
    "status": 1,
    "created_at": "2025-10-07T12:00:00Z"
  }
}
```

---

## 19. Family Groups

**Description**: Group customers in families for collective management

### POST /api/family-groups

**Request Body**:
```json
{
  "name": "string",
  "head_customer_id": "integer|nullable",
  "status": "integer"
}
```

**Validation Rules**:

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| name | string | Yes | required\|string\|max:255 | Family group name |
| head_customer_id | integer | No | nullable\|exists:customers,id | Family head customer ID |
| status | integer | Yes | required\|in:0,1 | Status: 1 = Active, 0 = Inactive |

**Example Request**:
```json
{
  "name": "Kumar Family",
  "head_customer_id": 1,
  "status": 1
}
```

**Success Response (201)**:
```json
{
  "success": true,
  "message": "Family Group created successfully",
  "data": {
    "id": 1,
    "name": "Kumar Family",
    "head_customer_id": 1,
    "head_customer_name": "Rajesh Kumar",
    "members_count": 1,
    "status": 1,
    "created_at": "2025-10-07T12:00:00Z"
  }
}
```

---

## Appendix

### HTTP Status Codes

| Code | Description | Usage |
|------|-------------|-------|
| 200 | OK | Successful GET, PUT, PATCH requests |
| 201 | Created | Successful POST requests |
| 204 | No Content | Successful DELETE requests |
| 400 | Bad Request | Invalid request format |
| 401 | Unauthorized | Authentication required |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation errors |
| 500 | Internal Server Error | Server-side errors |

### Rate Limiting

- **Limit**: 60 requests per minute per user
- **Headers**:
  - `X-RateLimit-Limit`: 60
  - `X-RateLimit-Remaining`: 45
  - `X-RateLimit-Reset`: 1633024800

### Pagination

All list endpoints support pagination:

**Query Parameters**:
```
?page=1&per_page=15
```

**Response Meta**:
```json
{
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7,
    "from": 1,
    "to": 15
  }
}
```

### Filtering & Sorting

**Query Parameters**:
```
?search=keyword
&status=1
&sort=created_at
&direction=desc
```

### Date Range Queries

```
?start_date=2025-01-01
&end_date=2025-12-31
```

---

## Contact & Support

**Technical Support**: tech@parthrawal.in
**Documentation Issues**: Report to development team
**API Version**: v1.0.0
**Last Updated**: 2025-10-07

---

**End of API Validation Documentation**
