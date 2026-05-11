@php
    $headerStyle = \App\Models\Setting::get('header_style', '1');
    $activeTheme = \App\Models\Setting::get('active_theme', 'minimal');

    // Auto mode: load theme-native header if it exists
    if ($headerStyle === 'auto' || $headerStyle === '0') {
        $themeNativePath = 'themes.' . $activeTheme . '.components.header';
        $themeNativeFile = resource_path('views/themes/' . $activeTheme . '/components/header.blade.php');
        if (file_exists($themeNativeFile)) {
            $headerStyle = 'theme_native';
        } else {
            $headerStyle = '1'; // Fallback to Style 1 (Classic)
        }
    }

    $validStyles = ['1', '2'];
@endphp

@if($headerStyle === 'theme_native')
    @include('themes.' . $activeTheme . '.components.header')
@elseif(in_array($headerStyle, $validStyles))
    @include('themes.components.headers.style' . $headerStyle)
@else
    @include('themes.components.headers.style1')
@endif
