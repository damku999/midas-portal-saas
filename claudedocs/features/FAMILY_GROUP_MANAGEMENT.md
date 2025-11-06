# Family Group Management

**Version:** 1.0
**Last Updated:** 2025-11-06
**Status:** Production

## Overview

Family group system for managing related customers, shared policies, and family-based insurance products.

### Key Features

- **Family Groups**: Organize customers into family units
- **Family Head**: Designated primary contact for family
- **Family Members**: Link customers to family groups
- **Shared Access**: Family members can view each other's policies
- **Family Policies**: Track policies that cover multiple family members

## Data Models

### FamilyGroup Model

**File**: `app/Models/FamilyGroup.php`

**Attributes**:
- `id` - Primary key
- `name` - Family name/identifier
- `family_head_id` - Customer ID of family head
- `status` - Active/inactive (boolean)
- `created_by/updated_by/deleted_by` - Audit trail

**Relationships**:
```php
$familyGroup->familyHead(); // BelongsTo Customer
$familyGroup->familyMembers(); // HasMany FamilyMember
$familyGroup->customers(); // HasMany Customer (via family_group_id)
```

**Methods**:
```php
$familyGroup->isActive(); // Check if active
```

### FamilyMember Model

**File**: `app/Models/FamilyMember.php`

**Attributes**:
- `id` - Primary key
- `family_group_id` - Foreign key to family_groups
- `customer_id` - Foreign key to customers
- `relationship` - Relationship to family head (spouse, child, parent, etc.)
- `is_dependent` - Whether member is dependent
- `status` - Active/inactive

**Relationships**:
```php
$familyMember->familyGroup(); // BelongsTo FamilyGroup
$familyMember->customer(); // BelongsTo Customer
```

### Customer Integration

**Customer Model** has:
```php
$customer->family_group_id; // Foreign key
$customer->familyGroup(); // BelongsTo FamilyGroup
$customer->familyMembers(); // HasMany FamilyMember

// Check if customer is family head
$customer->isFamilyHead(); // Returns boolean
```

## Database Schema

### family_groups Table
```sql
CREATE TABLE family_groups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    family_head_id BIGINT UNSIGNED NULL,
    status BOOLEAN DEFAULT 1,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    deleted_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (family_head_id) REFERENCES customers(id)
);
```

### family_members Table
```sql
CREATE TABLE family_members (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    family_group_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    relationship VARCHAR(50) NOT NULL,
    is_dependent BOOLEAN DEFAULT 0,
    status BOOLEAN DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (family_group_id) REFERENCES family_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);
```

### customers Table Update
```sql
ALTER TABLE customers
ADD COLUMN family_group_id BIGINT UNSIGNED NULL,
ADD FOREIGN KEY (family_group_id) REFERENCES family_groups(id);
```

## Usage Examples

### Create Family Group

```php
use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use App\Models\Customer;

// Create family group
$family = FamilyGroup::create([
    'name' => 'Kumar Family',
    'family_head_id' => $headCustomer->id,
    'status' => true,
    'created_by' => auth()->id()
]);

// Link family head
$headCustomer->update(['family_group_id' => $family->id]);

// Add family members
FamilyMember::create([
    'family_group_id' => $family->id,
    'customer_id' => $spouseCustomer->id,
    'relationship' => 'spouse',
    'is_dependent' => false,
    'status' => true
]);

FamilyMember::create([
    'family_group_id' => $family->id,
    'customer_id' => $childCustomer->id,
    'relationship' => 'child',
    'is_dependent' => true,
    'status' => true
]);
```

### Access Family Information

```php
// Get all family members
$family = FamilyGroup::with(['familyHead', 'familyMembers.customer'])->find($familyId);

foreach ($family->familyMembers as $member) {
    echo "{$member->customer->name} - {$member->relationship}\n";
}

// Get all policies for family
$familyPolicies = CustomerInsurance::whereIn('customer_id',
    $family->customers->pluck('id')
)->get();

// Check if customer belongs to a family
if ($customer->familyGroup) {
    echo "Part of: {$customer->familyGroup->name}";
}
```

### Family Portal Access

```php
// Allow family members to view each other's policies (in controller)
public function showFamilyPolicies()
{
    $customer = auth('customer')->user();

    if (!$customer->family_group_id) {
        return redirect()->back()->with('error', 'Not part of a family group');
    }

    $family = $customer->familyGroup;
    $memberIds = $family->customers->pluck('id');

    $policies = CustomerInsurance::whereIn('customer_id', $memberIds)
        ->with(['customer', 'insuranceCompany', 'policyType'])
        ->orderBy('start_date', 'desc')
        ->get();

    return view('customer.family-policies', compact('family', 'policies'));
}
```

### Remove Family Member

```php
// Remove member from family
$familyMember = FamilyMember::find($memberId);
$familyMember->delete();

// Or deactivate
$familyMember->update(['status' => false]);

// Update customer record
$customer = $familyMember->customer;
$customer->update(['family_group_id' => null]);
```

## Best Practices

1. **Always Designate Family Head**: Required for family operations
2. **Validate Relationships**: Ensure relationship types are consistent
3. **Audit Trail**: Log all family changes for compliance
4. **Privacy Controls**: Respect member privacy settings
5. **Dependent Tracking**: Track dependents for insurance eligibility

## Related Documentation

- **[CUSTOMER_MANAGEMENT.md](../modules/CUSTOMER_MANAGEMENT.md)** - Customer model integration
- **[POLICY_MANAGEMENT.md](../modules/POLICY_MANAGEMENT.md)** - Family policy handling
- **[DATABASE_SCHEMA.md](../core/DATABASE_SCHEMA.md)** - Family tables schema

---

**Last Updated**: 2025-11-06
**Document Version**: 1.0
