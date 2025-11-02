# Lead Management Module - Implementation Plan

> ⚠️ **HISTORICAL DOCUMENT** - This was the original planning document
>
> ✅ **Implementation is COMPLETE** - See [LEAD_MANAGEMENT_COMPLETE.md](LEAD_MANAGEMENT_COMPLETE.md) for current status
>
> This document is kept for historical reference and understanding the original design decisions.

---

**Project**: Midas Portal Insurance Management System
**Module**: Lead Management System
**Status**: ✅ COMPLETED (This is the planning document)
**Last Updated**: 2025-11-01

---

## Overview

Comprehensive lead management module for insurance brokers to capture, track, nurture, and convert leads into customers. Seamlessly integrates with existing Customers, Quotations, Policies, and Notifications modules.

---

## Database Schema

### 1. lead_sources (Master Table)
```sql
id                  BIGINT UNSIGNED PRIMARY KEY
name                VARCHAR(100) UNIQUE - Lead source name
description         VARCHAR(255) NULLABLE - Description
is_active           BOOLEAN DEFAULT true - Active status
display_order       INTEGER DEFAULT 0 - Sort order
created_at          TIMESTAMP
updated_at          TIMESTAMP
deleted_at          TIMESTAMP NULLABLE

Indexes:
- is_active
- display_order
```

**Default Sources**: Website, Referral, Social Media, Cold Call, Walk-in, Email Campaign, Trade Show, Partner, Existing Customer, Other

---

### 2. lead_statuses (Master Table)
```sql
id                  BIGINT UNSIGNED PRIMARY KEY
name                VARCHAR(100) UNIQUE - Status name
description         VARCHAR(255) NULLABLE - Description
color               VARCHAR(20) NULLABLE - Badge color (success, warning, danger)
is_active           BOOLEAN DEFAULT true - Active status
is_converted        BOOLEAN DEFAULT false - Indicates conversion
is_lost             BOOLEAN DEFAULT false - Indicates lost lead
display_order       INTEGER DEFAULT 0 - Sort order
created_at          TIMESTAMP
updated_at          TIMESTAMP
deleted_at          TIMESTAMP NULLABLE

Indexes:
- is_active
- [is_converted, is_lost]
- display_order
```

**Default Statuses**:
- New (color: info)
- Contacted (color: primary)
- Quotation Sent (color: warning)
- Interested (color: success)
- Converted (color: success, is_converted: true)
- Lost (color: danger, is_lost: true)

---

### 3. leads (Main Entity)
```sql
id                      BIGINT UNSIGNED PRIMARY KEY
lead_number             VARCHAR(50) UNIQUE - Format: LD-YYYYMM-XXXX
name                    VARCHAR(255) - Lead full name
email                   VARCHAR(255) NULLABLE - Email address
mobile_number           VARCHAR(20) - Primary mobile
alternate_mobile        VARCHAR(20) NULLABLE - Alternate mobile
city                    VARCHAR(100) NULLABLE
state                   VARCHAR(100) NULLABLE
pincode                 VARCHAR(10) NULLABLE
address                 TEXT NULLABLE - Full address
date_of_birth           DATE NULLABLE
age                     INTEGER NULLABLE - Calculated field
occupation              VARCHAR(255) NULLABLE
source_id               FOREIGN KEY → lead_sources.id
product_interest        VARCHAR(255) NULLABLE - Vehicle/Life/Health
status_id               FOREIGN KEY → lead_statuses.id
priority                ENUM('low', 'medium', 'high') DEFAULT 'medium'
assigned_to             FOREIGN KEY → users.id NULLABLE
relationship_manager_id FOREIGN KEY → relationship_managers.id NULLABLE
reference_user_id       FOREIGN KEY → reference_users.id NULLABLE
next_follow_up_date     DATE NULLABLE
remarks                 TEXT NULLABLE - Internal notes
converted_customer_id   FOREIGN KEY → customers.id NULLABLE
converted_at            TIMESTAMP NULLABLE - Conversion timestamp
conversion_notes        TEXT NULLABLE - Conversion details
lost_reason             TEXT NULLABLE - Reason for loss
lost_at                 TIMESTAMP NULLABLE - Loss timestamp
created_by              FOREIGN KEY → users.id
updated_by              FOREIGN KEY → users.id NULLABLE
created_at              TIMESTAMP
updated_at              TIMESTAMP
deleted_at              TIMESTAMP NULLABLE

Indexes:
- lead_number UNIQUE
- email
- mobile_number
- [source_id, status_id]
- [assigned_to, status_id]
- next_follow_up_date
- converted_customer_id
- [created_at, status_id]
```

