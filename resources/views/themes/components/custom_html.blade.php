{{--
    Custom HTML Block Component
    Renders raw HTML/JS injected by admin via Theme Builder.
    This block is always treated as full-width.
--}}
@if(!empty($block['html_code']))
<div class="custom-html-block w-full">
    {!! $block['html_code'] !!}
</div>
@endif
