<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiBulkCampaign extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'keywords',
        'total_count',
        'processed_count',
        'interval_minutes',
        'status',
        'last_run_at',
        'next_run_at',
        'error_log',
        'settings'
    ];

    protected $casts = [
        'keywords' => 'array',
        'settings' => 'array',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
