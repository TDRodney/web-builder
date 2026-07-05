@php
    $span = $block['props']['span'] ?? '';
    $gridColumn = 'auto';
    $flexBasis = 'auto';
    if ($span) {
        if (is_string($span) && (str_contains($span, '%') || str_contains($span, 'px') || str_contains($span, '/'))) {
            $flexBasis = $span;
        } else {
            $gridColumn = "span {$span} / span {$span}";
        }
    }
    $colPaddingVal = $block['props']['padding'] ?? '0px';
    $colPadding = is_numeric($colPaddingVal) ? "{$colPaddingVal}px" : $colPaddingVal;
    $colWidthVal = $block['props']['width'] ?? 'auto';
    $colWidth = is_numeric($colWidthVal) ? "{$colWidthVal}px" : $colWidthVal;
    $colHeightVal = $block['props']['height'] ?? 'auto';
    $colHeight = is_numeric($colHeightVal) ? "{$colHeightVal}px" : $colHeightVal;
    $colGapVal = $block['props']['gap'] ?? '0px';
    $colGap = is_numeric($colGapVal) ? "{$colGapVal}px" : $colGapVal;
@endphp
<div style="grid-column: {{ $gridColumn }}; flex-basis: {{ $flexBasis }}; padding: {{ $colPadding }}; width: {{ $colWidth }}; height: {{ $colHeight }}; gap: {{ $colGap }};" class="w-full min-h-[50px]">
    @if(!empty($block['children']))
        @foreach($block['children'] as $child)
            @include('partials.block', ['block' => $child])
        @endforeach
    @endif
</div>
