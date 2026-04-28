<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoNewsSource extends Model
{
    protected $fillable = [
        'name',
        'source_url',
        'category_id',
        'posts_per_run',
        'fetch_interval_hours',
        'duration_days',
        'expires_at',
        'last_run_at',
        'featured_image_source',
        'in_content_images_count',
        'in_content_image_source',
        'is_active',
    ];

    protected $casts = [
        'last_run_at' => 'datetime',
        'expires_at'  => 'datetime',
        'is_active'   => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
