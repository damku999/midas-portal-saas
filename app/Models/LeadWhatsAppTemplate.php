<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadWhatsAppTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lead_whatsapp_templates';

    protected $fillable = [
        'name',
        'category',
        'message_template',
        'variables',
        'attachment_path',
        'is_active',
        'usage_count',
        'created_by',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Helper Methods

    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    public function hasAttachment(): bool
    {
        return !empty($this->attachment_path);
    }

    public function getAttachmentUrl(): ?string
    {
        return $this->attachment_path ? asset('storage/' . $this->attachment_path) : null;
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Render template with provided variables
     *
     * @param array $data Key-value pairs to replace in template
     * @return string Rendered message
     */
    public function render(array $data): string
    {
        $message = $this->message_template;

        foreach ($data as $key => $value) {
            $placeholder = '{' . $key . '}';
            $message = str_replace($placeholder, $value, $message);
        }

        return $message;
    }

    /**
     * Get available variables from template
     *
     * @return array List of variable names found in template
     */
    public function getTemplateVariables(): array
    {
        preg_match_all('/\{(\w+)\}/', $this->message_template, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Validate that all required variables are provided
     *
     * @param array $data Data to validate
     * @return array Missing variables
     */
    public function validateVariables(array $data): array
    {
        $templateVars = $this->getTemplateVariables();
        $providedVars = array_keys($data);

        return array_diff($templateVars, $providedVars);
    }

    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopePopular($query, int $minUsage = 10)
    {
        return $query->where('usage_count', '>=', $minUsage)
            ->orderBy('usage_count', 'desc');
    }

    public function scopeCreatedBy($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeWithAttachment($query)
    {
        return $query->whereNotNull('attachment_path');
    }
}
