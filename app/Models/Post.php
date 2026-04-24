<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category_id', 'title', 'slug', 'original_slug', 'summary', 'content', 
        'featured_image', 'status', 'is_featured', 'meta_title', 
        'meta_description', 'meta_keywords', 'canonical_url', 'views', 'is_dofollow'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
    
    /**
     * Set attribute mutator for link processing
     * We'll hook into saving event to parse HTML and auto-nofollow external links 
     */
    protected static function booted()
    {
        static::saving(function ($post) {
            if ($post->content) {
                $post->content = self::processLinks($post->content, $post->is_dofollow);
            }
        });
    }

    public static function processLinks($html, $isDofollow = false)
    {
        if (empty($html)) return $html;

        $dom = new \DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $links = $dom->getElementsByTagName('a');
        $appUrl = parse_url(config('app.url'), PHP_URL_HOST);

        foreach ($links as $link) {
            if ($link instanceof \DOMElement) {
                $href = $link->getAttribute('href');
                $host = parse_url($href, PHP_URL_HOST);

                // If it's an external link
                if ($host && $host !== $appUrl) {
                    if (!$isDofollow) {
                        $link->setAttribute('rel', 'nofollow sponsored');
                    } else {
                        // User is allowed dofollow, so we can just use external or strip it
                        $link->removeAttribute('rel');
                    }
                    $link->setAttribute('target', '_blank');
                } else {
                    // internal link, strip rel just in case
                    $link->removeAttribute('rel');
                }
            }
        }

        return $dom->saveHTML();
    }
}
