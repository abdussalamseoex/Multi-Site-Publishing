{{-- Ledger Theme Header: Finance/Business — Trustworthy, Navy, Gold --}}
@php
@php
    $siteHeaderBg   = \App\Models\Setting::get('header_bg_color');
    $siteHeaderText = \App\Models\Setting::get('header_text_color');
    $primaryColor   = \App\Models\Setting::get('primary_color', '#4f46e5');

    $navBg       = $siteHeaderBg ? '' : 'bg-blue-950';
    $navText     = $siteHeaderText ? '' : 'text-blue-100';
    $logoFilter  = ($siteHeaderBg && strtolower($siteHeaderBg) == '#ffffff') ? '' : 'brightness-0 invert';
    
    $customStyles = "";
    if ($siteHeaderBg) $customStyles .= "background-color: {$siteHeaderBg} !important;";
    if ($siteHeaderText) $customStyles .= "color: {$siteHeaderText} !important;";

    $accentClass = 'hover:opacity-80';
    $borderClass = 'border-b border-opacity-20';
    $topBarBg    = 'bg-blue-900';
    $ctaBg       = '';
    $ctaText     = 'text-blue-950 font-black';
@endphp

@push('styles')
<style>
    #main-nav { {!! $customStyles !!} }
    @if($siteHeaderText)
    #main-nav .nav-link, #main-nav a { color: {{ $siteHeaderText }} !important; }
    @endif
</style>
@endpush
@endphp
@include('themes.components.headers._base')
