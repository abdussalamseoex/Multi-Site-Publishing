<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = [
        'ip_address',
        'url',
        'referrer',
        'user_agent',
        'country',
    ];
}
