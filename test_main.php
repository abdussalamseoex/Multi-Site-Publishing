<?php
$dir = __DIR__ . '/resources/views/themes/';
$themes = ['minimal', 'blog', 'news', 'magazine', 'vanguard', 'nexus', 'ledger', 'vitality', 'estate', 'voyage'];
foreach($themes as $theme) {
    if(file_exists($dir . $theme . '/home.blade.php')) {
        $c = file_get_contents($dir . $theme . '/home.blade.php');
        if (strpos($c, 'max-w-7xl mx-auto') !== false) {
            echo $theme . " HAS MAX-W\n";
        } else {
            echo $theme . " NO MAX-W\n";
        }
    }
}
