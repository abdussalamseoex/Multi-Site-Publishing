<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SeoMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only inject into HTML responses and successful requests
        if (
            $response->status() == 200 
            && $response->headers->has('Content-Type') 
            && str_contains($response->headers->get('Content-Type'), 'text/html')
        ) {
            $content = $response->getContent();
            
            $seoTags = $this->generateSeoTags($request);

            if ($seoTags && str_contains($content, '</head>')) {
                $content = str_replace('</head>', $seoTags . "\n</head>", $content);
                $response->setContent($content);
            }
        }

        return $response;
    }

    private function generateSeoTags(Request $request)
    {
        $html = "\n<!-- Dynamic SEO Tags by SeoMiddleware -->\n";
        $currentUrl = url()->current();
        
        $html .= '<link rel="canonical" href="' . $currentUrl . '" />' . "\n";

        $noIndexPaths = ['/search', '/login', '/register', '/dashboard', '/admin', '/password', '/email'];
        $shouldIndex = true;

        $requestUri = $request->getRequestUri();
        foreach ($noIndexPaths as $path) {
            if (Str::startsWith($requestUri, $path)) {
                $shouldIndex = false;
                break;
            }
        }

        if (!$shouldIndex) {
            $html .= '<meta name="robots" content="noindex, nofollow" />' . "\n";
        } else {
            $html .= '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />' . "\n";
        }

        $siteName = \App\Models\Setting::get('site_title', config('app.name'));
        
        // Setup base OG data
        $ogTitle = $siteName;
        $ogDescription = \App\Models\Setting::get('seo_description', '');
        $ogImage = \App\Models\Setting::get('default_og_image');
        if (!$ogImage) {
            $ogImage = \App\Models\Setting::get('site_logo') ? url(\App\Models\Setting::get('site_logo')) : '';
        } else {
            // Ensure URL format if it's a relative path
            if (!filter_var($ogImage, FILTER_VALIDATE_URL)) {
                $ogImage = url($ogImage);
            }
        }
        $ogType = 'website';
        
        $post = null;
        if ($request->routeIs('frontend.post')) {
            $slug = $request->route('slug');
            $post = \App\Models\Post::where('slug', $slug)->first();
            if ($post) {
                $ogTitle = $post->title;
                $ogDescription = $post->meta_description ?: Str::limit(strip_tags($post->content), 150);
                if ($post->og_image) {
                    $ogImage = url($post->og_image);
                } elseif ($post->featured_image) {
                    $ogImage = url($post->featured_image);
                }
                $ogType = 'article';
            }
        } elseif ($request->routeIs('frontend.category')) {
            $slug = $request->route('slug');
            $category = \App\Models\Category::where('slug', $slug)->first();
            if ($category) {
                $ogTitle = $category->name . ' - ' . $siteName;
                if ($category->description) {
                    $ogDescription = $category->description;
                }
            }
        }

        // Output OG & Twitter tags
        $html .= '<meta property="og:type" content="' . $ogType . '" />' . "\n";
        $html .= '<meta property="og:title" content="' . htmlspecialchars($ogTitle) . '" />' . "\n";
        if ($ogDescription) {
            $html .= '<meta property="og:description" content="' . htmlspecialchars($ogDescription) . '" />' . "\n";
        }
        $html .= '<meta property="og:url" content="' . $currentUrl . '" />' . "\n";
        if ($ogImage) {
            $html .= '<meta property="og:image" content="' . $ogImage . '" />' . "\n";
            $html .= '<meta name="twitter:card" content="summary_large_image" />' . "\n";
            $html .= '<meta name="twitter:image" content="' . $ogImage . '" />' . "\n";
        }
        $html .= '<meta name="twitter:title" content="' . htmlspecialchars($ogTitle) . '" />' . "\n";
        if ($ogDescription) {
            $html .= '<meta name="twitter:description" content="' . htmlspecialchars($ogDescription) . '" />' . "\n";
        }

        $schema = [];
        
        $schema[] = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $siteName,
            'url' => url('/'),
            'logo' => \App\Models\Setting::get('site_logo') ? url(\App\Models\Setting::get('site_logo')) : ''
        ];

        // Breadcrumbs
        $segments = $request->segments();
        if (count($segments) > 0 && $shouldIndex) {
            $itemListElement = [];
            $itemListElement[] = [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => url('/')
            ];
            
            $url = url('/');
            $position = 2;
            foreach ($segments as $segment) {
                $url .= '/' . $segment;
                $itemListElement[] = [
                    '@type' => 'ListItem',
                    'position' => $position,
                    'name' => ucfirst(str_replace('-', ' ', $segment)),
                    'item' => $url
                ];
                $position++;
            }
            
            $schema[] = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => $itemListElement
            ];
        }

        // Article Schema
        if ($post) {
            $schema[] = [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $post->title,
                'image' => $post->featured_image ? url($post->featured_image) : '',
                'datePublished' => $post->created_at->toIso8601String(),
                'dateModified' => $post->updated_at->toIso8601String(),
                'author' => [
                    '@type' => 'Person',
                    'name' => $post->user ? $post->user->name : 'Admin'
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => $siteName,
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => \App\Models\Setting::get('site_logo') ? url(\App\Models\Setting::get('site_logo')) : ''
                    ]
                ]
            ];
        }

        foreach ($schema as $s) {
            $html .= '<script type="application/ld+json">' . json_encode($s, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
        }

        return $html;
    }
}
