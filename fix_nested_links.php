<?php
$dir = __DIR__ . '/resources/views/themes/';
$themes = ['minimal', 'blog', 'news', 'magazine', 'vanguard', 'nexus', 'ledger', 'vitality', 'estate', 'voyage'];

function fixNestedLinks($content) {
    // We want to find cases where an <a href="{{ route('frontend.post'...> 
    // wraps around <a href="{{ ...category...
    
    // Split the content by "<a " to trace depths roughly (ignoring HTML comments, but close enough for these themes).
    // Let's use a regex to find a block starting with <a href="{{ route('frontend.post'... and ending with </a>
    // that CONTAINS another "<a " inside it.
    
    // Since PCRE allows recursion or lazy matching:
    // <a href="{{ route('frontend.post' [^>]+ >  ...  <a href=...category... </a> ... </a>
    
    $offset = 0;
    while (($pos = strpos($content, '<a href="{{ route(\'frontend.post\'', $offset)) !== false) {
        // Find where this <a> starts and ends
        $endOfOpeningTag = strpos($content, '>', $pos);
        if ($endOfOpeningTag === false) break;
        
        $openingTag = substr($content, $pos, $endOfOpeningTag - $pos + 1);
        
        // Find the matching closing </a>
        // We will keep track of nested <a>s to find the correct </a>
        $searchOffset = $endOfOpeningTag + 1;
        $depth = 1;
        $closingPos = -1;
        
        while ($searchOffset < strlen($content)) {
            $nextA = strpos($content, '<a ', $searchOffset);
            $nextClose = strpos($content, '</a>', $searchOffset);
            
            if ($nextClose === false) {
                break; 
            }
            
            if ($nextA !== false && $nextA < $nextClose) {
                $depth++;
                $searchOffset = $nextA + 1;
            } else {
                $depth--;
                if ($depth === 0) {
                    $closingPos = $nextClose;
                    break;
                }
                $searchOffset = $nextClose + 1;
            }
        }
        
        if ($closingPos !== -1) {
            $innerContent = substr($content, $endOfOpeningTag + 1, $closingPos - ($endOfOpeningTag + 1));
            
            // Check if inner content contains a category link!
            if (strpos($innerContent, "route('frontend.category'") !== false) {
                // WE FOUND A NESTED LINK!
                // Transform the outer <a ...> into <article ... relative>
                // and inject a stretched link inside.
                
                // Extract href and class from the opening <a>
                preg_match('/href="([^"]+)"/', $openingTag, $hrefMatch);
                preg_match('/class="([^"]+)"/', $openingTag, $classMatch);
                
                $href = $hrefMatch[1] ?? '';
                $classes = $classMatch[1] ?? '';
                
                if (strpos($classes, 'relative') === false) {
                    $classes .= ' relative';
                }
                
                $newOpening = '<article class="' . $classes . '">' . "\n" . '<a href="' . $href . '" class="absolute inset-0 z-0"></a>';
                $newClosing = '</article>';
                
                // Also we need to make sure inner links have relative z-10 otherwise they can't be clicked!
                // Let's add relative z-10 to the category links inside this block
                $innerContent = preg_replace_callback('/(<a[^>]*route\(\'frontend\.category\'[^>]*class=")([^"]*)(")/is', function($m) {
                    $cls = $m[2];
                    if (strpos($cls, 'relative') === false && strpos($cls, 'absolute') === false) {
                        $cls .= ' relative z-10';
                    } elseif (strpos($cls, 'absolute') !== false && strpos($cls, 'z-') === false) {
                        $cls .= ' z-10';
                    }
                    if (strpos($cls, 'z-10') === false && strpos($cls, 'z-') === false) {
                        $cls .= ' z-10';
                    }
                    return $m[1] . $cls . $m[3];
                }, $innerContent);
                
                // Special case for Absolute Badges, we already made them absolute. We just need to give them custom z-index.
                
                $newBlock = $newOpening . $innerContent . $newClosing;
                
                // Replace in content
                $content = substr_replace($content, $newBlock, $pos, $closingPos + 4 - $pos);
                
                // Adjust offset
                $offset = $pos + strlen($newOpening);
                continue;
            }
        }
        
        $offset = $endOfOpeningTag + 1;
    }
    
    return $content;
}

foreach($themes as $theme) {
    foreach(['home.blade.php', 'post.blade.php'] as $file) {
        $path = $dir . $theme . '/' . $file;
        if(file_exists($path)) {
            $content = file_get_contents($path);
            $newContent = fixNestedLinks($content);
            if ($content !== $newContent) {
                file_put_contents($path, $newContent);
                echo "Fixed nested links in $theme/$file\n";
            }
        }
    }
}
echo "Done!\n";
