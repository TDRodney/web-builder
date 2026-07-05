<?php

return [
    'types' => [
        'HeroBlock',
        'FeatureBlock',
        'LayoutGrid',
        'LayoutColumn',
        'AtomicText',
    ],
    'nesting' => [
        'LayoutGrid' => ['LayoutColumn'],
        'LayoutColumn' => ['HeroBlock', 'FeatureBlock', 'LayoutGrid', 'LayoutColumn', 'AtomicText'],
    ],
];
