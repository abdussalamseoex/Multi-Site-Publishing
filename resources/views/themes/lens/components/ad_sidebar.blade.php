@php $adSidebar = \App\Models\Setting::get('ad_sidebar_code'); @endphp
@if($adSidebar)
    <div class="w-full flex justify-center mb-6 overflow-hidden">
        {!! $adSidebar !!}
    </div>
@else
    <div class="bg-gray-50 p-6 text-center border border-dashed border-gray-300 mb-12">
        <span class="text-[10px] uppercase text-gray-400 tracking-[0.2em] mb-4 block">Advertisement</span>
        <div class="w-full bg-gray-200 h-[250px] flex items-center justify-center text-gray-400 font-mono text-xs">
            300x250 Ad Space
        </div>
    </div>
@endif
