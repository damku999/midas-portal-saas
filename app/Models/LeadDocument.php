<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class LeadDocument extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'document_type',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    // Relationships

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Query Scopes

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('document_type', $type);
    }

    public function scopeByLead(Builder $query, int $leadId): Builder
    {
        return $query->where('lead_id', $leadId);
    }

    public function scopeByUploader(Builder $query, int $userId): Builder
    {
        return $query->where('uploaded_by', $userId);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper Methods

    public function getUrl(): string
    {
        return Storage::url($this->file_path);
    }

    public function getFullPath(): string
    {
        return Storage::path($this->file_path);
    }

    public function exists(): bool
    {
        return Storage::exists($this->file_path);
    }

    public function delete(): ?bool
    {
        if ($this->exists()) {
            Storage::delete($this->file_path);
        }

        return parent::delete();
    }

    public function getHumanReadableSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function isDocument(): bool
    {
        $documentMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        return in_array($this->mime_type, $documentMimes);
    }
}