---

### 4. lead_activities (Activity Tracking)
```sql
id                  BIGINT UNSIGNED PRIMARY KEY
lead_id             FOREIGN KEY → leads.id
activity_type       ENUM('call', 'email', 'meeting', 'note', 'status_change', 'assignment', 'document', 'quotation')
subject             VARCHAR(255) - Activity subject/title
description         TEXT NULLABLE - Activity details
outcome             VARCHAR(255) NULLABLE - Meeting/call outcome
next_action         TEXT NULLABLE - Planned next steps
scheduled_at        TIMESTAMP NULLABLE - Scheduled activity time
completed_at        TIMESTAMP NULLABLE - Activity completion time
created_by          FOREIGN KEY → users.id
created_at          TIMESTAMP
updated_at          TIMESTAMP

Indexes:
- [lead_id, activity_type]
- [lead_id, scheduled_at]
- [created_by, created_at]
- scheduled_at (for reminders)
```

---

### 5. lead_documents (Attachments)
```sql
id                  BIGINT UNSIGNED PRIMARY KEY
lead_id             FOREIGN KEY → leads.id
document_type       VARCHAR(100) - Document category
file_name           VARCHAR(255) - Original filename
file_path           VARCHAR(500) - Storage path
file_size           INTEGER - Size in bytes
mime_type           VARCHAR(100) - File MIME type
uploaded_by         FOREIGN KEY → users.id
created_at          TIMESTAMP
updated_at          TIMESTAMP

Indexes:
- [lead_id, document_type]
- uploaded_by
```

---

## Architecture Components

### Models (app/Models/)
1. **Lead.php**
   - Relationships: belongsTo (LeadSource, LeadStatus, User[assigned], Customer[converted])
   - hasMany (LeadActivity, LeadDocument)
   - Scopes: active(), converted(), lost(), byStatus(), bySource(), assignedTo()
   - Mutators: lead_number auto-generation, age calculation
   - Casts: date_of_birth (date), next_follow_up_date (date), converted_at (datetime)

2. **LeadSource.php**
   - Relationships: hasMany (Lead)
   - Scopes: active(), ordered()

3. **LeadStatus.php**
   - Relationships: hasMany (Lead)
   - Scopes: active(), converted(), lost(), ordered()

4. **LeadActivity.php**
   - Relationships: belongsTo (Lead, User[created_by])
   - Scopes: scheduled(), completed(), byType()

5. **LeadDocument.php**
   - Relationships: belongsTo (Lead, User[uploaded_by])
   - Scopes: byType()

### Repository (app/Repositories/)
**LeadRepository.php**
- `all($filters)` - Paginated leads with filters
- `find($id)` - Single lead with relationships
- `create($data)` - Create with lead_number generation
- `update($id, $data)` - Update lead
- `delete($id)` - Soft delete
- `convert($leadId, $customerId, $notes)` - Convert to customer
- `markAsLost($leadId, $reason)` - Mark as lost
- `assignTo($leadId, $userId)` - Assign to user
- `updateStatus($leadId, $statusId, $notes)` - Change status
- `getByStatus($statusId)` - Leads by status
- `getUpcomingFollowUps($userId, $days)` - Follow-up reminders

### Service (app/Services/)
**LeadService.php**
- Business logic layer
- Integration with NotificationLoggerService, AuditService, ExcelExportService
- Methods:
  - `createLead($data)` - Create with notifications
  - `updateLead($id, $data)` - Update with audit
  - `convertToCustomer($leadId, $customerData)` - Full conversion workflow
  - `sendFollowUpReminders()` - Scheduled reminders
  - `generateLeadReport($filters)` - Analytics
  - `exportLeads($filters)` - Excel export
  - `bulkAssign($leadIds, $userId)` - Bulk operations

