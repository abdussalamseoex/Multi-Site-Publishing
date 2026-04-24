<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointTopupRequest extends Model
{
    protected $fillable = [
        'user_id',
        'requested_points',
        'transaction_id',
        'notes',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
