# Database Documentation

## Entity Relationship Diagram (ERD)

### Core System Tables

```
users (Admin Users)
├── id (PK)
├── first_name
├── last_name
├── email (unique)
├── mobile_number
├── password
├── role_id → roles
├── status (boolean)
├── email_verified_at
├── remember_token
├── created_at, updated_at, deleted_at (soft delete)
└── created_by, updated_by, deleted_by

roles (Spatie Permission)
├── id (PK)
├── name
├── guard_name
└── timestamps

permissions (Spatie Permission)
├── id (PK)
├── name
├── guard_name
└── timestamps
```

### Customer Management Tables

```
customers (Customer Portal Users)
├── id (PK)
├── name
├── email
├── mobile_number
├── date_of_birth
├── wedding_anniversary_date
├── engagement_anniversary_date
├── type (enum: Retail/Corporate)
├── customer_type_id → customer_types
├── family_group_id → family_groups
├── status (boolean)
├── pan_card_number, aadhar_card_number, gst_number
├── pan_card_path, aadhar_card_path, gst_path
├── password, email_verified_at
├── password_changed_at, must_change_password
├── email_verification_token
├── password_reset_token, password_reset_expires_at, password_reset_sent_at
├── remember_token
├── created_at, updated_at, deleted_at
├── created_by, updated_by, deleted_by
└── indexes: email, mobile_number, type, family_group_id

customer_types (Master Data)
├── id (PK)
├── name
├── description
├── status (boolean)
├── sort_order
├── created_at, updated_at, deleted_at
├── created_by, updated_by, deleted_by
└── index: status

family_groups
├── id (PK)
├── name
├── family_head_id
├── status (boolean)
├── created_at, updated_at, deleted_at
├── created_by, updated_by, deleted_by
└── index: family_head_id

family_members
├── id (PK)
├── family_group_id → family_groups
├── customer_id → customers
├── relationship
├── is_head (boolean)
├── status (boolean)
├── created_at, updated_at, deleted_at
├── created_by, updated_by, deleted_by
├── unique: (family_group_id, customer_id)
└── index: is_head

customer_audit_logs
├── id (PK)
├── customer_id → customers
├── action
├── description
├── ip_address
├── user_agent
└── created_at
```

### Master Data Tables

```
insurance_companies (Master Data)
├── id (PK)
├── name
├── email (nullable)
├── mobile_number (nullable)
├── status (boolean)
├── created_at, updated_at, deleted_at
└── created_by, updated_by, deleted_by

branches (Master Data)
├── id (PK)
├── name
├── status (boolean)
├── created_at, updated_at, deleted_at
└── created_by, updated_by, deleted_by

brokers (Master Data)
├── id (PK)
├── name
├── email (nullable)
├── mobile_number (nullable)
├── status (boolean)
├── created_at, updated_at, deleted_at
└── created_by, updated_by, deleted_by

relationship_managers (Master Data)
├── id (PK)
├── name
├── email (nullable)
├── mobile_number (nullable)
├── status (boolean)
├── created_at, updated_at, deleted_at
└── created_by, updated_by, deleted_by

reference_users (Master Data)
├── id (PK)
├── name
├── email (nullable)
├── mobile_number (nullable)
├── status (boolean)
├── created_at, updated_at, deleted_at
└── created_by, updated_by, deleted_by

fuel_types (Master Data)
├── id (PK)
├── name
├── status (boolean)
├── created_at, updated_at, deleted_at
└── created_by, updated_by, deleted_by

policy_types (Master Data)
├── id (PK)
├── name
├── status (boolean)
├── created_at, updated_at, deleted_at
└── created_by, updated_by, deleted_by

premium_types (Master Data)
├── id (PK)
├── name
├── is_vehicle (boolean)
├── is_life_insurance_policies (boolean)
├── status (boolean)
├── created_at, updated_at, deleted_at
└── created_by, updated_by, deleted_by

commission_types (Master Data)
├── id (PK)
├── name
├── status (boolean)
├── created_at, updated_at, deleted_at
└── created_by, updated_by, deleted_by

addon_covers (Master Data)
├── id (PK)
├── name
├── description
├── order_no
├── status (boolean)
├── created_at, updated_at, deleted_at
└── created_by, updated_by, deleted_by
```

