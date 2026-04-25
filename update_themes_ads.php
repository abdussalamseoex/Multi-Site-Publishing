<?php
$files = glob('resources/views/themes/*/post.blade.php');
foreach ($files as $file) {
    $content = file_get_contents($file);
    // Replace content
    $content = str_replace('{!! $post->content !!}', '{!! \App\Helpers\AdHelper::injectInArticleAds($post->content) !!}', $content);
    
    // Replace sidebar
    $content = preg_replace('/@php \$adSidebar = \\\App\\\Models\\\Setting::get\(\'ad_sidebar_code\'\); @endphp.*?@endif/s', '@if(!empty(\App\Models\Setting::get(\'ad_placement_sidebar\')))' . "\n" . '                    <div class="widget text-center overflow-hidden flex justify-center">' . "\n" . '                        <x-ad-slot placement="sidebar" />' . "\n" . '                    </div>' . "\n" . '                @endif', $content);
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}
