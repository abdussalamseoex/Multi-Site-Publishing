{{-- Vanguard Theme Header: Dynamic Branding Support --}}
@php
    $siteHeaderBg   = \App\Models\Setting::get('header_bg_color');
    $siteHeaderText = \App\Models\Setting::get('header_text_color');
    $primaryColor   = \App\Models\Setting::get('primary_color', '#4f46e5');

    // Default styles for Vanguard if not overridden
    $navBg       = $siteHeaderBg ? '' : 'bg-gray-950';
    $navText     = $siteHeaderText ? '' : 'text-gray-200';
    $logoFilter  = ($siteHeaderBg && strtolower($siteHeaderBg) == '#ffffff') ? '' : 'brightness-0 invert';
    
    // Custom inline styles for Dynamic Branding
    $customStyles = "";
    if ($siteHeaderBg) {
        $customStyles .= "background-color: {$siteHeaderBg} !important;";
    }
    if ($siteHeaderText) {
        $customStyles .= "color: {$siteHeaderText} !important;";
    }

    $accentClass = 'hover:opacity-80';
    $borderClass = 'border-b border-opacity-20';
    $topBarBg    = 'bg-black';
    $ctaBg       = ''; // Handled by primary color var in base
    $ctaText     = 'text-white';
@endphp

@push('styles')
<style>
    #main-nav { {!! $customStyles !!} }
    @if($siteHeaderText)
    #main-nav .nav-link, #main-nav a { color: {{ $siteHeaderText }} !important; }
    @endif
    #main-nav .primary-btn { background-color: {{ $primaryColor }} !important; }
</style>
@endpush

@include('themes.components.headers._base')
