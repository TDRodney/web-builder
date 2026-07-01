@php
    $paddingVal = $block['props']['padding'] ?? 0;
    $padding = is_numeric($paddingVal) ? "{$paddingVal}px" : $paddingVal;
    $bgColor = $block['props']['backgroundColor'] ?? 'transparent';
@endphp
<div style="padding: {{ $padding }}; background-color: {{ $bgColor }};" class="transition-all relative my-2">
    @if($block['type'] === 'HeroBlock')
        <div class="text-center">
            <h1 class="text-4xl font-extrabold tracking-tight sm:text-6xl text-slate-900">
                {{ $block['props']['headline'] ?? 'Default Headline' }}
            </h1>
            <p class="mt-4 text-xl text-slate-500">
                {{ $block['props']['subheadline'] ?? 'Default subheadline' }}
            </p>
        </div>
    @elseif($block['type'] === 'FeatureBlock')
        <div class="py-4 border-t border-slate-100">
            <h3 class="text-lg font-bold text-slate-800">{{ $block['props']['title'] ?? 'Feature Item' }}</h3>
            <p class="text-sm text-slate-600 mt-1">{{ $block['props']['body'] ?? 'Feature description details go here.' }}</p>
        </div>
    @elseif($block['type'] === 'AtomicText')
        @php
            $fontSize = $block['props']['fontSize'] ?? '';
            $fontStyle = $fontSize ? (is_numeric($fontSize) ? "font-size: {$fontSize}px;" : "font-size: {$fontSize};") : '';
            $color = $block['props']['color'] ?? '';
            $colorStyle = $color ? (str_starts_with($color, '--') ? "color: var({$color});" : "color: {$color};") : '';
        @endphp
        <div style="{{ $fontStyle }} {{ $colorStyle }}">
            {{ $block['props']['content'] ?? '' }}
        </div>
    @elseif($block['type'] === 'LayoutGrid')
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
    @elseif($block['type'] === 'LayoutColumn')
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
    @endif
</div>
