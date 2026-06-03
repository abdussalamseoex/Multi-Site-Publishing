<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    const SITEMAP_LIMIT = 50000;

    public function sitemapIndex()
    {
        @ini_set('memory_limit', '256M');

        // Dynamically build sitemap index based on max timestamps & counts
        $lastPost = Post::where('status', 'published')->latest('updated_at')->first(['updated_at']);
        $lastPostTime = $lastPost ? $lastPost->updated_at->timestamp : 0;
        $postCount = Post::where('status', 'published')->count();

        $lastPage = \App\Models\Page::latest('updated_at')->first(['updated_at']);
        $lastPageTime = $lastPage ? $lastPage->updated_at->timestamp : 0;
        $pageCount = \App\Models\Page::count();

        $lastCategory = Category::latest('updated_at')->first(['updated_at']);
        $lastCategoryTime = $lastCategory ? $lastCategory->updated_at->timestamp : 0;
        $categoryCount = Category::count();

        $customXml = \App\Models\Setting::get('custom_sitemap_xml', '');
        $customXmlHash = md5($customXml);

        $cacheKey = 'sitemap_index_v3_' . $lastPostTime . '_' . $postCount . '_' . $lastPageTime . '_' . $pageCount . '_' . $lastCategoryTime . '_' . $categoryCount . '_' . $customXmlHash;

        $xml = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($postCount, $pageCount, $categoryCount, $lastPost, $lastPage, $lastCategory, $customXml) {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            // Posts Sitemaps
            $postPages = max(1, ceil($postCount / self::SITEMAP_LIMIT));
            $postLastMod = $lastPost ? $lastPost->updated_at->toAtomString() : now()->toAtomString();
            if ($postPages > 1) {
                for ($i = 1; $i <= $postPages; $i++) {
                    $xml .= '  <sitemap>' . "\n";
                    $xml .= '    <loc>' . url("/post-sitemap{$i}.xml") . '</loc>' . "\n";
                    $xml .= '    <lastmod>' . $postLastMod . '</lastmod>' . "\n";
                    $xml .= '  </sitemap>' . "\n";
                }
            } else {
                $xml .= '  <sitemap>' . "\n";
                $xml .= '    <loc>' . url('/post-sitemap.xml') . '</loc>' . "\n";
                $xml .= '    <lastmod>' . $postLastMod . '</lastmod>' . "\n";
                $xml .= '  </sitemap>' . "\n";
            }

            // Pages Sitemaps
            $pagePages = max(1, ceil($pageCount / self::SITEMAP_LIMIT));
            $pageLastMod = $lastPage ? $lastPage->updated_at->toAtomString() : now()->toAtomString();
            if ($pagePages > 1) {
                for ($i = 1; $i <= $pagePages; $i++) {
                    $xml .= '  <sitemap>' . "\n";
                    $xml .= '    <loc>' . url("/page-sitemap{$i}.xml") . '</loc>' . "\n";
                    $xml .= '    <lastmod>' . $pageLastMod . '</lastmod>' . "\n";
                    $xml .= '  </sitemap>' . "\n";
                }
            } else {
                $xml .= '  <sitemap>' . "\n";
                $xml .= '    <loc>' . url('/page-sitemap.xml') . '</loc>' . "\n";
                $xml .= '    <lastmod>' . $pageLastMod . '</lastmod>' . "\n";
                $xml .= '  </sitemap>' . "\n";
            }

            // Categories Sitemaps
            if ($categoryCount > 0) {
                $catPages = max(1, ceil($categoryCount / self::SITEMAP_LIMIT));
                $catLastMod = $lastCategory ? $lastCategory->updated_at->toAtomString() : now()->toAtomString();
                if ($catPages > 1) {
                    for ($i = 1; $i <= $catPages; $i++) {
                        $xml .= '  <sitemap>' . "\n";
                        $xml .= '    <loc>' . url("/category-sitemap{$i}.xml") . '</loc>' . "\n";
                        $xml .= '    <lastmod>' . $catLastMod . '</lastmod>' . "\n";
                        $xml .= '  </sitemap>' . "\n";
                    }
                } else {
                    $xml .= '  <sitemap>' . "\n";
                    $xml .= '    <loc>' . url('/category-sitemap.xml') . '</loc>' . "\n";
                    $xml .= '    <lastmod>' . $catLastMod . '</lastmod>' . "\n";
                    $xml .= '  </sitemap>' . "\n";
                }
            }

            // Custom XML Sitemap (if exists)
            if (!empty($customXml)) {
                $xml .= '  <sitemap>' . "\n";
                $xml .= '    <loc>' . url('/custom-sitemap.xml') . '</loc>' . "\n";
                $xml .= '    <lastmod>' . now()->toAtomString() . '</lastmod>' . "\n";
                $xml .= '  </sitemap>' . "\n";
            }

            $xml .= '</sitemapindex>';
            return $xml;
        });

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    public function postSitemap($page = 1)
    {
        $page = max(1, (int)$page);
        @ini_set('memory_limit', '256M');

        $lastPost = Post::where('status', 'published')->latest('updated_at')->first(['updated_at']);
        $lastPostTime = $lastPost ? $lastPost->updated_at->timestamp : 0;
        $postCount = Post::where('status', 'published')->count();

        $cacheKey = 'sitemap_posts_v3_' . $page . '_' . $lastPostTime . '_' . $postCount;

        $xml = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($page) {
            $posts = Post::where('status', 'published')
                ->orderBy('id', 'asc')
                ->skip(($page - 1) * self::SITEMAP_LIMIT)
                ->take(self::SITEMAP_LIMIT)
                ->get();

            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

            foreach ($posts as $post) {
                $xml .= '  <url>' . "\n";
                $xml .= '    <loc>' . route('frontend.post', $post->slug) . '</loc>' . "\n";
                $xml .= '    <lastmod>' . $post->updated_at->toAtomString() . '</lastmod>' . "\n";
                $xml .= '    <changefreq>weekly</changefreq>' . "\n";
                $xml .= '    <priority>0.9</priority>' . "\n";

                if ($post->featured_image) {
                    $imgUrl = \Illuminate\Support\Str::startsWith($post->featured_image, 'http') 
                        ? $post->featured_image 
                        : url($post->featured_image);
                    
                    $xml .= '    <image:image>' . "\n";
                    $xml .= '      <image:loc>' . htmlspecialchars($imgUrl) . '</image:loc>' . "\n";
                    $xml .= '      <image:title>' . htmlspecialchars($post->title) . '</image:title>' . "\n";
                    $xml .= '    </image:image>' . "\n";
                }

                $xml .= '  </url>' . "\n";
            }

            $xml .= '</urlset>';
            return $xml;
        });

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    public function pageSitemap($page = 1)
    {
        $page = max(1, (int)$page);
        @ini_set('memory_limit', '256M');

        $lastPage = \App\Models\Page::latest('updated_at')->first(['updated_at']);
        $lastPageTime = $lastPage ? $lastPage->updated_at->timestamp : 0;
        $pageCount = \App\Models\Page::count();

        $cacheKey = 'sitemap_pages_v3_' . $page . '_' . $lastPageTime . '_' . $pageCount;

        $xml = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($page) {
            $pages = \App\Models\Page::orderBy('id', 'asc')
                ->skip(($page - 1) * self::SITEMAP_LIMIT)
                ->take(self::SITEMAP_LIMIT)
                ->get();

            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            // Add Homepage on the first page
            if ($page === 1) {
                // Determine homepage lastmod based on last post update
                $lastPost = Post::where('status', 'published')->latest('updated_at')->first(['updated_at']);
                $homepageLastMod = $lastPost ? $lastPost->updated_at->toAtomString() : now()->toAtomString();
                
                $xml .= '  <url>' . "\n";
                $xml .= '    <loc>' . url('/') . '</loc>' . "\n";
                $xml .= '    <lastmod>' . $homepageLastMod . '</lastmod>' . "\n";
                $xml .= '    <changefreq>daily</changefreq>' . "\n";
                $xml .= '    <priority>1.0</priority>' . "\n";
                $xml .= '  </url>' . "\n";
            }

            foreach ($pages as $pageItem) {
                $xml .= '  <url>' . "\n";
                $xml .= '    <loc>' . route('frontend.page', $pageItem->slug) . '</loc>' . "\n";
                $xml .= '    <lastmod>' . $pageItem->updated_at->toAtomString() . '</lastmod>' . "\n";
                $xml .= '    <changefreq>monthly</changefreq>' . "\n";
                $xml .= '    <priority>0.7</priority>' . "\n";
                $xml .= '  </url>' . "\n";
            }

            $xml .= '</urlset>';
            return $xml;
        });

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    public function categorySitemap($page = 1)
    {
        $page = max(1, (int)$page);
        @ini_set('memory_limit', '256M');

        $lastCategory = Category::latest('updated_at')->first(['updated_at']);
        $lastCategoryTime = $lastCategory ? $lastCategory->updated_at->timestamp : 0;
        $categoryCount = Category::count();

        $cacheKey = 'sitemap_categories_v3_' . $page . '_' . $lastCategoryTime . '_' . $categoryCount;

        $xml = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($page) {
            $categories = Category::orderBy('id', 'asc')
                ->skip(($page - 1) * self::SITEMAP_LIMIT)
                ->take(self::SITEMAP_LIMIT)
                ->get();

            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            foreach ($categories as $category) {
                $xml .= '  <url>' . "\n";
                $xml .= '    <loc>' . route('frontend.category', $category->slug) . '</loc>' . "\n";
                $xml .= '    <lastmod>' . $category->updated_at->toAtomString() . '</lastmod>' . "\n";
                $xml .= '    <changefreq>weekly</changefreq>' . "\n";
                $xml .= '    <priority>0.8</priority>' . "\n";
                $xml .= '  </url>' . "\n";
            }

            $xml .= '</urlset>';
            return $xml;
        });

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    public function customSitemap()
    {
        $customXml = \App\Models\Setting::get('custom_sitemap_xml', '');
        if (empty($customXml)) {
            abort(404);
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $xml .= $customXml . "\n";
        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    public function indexNowKeyVerification($key)
    {
        $storedKey = \App\Models\Setting::get('indexnow_key');
        if ($storedKey && $key === $storedKey) {
            return response($storedKey, 200)->header('Content-Type', 'text/plain');
        }
        abort(404);
    }

    public function robots()
    {
        $defaultTxt = "User-agent: *\nDisallow: /admin/\nDisallow: /checkout/\nDisallow: /search\nDisallow: /login\nDisallow: /register\nDisallow: /dashboard\nAllow: /\n\nSitemap: " . url('/sitemap.xml');
        $txt = \App\Models\Setting::get('custom_robots_txt', $defaultTxt);

        return response($txt, 200)
                ->header('Content-Type', 'text/plain')
                ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
    }
}
