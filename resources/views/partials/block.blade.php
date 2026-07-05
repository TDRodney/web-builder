@php
    use Illuminate\Support\Facades\Log;

    $isValidBlock = is_array($block) && isset($block['type']);
    if (!$isValidBlock) {
        Log::warning('[Blade Block Render] Invalid block data schema detected.', ['block' => $block]);
    }
    
    $type = $isValidBlock ? $block['type'] : null;
    
    $templateMapping = [
        'HeroBlock' => 'hero',
        'FeatureBlock' => 'feature',
        'AtomicText' => 'atomic-text',
        'LayoutGrid' => 'layout-grid',
        'LayoutColumn' => 'layout-column',
    ];

    $templateName = $type ? ($templateMapping[$type] ?? null) : null;

    if ($isValidBlock && !$templateName) {
        Log::warning("[Blade Block Render] Unrecognized block type '{$type}' attempted.", ['block' => $block]);
    }

    $paddingVal = $isValidBlock && isset($block['props']['padding']) ? $block['props']['padding'] : 0;
    $padding = is_numeric($paddingVal) ? "{$paddingVal}px" : $paddingVal;
    $bgColor = $isValidBlock && isset($block['props']['backgroundColor']) ? $block['props']['backgroundColor'] : 'transparent';
@endphp

@if($isValidBlock && $templateName)
    <div style="padding: {{ $padding }}; background-color: {{ $bgColor }};" class="transition-all relative my-2">
        @include("partials.blocks.{$templateName}", ['block' => $block])
    </div>
@else
    <!-- [Renderer Error] Block Type '{{ $type ?? 'Unknown' }}' failed to render due to invalid schema or missing template mapping. -->
    @if(config('app.debug'))
        <div style="padding: 10px; border: 1px dashed #ef4444; background-color: #fef2f2; color: #b91c1c; font-size: 11px; font-family: monospace; border-radius: 6px; margin: 8px 0; display: block;">
            <strong>[Renderer Error]</strong> Failed to render block of type: <code>{{ $type ?? 'NULL' }}</code>. See Laravel logs for structural mismatch payload.
        </div>
    @endif
@endif
