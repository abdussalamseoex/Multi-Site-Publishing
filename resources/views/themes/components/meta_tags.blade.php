@php
    /**
     * Universal SEO Meta Tags Component
     */

    $siteTitle    = \App\Models\Setting::get('site_title', config('app.name'));
    $siteTagline  = \App\Models\Setting::get('site_tagline', '');
    $siteLogo     = \App\Models\Setting::get('site_logo');
    $defaultOg    = \App\Models\Setting::get('default_og_image');
    $siteUrl      = url('/');
    $canonicalUrl = url()->current();

    // Homepage-specific overrides from SEO settings
    $homepageMetaTitle = \App\Models\Setting::get('homepage_meta_title', '');
    $homepageMetaDesc  = \App\Models\Setting::get('homepage_meta_description', '');
    $homepageMetaKw    = \App\Models\Setting::get('homepage_meta_keywords', '');

    $isPost    = isset($post) && $post instanceof \App\Models\Post;
    $isHome    = isset($isHomepage) && $isHomepage;

    // --- Build META TITLE ---
    if ($isPost) {
        $metaTitle = $post->meta_title ?? $post->title;
        $metaTitle = trim($metaTitle) . ' - ' . $siteTitle;
    } elseif ($isHome && $homepageMetaTitle) {
        $metaTitle = trim($homepageMetaTitle);
    } elseif (isset($category) && $category instanceof \App\Models\Category) {
        $metaTitle = $category->name . ' - ' . $siteTitle;
    } else {
        $metaTitle = $siteTitle . ($siteTagline ? ' | ' . $siteTagline : '');
    }

    // --- Build META DESCRIPTION ---
    if ($isPost) {
        $rawDesc = $post->meta_description
            ?? $post->summary
            ?? Str::limit(strip_tags($post->content), 155);
        $metaDesc = Str::limit(strip_tags($rawDesc), 155);
    } elseif ($isHome && $homepageMetaDesc) {
        $metaDesc = Str::limit($homepageMetaDesc, 155);
    } elseif (isset($category) && $category instanceof \App\Models\Category && $category->description) {
        $metaDesc = Str::limit($category->description, 155);
    } else {
        $metaDesc = Str::limit($siteTagline ?: $siteTitle, 155);
    }

    // --- Build META KEYWORDS ---
    $metaKeywords = null;
    if ($isPost && $post->meta_keywords) {
        $metaKeywords = $post->meta_keywords;
    } elseif ($isHome && $homepageMetaKw) {
        $metaKeywords = $homepageMetaKw;
    }

    // --- Build OG IMAGE ---
    if ($isPost && $post->featured_image) {
        $ogImage = Str::startsWith($post->featured_image, 'http') ? $post->featured_image : url($post->featured_image);
    } elseif ($defaultOg) {
        $ogImage = Str::startsWith($defaultOg, 'http') ? $defaultOg : url($defaultOg);
    } elseif ($siteLogo) {
        $ogImage = Str::startsWith($siteLogo, 'http') ? $siteLogo : url($siteLogo);
    } else {
        $ogImage = null;
    }

    $ogType = $isPost ? 'article' : 'website';
    $favicon = \App\Models\Setting::get('site_favicon');
    $faviconUrl = $favicon ? url($favicon) : asset('favicon.ico');

    // --- Generate JSON-LD schema to avoid Blade issues ---
    $schema = [];
    if ($isPost) {
        $schema = [
            "@context" => "https://schema.org",
            "@type" => "Article",
            "headline" => $post->title,
            "description" => $metaDesc,
            "url" => $canonicalUrl,
            "datePublished" => $post->created_at->toIso8601String(),
            "dateModified" => $post->updated_at->toIso8601String(),
            "author" => [
                "@type" => "Person",
                "name" => $post->user->name ?? 'Author'
            ],
            "publisher" => [
                "@type" => "Organization",
                "name" => $siteTitle
            ]
        ];
        if ($ogImage) {
            $schema["publisher"]["logo"] = ["@type" => "ImageObject", "url" => $ogImage];
            $schema["image"] = $ogImage;
        }
    } else {
        $schema = [
            "@context" => "https://schema.org",
            "@type" => "WebSite",
            "name" => $siteTitle,
            "url" => $siteUrl,
            "description" => $metaDesc
        ];
    }
    $jsonLd = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
@endphp

<link rel="icon" type="image/png" href="{{ $faviconUrl }}">
<link rel="shortcut icon" href="{{ $faviconUrl }}">

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDesc }}">
@if($metaKeywords)
<meta name="keywords" content="{{ $metaKeywords }}">
@endif
<link rel="canonical" href="{{ $canonicalUrl }}">

<meta property="og:site_name" content="{{ $siteTitle }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDesc }}">
@if($ogImage)
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
@endif

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDesc }}">
@if($ogImage)
<meta name="twitter:image" content="{{ $ogImage }}">
@endif

<script type="application/ld+json">
{!! $jsonLd !!}
</script>

<style>
/* Global fix for Tailwind CSS reset on lists inside prose/post content */
.prose ul, .ql-editor ul, article ul {
    list-style-type: disc !important;
    padding-left: 1.75rem !important;
    margin-top: 0.75rem !important;
    margin-bottom: 0.75rem !important;
}
.prose ol, .ql-editor ol, article ol {
    list-style-type: decimal !important;
    padding-left: 1.75rem !important;
    margin-top: 0.75rem !important;
    margin-bottom: 0.75rem !important;
}
.prose ul li, .ql-editor ul li, article ul li,
.prose ol li, .ql-editor ol li, article ol li {
    margin-bottom: 0.35rem !important;
    display: list-item !important;
}
.prose ul ul, .ql-editor ul ul, article ul ul {
    list-style-type: circle !important;
}
.prose table, .ql-editor table, article table {
    width: 100% !important;
    border-collapse: collapse !important;
    margin: 1.5rem 0 !important;
}
.prose th, .ql-editor th, article th {
    background-color: #f8fafc !important;
    border: 1px solid #cbd5e1 !important;
    padding: 0.75rem 1rem !important;
    font-weight: 700 !important;
    text-align: left !important;
    color: #0f172a !important;
}
.prose td, .ql-editor td, article td {
    border: 1px solid #e2e8f0 !important;
    padding: 0.75rem 1rem !important;
    color: #334155 !important;
}
</style>
