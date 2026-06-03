<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected static function booted()
    {
        static::saved(function ($page) {
            try {
                \App\Services\SeoService::submitSitemapPing();
            } catch (\Exception $e) {}
        });

        static::deleted(function ($page) {
            try {
                \App\Services\SeoService::submitSitemapPing();
            } catch (\Exception $e) {}
        });
    }
}
