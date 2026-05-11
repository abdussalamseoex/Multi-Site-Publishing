@php
    $footerStyle = \App\Models\Setting::get('footer_style', '1');
    $validStyles = ['1', '2'];
    if (!in_array($footerStyle, $validStyles)) { $footerStyle = '1'; }
@endphp

@include('themes.components.footers.style' . $footerStyle)
