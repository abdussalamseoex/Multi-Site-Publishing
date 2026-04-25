<?php
$files = glob('resources/views/themes/*/components/hero_grid.blade.php');
foreach ($files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'is_featured') !== false && strpos($content, 'featuredPosts->merge') === false) {
        preg_match('/\$block\[\'limit\'\] \?\? (\d+)/', $content, $matches);
        $limit = isset($matches[1]) ? $matches[1] : 4;
        
        $replacement = "@php
    \$limit = \$block['limit'] ?? $limit;
    \$query = \\App\\Models\\Post::where('status', 'published');
    if (!empty(\$block['category_id'])) {
        \$query->where('category_id', \$block['category_id']);
    }
    
    // First try to get featured posts
    \$featuredPosts = (clone \$query)->where('is_featured', true)->latest()->take(\$limit)->get();
    
    // If not enough featured posts, fill the gap with latest regular posts
    if (\$featuredPosts->count() < \$limit) {
        \$remaining = \$limit - \$featuredPosts->count();
        \$regularPosts = (clone \$query)->where('is_featured', false)->latest()->take(\$remaining)->get();
        \$featuredPosts = \$featuredPosts->merge(\$regularPosts);
    }
@endphp";
        
        $content = preg_replace('/@php.*?@endphp/s', $replacement, $content, 1);
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
echo "Done.\n";
