<?php

$dir = new RecursiveDirectoryIterator('resources/views/themes');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/.*\.blade\.php$/', RegexIterator::GET_MATCH);

$count = 0;

foreach ($files as $file) {
    $path = $file[0];
    
    // Skip the generic category.blade.php we just created
    if (basename($path) === 'category.blade.php') continue;

    $content = file_get_contents($path);
    $originalContent = $content;

    // Pattern to match <span>...{{ $var->category->name ... }}...</span>
    // We capture the opening tag, the variable name, and the closing tag
    // Note: We use s modifier so dot matches newline.
    $pattern = '/(<(?:span|div)[^>]*>)\s*\{\{\s*\$([a-zA-Z0-9_\-]+)->category->name.*?\}\}\s*(<\/(?:span|div)>)/s';
    
    $content = preg_replace_callback($pattern, function($matches) {
        $fullMatch = $matches[0];
        $openTag = $matches[1];
        $varName = $matches[2];
        $closeTag = $matches[3];

        // Ensure we don't double wrap if it's already inside an <a> tag
        // (We can't easily check enclosing tags with simple regex, but we know our templates generally didn't wrap categories in <a> except maybe in specific places).
        
        return "<a href=\"{{ isset(\${$varName}->category) ? route('frontend.category', \${$varName}->category->slug) : '#' }}\" class=\"hover:opacity-80 transition\">{$fullMatch}</a>";
    }, $content);

    // Some places use {{ $category->name }} literally if it's a category loop.
    // Like in news/home.blade.php Gallery strip:
    // <h4 class="...">{{ $cat->name }}</h4>
    $pattern2 = '/(<(?:h4)[^>]*>)\s*\{\{\s*\$([a-zA-Z0-9_\-]+)->name\s*\}\}\s*(<\/h4>)/s';
    $content = preg_replace_callback($pattern2, function($matches) {
        $fullMatch = $matches[0];
        $openTag = $matches[1];
        $varName = $matches[2];
        
        // Only if it's likely a category (e.g. $cat)
        if ($varName === 'cat') {
            return "<a href=\"{{ route('frontend.category', \${$varName}->slug) }}\">{$fullMatch}</a>";
        }
        return $fullMatch;
    }, $content);

    if ($content !== $originalContent) {
        file_put_contents($path, $content);
        $count++;
        echo "Updated: $path\n";
    }
}

echo "Total files updated: $count\n";
