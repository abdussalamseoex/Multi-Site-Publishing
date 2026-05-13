@php
    /**
     * Universal SEO Meta Tags Component
     * Priority Logic:
     *   Title:       Post Meta Title > Post Title > Homepage Title > Site Title
     *   Description: Post Meta Desc  > Post Summary > Homepage Desc > Site Tagline
     *   OG Image:    Post Featured Image > Default OG Image > Site Logo
     *
     * Variables:
     *   $post        (optional) - Post model for article pages
     *   $activeTheme (optional) - String, current theme name
     *   $isHomepage  (optional) - Bool, true on homepage
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
    if ($isPost && $post->meta_keywords) {
        $metaKeywords = $post->meta_keywords;
    } elseif ($isHome && $homepageMetaKw) {
        $metaKeywords = $homepageMetaKw;
    } else {
        $metaKeywords = null;
    }

    // --- Build OG IMAGE ---
    if ($isPost && $post->featured_image) {
        $ogImage = Str::startsWith($post->featured_image, 'http')
            ? $post->featured_image
            : url($post->featured_image);
    } elseif ($defaultOg) {
        $ogImage = Str::startsWith($defaultOg, 'http') ? $defaultOg : url($defaultOg);
    } elseif ($siteLogo) {
        $ogImage = Str::startsWith($siteLogo, 'http') ? $siteLogo : url($siteLogo);
    } else {
        $ogImage = null;
    }

    // --- OG Type ---
    $ogType = $isPost ? 'article' : 'website';

    // --- Favicon ---
    $favicon = \App\Models\Setting::get('site_favicon');
    $faviconUrl = $favicon ? url($favicon) : asset('favicon.ico');
@endphp

{{-- Favicon --}}
<link rel="icon" type="image/png" href="{{ $faviconUrl }}">
<link rel="shortcut icon" href="{{ $faviconUrl }}">

{{-- Core SEO --}}
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDesc }}">
@if($metaKeywords)
<meta name="keywords" content="{{ $metaKeywords }}">
@endif
<link rel="canonical" href="{{ $canonicalUrl }}">

{{-- Open Graph --}}
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

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDesc }}">
@if($ogImage)
<meta name="twitter:image" content="{{ $ogImage }}">
@endif

{{-- Article-specific Schema.org JSON-LD --}}
@if($isPost)
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "{{ addslashes($post->title) }}",
  "description": "{{ addslashes($metaDesc) }}",
  "url": "{{ $canonicalUrl }}",
  "datePublished": "{{ $post->created_at->toIso8601String() }}",
  "dateModified": "{{ $post->updated_at->toIso8601String() }}",
  "author": {
    "@type": "Person",
    "name": "{{ addslashes($post->user->name ?? 'Author') }}"
  },
  "publisher": {
    "@type": "Organization",
    "name": "{{ addslashes($siteTitle) }}"{{ $ogImage ? ',
    "logo": { "@type": "ImageObject", "url": "' . addslashes($ogImage) . '" }' : '' }}
  }{{ $ogImage ? ',
  "image": "' . addslashes($ogImage) . '"' : '' }}
}
</script>
@else
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "{{ addslashes($siteTitle) }}",
  "url": "{{ $siteUrl }}",
  "description": "{{ addslashes($metaDesc) }}"
}
</script>
@endif
