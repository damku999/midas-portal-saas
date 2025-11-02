<?php

namespace App\Models\Customer;

use App\Models\Customer;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
/**
 * App\Models\Customer\CustomerTwoFactorAuth
 *
 * @property int $id
 * @property string $authenticatable_type
 * @property int $authenticatable_id
 * @property string|null $secret
 * @property array|null $recovery_codes
 * @property Carbon|null $enabled_at
 * @property Carbon|null $confirmed_at
 * @property bool $is_active
 * @property string|null $backup_method
 * @property string|null $backup_destination
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model|Model $authenticatable
 *
 * @method static Builder|CustomerTwoFactorAuth customersOnly()
 * @method static Builder|CustomerTwoFactorAuth newModelQuery()
 * @method static Builder|CustomerTwoFactorAuth newQuery()
 * @method static Builder|CustomerTwoFactorAuth query()
 * @method static Builder|CustomerTwoFactorAuth whereAuthenticatableId($value)
 * @method static Builder|CustomerTwoFactorAuth whereAuthenticatableType($value)
 * @method static Builder|CustomerTwoFactorAuth whereBackupDestination($value)
 * @method static Builder|CustomerTwoFactorAuth whereBackupMethod($value)
 * @method static Builder|CustomerTwoFactorAuth whereConfirmedAt($value)
 * @method static Builder|CustomerTwoFactorAuth whereCreatedAt($value)
 * @method static Builder|CustomerTwoFactorAuth whereEnabledAt($value)
 * @method static Builder|CustomerTwoFactorAuth whereId($value)
 * @method static Builder|CustomerTwoFactorAuth whereIsActive($value)
 * @method static Builder|CustomerTwoFactorAuth whereRecoveryCodes($value)
 * @method static Builder|CustomerTwoFactorAuth whereSecret($value)
 * @method static Builder|CustomerTwoFactorAuth whereUpdatedAt($value)
 *
 * @mixin Model
 */
class CustomerTwoFactorAuth extends Model
{
    protected $table = 'two_factor_auth';

    protected $fillable = [
        'authenticatable_type',
        'authenticatable_id',
        'secret',
        'recovery_codes',
        'enabled_at',
        'confirmed_at',
        'is_active',
        'backup_method',
        'backup_destination',
    ];

    protected $casts = [
        'recovery_codes' => 'array',
        'enabled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'secret',
        'recovery_codes',
    ];

    /**
     * Get the authenticatable model (Customer only)
     */
    public function authenticatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to only customer records
     */
    protected function scopeCustomersOnly($query)
    {
        return $query->where('authenticatable_type', Customer::class);
    }

    /**
     * Encrypt/decrypt the secret when storing/retrieving
     */
    protected function secret(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (! $value) {
                    return;
                }

                // Handle legacy unencrypted secrets (backwards compatibility)
                try {
                    return Crypt::decryptString($value);
                } catch (DecryptException) {
                    // If decryption fails, assume it's an old unencrypted secret
                    Log::warning('Found legacy unencrypted customer 2FA secret', [
                        'customer_id' => $this->authenticatable_id,
                        'secret_preview' => substr($value, 0, 8).'...',
                    ]);

                    return $value;
                }
            },
            set: fn ($value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    /**
     * Encrypt/decrypt recovery codes when storing/retrieving
     */
    protected function recoveryCodes(): Attribute
    {
        return Attribute::make(
            get: static function ($value): ?array {
                if (! $value) {
                    return null;
                }

                $codes = json_decode($value, true);

                return array_map(fn ($code) => Crypt::decryptString($code), $codes);
            },
            set: static function ($value) {
                if (! $value) {
                    return;
                }

                $encryptedCodes = array_map(fn ($code) => Crypt::encryptString($code), $value);

                return json_encode($encryptedCodes);
            },
        );
    }

    /**
     * Check if 2FA is fully configured
     */
    public function isFullyConfigured(): bool
    {
        return ! empty($this->secret) &&
               ! empty($this->recovery_codes) &&
               $this->confirmed_at !== null &&
               $this->is_active;
    }

    /**
     * Check if setup is pending confirmation
     */
    public function isPendingConfirmation(): bool
    {
        return ! empty($this->secret) &&
               ! empty($this->recovery_codes) &&
               $this->confirmed_at === null &&
               $this->is_active;
    }

    /**
     * Generate new recovery codes
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            // Generate 8-character alphanumeric codes for customers
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }

        $this->recovery_codes = $codes;
        $this->save();

        return $codes;
    }

    /**
     * Get remaining recovery codes count
     */
    public function getRemainingRecoveryCodesCount(): int
    {
        return is_array($this->recovery_codes) ? count($this->recovery_codes) : 0;
    }

    /**
     * Use a recovery code
     */
    public function useRecoveryCode(string $code): bool
    {
        $upperCode = strtoupper($code);

        // Debug logging for customer recovery codes
        Log::debug('Customer recovery code verification attempt', [
            'input_code' => $code,
            'upper_code' => $upperCode,
            'stored_codes' => $this->recovery_codes,
            'codes_exist' => is_array($this->recovery_codes),
            'code_in_array' => is_array($this->recovery_codes) && in_array($upperCode, $this->recovery_codes),
        ]);

        if (! is_array($this->recovery_codes)) {
            Log::warning('Customer recovery code verification failed - no codes stored', [
                'input_code' => $code,
                'recovery_codes_type' => gettype($this->recovery_codes),
            ]);

            return false;
        }

        $codeIndex = array_search($upperCode, $this->recovery_codes, true);
        if ($codeIndex !== false) {
            $codes = $this->recovery_codes;
            unset($codes[$codeIndex]);
            $this->recovery_codes = array_values($codes);
            $this->save();

            Log::info('Customer recovery code used successfully', [
                'used_code' => $upperCode,
                'remaining_codes' => count($this->recovery_codes),
            ]);

            return true;
        }

        Log::warning('Customer recovery code verification failed', [
            'input_code' => $code,
            'available_codes_count' => count($this->recovery_codes),
        ]);

        return false;
    }

    /**
     * Mark as confirmed
     */
    public function confirm(): void
    {
        $this->confirmed_at = now();
        $this->is_active = true;
        $this->save();
    }

    /**
     * Disable 2FA
     */
    public function disable(): void
    {
        $this->is_active = false;
        $this->save();
    }
}
