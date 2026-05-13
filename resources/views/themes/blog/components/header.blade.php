{{-- Blog Theme Header: Dynamic Branding Support --}}
@php
    $siteHeaderBg   = \App\Models\Setting::get('header_bg_color');
    $siteHeaderText = \App\Models\Setting::get('header_text_color');
    $primaryColor   = \App\Models\Setting::get('primary_color', '#4f46e5');

    $navBg       = $siteHeaderBg ? '' : 'bg-white';
    $navText     = $siteHeaderText ? '' : 'text-gray-800';
    $logoFilter  = ($siteHeaderBg && strtolower($siteHeaderBg) != '#ffffff') ? 'brightness-0 invert' : '';
    
    $customStyles = "";
    if ($siteHeaderBg) $customStyles .= "background-color: {$siteHeaderBg} !important;";
    if ($siteHeaderText) $customStyles .= "color: {$siteHeaderText} !important;";

    $accentClass = 'hover:text-indigo-600';
    $borderClass = 'border-b border-gray-100';
    $topBarBg    = 'bg-indigo-600';
    $ctaBg       = ''; 
    $ctaText     = 'text-white';
@endphp

@push('styles')
<style>
    #main-nav { {!! $customStyles !!} }
    @if($siteHeaderText)
    #main-nav .nav-link, #main-nav a { color: {{ $siteHeaderText }} !important; }
    @endif
</style>
@endpush

@include('themes.components.headers._base')
