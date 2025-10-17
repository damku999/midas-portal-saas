# Factory Files Report
**Date**: 2025-10-07
**Status**: ✅ COMPLETED
**Purpose**: Analysis of existing and missing factory files for test case development

---

## Summary
- **Total Models**: 33
- **Total Factories**: 33 (100%)
- **Previously Existing**: 18 (54.5%)
- **Newly Created**: 15 (45.5%)

---

## ✅ Existing Factories (18)

| Model | Factory File | Status |
|-------|--------------|--------|
| User | UserFactory.php | ✅ Exists |
| Customer | CustomerFactory.php | ✅ Exists |
| CustomerAuditLog | CustomerAuditLogFactory.php | ✅ Exists |
| CustomerInsurance | CustomerInsuranceFactory.php | ✅ Exists |
| FamilyGroup | FamilyGroupFactory.php | ✅ Exists |
| FamilyMember | FamilyMemberFactory.php | ✅ Exists |
| InsuranceCompany | InsuranceCompanyFactory.php | ✅ Exists |
| PolicyType | PolicyTypeFactory.php | ✅ Exists |
| Quotation | QuotationFactory.php | ✅ Exists |
| QuotationCompany | QuotationCompanyFactory.php | ✅ Exists |
| Claim | ClaimFactory.php | ✅ Exists |
| ClaimStage | ClaimStageFactory.php | ✅ Exists |
| ClaimDocument | ClaimDocumentFactory.php | ✅ Exists |
| ClaimLiabilityDetail | ClaimLiabilityDetailFactory.php | ✅ Exists |
| AddonCover | AddonCoverFactory.php | ✅ Exists |
| Branch | BranchFactory.php | ✅ Exists |
| Broker | BrokerFactory.php | ✅ Exists |
| ReferenceUser | ReferenceUserFactory.php | ✅ Exists |

---

## ✅ Newly Created Factories (15)

| # | Model | Factory File | Priority | Status |
|---|-------|---------------------|----------|--------|
| 1 | FuelType | FuelTypeFactory.php | High | ✅ Created |
| 2 | PremiumType | PremiumTypeFactory.php | High | ✅ Created |
| 3 | CustomerType | CustomerTypeFactory.php | High | ✅ Created |
| 4 | QuotationStatus | QuotationStatusFactory.php | High | ✅ Created |
| 5 | CommissionType | CommissionTypeFactory.php | High | ✅ Created |
| 6 | RelationshipManager | RelationshipManagerFactory.php | Medium | ✅ Created |
| 7 | AppSetting | AppSettingFactory.php | Medium | ✅ Created |
| 8 | NotificationTemplate | NotificationTemplateFactory.php | Medium | ✅ Created |
| 9 | NotificationType | NotificationTypeFactory.php | Medium | ✅ Created |
| 10 | Report | ReportFactory.php | Low | ✅ Created |
| 11 | AuditLog | AuditLogFactory.php | Low | ✅ Created |
| 12 | DeviceTracking | DeviceTrackingFactory.php | Low | ✅ Created |
| 13 | SecuritySetting | SecuritySettingFactory.php | Low | ✅ Created |
| 14 | TrustedDevice | TrustedDeviceFactory.php | Low | ✅ Created |
| 15 | TwoFactorAuth | TwoFactorAuthFactory.php | Low | ✅ Created |
| 16 | TwoFactorAttempt | TwoFactorAttemptFactory.php | Low | ✅ Created |

---

## Priority Explanation

**High Priority**: Core business models used in main workflows (insurance, customers, quotations)
**Medium Priority**: Supporting features and admin functionality
**Low Priority**: Logging, security, and audit trail models

---

## Completed Tasks ✅

1. ✅ Created all 15 missing factory files with proper field definitions
2. ✅ Added `HasFactory` trait to 5 models that didn't have it
3. ✅ Added factory method references in all model docblocks for IDE support
4. ✅ All factories follow consistent patterns with realistic test data

---

## Model-Factory Checklist

### High Priority ✅
- [x] FuelTypeFactory - Created with 6 fuel types (Petrol, Diesel, CNG, LPG, Electric, Hybrid)
- [x] PremiumTypeFactory - Created with 4 premium types and vehicle/life flags
- [x] CustomerTypeFactory - Created with 5 customer types (Individual, Corporate, VIP, SME, Enterprise)
- [x] QuotationStatusFactory - Created with 5 statuses (Draft, Generated, Sent, Accepted, Rejected)
- [x] CommissionTypeFactory - Created with 5 commission types

### Medium Priority ✅
- [x] RelationshipManagerFactory - Created with name, email, mobile fields
- [x] AppSettingFactory - Created with key, value, type, category, encryption support
- [x] NotificationTemplateFactory - Created with channel, template, variables support
- [x] NotificationTypeFactory - Created with code, category, notification channel defaults

### Low Priority ✅
- [x] ReportFactory - Created with user relationship and column selections
- [x] AuditLogFactory - Created with polymorphic relationships, risk scoring, location data
- [x] DeviceTrackingFactory - Created with device fingerprinting, trust scoring, security events
- [x] SecuritySettingFactory - Created with 2FA, session timeout, device trust settings
- [x] TrustedDeviceFactory - Created with device identification, browser/platform detection
- [x] TwoFactorAuthFactory - Created with TOTP secret, recovery codes, backup methods
- [x] TwoFactorAttemptFactory - Created with code types, success/failure tracking

---

## Factory Features

All created factories include:
- **Realistic fake data** using Faker library
- **State methods** for common test scenarios (e.g., `trusted()`, `blocked()`, `suspended()`)
- **Proper relationships** with related models using `Model::factory()`
- **Appropriate defaults** matching database schema and business logic
- **Type casting** for booleans, integers, dates, and JSON fields

---

## Usage Examples

### Basic Usage
```php
// Create a single model
$fuelType = FuelType::factory()->create();

// Create multiple models
$customers = Customer::factory()->count(10)->create();

// Create with specific attributes
$quotation = Quotation::factory()->create(['status' => 'accepted']);
```

### Using State Methods
```php
// Create trusted device
$device = DeviceTracking::factory()->trusted()->create();

// Create blocked device
$blockedDevice = DeviceTracking::factory()->blocked()->create();

// Create suspended 2FA
$twoFactor = TwoFactorAuth::factory()->pending()->create();
```

### With Relationships
```php
// Create notification template with type
$template = NotificationTemplate::factory()
    ->for(NotificationType::factory())
    ->create();

// Create report for specific user
$report = Report::factory()
    ->for(User::find(1))
    ->create();
```

---

## Models Updated with HasFactory

The following 5 models were updated to include `HasFactory` trait:
1. ✅ Report
2. ✅ SecuritySetting
3. ✅ TrustedDevice
4. ✅ TwoFactorAuth
5. ✅ TwoFactorAttempt

All other models already had the trait.

---

## IDE Support Added

Added `@method static factory()` docblock references to all 15 model files for full IDE autocomplete support:
- FuelType, PremiumType, CustomerType, QuotationStatus, CommissionType
- RelationshipManager, AppSetting, NotificationTemplate, NotificationType
- Report, AuditLog, DeviceTracking, SecuritySetting, TrustedDevice, TwoFactorAuth, TwoFactorAttempt
