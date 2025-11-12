<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'name',
        'company',
        'role',
        'testimonial',
        'rating',
        'photo',
        'status',
        'display_order',
    ];

    protected $casts = [
        'rating' => 'integer',
        'display_order' => 'integer',
    ];

    /**
     * Scope to get only active testimonials
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to order by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc')->orderBy('created_at', 'desc');
    }

    /**
     * Get star rating HTML
     */
    public function getStarsHtmlAttribute()
    {
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $this->rating) {
                $stars .= '<i class="fas fa-star text-warning"></i>';
            } else {
                $stars .= '<i class="far fa-star text-warning"></i>';
            }
        }
        return $stars;
    }
}