### Business Transaction Tables

```
customer_insurances (Main Policy Table)
├── id (PK)
├── issue_date
├── branch_id → branches
├── broker_id → brokers
├── relationship_manager_id → relationship_managers
├── customer_id → customers
├── insurance_company_id → insurance_companies
├── premium_type_id → premium_types
├── policy_type_id → policy_types
├── fuel_type_id → fuel_types
├── commission_type_id → commission_types
├── policy_no
├── registration_no
├── rto
├── make_model
├── commission_on (enum: net_premium, od_premium, tp_premium)
├── start_date, expired_date, tp_expiry_date, maturity_date
├── od_premium, tp_premium, net_premium, premium_amount
├── gst, final_premium_with_gst
├── sgst1, cgst1, sgst2, cgst2
├── my_commission_percentage, my_commission_amount
├── transfer_commission_percentage, transfer_commission_amount
├── reference_commission_percentage, reference_commission_amount
├── actual_earnings
├── ncb_percentage
├── mode_of_payment, cheque_no
├── policy_document_path
├── gross_vehicle_weight, mfg_year
├── reference_by → reference_users
├── plan_name, premium_paying_term, policy_term
├── sum_insured, pension_amount_yearly, approx_maturity_amount
├── life_insurance_payment_mode
├── remarks
├── status (tinyint)
├── is_renewed, renewed_date, new_insurance_id
├── created_at, updated_at, deleted_at
├── created_by, updated_by, deleted_by
└── indexes: branch_id, customer_id, registration_no, policy_no

quotations
├── id (PK)
├── customer_id → customers (FK cascade)
├── vehicle_number
├── make_model_variant
├── rto_location
├── manufacturing_year
├── cubic_capacity_kw
├── seating_capacity
├── fuel_type (enum)
├── ncb_percentage (decimal)
├── idv_vehicle, idv_trailer, idv_cng_lpg_kit
├── idv_electrical_accessories, idv_non_electrical_accessories
├── total_idv
├── addon_covers (JSON)
├── policy_type (enum: Comprehensive, Own Damage, Third Party)
├── policy_tenure_years
├── status (enum: Draft, Generated, Sent, Accepted, Rejected)
├── sent_at
├── whatsapp_number
├── notes
└── timestamps

quotation_statuses (Master Data)
├── id (PK)
├── name
├── description
├── color_code
├── is_final (boolean)
├── sort_order
├── status (boolean)
├── created_at, updated_at, deleted_at
└── created_by, updated_by, deleted_by

quotation_companies (Quotation Details)
├── id (PK)
├── quotation_id → quotations
├── insurance_company_id → insurance_companies
├── od_premium, tp_premium, net_premium
├── gst, total_premium
├── selected (boolean)
├── created_at, updated_at
└── created_by, updated_by
```

### Claims Management Tables

```
claims
├── id (PK)
├── claim_number (unique)
├── customer_id → customers (FK cascade)
├── customer_insurance_id → customer_insurances (FK cascade)
├── insurance_type (enum: Health, Vehicle)
├── incident_date
├── description
├── whatsapp_number
├── send_email_notifications (boolean)
├── status (boolean)
├── created_at, updated_at, deleted_at
└── created_by, updated_by, deleted_by

claim_liability_details
├── id (PK)
├── claim_id → claims
├── total_loss_amount
├── liability_coverage
├── settlement_amount
├── payment_status
├── payment_date
├── created_at, updated_at
└── created_by, updated_by

claim_stages
├── id (PK)
├── claim_id → claims
├── stage_name
├── stage_status
├── notes
├── stage_date
├── created_at, updated_at
└── created_by, updated_by

claim_documents
├── id (PK)
├── claim_id → claims
├── document_type
├── document_path
├── document_name
├── uploaded_by
├── created_at, updated_at
└── created_by, updated_by
```

### Application Configuration Tables

```
app_settings
├── id (PK)
├── key (unique)
├── category
├── type
├── value (text)
├── label
├── description
├── options (JSON)
├── validation_rules
├── sort_order
├── is_active (boolean)
├── is_sensitive (boolean)
├── created_at, updated_at
└── created_by, updated_by
```

### Security & Audit Tables

