<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category_id', 'auto_news_source_id', 'title', 'slug', 'original_slug', 'summary', 'content', 
        'featured_image', 'og_image', 'status', 'scheduled_at', 'is_featured', 'meta_title', 
        'meta_description', 'meta_keywords', 'canonical_url', 'views', 'is_dofollow'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'is_featured'  => 'boolean',
        'is_dofollow'  => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '<=', now());
            });
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled' || ($this->scheduled_at && $this->scheduled_at->isFuture());
    }

    public static function autoPublishDuePosts(): int
    {
        $duePosts = self::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($duePosts as $post) {
            $post->status = 'published';
            $post->save();
            $count++;
        }

        if ($count > 0) {
            \Illuminate\Support\Facades\Cache::forget('sitemap_xml');
            try {
                \App\Services\SeoService::submitSitemapPing();
            } catch (\Exception $e) {}
        }

        return $count;
    }

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
        static::creating(function ($post) {
            // We no longer blindly overwrite is_dofollow here because PostController 
            // calculates and assigns it correctly based on user permissions or admin role.
            // If it's not set at all, we can default it to false.
            if (!isset($post->is_dofollow)) {
                $post->is_dofollow = false;
            }
        });

        static::saving(function ($post) {
            if ($post->content) {
                $post->content = self::processLinks($post->content, $post->is_dofollow);
            }
        });

        static::saved(function ($post) {
            \Illuminate\Support\Facades\Cache::forget('sitemap_xml');

            if ($post->status === 'published' && ($post->wasRecentlyCreated || $post->wasChanged('status') || $post->wasChanged('title') || $post->wasChanged('content') || $post->wasChanged('featured_image'))) {
                try {
                    \App\Services\SeoService::pingForPost($post);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('SeoService post save ping error: ' . $e->getMessage());
                }
            }
        });

        static::deleted(function ($post) {
            \Illuminate\Support\Facades\Cache::forget('sitemap_xml');

            try {
                \App\Services\SeoService::submitSitemapPing();
            } catch (\Exception $e) {}
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
                    $existingRel = $link->getAttribute('rel');
                    
                    if (!$isDofollow) {
                        // User has no permission, force nofollow
                        $link->setAttribute('rel', 'nofollow sponsored');
                    } else {
                        // User has permission. 
                        // If they explicitly added rel="nofollow" in the editor, respect it.
                        if ($existingRel && str_contains(strtolower($existingRel), 'nofollow')) {
                            $link->setAttribute('rel', 'nofollow');
                        } else {
                            // Otherwise, it's dofollow. Remove rel or keep dofollow
                            $link->removeAttribute('rel');
                        }
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
