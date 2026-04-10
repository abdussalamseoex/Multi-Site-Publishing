<?php

$dir = __DIR__ . '/resources/views/themes/';
$themes = ['minimal', 'blog', 'news', 'magazine', 'vanguard', 'nexus', 'ledger', 'vitality', 'estate', 'voyage'];

function repairBrokenArticles($content) {
    // Pattern to match the corrupted injection
    $pattern = '/<article class=" relative">\s*<a href="" class="absolute inset-0 z-0"><\/a>slug\)\s*\}\}"\s*class="([^"]+)">/s';
    
    $offset = 0;
    while(preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE, $offset)) {
        $fullMatch = $matches[0][0];
        $matchPos = $matches[0][1];
        $classes = $matches[1][0];
        
        $before = substr($content, 0, $matchPos);
        $varName = 'post'; // Default fallback
        
        // Find nearest loop variable like "as $post)" or "as $index => $post)"
        if (preg_match_all('/as\s+(?:\$[a-zA-Z0-9_]+\s*=>\s*)?\$([a-zA-Z0-9_]+)\s*\)/is', $before, $varMatches)) {
            $varName = end($varMatches[1]);
        }
        
        // Let's also check if there's a recent $hero = $something->first() nearby 
        // that is closer than the loop variable
        $lastLoopPos = strrpos($before, end($varMatches[0]));
        
        if (preg_match_all('/@php\s+\$([a-zA-Z0-9_]+)\s*=\s*\$[a-zA-Z0-9_]+(?:->first\(\)|\[\d+\])/is', $before, $phpMatches, PREG_OFFSET_CAPTURE)) {
            $lastPhpMatch = end($phpMatches[0]);
            $lastPhpPos = $lastPhpMatch[1];
            if ($lastPhpPos > $lastLoopPos) {
                // PHP assignment is closer!
                $varName = end($phpMatches[1])[0];
            }
        }
        
        // Edge cases that might be hard-coded from top of file
        if (strpos($before, '$mainHero =') !== false && !strpos($before, 'as $', strrpos($before, '$mainHero ='))) {
             if(strpos(substr($before, -500), '$mainHero') !== false) $varName = 'mainHero';
        }
        if (strpos($before, '$mainFeatured =') !== false && !strpos($before, 'as $', strrpos($before, '$mainFeatured ='))) {
            if(strpos(substr($before, -500), '$mainFeatured') !== false) $varName = 'mainFeatured';
        }
        if (strpos($before, '$firstMiss =') !== false && !strpos($before, 'as $', strrpos($before, '$firstMiss ='))) {
            if(strpos(substr($before, -500), '$firstMiss') !== false) $varName = 'firstMiss';
        }
        
        $repaired = '<article class="' . $classes . ' relative">' . "\n" . '    <a href="{{ route(\'frontend.post\', $' . $varName . '->slug) }}" class="absolute inset-0 z-0"></a>';
        
        $content = substr_replace($content, $repaired, $matchPos, strlen($fullMatch));
        $offset = $matchPos + strlen($repaired);
    }
    
    return $content;
}

foreach($themes as $theme) {
    foreach(['home.blade.php', 'post.blade.php'] as $file) {
        $path = $dir . $theme . '/' . $file;
        if(file_exists($path)) {
            $content = file_get_contents($path);
            $newContent = repairBrokenArticles($content);
            if ($content !== $newContent) {
                file_put_contents($path, $newContent);
                echo "Repaired $theme/$file\n";
            }
        }
    }
}
echo "Repair Done!\n";