```
audit_logs
├── id (PK)
├── user_type
├── user_id
├── action
├── table_name
├── record_id
├── old_values (JSON)
├── new_values (JSON)
├── ip_address
├── user_agent
├── url
├── risk_score
└── created_at

security_events
├── id (PK)
├── user_id
├── event_type
├── severity
├── ip_address
├── user_agent
├── details (JSON)
├── resolved (boolean)
├── resolved_at
├── resolved_by
└── created_at

two_factor_auth
├── id (PK)
├── user_id (unique)
├── secret_key
├── is_enabled (boolean)
├── backup_codes (JSON)
├── last_used_at
├── created_at, updated_at
└── deleted_at

two_factor_attempts
├── id (PK)
├── user_id
├── success (boolean)
├── ip_address
├── user_agent
└── created_at

trusted_devices
├── id (PK)
├── user_id
├── device_name
├── device_fingerprint
├── ip_address
├── user_agent
├── last_used_at
├── trusted_until
├── is_active (boolean)
└── created_at, updated_at

device_tracking
├── id (PK)
├── user_id
├── device_fingerprint
├── ip_address
├── user_agent
├── last_activity
├── login_count
└── created_at, updated_at
```

### Activity Logging Tables (Spatie)

```
activity_log
├── id (PK)
├── log_name
├── description
├── subject_type
├── subject_id
├── causer_type
├── causer_id
├── properties (JSON)
├── batch_uuid
└── created_at, updated_at

reports
├── id (PK)
├── name
├── user_id
├── start_date
├── end_date
├── filters (JSON)
├── created_at, updated_at
└── created_by, updated_by
```

## Relationships Summary

### One-to-Many Relationships

1. **users → reports**: One admin user has many reports
2. **customers → customer_insurances**: One customer has many insurance policies
3. **customers → quotations**: One customer has many quotations
4. **customers → claims**: One customer has many claims
5. **family_groups → customers**: One family group has many customers
6. **family_groups → family_members**: One family group has many members
7. **insurance_companies → customer_insurances**: One company has many policies
8. **branches → customer_insurances**: One branch has many policies
9. **brokers → customer_insurances**: One broker has many policies
10. **relationship_managers → customer_insurances**: One RM has many policies
11. **fuel_types → customer_insurances**: One fuel type has many policies
12. **policy_types → customer_insurances**: One policy type has many policies
13. **premium_types → customer_insurances**: One premium type has many policies
14. **commission_types → customer_insurances**: One commission type has many policies
15. **customer_insurances → claims**: One insurance policy has many claims
16. **claims → claim_stages**: One claim has many stages
17. **claims → claim_documents**: One claim has many documents
18. **quotations → quotation_companies**: One quotation has many company quotes

### Many-to-One Relationships (BelongsTo)

1. **customer_insurances → customer**: Policy belongs to customer
2. **customer_insurances → branch**: Policy belongs to branch
3. **customer_insurances → broker**: Policy belongs to broker
4. **customer_insurances → relationship_manager**: Policy belongs to RM
5. **customer_insurances → insurance_company**: Policy belongs to company
6. **customer_insurances → fuel_type**: Policy belongs to fuel type
7. **customer_insurances → policy_type**: Policy belongs to policy type
8. **customer_insurances → premium_type**: Policy belongs to premium type
9. **customer_insurances → commission_type**: Policy belongs to commission type
10. **customers → family_group**: Customer belongs to family group
11. **customers → customer_type**: Customer belongs to type (Retail/Corporate)
12. **family_members → family_group**: Member belongs to group
13. **family_members → customer**: Member is a customer

### One-to-One Relationships

1. **customers ↔ family_member**: Customer has one family member record

### Many-to-Many Relationships (via Spatie)

1. **users ↔ roles**: Through model_has_roles
2. **users ↔ permissions**: Through model_has_permissions
3. **roles ↔ permissions**: Through role_has_permissions

## Indexes

### Primary Indexes (id columns)
All tables have primary key on `id` column

### Unique Indexes
1. **users.email**: Unique email constraint
2. **customers.email**: Unique email constraint (via index)
3. **claims.claim_number**: Unique claim number
4. **family_members**: Unique constraint on (family_group_id, customer_id)
5. **app_settings.key**: Unique configuration key
6. **two_factor_auth.user_id**: Unique per user

