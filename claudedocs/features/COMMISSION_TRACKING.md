# Commission Tracking

**Version:** 1.0
**Last Updated:** 2025-11-06
**Status:** Production

## Overview

Commission tracking system for insurance policies with support for own commission, transfer commission, and reference commission calculations.

### Commission Types

1. **Own Commission (`my_commission_`)**
   - Commission earned directly by the broker/agent
   - Percentage-based calculation from policy premium

2. **Transfer Commission (`transfer_commission_`)**
   - Commission transferred to another broker/agent
   - Used when policy is transferred between agents

3. **Reference Commission (`reference_commission_`)**
   - Commission paid for customer referrals
   - Tracked via `reference_by` field

### Data Structure

**Fields in `customer_insurances` table**:
```
- my_commission_percentage (float, nullable)
- my_commission_amount (float, nullable)
- transfer_commission_percentage (float, nullable)
- transfer_commission_amount (float, nullable)
- reference_commission_percentage (float, nullable)
- reference_commission_amount (float, nullable)
- actual_earnings (float, nullable) - Net earnings after transfers
- commission_on (string, nullable) - What commission is calculated on
```

### Commission Calculation

**Formula**:
```
Commission Amount = (Premium × Commission Percentage) / 100
Actual Earnings = My Commission - Transfer Commission - Reference Commission
```

**Example**:
```php
$policy = CustomerInsurance::find($id);

// Own Commission
$myCommission = ($policy->final_premium_with_gst * $policy->my_commission_percentage) / 100;

// Transfer Commission (if any)
$transferCommission = ($policy->final_premium_with_gst * $policy->transfer_commission_percentage) / 100;

// Reference Commission (if any)
$referenceCommission = ($policy->final_premium_with_gst * $policy->reference_commission_percentage) / 100;

// Actual Earnings
$actualEarnings = $myCommission - $transferCommission - $referenceCommission;

$policy->update([
    'my_commission_amount' => $myCommission,
    'transfer_commission_amount' => $transferCommission,
    'reference_commission_amount' => $referenceCommission,
    'actual_earnings' => $actualEarnings
]);
```

### CommissionType Model

**Attributes**:
- `id` - Primary key
- `name` - Commission type name
- `code` - Unique identifier
- `percentage` - Default percentage
- `is_active` - Enable/disable

### Relationships

**Policy Relations**:
```php
$policy->branch; // Branch earning commission
$policy->broker; // Broker earning commission
$policy->relationshipManager; // RM involved
$policy->customer; // Policy holder
$policy->commissionType; // Commission type applied
```

**Reference Tracking**:
```php
// reference_by field stores referrer ID
$referrer = Customer::find($policy->reference_by);
```

### Reports & Analytics

**Commission Summary**:
```php
// Total commissions by period
$commissions = CustomerInsurance::whereBetween('issue_date', [$start, $end])
    ->selectRaw('
        SUM(my_commission_amount) as total_my_commission,
        SUM(transfer_commission_amount) as total_transfer_commission,
        SUM(reference_commission_amount) as total_reference_commission,
        SUM(actual_earnings) as total_earnings
    ')
    ->first();
```

**Commission by Broker**:
```php
$brokerCommissions = CustomerInsurance::where('broker_id', $brokerId)
    ->whereBetween('issue_date', [$start, $end])
    ->sum('my_commission_amount');
```

**Commission by Branch**:
```php
$branchCommissions = CustomerInsurance::where('branch_id', $branchId)
    ->whereBetween('issue_date', [$start, $end])
    ->selectRaw('
        SUM(my_commission_amount) as total,
        COUNT(*) as policy_count
    ')
    ->first();
```

## Related Documentation

- **[POLICY_MANAGEMENT.md](../modules/POLICY_MANAGEMENT.md)** - Policy management integration
- **[DATABASE_SCHEMA.md](../core/DATABASE_SCHEMA.md)** - Commission fields schema

---

**Last Updated**: 2025-11-06
**Document Version**: 1.0
