@php
    $sidebarLayoutRaw = \App\Models\Setting::get('theme_sidebar_' . $activeTheme);
    $sidebarBlocks = $sidebarLayoutRaw ? json_decode($sidebarLayoutRaw, true) : [];
    
    if (empty($sidebarBlocks)) {
        $sidebarBlocks = [
            ['id' => uniqid(), 'type' => 'popular_posts', 'title' => 'Most Popular', 'limit' => 5],
            ['id' => uniqid(), 'type' => 'ad_block', 'title' => 'Sidebar Ad']
        ];
    }
@endphp

@foreach($sidebarBlocks as $block)
    @if(view()->exists("themes.{$activeTheme}.components.{$block['type']}"))
        @include("themes.{$activeTheme}.components.{$block['type']}", ['block' => $block])
    @else
        @include("themes.good.components.{$block['type']}", ['block' => $block])
    @endif
@endforeach
