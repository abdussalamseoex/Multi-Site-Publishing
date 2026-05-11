@php
    $footerStyle = \App\Models\Setting::get('footer_style', '1');
    $activeTheme = \App\Models\Setting::get('active_theme', 'minimal');

    // Auto mode: load theme-native footer if it exists
    if ($footerStyle === 'auto' || $footerStyle === '0') {
        $themeNativeFile = resource_path('views/themes/' . $activeTheme . '/components/footer.blade.php');
        if (file_exists($themeNativeFile)) {
            $footerStyle = 'theme_native';
        } else {
            $footerStyle = '1'; // Fallback to Style 1 (Classic)
        }
    }

    $validStyles = ['1', '2'];
@endphp

@if($footerStyle === 'theme_native')
    @include('themes.' . $activeTheme . '.components.footer')
@elseif(in_array($footerStyle, $validStyles))
    @include('themes.components.footers.style' . $footerStyle)
@else
    @include('themes.components.footers.style1')
@endif
