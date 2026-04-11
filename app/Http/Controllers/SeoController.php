<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    public function sitemap()
    {
        $posts = Post::where('status', 'published')->orderBy('updated_at', 'desc')->get();
        $categories = Category::all();

        $xml = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        // Homepage
        $xml .= '<url><loc>' . url('/') . '</loc><changefreq>daily</changefreq><priority>1.0</priority></url>';

        // Posts
        foreach ($posts as $post) {
            $xml .= '<url>';
            $xml .= '<loc>' . route('frontend.post', $post->slug) . '</loc>';
            $xml .= '<lastmod>' . $post->updated_at->toAtomString() . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        // Custom XML Injections
        $customXml = \App\Models\Setting::get('custom_sitemap_xml', '');
        if ($customXml) {
            $xml .= "\n" . $customXml . "\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    public function robots()
    {
        $defaultTxt = "User-agent: *\nDisallow: /admin/\nDisallow: /checkout/\nAllow: /\n\nSitemap: " . url('/sitemap.xml');
        $txt = \App\Models\Setting::get('custom_robots_txt', $defaultTxt);

        return response($txt, 200)
                ->header('Content-Type', 'text/plain')
                ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
    }
}
