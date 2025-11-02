<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Models\Tenant;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'central';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_user_id',
        'tenant_id',
        'action',
        'description',
        'details',
        'subject_type',
        'subject_id',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'details' => 'array',
    ];

    /**
     * Get the user that performed the action.
     */
    public function tenantUser()
    {
        return $this->belongsTo(TenantUser::class);
    }

    /**
     * Get the tenant associated with this log.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    /**
     * Get the subject model.
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Create an audit log entry.
     */
    public static function log(
        string $action,
        string $description,
        ?TenantUser $user = null,
        ?string $tenantId = null,
        ?array $details = null,
        ?string $subjectType = null,
        ?string $subjectId = null
    ): self {
        return static::create([
            'tenant_user_id' => $user?->id ?? auth('central')->id(),
            'tenant_id' => $tenantId,
            'action' => $action,
            'description' => $description,
            'details' => $details,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Scope to filter by action.
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by tenant.
     */
    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('tenant_user_id', $userId);
    }

    /**
     * Scope to get recent logs.
     */
    public function scopeRecent($query, int $limit = 50)
    {
        return $query->latest()->limit($limit);
    }
}
