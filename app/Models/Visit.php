<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = [
        'session_id',
        'ip_address',
        'url',
        'page_title',
        'referrer',
        'user_agent',
        'country',
        'country_code',
        'city',
        'device_type',
        'browser',
        'os',
        'dwell_seconds',
        'is_bot',
        'bot_type',
    ];

    public function session()
    {
        return $this->belongsTo(ActiveSession::class, 'session_id', 'session_id');
    }
}
