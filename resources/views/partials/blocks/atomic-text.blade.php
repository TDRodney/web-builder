@php
    $fontSize = $block['props']['fontSize'] ?? '';
    $fontStyle = $fontSize ? (is_numeric($fontSize) ? "font-size: {$fontSize}px;" : "font-size: {$fontSize};") : '';
    $color = $block['props']['color'] ?? '';
    $colorStyle = $color ? (str_starts_with($color, '--') ? "color: var({$color});" : "color: {$color};") : '';
@endphp
<div style="{{ $fontStyle }} {{ $colorStyle }}">
    {{ $block['props']['content'] ?? '' }}
</div>