### Controllers (app/Http/Controllers/)
**LeadController.php**
- CRUD operations
- RESTful API endpoints
- Routes: index, create, store, show, edit, update, destroy
- Additional: convert, assignBulk, export, dashboard

### Form Requests (app/Http/Requests/)
1. **StoreLeadRequest.php** - Validation for create
2. **UpdateLeadRequest.php** - Validation for update
3. **ConvertLeadRequest.php** - Validation for conversion

### Events & Listeners (app/Events/, app/Listeners/)

**Events**:
- LeadCreated - Trigger welcome notification
- LeadAssigned - Notify assigned user
- LeadStatusChanged - Status change notifications
- LeadConverted - Conversion celebration
- FollowUpDue - Reminder notifications

**Listeners**:
- SendLeadCreatedNotification
- SendLeadAssignmentNotification
- SendStatusChangeNotification
- SendConversionNotification
- SendFollowUpReminder

---

## Integration Points

### 1. Customers Module
- **Conversion Workflow**: Lead → Customer creation
- **Data Mapping**: Name, email, mobile, DOB, address → Customer fields
- **Linking**: `converted_customer_id` foreign key
- **Family Groups**: Auto-assign if applicable

### 2. Quotations Module
- **Lead Quotations**: Attach quotations to leads before conversion
- **Conversion Transfer**: Transfer quotations to customer on conversion
- **Status Sync**: Update lead status when quotation sent

### 3. Customer Insurances (Policies)
- **Policy Linking**: Link issued policies to original lead
- **Source Tracking**: Track which lead source generated most policies
- **Commission Attribution**: Attribute commissions to lead sources

### 4. Notifications System
- **Multi-Channel**: Email, WhatsApp, SMS for lead communications
- **Templates**: Create lead-specific templates
- **Automated**: Follow-up reminders, status changes, assignments
- **Webhooks**: Delivery tracking for communications

### 5. Reporting & Analytics
- **Conversion Funnel**: Track lead → quotation → customer → policy
- **Source Performance**: ROI by lead source
- **Agent Performance**: Conversion rates by assigned user
- **Time Metrics**: Average time from lead to conversion

---

## Implementation Phases

### **Phase 1: Core Structure** (Week 1) - **IN PROGRESS**
- [x] Create migration: lead_sources
- [x] Create migration: lead_statuses
- [ ] Create migration: leads
- [ ] Create migration: lead_activities
- [ ] Create migration: lead_documents
- [ ] Create seeders: lead_sources, lead_statuses
- [ ] Run migrations and verify database
- [ ] Create Models: Lead, LeadSource, LeadStatus, LeadActivity, LeadDocument
- [ ] Create LeadRepository
- [ ] Create LeadService (basic CRUD)

### **Phase 2: CRUD Operations** (Week 1-2)
- [ ] Create LeadController with RESTful methods
- [ ] Create Form Requests: StoreLeadRequest, UpdateLeadRequest
- [ ] Create permissions and roles (lead.create, lead.view, lead.edit, lead.delete)
- [ ] Create views: index, create, edit, show
- [ ] Implement lead_number auto-generation
- [ ] Add validation and error handling

### **Phase 3: Workflow & Activities** (Week 2)
- [ ] Implement status change workflow
- [ ] Create LeadActivity logging system
- [ ] Implement lead assignment functionality
- [ ] Create activity timeline UI
- [ ] Add document upload functionality
- [ ] Implement follow-up date tracking

### **Phase 4: Conversion Integration** (Week 2-3)
- [ ] Build lead → customer conversion workflow
- [ ] Implement quotation linking to leads
- [ ] Create conversion validation rules
- [ ] Add policy linkage after conversion
- [ ] Build "Mark as Lost" functionality with reasons

