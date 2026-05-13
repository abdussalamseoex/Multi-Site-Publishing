{{-- Estate Theme Header: Dynamic Branding Support --}}
@php
    $siteHeaderBg   = \App\Models\Setting::get('header_bg_color');
    $siteHeaderText = \App\Models\Setting::get('header_text_color');
    $primaryColor   = \App\Models\Setting::get('primary_color', '#4f46e5');

    $navBg       = $siteHeaderBg ? '' : 'bg-slate-900';
    $navText     = $siteHeaderText ? '' : 'text-slate-100';
    $logoFilter  = ($siteHeaderBg && strtolower($siteHeaderBg) == '#ffffff') ? '' : 'brightness-0 invert';
    
    $customStyles = "";
    if ($siteHeaderBg) $customStyles .= "background-color: {$siteHeaderBg} !important;";
    if ($siteHeaderText) $customStyles .= "color: {$siteHeaderText} !important;";

    $accentClass = 'hover:text-amber-500';
    $borderClass = 'border-b border-slate-800';
    $topBarBg    = 'bg-slate-800';
    $ctaBg       = ''; 
    $ctaText     = 'text-white font-bold';
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
