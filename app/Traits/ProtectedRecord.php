<?php

namespace App\Traits;

use App\Exceptions\ProtectedRecordException;
use Illuminate\Support\Str;

trait ProtectedRecord
{
    /**
     * Boot the ProtectedRecord trait
     */
    protected static function bootProtectedRecord(): void
    {
        // Auto-protect records on creation if email matches protected patterns
        static::creating(function ($model) {
            if ($model->shouldBeProtected()) {
                $model->is_protected = true;
                $model->protected_reason = $model->getProtectionReason();
            }
        });

        // Prevent deletion of protected records
        static::deleting(function ($model) {
            if ($model->isProtected() && config('protection.rules.prevent_soft_deletion', true)) {
                throw ProtectedRecordException::deletionPrevented($model, [
                    'deletion_type' => 'soft_delete',
                    'model_class' => get_class($model),
                ]);
            }
        });

        // Prevent force deletion of protected records
        static::forceDeleting(function ($model) {
            if ($model->isProtected() && config('protection.rules.prevent_force_deletion', true)) {
                throw ProtectedRecordException::deletionPrevented($model, [
                    'deletion_type' => 'force_delete',
                    'model_class' => get_class($model),
                ]);
            }
        });

        // Prevent critical field modifications on protected records
        static::updating(function ($model) {
            if ($model->isProtected()) {
                // Prevent email changes
                if ($model->isDirty('email') && config('protection.rules.prevent_email_change', true)) {
                    throw ProtectedRecordException::emailChangePrevented($model, [
                        'old_email' => $model->getOriginal('email'),
                        'new_email' => $model->email,
                    ]);
                }

                // Prevent status deactivation
                if ($model->isDirty('status') && ! $model->status && config('protection.rules.prevent_status_deactivation', true)) {
                    throw ProtectedRecordException::statusChangePrevented($model, [
                        'old_status' => $model->getOriginal('status'),
                        'new_status' => $model->status,
                    ]);
                }
            }
        });
    }

    /**
     * Check if this record is protected
     */
    public function isProtected(): bool
    {
        return (bool) ($this->is_protected ?? false);
    }

    /**
     * Check if this record should be automatically protected based on email
     */
    public function shouldBeProtected(): bool
    {
        if (! isset($this->email) || empty($this->email)) {
            return false;
        }

        // Check if email is in protected emails list
        if ($this->isEmailProtected($this->email)) {
            return true;
        }

        // Check if email domain is protected
        if ($this->isEmailDomainProtected($this->email)) {
            return true;
        }

        return false;
    }

    /**
     * Check if specific email is in protected list
     */
    protected function isEmailProtected(string $email): bool
    {
        $protectedEmails = config('protection.protected_emails', []);

        return in_array(strtolower($email), array_map('strtolower', $protectedEmails), true);
    }

    /**
     * Check if email domain is protected
     */
    protected function isEmailDomainProtected(string $email): bool
    {
        $protectedDomains = config('protection.protected_domains', []);

        foreach ($protectedDomains as $domain) {
            if (Str::endsWith(strtolower($email), '@'.strtolower($domain))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the reason why this record is protected
     */
    public function getProtectionReason(): string
    {
        if (! isset($this->email)) {
            return 'System Protected Record';
        }

        if ($this->isEmailProtected($this->email)) {
            return 'Webmonks Super Admin Account';
        }

        if ($this->isEmailDomainProtected($this->email)) {
            return 'Webmonks Domain Protected';
        }

        return 'System Protected Record';
    }

    /**
     * Manually protect this record
     */
    public function protect(?string $reason = null): bool
    {
        $this->is_protected = true;
        $this->protected_reason = $reason ?? $this->getProtectionReason();

        return $this->save();
    }

    /**
     * Unprotect this record (with caution!)
     *
     * @throws ProtectedRecordException if emergency bypass is not enabled
     */
    public function unprotect(): bool
    {
        // Check emergency bypass
        if (! config('protection.emergency_bypass.enabled', false)) {
            throw ProtectedRecordException::modificationPrevented(
                $this,
                'unprotect',
                ['reason' => 'Emergency bypass is not enabled']
            );
        }

        // Log the unprotection
        \Log::critical('Protected record unprotection attempt', [
            'model' => get_class($this),
            'id' => $this->id,
            'email' => $this->email ?? 'N/A',
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
        ]);

        $this->is_protected = false;
        $this->protected_reason = null;

        return $this->save();
    }

    /**
     * Scope to query only protected records
     */
    public function scopeProtected($query)
    {
        return $query->where('is_protected', true);
    }

    /**
     * Scope to query only non-protected records
     */
    public function scopeUnprotected($query)
    {
        return $query->where('is_protected', false);
    }

    /**
     * Check if deletion is allowed for this record
     */
    public function canBeDeleted(): bool
    {
        return ! $this->isProtected();
    }

    /**
     * Check if status can be changed to inactive
     */
    public function canBeDeactivated(): bool
    {
        if (! $this->isProtected()) {
            return true;
        }

        return ! config('protection.rules.prevent_status_deactivation', true);
    }

    /**
     * Check if email can be changed
     */
    public function canChangeEmail(): bool
    {
        if (! $this->isProtected()) {
            return true;
        }

        return ! config('protection.rules.prevent_email_change', true);
    }

    /**
     * Get protected badge HTML for display
     */
    public function getProtectedBadgeAttribute(): string
    {
        if (! $this->isProtected()) {
            return '';
        }

        return sprintf(
            '<span class="badge badge-warning" title="%s"><i class="fas fa-shield-alt"></i> Protected</span>',
            htmlspecialchars($this->protected_reason ?? 'Protected Record')
        );
    }

    /**
     * Ensure record has protection fields in fillable array
     */
    public function initializeProtectedRecord(): void
    {
        if (property_exists($this, 'fillable')) {
            $this->fillable = array_merge($this->fillable, ['is_protected', 'protected_reason']);
        }

        if (property_exists($this, 'casts')) {
            $this->casts = array_merge($this->casts, [
                'is_protected' => 'boolean',
            ]);
        }
    }
}
