<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SeoService
{
    /**
     * Trigger search engine notifications for a published post.
     *
     * @param Post $post
     * @return void
     */
    public static function pingForPost(Post $post)
    {
        if ($post->status !== 'published') {
            return;
        }

        try {
            $postUrl = route('frontend.post', $post->slug);
            
            // 1. Submit to IndexNow (Bing, Yandex, etc.)
            self::submitToIndexNow($postUrl);
            
            // 2. Submit Sitemap Ping (Google, Bing)
            self::submitSitemapPing();
        } catch (\Exception $e) {
            Log::error('SeoService pingForPost error: ' . $e->getMessage());
        }
    }

    /**
     * Submit sitemap location to Google and Bing with throttling.
     *
     * @return void
     */
    public static function submitSitemapPing()
    {
        // Throttle to prevent hitting rate limits (max once per 5 minutes)
        if (Cache::has('seo_sitemap_last_ping')) {
            return;
        }

        $sitemapUrl = url('/sitemap.xml');

        // Ping Google
        try {
            Http::timeout(3)->get('https://www.google.com/ping?sitemap=' . urlencode($sitemapUrl));
        } catch (\Exception $e) {
            Log::warning('Google sitemap ping failed: ' . $e->getMessage());
        }

        // Ping Bing
        try {
            Http::timeout(3)->get('https://www.bing.com/ping?sitemap=' . urlencode($sitemapUrl));
        } catch (\Exception $e) {
            Log::warning('Bing sitemap ping failed: ' . $e->getMessage());
        }

        Cache::put('seo_sitemap_last_ping', true, 300); // 5 minutes lock
    }

    /**
     * Submit a specific URL to search engines using the IndexNow protocol.
     *
     * @param string $url
     * @return void
     */
    public static function submitToIndexNow(string $url)
    {
        $key = self::getOrCreateIndexNowKey();
        if (!$key) {
            return;
        }

        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'] ?? request()->getHost();
        $scheme = $parsedUrl['scheme'] ?? 'https';

        // IndexNow Key verification endpoint file path URL
        $keyLocation = $scheme . '://' . $host . '/' . $key . '.txt';

        try {
            $response = Http::timeout(3)
                ->withHeaders(['Content-Type' => 'application/json; charset=utf-8'])
                ->post('https://api.indexnow.org/indexnow', [
                    'host' => $host,
                    'key' => $key,
                    'keyLocation' => $keyLocation,
                    'urlList' => [$url]
                ]);

            if (!$response->successful()) {
                Log::warning('IndexNow submission failed. Status: ' . $response->status() . ' Body: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::warning('IndexNow request failed: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve the IndexNow API key from settings or create a new one.
     *
     * @return string|null
     */
    public static function getOrCreateIndexNowKey()
    {
        $key = Setting::get('indexnow_key');
        
        if (!$key) {
            // Generate a secure 32-character hexadecimal key
            $key = Str::random(32);
            Setting::set('indexnow_key', $key);
        }

        return $key;
    }
}
