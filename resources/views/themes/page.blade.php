<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->meta_title ?? $page->title }}</title>
    <meta name="description" content="{{ $page->meta_description }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    @php
        $primary = \App\Models\Setting::get('primary_color', '#111827');
        $logo = \App\Models\Setting::get('site_logo');
        $headerMenu = \App\Models\Menu::where('location', 'header')->with('items')->first() ?? \App\Models\Menu::with('items')->first();
        $footerMenu = \App\Models\Menu::where('location', 'footer')->with('items')->first();
    @endphp
    <style>
        :root { --primary: {{ $primary }}; }
        body { font-family: 'Inter', sans-serif; background-color: #fafafa; }
        .text-primary { color: var(--primary); }
        .bg-primary { background-color: var(--primary); }
        .prose p { margin-bottom: 1.5em; line-height: 1.8; color: #4b5563; }
        .prose h1, .prose h2, .prose h3 { font-weight: bold; margin-bottom: 0.5em; color: #111827; }
        .prose h2 { font-size: 1.5rem; margin-top: 2em; }
        .prose ul, .prose ol { margin-left: 1.5em; margin-bottom: 1.5em; }
        .prose a { color: var(--primary); text-decoration: underline; }
    </style>
</head>
<body class="text-gray-900 antialiased flex flex-col min-h-screen">

    @include('themes.components.header')

    <main class="flex-grow max-w-3xl mx-auto px-6 py-16 w-full">
        <article class="bg-white p-8 md:p-12 shadow-sm rounded-xl border border-gray-100">
            <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight mb-8">{{ $page->title }}</h1>
            <div class="prose max-w-none">
                {!! $page->content !!}
            </div>
        </article>
    </main>

    @include('themes.components.footer')
</body>
</html>
