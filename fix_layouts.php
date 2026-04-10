<?php

$dir = __DIR__ . '/resources/views/themes/';
$themes = ['minimal', 'blog', 'news', 'magazine', 'vanguard', 'nexus', 'ledger', 'vitality', 'estate', 'voyage'];

function extractVariableName($match) {
    preg_match('/\{\{\s*\$([a-zA-Z0-9_\-]+)->title\s*\}\}/', $match, $m);
    return $m[1] ?? 'post';
}

foreach($themes as $theme) {
    foreach(['home.blade.php', 'post.blade.php'] as $file) {
        $path = $dir . $theme . '/' . $file;
        if(file_exists($path)) {
            $content = file_get_contents($path);
            
            // 1. Move absolute positioning classes from child span/div to parent a tag
            // Looking for <a href="..." class="..."><span class="absolute ...">...</span></a>
            $patternAuth = '/(<a[^>]*class=")([^"]*)(">\s*<(?:span|div)[^>]*class="[^"]*)(absolute[^"]*)(".*?>)/is';
            
            $content = preg_replace_callback($patternAuth, function($matches) {
                $aStart = $matches[1];
                $aClasses = $matches[2];
                $innerStart = $matches[3];
                $innerClassesStr = $matches[4]; // absolute top-3 left-3 etc
                $innerEnd = $matches[5];
                
                // Extract positioning classes
                preg_match_all('/\b(absolute|top-[\w\-]+|right-[\w\-]+|bottom-[\w\-]+|left-[\w\-]+|z-\d+|inset-[\w\-]+)\b/', $innerClassesStr, $extracted);
                $toMove = implode(' ', $extracted[0]);
                
                // Remove from inner classes
                $newInnerClassesStr = preg_replace('/\b(absolute|top-[\w\-]+|right-[\w\-]+|bottom-[\w\-]+|left-[\w\-]+|z-\d+|inset-[\w\-]+)\b/', '', $innerClassesStr);
                $newInnerClassesStr = trim(preg_replace('/\s+/', ' ', $newInnerClassesStr));
                
                // Add absolute tracking to outer A tag
                if (!str_contains($aClasses, 'block') && !str_contains($aClasses, 'inline-block')) {
                    $toMove = 'inline-block ' . $toMove;
                }
                
                $newOuter = $aStart . trim($aClasses . ' ' . $toMove) . $innerStart . $newInnerClassesStr . $innerEnd;
                return $newOuter;
            }, $content);


            // 2. Add line-clamp-2 to headers (h2-h5) that don't have line-clamp
            $titlePattern = '/(<h[1-6][^>]*class=")([^"]*)("[^>]*>\s*\{\{\s*\$[a-zA-Z0-9_\-]+->title\s*\}\}\s*<\/h[1-6]>)/is';
            
            $content = preg_replace_callback($titlePattern, function($matches) {
                $aStart = $matches[1];
                $classes = trim($matches[2]);
                $aEnd = $matches[3];
                
                if (strpos($classes, 'line-clamp') === false) {
                    $classes .= ' line-clamp-2';
                }
                
                return $aStart . $classes . $aEnd;
            }, $content);
            
            file_put_contents($path, $content);
        }
    }
}
echo "Layout fixes applied!\n";
