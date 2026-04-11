@php
    $logo = \App\Models\Setting::get('site_logo');
    $title = \App\Models\Setting::get('site_title', config('app.name'));
@endphp

@if($logo)
    <img src="{{ url($logo) }}" alt="{{ $title }}" {{ $attributes->merge(['style' => 'max-height: 80px; width: auto;']) }}>
@else
    <span {{ $attributes->merge(['class' => 'font-black text-2xl tracking-wider text-indigo-600']) }}>{{ strtoupper($title) }}</span>
@endif

