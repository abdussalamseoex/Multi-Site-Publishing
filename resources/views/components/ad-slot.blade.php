@props(['placement', 'class' => ''])

@php
    $placementKey = 'ad_placement_' . $placement;
    $selectedUnitKey = \App\Models\Setting::get($placementKey);
    $adHtml = '';

    if (!empty($selectedUnitKey)) {
        $adHtml = \App\Models\Setting::get($selectedUnitKey);
    }
@endphp

@if(!empty($adHtml))
    <div class="ad-container ad-{{ $placement }} {{ $class }} flex justify-center items-center my-4 overflow-hidden">
        {!! $adHtml !!}
    </div>
@endif
