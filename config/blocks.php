<?php

return [
    'types' => [
        'HeroBlock',
        'FeatureBlock',
        'LayoutGrid',
        'LayoutColumn',
        'AtomicText',
        'ButtonBlock',
        'DividerBlock',
        'SpacerBlock',
    ],
    'nesting' => [
        'LayoutGrid' => ['HeroBlock', 'FeatureBlock', 'LayoutGrid', 'LayoutColumn', 'AtomicText', 'ButtonBlock', 'DividerBlock', 'SpacerBlock'],
        'LayoutColumn' => ['HeroBlock', 'FeatureBlock', 'LayoutGrid', 'LayoutColumn', 'AtomicText', 'ButtonBlock', 'DividerBlock', 'SpacerBlock'],
    ],
];
