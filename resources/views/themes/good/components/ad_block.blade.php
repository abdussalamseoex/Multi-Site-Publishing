<section class="mb-10 text-center flex justify-center w-full">
    @php
        $adCode = !empty($block['ad_code']) ? $block['ad_code'] : \App\Models\Setting::get('ad_header', '');
        // Determine if we are likely in a sidebar by checking if the parent wrapper is narrow, but we can just use responsive CSS
    @endphp
    
    @if(!empty($adCode))
        <div class="w-full overflow-hidden flex justify-center py-4 bg-gray-50 border border-gray-200 rounded">
            {!! $adCode !!}
        </div>
    @else
        <div class="w-full min-h-[90px] md:min-h-[250px] bg-gray-200 text-gray-400 flex items-center justify-center font-bold uppercase tracking-widest text-xs rounded border border-gray-300 px-4 text-center">
            Ad Space<br>(Responsive)
        </div>
    @endif
</section>
