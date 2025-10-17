<?php

namespace App\Models;

use App\Traits\TableRecordObserver;
use Database\Factories\ClaimStageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Models\ClaimStage
 *
 * @property int $id
 * @property int $claim_id
 * @property string $stage_name
 * @property string|null $description
 * @property bool $is_current
 * @property bool $is_completed
 * @property Carbon|null $stage_date
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Claim|null $claim
 * @property-read string|null $stage_date_formatted
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 *
 * @method static ClaimStageFactory factory($count = null, $state = [])
 * @method static Builder|ClaimStage newModelQuery()
 * @method static Builder|ClaimStage newQuery()
 * @method static Builder|ClaimStage onlyTrashed()
 * @method static Builder|ClaimStage permission($permissions)
 * @method static Builder|ClaimStage query()
 * @method static Builder|ClaimStage role($roles, $guard = null)
 * @method static Builder|ClaimStage whereClaimId($value)
 * @method static Builder|ClaimStage whereCreatedAt($value)
 * @method static Builder|ClaimStage whereCreatedBy($value)
 * @method static Builder|ClaimStage whereDeletedAt($value)
 * @method static Builder|ClaimStage whereDeletedBy($value)
 * @method static Builder|ClaimStage whereDescription($value)
 * @method static Builder|ClaimStage whereId($value)
 * @method static Builder|ClaimStage whereIsCompleted($value)
 * @method static Builder|ClaimStage whereIsCurrent($value)
 * @method static Builder|ClaimStage whereNotes($value)
 * @method static Builder|ClaimStage whereStageDate($value)
 * @method static Builder|ClaimStage whereStageName($value)
 * @method static Builder|ClaimStage whereUpdatedAt($value)
 * @method static Builder|ClaimStage whereUpdatedBy($value)
 * @method static Builder|ClaimStage withTrashed()
 * @method static Builder|ClaimStage withoutTrashed()
 *
 * @mixin Model
 */
class ClaimStage extends Model
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use LogsActivity;
    use SoftDeletes;
    use TableRecordObserver;

    protected $fillable = [
        'claim_id',
        'stage_name',
        'description',
        'is_current',
        'is_completed',
        'stage_date',
        'notes',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'is_completed' => 'boolean',
        'stage_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    /**
     * Get the claim that owns the stage.
     */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    /**
     * Set this stage as current and mark previous as not current.
     */
    public function setAsCurrent(): void
    {
        // Mark all other stages for this claim as not current
        self::query()->where('claim_id', $this->claim_id)
            ->where('id', '!=', $this->id)
            ->update(['is_current' => false]);

        // Mark this stage as current
        $this->update(['is_current' => true]);
    }

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    /**
     * Get formatted stage date.
     */
    protected function getStageDateFormattedAttribute(): ?string
    {
        return $this->stage_date ? formatDateForUi($this->stage_date) : null;
    }

    /**
     * Set stage date from UI format.
     */
    protected function setStageDateAttribute($value): void
    {
        if ($value) {
            $this->attributes['stage_date'] = formatDateForDatabase($value);
        }
    }
}
