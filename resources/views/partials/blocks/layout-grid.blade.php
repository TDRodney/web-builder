@php
    $columns = $block['props']['columns'] ?? 3;
    $gapVal = $block['props']['gap'] ?? '1rem';
    $gap = is_numeric($gapVal) ? "{$gapVal}px" : $gapVal;
    $gridPaddingVal = $block['props']['padding'] ?? '1rem';
    $gridPadding = is_numeric($gridPaddingVal) ? "{$gridPaddingVal}px" : $gridPaddingVal;
    $widthVal = $block['props']['width'] ?? 'auto';
    $width = is_numeric($widthVal) ? "{$widthVal}px" : $widthVal;
    $heightVal = $block['props']['height'] ?? 'auto';
    $height = is_numeric($heightVal) ? "{$heightVal}px" : $heightVal;
@endphp
<div style="display: grid; grid-template-columns: repeat({{ $columns }}, minmax(0, 1fr)); gap: {{ $gap }}; padding: {{ $gridPadding }}; width: {{ $width }}; height: {{ $height }};">
    @if(!empty($block['children']))
        @foreach($block['children'] as $child)
            @include('partials.block', ['block' => $child])
        @endforeach
    @endif
</div>
