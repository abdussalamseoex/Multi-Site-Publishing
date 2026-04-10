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

        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    public function robots()
    {
        $txt = "User-agent: *\n";
        $txt .= "Disallow: /admin/\n";
        $txt .= "Disallow: /checkout/\n";
        $txt .= "Allow: /\n\n";
        $txt .= "Sitemap: " . url('/sitemap.xml');

        return response($txt, 200)->header('Content-Type', 'text/plain');
    }
}
