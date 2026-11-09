<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActiveSession extends Model
{
    protected $fillable = [
        'session_id',
        'ip_address',
        'country',
        'country_code',
        'city',
        'device_type',
        'browser',
        'os',
        'referrer',
        'referrer_domain',
        'landing_page',
        'current_page',
        'current_page_title',
        'page_views',
        'dwell_seconds',
        'is_bot',
        'status',
        'started_at',
        'last_activity_at',
    ];

    protected $casts = [
        'is_bot'           => 'boolean',
        'page_views'       => 'integer',
        'dwell_seconds'    => 'integer',
        'started_at'       => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public function visits()
    {
        return $this->hasMany(Visit::class, 'session_id', 'session_id')->orderBy('created_at', 'asc');
    }

    public function scopeLiveHuman($query)
    {
        return $query->where('is_bot', false)
            ->where('last_activity_at', '>=', now()->subMinutes(3));
    }

    /**
     * Determine computed real-time status: 'active', 'idle', or 'left'
     */
    public function getComputedStatusAttribute(): string
    {
        if ($this->status === 'left') {
            return 'left';
        }

        if (!$this->last_activity_at) {
            return 'left';
        }

        $diff = now()->diffInSeconds($this->last_activity_at);
        if ($diff <= 45) {
            return 'active';
        } elseif ($diff <= 180) {
            return 'idle';
        } else {
            return 'left';
        }
    }
}
