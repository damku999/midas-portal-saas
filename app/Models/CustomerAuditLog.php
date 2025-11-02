<?php

namespace App\Models;

use Database\Factories\CustomerAuditLogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * App\Models\CustomerAuditLog
 *
 * @property int $id
 * @property int $customer_id
 * @property string $action login, logout, view_policy, download_document, etc.
 * @property string|null $resource_type policy, profile, family_data
 * @property int|null $resource_id ID of the resource being accessed
 * @property string|null $description Human readable description
 * @property array|null $metadata Additional data (JSON-like string, IP, user agent, etc.)
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $session_id
 * @property bool $success
 * @property string|null $failure_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Customer|null $customer
 *
 * @method static CustomerAuditLogFactory factory($count = null, $state = [])
 * @method static Builder|CustomerAuditLog newModelQuery()
 * @method static Builder|CustomerAuditLog newQuery()
 * @method static Builder|CustomerAuditLog query()
 * @method static Builder|CustomerAuditLog whereAction($value)
 * @method static Builder|CustomerAuditLog whereCreatedAt($value)
 * @method static Builder|CustomerAuditLog whereCustomerId($value)
 * @method static Builder|CustomerAuditLog whereDescription($value)
 * @method static Builder|CustomerAuditLog whereFailureReason($value)
 * @method static Builder|CustomerAuditLog whereId($value)
 * @method static Builder|CustomerAuditLog whereIpAddress($value)
 * @method static Builder|CustomerAuditLog whereMetadata($value)
 * @method static Builder|CustomerAuditLog whereResourceId($value)
 * @method static Builder|CustomerAuditLog whereResourceType($value)
 * @method static Builder|CustomerAuditLog whereSessionId($value)
 * @method static Builder|CustomerAuditLog whereSuccess($value)
 * @method static Builder|CustomerAuditLog whereUpdatedAt($value)
 * @method static Builder|CustomerAuditLog whereUserAgent($value)
 *
 * @mixin Model
 */
class CustomerAuditLog extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'action',
        'resource_type',
        'resource_id',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
        'session_id',
        'success',
        'failure_reason',
    ];

    protected $casts = [
        'metadata' => 'array',
        'success' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public static function logAction(string $action, ?string $description = null, array $metadata = []): void
    {
        $customer = auth('customer')->user();

        if (! $customer) {
            return;
        }

        self::query()->create([
            'customer_id' => $customer->id,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'success' => true,
        ]);
    }

    public static function logPolicyAction(string $action, CustomerInsurance $customerInsurance, ?string $description = null, array $metadata = []): void
    {
        $customer = auth('customer')->user();

        if (! $customer) {
            return;
        }

        self::query()->create([
            'customer_id' => $customer->id,
            'action' => $action,
            'resource_type' => 'policy',
            'resource_id' => $customerInsurance->id,
            'description' => $description !== null && $description !== '' && $description !== '0' ? $description : sprintf('Customer %s policy %s', $action, $customerInsurance->policy_no),
            'metadata' => array_merge([
                'policy_no' => $customerInsurance->policy_no,
                'policy_holder' => $customerInsurance->customer->name,
                'insurance_company' => $customerInsurance->insuranceCompany->name ?? 'Unknown',
            ], $metadata),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'success' => true,
        ]);
    }

    public static function logFailure(string $action, string $reason, array $metadata = []): void
    {
        $customer = auth('customer')->user();

        if (! $customer) {
            return;
        }

        self::query()->create([
            'customer_id' => $customer->id,
            'action' => $action,
            'description' => 'Failed: '.$reason,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'success' => false,
            'failure_reason' => $reason,
        ]);
    }
}