### **Phase 5: Notifications & Reminders** (Week 3)
- [ ] Create Events: LeadCreated, LeadAssigned, LeadStatusChanged, etc.
- [ ] Create Listeners for notifications
- [ ] Create notification templates for leads
- [ ] Implement follow-up reminder scheduler
- [ ] Add WhatsApp/SMS integration for lead communications

### **Phase 6: Analytics & Reporting** (Week 3-4)
- [ ] Create lead dashboard with KPIs
- [ ] Build conversion funnel visualization
- [ ] Implement source performance reports
- [ ] Create agent performance metrics
- [ ] Add Excel export functionality
- [ ] Build lead activity reports

### **Phase 7: Testing & Polish** (Week 4)
- [ ] Write unit tests (LeadRepository, LeadService)
- [ ] Write feature tests (LeadController, workflows)
- [ ] Write integration tests (conversion, notifications)
- [ ] Performance optimization (eager loading, indexing)
- [ ] Code review and refactoring
- [ ] Documentation updates (API docs, user guide)

---

## Technical Standards

### Code Quality
- **Style Guide**: PSR-12 (enforced by Laravel Pint)
- **Static Analysis**: PHPStan Level 5
- **Testing**: Pest PHP with 80%+ coverage goal
- **Documentation**: PHPDoc for all public methods

### Security
- **Authorization**: Spatie Permission for RBAC
- **Validation**: Form Requests for all inputs
- **Sanitization**: Blade escaping, XSS prevention
- **Audit Logging**: Track all lead modifications

### Performance
- **Eager Loading**: Prevent N+1 queries
- **Caching**: Cache master data (sources, statuses)
- **Indexing**: All foreign keys and filter columns
- **Queue**: Background jobs for notifications

---

## API Endpoints

### Lead Management
```
GET    /leads                    # List leads (with filters)
GET    /leads/create             # Show create form
POST   /leads/store              # Create new lead
GET    /leads/{id}               # Show lead details
GET    /leads/{id}/edit          # Show edit form
PUT    /leads/{id}/update        # Update lead
DELETE /leads/{id}/destroy       # Delete lead
```

### Additional Operations
```
POST   /leads/{id}/convert       # Convert lead to customer
POST   /leads/{id}/assign        # Assign lead to user
POST   /leads/bulk-assign        # Bulk assign leads
POST   /leads/{id}/status        # Change lead status
POST   /leads/{id}/activity      # Add activity log
POST   /leads/{id}/document      # Upload document
GET    /leads/export             # Export to Excel
GET    /leads/dashboard          # Analytics dashboard
```

---

## Success Metrics

### Performance KPIs
- Lead capture time: < 2 minutes
- Conversion tracking: 100% accuracy
- Follow-up compliance: > 90%
- Data integrity: Zero duplicate leads

### Business KPIs
- Lead-to-customer conversion rate
- Average time from lead to conversion
- Lead source ROI analysis
- Agent performance benchmarking

---

## Risk Mitigation

### Data Integrity
- Unique lead_number generation with mutex locks
- Foreign key constraints on all relationships
- Soft deletes to preserve history
- Regular database backups

### Performance
- Database indexing on high-traffic columns
- Query optimization with eager loading
- Caching strategy for master data
- Queue-based background processing

### Security
- Role-based access control (RBAC)
- Input validation and sanitization
- Audit logging for compliance
- Secure file upload handling

---

## Timeline Summary

- **Week 1**: Core structure + CRUD operations
- **Week 2**: Workflow + Conversion integration
- **Week 3**: Notifications + Analytics
- **Week 4**: Testing + Polish

**Total Duration**: 4 weeks
**Team**: Development team with existing Midas Portal knowledge

---

## Current Progress

**Date**: 2025-11-01
**Phase**: Phase 1 - Core Structure
**Status**: In Progress

### Completed
✅ Migration created: lead_sources (2025_11_01_182021)
✅ Migration created: lead_statuses (2025_11_01_182131)

### In Progress
🔄 Migration: leads table

### Next Steps
1. Complete leads migration
2. Create lead_activities migration
3. Create lead_documents migration
4. Create seeders for master data
5. Run and verify migrations

---

**Document Version**: 1.0
**Last Updated**: 2025-11-01 18:30:00
