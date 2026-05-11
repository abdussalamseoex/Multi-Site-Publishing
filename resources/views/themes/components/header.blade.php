@php
    $headerStyle = \App\Models\Setting::get('header_style', '1');
    $validStyles = ['1', '2'];
    if (!in_array($headerStyle, $validStyles)) { $headerStyle = '1'; }
@endphp

@include('themes.components.headers.style' . $headerStyle)
