<?php

namespace App\Models;

use Database\Factories\TwoFactorAttemptFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
/**
 * App\Models\TwoFactorAttempt
 *
 * @property int $id
 * @property string $authenticatable_type
 * @property int $authenticatable_id
 * @property string $code_type
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property bool $successful
 * @property string|null $failure_reason
 * @property Carbon $attempted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model|Model $authenticatable
 *
 * @method static Builder|TwoFactorAttempt codeType(string $codeType)
 * @method static TwoFactorAttemptFactory factory($count = null, $state = [])
 * @method static Builder|TwoFactorAttempt failed()
 * @method static Builder|TwoFactorAttempt newModelQuery()
 * @method static Builder|TwoFactorAttempt newQuery()
 * @method static Builder|TwoFactorAttempt query()
 * @method static Builder|TwoFactorAttempt recent(int $minutes = 15)
 * @method static Builder|TwoFactorAttempt successful()
 * @method static Builder|TwoFactorAttempt whereAttemptedAt($value)
 * @method static Builder|TwoFactorAttempt whereAuthenticatableId($value)
 * @method static Builder|TwoFactorAttempt whereAuthenticatableType($value)
 * @method static Builder|TwoFactorAttempt whereCodeType($value)
 * @method static Builder|TwoFactorAttempt whereCreatedAt($value)
 * @method static Builder|TwoFactorAttempt whereFailureReason($value)
 * @method static Builder|TwoFactorAttempt whereId($value)
 * @method static Builder|TwoFactorAttempt whereIpAddress($value)
 * @method static Builder|TwoFactorAttempt whereSuccessful($value)
 * @method static Builder|TwoFactorAttempt whereUpdatedAt($value)
 * @method static Builder|TwoFactorAttempt whereUserAgent($value)
 *
 * @mixin Model
 */
class TwoFactorAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'authenticatable_type',
        'authenticatable_id',
        'code_type',
        'ip_address',
        'user_agent',
        'successful',
        'failure_reason',
        'attempted_at',
    ];

    protected $casts = [
        'successful' => 'boolean',
        'attempted_at' => 'datetime',
    ];

    /**
     * Get the authenticatable entity (User or Customer)
     */
    public function authenticatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for successful attempts
     */
    protected function scopeSuccessful($query)
    {
        return $query->where('successful', true);
    }

    /**
     * Scope for failed attempts
     */
    protected function scopeFailed($query)
    {
        return $query->where('successful', false);
    }

    /**
     * Scope for recent attempts
     */
    protected function scopeRecent($query, int $minutes = 15)
    {
        return $query->where('attempted_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Scope for specific code type
     */
    protected function scopeCodeType($query, string $codeType)
    {
        return $query->where('code_type', $codeType);
    }

    /**
     * Get attempt result display
     */
    public function getResultDisplay(): string
    {
        return $this->successful ? 'Success' : 'Failed';
    }

    /**
     * Get code type display
     */
    public function getCodeTypeDisplay(): string
    {
        return match ($this->code_type) {
            'totp' => 'Authenticator App',
            'recovery' => 'Recovery Code',
            'sms' => 'SMS',
            default => ucfirst($this->code_type)
        };
    }
}