### Performance Indexes
1. **customers**: email, mobile_number, type, family_group_id
2. **customer_insurances**: branch_id, customer_id, registration_no, policy_no
3. **family_groups**: family_head_id
4. **family_members**: is_head
5. **customer_types**: status
6. **quotations**: customer_id (via FK)

## Foreign Keys

### With CASCADE Delete
1. **quotations.customer_id** → customers (cascade)
2. **claims.customer_id** → customers (cascade)
3. **claims.customer_insurance_id** → customer_insurances (cascade)

### Without CASCADE (Manual Control)
- All other foreign keys are managed through application-level constraints
- Soft deletes are used to preserve referential integrity

## Data Types

### Common Patterns

1. **IDs**: `unsignedBigInteger` or `bigInteger`
2. **Names**: `string(255)` default
3. **Status**: `boolean` or `tinyInteger`
4. **Dates**: `date`, `timestamp`, `datetime`
5. **Money**: `double` or `decimal(12,2)`
6. **Percentages**: `decimal(5,2)` or `double`
7. **JSON**: `json` for addon_covers, filters, properties
8. **Text**: `text` for long descriptions, remarks, notes
9. **Enums**: `enum` for fixed options (commission_on, insurance_type, etc.)

## Soft Deletes

All major business tables support soft deletes:
- users
- customers
- customer_insurances
- insurance_companies
- branches
- brokers
- relationship_managers
- reference_users
- claims
- family_groups
- family_members
- customer_types
- fuel_types
- policy_types
- premium_types
- commission_types
- addon_covers
- quotation_statuses

## Audit Trail Columns

All tables include:
- `created_at`: Timestamp when record was created
- `updated_at`: Timestamp when record was last updated
- `deleted_at`: Timestamp when record was soft deleted (nullable)
- `created_by`: User ID who created the record
- `updated_by`: User ID who last updated the record
- `deleted_by`: User ID who deleted the record

## Migration Sequence

Migrations are ordered by date prefix to maintain dependency order:

1. **2024_01_01_000001**: users
2. **2024_01_01_000007**: customer_types
3. **2024_01_01_000008**: family_groups
4. **2024_01_01_000009**: customers
5. **2024_01_01_000010**: family_members
6. **2024_01_01_000011**: customer_audit_logs
7. **2024_01_01_000012**: insurance_companies
8. **2024_01_01_000013**: branches
9. **2024_01_01_000014**: brokers
10. **2024_01_01_000015**: relationship_managers
11. **2024_01_01_000016**: reference_users
12. **2024_01_01_000017**: customer_insurances
13. **2024_01_01_000018**: quotation_statuses
14. **2024_01_01_000019**: quotations
15. **2024_01_01_000020**: quotation_companies
16. **2024_01_01_000021**: claims
17. **2024_01_01_000022**: claim_liability_details
18. **2024_01_01_000023**: claim_stages
19. **2024_01_01_000024**: claim_documents
20. **2024_01_01_000025**: commission_types
21. **2024_01_01_000026**: fuel_types
22. **2024_01_01_000027**: policy_types
23. **2024_01_01_000028**: premium_types
24. **2024_01_01_000029**: addon_covers
25. **2024_01_01_000030**: reports
26. **2024_05_28_164618**: activity_log (Spatie)
27. **2024_05_28_164623**: failed_jobs
28. **2024_05_28_164626**: model_has_permissions
29. **2024_05_28_164627**: model_has_roles
30. **2024_05_28_164628**: password_resets
31. **2024_05_28_164629**: permissions
32. **2024_05_28_164636**: role_has_permissions
33. **2024_05_28_164637**: roles
34. **2025_09_20+**: Security-related tables (2FA, device tracking, audit logs)

## Database Statistics

- **Total Tables**: 40+
- **Master Data Tables**: 10 (insurance_companies, branches, brokers, relationship_managers, reference_users, fuel_types, policy_types, premium_types, commission_types, addon_covers)
- **Transaction Tables**: 5 (customer_insurances, quotations, quotation_companies, claims, claim_*)
- **Security Tables**: 6 (audit_logs, security_events, 2FA tables, device_tracking)
- **Supporting Tables**: 19 (users, customers, family management, Spatie tables, etc.)
