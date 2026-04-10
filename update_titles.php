<?php
$dir = __DIR__ . '/resources/views/themes/';
$themes = ['minimal', 'blog', 'news', 'magazine', 'vanguard', 'nexus', 'ledger', 'vitality', 'estate', 'voyage'];

foreach($themes as $theme) {
    $file = $dir . $theme . '/home.blade.php';
    if(file_exists($file)) {
        $c = file_get_contents($file);
        
        // Find <title>...</title>
        $c = preg_replace('/<title>(.*?)<\/title>/is', '<title>{{ isset($category) ? $category->name . \' - \' : \'\' }}$1</title>', $c);
        
        // Prevent double injection if run twice
        $c = str_replace('{{ isset($category) ? $category->name . \' - \' : \'\' }}{{ isset($category) ? $category->name . \' - \' : \'\' }}', '{{ isset($category) ? $category->name . \' - \' : \'\' }}', $c);
        
        file_put_contents($file, $c);
    }
}
echo "Titles updated!\n";
