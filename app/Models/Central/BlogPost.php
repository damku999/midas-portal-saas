<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'central';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'featured_image_thumb',
        'category',
        'tags',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'status',
        'published_at',
        'author_id',
        'views_count',
        'reading_time_minutes',
    ];

    protected $casts = [
        'tags' => 'array',
        'published_at' => 'datetime',
        'views_count' => 'integer',
        'reading_time_minutes' => 'integer',
    ];

    protected $dates = [
        'published_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Scope to get only published posts
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope to filter by category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Generate slug from title
     */
    public static function generateSlug($title)
    {
        $slug = Str::slug($title);
        $count = static::where('slug', 'like', "{$slug}%")->count();

        return $count ? "{$slug}-{$count}" : $slug;
    }

    /**
     * Increment view count
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Get formatted published date
     */
    public function getPublishedDateAttribute()
    {
        return $this->published_at ? $this->published_at->format('M d, Y') : null;
    }

    /**
     * Get category display name
     */
    public function getCategoryNameAttribute()
    {
        $categories = [
            'product-updates' => 'Product Updates',
            'insurance-tips' => 'Insurance Tips',
            'claims' => 'Claims Management',
            'insurance-types' => 'Insurance Types',
            'addons' => 'Insurance Add-ons',
        ];

        return $categories[$this->category] ?? $this->category;
    }

    /**
     * Get category badge color
     */
    public function getCategoryBadgeAttribute()
    {
        $badges = [
            'product-updates' => 'primary',
            'insurance-tips' => 'success',
            'claims' => 'warning',
            'insurance-types' => 'info',
            'addons' => 'danger',
        ];

        return $badges[$this->category] ?? 'secondary';
    }
}
