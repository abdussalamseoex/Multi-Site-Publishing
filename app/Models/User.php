<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'role', 'status', 'total_posts', 'earnings', 'daily_post_limit', 'total_post_limit', 'is_unlimited', 'dofollow_default', 'points', 'bio', 'avatar', 'website', 'facebook', 'twitter', 'linkedin', 'instagram', 'username'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected static function booted()
    {
        static::creating(function ($user) {
            if (Schema::hasColumn('users', 'username')) {
                if (empty($user->username) && !empty($user->name)) {
                    $user->username = static::generateUniqueUsername($user->name);
                }
            }
        });

        static::updating(function ($user) {
            if (Schema::hasColumn('users', 'username')) {
                if ($user->isDirty('name') && empty($user->username)) {
                    $user->username = static::generateUniqueUsername($user->name, $user->id);
                }
            }
        });
    }

    public static function generateUniqueUsername($name, $ignoreId = null)
    {
        $slug = Str::slug($name);
        if (empty($slug)) {
            $slug = 'user-' . uniqid();
        }
        $original = $slug;
        $count = 1;
        while (static::where('username', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $original . '-' . $count++;
        }
        return $slug;
    }

    public function getSlugAttribute(): string
    {
        if (!empty($this->username)) {
            return $this->username;
        }
        return Str::slug($this->name ?: 'author-' . $this->id);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
