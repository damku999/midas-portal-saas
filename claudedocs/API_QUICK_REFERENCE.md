# API Quick Reference Guide
**Laravel Admin Panel - Insurance Management System**

Quick lookup for all API endpoints, validation rules, and common patterns.

---

## Quick Navigation
- [Endpoint Summary](#endpoint-summary)
- [Field Quick Reference](#field-quick-reference)
- [Validation Rules Cheat Sheet](#validation-rules-cheat-sheet)
- [Common Patterns](#common-patterns)

---

## Endpoint Summary

### 1. Branches
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | /api/branches | List all branches | Yes |
| POST | /api/branches | Create branch | Yes (branch-create) |
| GET | /api/branches/{id} | Get branch details | Yes (branch-list) |
| PUT | /api/branches/{id} | Update branch | Yes (branch-edit) |
| DELETE | /api/branches/{id} | Delete branch | Yes (branch-delete) |
| PATCH | /api/branches/{id}/status | Update status | Yes (branch-edit) |

**Required Fields**: `name`
**Unique Fields**: `name`

---

### 2. Brokers
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | /api/brokers | List all brokers | Yes |
| POST | /api/brokers | Create broker | Yes (broker-create) |
| PUT | /api/brokers/{id} | Update broker | Yes (broker-edit) |
| DELETE | /api/brokers/{id} | Delete broker | Yes (broker-delete) |
| PATCH | /api/brokers/{id}/status | Update status | Yes (broker-edit) |

**Required Fields**: `name`
**Unique Fields**: None
**Optional Fields**: `email`, `mobile_number`

---

### 3. Reference Users
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | /api/reference-users | List reference users | Yes |
| POST | /api/reference-users | Create reference user | Yes (reference_user-create) |
| PUT | /api/reference-users/{id} | Update reference user | Yes (reference_user-edit) |
| DELETE | /api/reference-users/{id} | Delete reference user | Yes (reference_user-delete) |

**Required Fields**: `name`
**Optional Fields**: `email`, `mobile_number`

---

### 4. Insurance Companies
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | /api/insurance-companies | List companies | Yes |
| POST | /api/insurance-companies | Create company | Yes (insurance_company-create) |
| PUT | /api/insurance-companies/{id} | Update company | Yes (insurance_company-edit) |
| DELETE | /api/insurance-companies/{id} | Delete company | Yes (insurance_company-delete) |

**Required Fields**: `name`
**Optional Fields**: `email`, `mobile_number`

---

### 5. Addon Covers
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | /api/addon-covers | List addon covers | Yes |
| POST | /api/addon-covers | Create addon cover | Yes (addon_cover-create) |
| PUT | /api/addon-covers/{id} | Update addon cover | Yes (addon_cover-edit) |
| DELETE | /api/addon-covers/{id} | Delete addon cover | Yes (addon_cover-delete) |

**Required Fields**: `name`, `is_vehicle`, `status`
**Optional Fields**: `description`

---

### 6. Customers
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | /api/customers | List customers | Yes |
| POST | /api/customers | Create customer | Yes (customer-create) |
| GET | /api/customers/{id} | Get customer details | Yes (customer-list) |
| PUT | /api/customers/{id} | Update customer | Yes (customer-edit) |
| DELETE | /api/customers/{id} | Delete customer | Yes (customer-delete) |
| PATCH | /api/customers/{id}/status | Update status | Yes (customer-edit) |

**Required Fields**: `name`, `email`, `mobile_number`, `status`, `type`
**Unique Fields**: `email`
**Conditional Fields**:
- Retail: `pan_card_number`, `aadhar_card_number`
- Corporate: `gst_number`

---

### 7. Customer Insurances (Policies)
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | /api/customer-insurances | List policies | Yes |
| POST | /api/customer-insurances | Create policy | Yes (customer-insurance-create) |
| GET | /api/customer-insurances/{id} | Get policy details | Yes (customer-insurance-list) |
| PUT | /api/customer-insurances/{id} | Update policy | Yes (customer-insurance-edit) |
| DELETE | /api/customer-insurances/{id} | Delete policy | Yes (customer-insurance-delete) |
| POST | /api/customer-insurances/{id}/renew | Renew policy | Yes (customer-insurance-edit) |

**Required Fields**: `customer_id`, `branch_id`, `broker_id`, `relationship_manager_id`, `insurance_company_id`, `policy_type_id`, `premium_type_id`, `policy_no`, `issue_date`, `start_date`, `expired_date`, `final_premium_with_gst`, `cgst1`, `sgst1`
**48+ Total Fields** (vehicle, health, life insurance)

---

### 8. Claims
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | /api/claims | List claims | Yes |
| POST | /api/claims | Create claim | Yes (claim-create) |
| GET | /api/claims/{id} | Get claim details | Yes (claim-list) |
| PUT | /api/claims/{id} | Update claim | Yes (claim-edit) |
| DELETE | /api/claims/{id} | Delete claim | Yes (claim-delete) |
| POST | /api/claims/{id}/stages | Add claim stage | Yes (claim-edit) |
| PATCH | /api/claims/{id}/claim-number | Update claim number | Yes (claim-edit) |

**Required Fields**: `customer_insurance_id`, `insurance_type`, `incident_date`, `status`
**Insurance Types**: `Health`, `Vehicle`

---

### 9. Fuel Types
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | /api/fuel-types | List fuel types | Yes |
| POST | /api/fuel-types | Create fuel type | Yes (fuel_type-create) |
| PUT | /api/fuel-types/{id} | Update fuel type | Yes (fuel_type-edit) |
| DELETE | /api/fuel-types/{id} | Delete fuel type | Yes (fuel_type-delete) |

**Required Fields**: `name`, `status`
**Common Values**: Petrol, Diesel, CNG, Electric, Hybrid

---

### 10. Policy Types
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | /api/policy-types | List policy types | Yes |
| POST | /api/policy-types | Create policy type | Yes (policy_type-create) |
| PUT | /api/policy-types/{id} | Update policy type | Yes (policy_type-edit) |
| DELETE | /api/policy-types/{id} | Delete policy type | Yes (policy_type-delete) |

**Required Fields**: `name`, `status`
**Common Values**: Comprehensive, Third Party, Own Damage

---

### 11. Premium Types
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | /api/premium-types | List premium types | Yes |
| POST | /api/premium-types | Create premium type | Yes (premium_type-create) |
| PUT | /api/premium-types/{id} | Update premium type | Yes (premium_type-edit) |
| DELETE | /api/premium-types/{id} | Delete premium type | Yes (premium_type-delete) |

**Required Fields**: `name`, `is_vehicle`, `is_life_insurance_policies`, `status`

---

### 12. Relationship Managers
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | /api/relationship-managers | List RMs | Yes |
| POST | /api/relationship-managers | Create RM | Yes (relationship_manager-create) |
| PUT | /api/relationship-managers/{id} | Update RM | Yes (relationship_manager-edit) |
| DELETE | /api/relationship-managers/{id} | Delete RM | Yes (relationship_manager-delete) |

**Required Fields**: `name`
**Optional Fields**: `email`, `mobile_number`

---

### 13. Quotations
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | /api/quotations | List quotations | Yes |
| POST | /api/quotations | Create quotation | Yes (quotation-create) |
| GET | /api/quotations/{id} | Get quotation details | Yes (quotation-list) |
| PUT | /api/quotations/{id} | Update quotation | Yes (quotation-edit) |
| DELETE | /api/quotations/{id} | Delete quotation | Yes (quotation-delete) |
| POST | /api/quotations/{id}/generate | Generate quotes | Yes (quotation-generate) |
| GET | /api/quotations/{id}/pdf | Download PDF | Yes (quotation-download-pdf) |
| POST | /api/quotations/{id}/whatsapp | Send via WhatsApp | Yes (quotation-send-whatsapp) |

**Required Fields**: `customer_id`, `make_model_variant`, `rto_location`, `manufacturing_year`, `cubic_capacity_kw`, `seating_capacity`, `fuel_type`

---

### 14. Users (Admins)
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | /api/users | List users | Yes |
| POST | /api/users | Create user | Yes (user-create) |
| GET | /api/users/{id} | Get user details | Yes (user-list) |
| PUT | /api/users/{id} | Update user | Yes (user-edit) |
| DELETE | /api/users/{id} | Delete user | Yes (user-delete) |
| PATCH | /api/users/{id}/status | Update status | Yes (user-edit) |
| PATCH | /api/users/{id}/password | Change password | Yes (user-edit) |

**Required Fields**: `first_name`, `email`, `role_id`, `new_password`, `status`
**Unique Fields**: `email`
**Password Requirements**: 8-16 chars, uppercase, lowercase, number, special char

---

### 15. Roles
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | /api/roles | List roles | Yes |
| POST | /api/roles | Create role | Yes (role-create) |
| PUT | /api/roles/{id} | Update role | Yes (role-edit) |
| DELETE | /api/roles/{id} | Delete role | Yes (role-delete) |
| POST | /api/roles/{id}/permissions | Assign permissions | Yes (role-edit) |

**Required Fields**: `name`
**Unique Fields**: `name`

---

### 16. Permissions
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | /api/permissions | List permissions | Yes |
| POST | /api/permissions | Create permission | Yes (permission-create) |
| PUT | /api/permissions/{id} | Update permission | Yes (permission-edit) |
| DELETE | /api/permissions/{id} | Delete permission | Yes (permission-delete) |

**Required Fields**: `name`
**Unique Fields**: `name`
**Naming Convention**: `{module}-{action}` (e.g., `customer-create`)

---

### 17. App Settings
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | /api/app-settings | Get all settings | Yes |
| GET | /api/app-settings/{key} | Get specific setting | Yes |
| PUT | /api/app-settings/{key} | Update setting | Yes (app_setting-edit) |
| POST | /api/app-settings/bulk | Bulk update | Yes (app_setting-edit) |

**80+ Settings** across 8 categories

---

### 18. Notification Templates
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | /api/notification-templates | List templates | Yes |
| POST | /api/notification-templates | Create template | Yes (notification_template-create) |
| PUT | /api/notification-templates/{id} | Update template | Yes (notification_template-edit) |
| DELETE | /api/notification-templates/{id} | Delete template | Yes (notification_template-delete) |
| POST | /api/notification-templates/preview | Preview template | Yes |

**Required Fields**: `notification_type_id`, `channel`, `body`, `variables`, `status`
**Channels**: `email`, `whatsapp`, `both`

---

### 19. Family Groups
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | /api/family-groups | List family groups | Yes |
| POST | /api/family-groups | Create family group | Yes (family_group-create) |
| PUT | /api/family-groups/{id} | Update family group | Yes (family_group-edit) |
| DELETE | /api/family-groups/{id} | Delete family group | Yes (family_group-delete) |
| POST | /api/family-groups/{id}/members | Add member | Yes (family_group-edit) |

**Required Fields**: `name`, `status`
**Optional Fields**: `head_customer_id`

---

## Field Quick Reference

### Common Required Fields

#### Contact Information
```json
{
  "email": "string|email|max:255",
  "mobile_number": "string|numeric|digits:10"
}
```

#### Status Fields
```json
{
  "status": "integer|in:0,1"  // 1 = Active, 0 = Inactive
}
```

#### Date Fields
```json
{
  "date": "date_format:d/m/Y"  // Format: DD/MM/YYYY
}
```

#### Foreign Keys
```json
{
  "customer_id": "integer|exists:customers,id",
  "branch_id": "integer|exists:branches,id",
  "broker_id": "integer|exists:brokers,id"
}
```

---

### Customer Insurance Fields (48+ fields)

#### Required Core Fields
```
customer_id, branch_id, broker_id, relationship_manager_id,
insurance_company_id, policy_type_id, premium_type_id,
policy_no, issue_date, start_date, expired_date,
final_premium_with_gst, cgst1, sgst1
```

#### Vehicle-Specific Fields
```
fuel_type_id, registration_no, rto, make_model,
od_premium, tp_premium, ncb_percentage, mfg_year
```

#### Life Insurance Fields
```
plan_name, premium_paying_term, policy_term,
sum_insured, maturity_date, pension_amount_yearly
```

#### Commission Fields
```
commission_on, my_commission_percentage, my_commission_amount,
transfer_commission_percentage, transfer_commission_amount,
reference_commission_percentage, reference_commission_amount,
actual_earnings
```

---

## Validation Rules Cheat Sheet

### String Validations
```
required          - Field is mandatory
nullable          - Field can be null/empty
string            - Must be a string
max:255           - Maximum 255 characters
min:8             - Minimum 8 characters
regex:/pattern/   - Must match regex pattern
```

### Numeric Validations
```
numeric           - Must be numeric
integer           - Must be an integer
digits:10         - Must be exactly 10 digits
min:0             - Minimum value 0
max:100           - Maximum value 100
```

### Date Validations
```
date              - Must be a valid date
date_format:d/m/Y - Must match DD/MM/YYYY format
before:today      - Must be before today
after:start_date  - Must be after start_date
before_or_equal:today - Cannot be in future
```

### Relationship Validations
```
exists:customers,id           - Must exist in customers table
unique:customers,email        - Must be unique in customers table
unique:customers,email,{id}   - Must be unique except current record
```

### Conditional Validations
```
required_if:type,Retail       - Required if type is Retail
required_with:companies       - Required when companies is present
nullable                      - Can be empty/null
```

### File Validations
```
file                          - Must be a file upload
max:1024                      - Maximum 1024 KB (1 MB)
mimetypes:application/pdf,image/jpeg,image/png - Allowed types
```

### Boolean Validations
```
boolean                       - Must be true/false
in:0,1                        - Must be 0 or 1
```

### Array Validations
```
array                         - Must be an array
array|min:1                   - Array with at least 1 item
companies.*.insurance_company_id - Validate each company's field
```

---

## Common Patterns

### 1. Create Resource Pattern
```
POST /api/{resource}
Body: { required_fields... }
Response: 201 Created
```

### 2. Update Resource Pattern
```
PUT /api/{resource}/{id}
Body: { fields_to_update... }
Response: 200 OK
```

### 3. List Resources Pattern
```
GET /api/{resource}?page=1&per_page=15&search=keyword
Response: 200 OK with pagination
```

### 4. Delete Resource Pattern
```
DELETE /api/{resource}/{id}
Response: 204 No Content
```

### 5. Update Status Pattern
```
PATCH /api/{resource}/{id}/status
Body: { "status": 1 }
Response: 200 OK
```

---

## Error Response Patterns

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"]
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

### Unauthorized (401)
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

### Forbidden (403)
```json
{
  "success": false,
  "message": "Insufficient permissions"
}
```

---

## Success Response Patterns

### Single Resource
```json
{
  "success": true,
  "message": "Resource created successfully",
  "data": { /* resource object */ }
}
```

### Resource List
```json
{
  "success": true,
  "data": [ /* array of resources */ ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100
  }
}
```

---

## Quick Tips

### Indian Mobile Number Format
- **Validation**: `numeric|digits:10`
- **Example**: `9876543210`
- **First digit**: Must be 6-9

### PAN Card Format
- **Validation**: `string|max:10`
- **Format**: `ABCDE1234F`
- **Pattern**: 5 letters + 4 digits + 1 letter

### GST Number Format
- **Validation**: `string|max:15`
- **Format**: `27AABCU9603R1Z5`

### Password Requirements
- **Length**: 8-16 characters
- **Must contain**: Uppercase, lowercase, number, special char
- **Regex**: `/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/`

### Date Formats
- **UI Format**: `DD/MM/YYYY` (07/10/2025)
- **Database Format**: `YYYY-MM-DD` (2025-10-07)
- **API Request**: Accept both formats
- **API Response**: ISO 8601 (`2025-10-07T12:00:00Z`)

---

## Rate Limiting
- **Limit**: 60 requests per minute
- **Headers**: `X-RateLimit-Limit`, `X-RateLimit-Remaining`

## Pagination
- **Default**: 15 items per page
- **Query**: `?page=1&per_page=15`
- **Max per page**: 100

## Filtering
- **Search**: `?search=keyword`
- **Status**: `?status=1`
- **Date Range**: `?start_date=2025-01-01&end_date=2025-12-31`
- **Sorting**: `?sort=created_at&direction=desc`

---

**End of Quick Reference**
