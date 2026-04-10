<?php

$dir = __DIR__ . '/resources/views/themes/';
$themes = ['minimal', 'blog', 'news', 'magazine', 'vanguard', 'nexus', 'ledger', 'vitality', 'estate', 'voyage'];

foreach($themes as $theme) {
    foreach(['home.blade.php', 'post.blade.php'] as $file) {
        $path = $dir . $theme . '/' . $file;
        if(file_exists($path)) {
            $content = file_get_contents($path);
            
            // Find category links
            $pattern = '/(<a[^>]*route\(\'frontend\.category\'[^>]*class=")(([^"])*)(")/is';
            
            $content = preg_replace_callback($pattern, function($matches) {
                $start = $matches[1];
                $classes = $matches[2];
                $end = $matches[4];
                
                if (strpos($classes, 'z-10') === false && strpos($classes, 'z-') === false) {
                    $classes .= ' z-10';
                }
                
                if (strpos($classes, 'relative') === false && strpos($classes, 'absolute') === false) {
                    $classes .= ' relative';
                }
                
                return $start . trim($classes) . $end;
            }, $content);
            
            file_put_contents($path, $content);
        }
    }
}
echo "Category z-index fixed!\n";
