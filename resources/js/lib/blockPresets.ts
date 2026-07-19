import { heroPatternPresets } from '@/lib/sectionPatterns';

export interface PresetBlockNode {
    type: string;
    props: Record<string, any>;
    children?: PresetBlockNode[];
    id?: string;
}

/** Wireframe sketch key used by the library thumbnail. */
export type PresetPreview =
    | 'centered'
    | 'split-right'
    | 'split-left'
    | 'editorial'
    | 'minimal'
    | 'features'
    | 'faq'
    | 'menu'
    | 'collections'
    | 'products';

/** Job-based grouping shown in the Presets tab. */
export type PresetGroup = 'heroes' | 'content' | 'commerce';

export interface BlockPreset {
    key: string;
    label: string;
    description: string;
    group: PresetGroup;
    preview: PresetPreview;
    /** @deprecated Prefer `group`. Kept for older callers. */
    category?: 'layout' | 'sections' | 'content';
    blocks: PresetBlockNode[];
}

export const PRESET_GROUP_ORDER: PresetGroup[] = [
    'heroes',
    'content',
    'commerce',
];

export const PRESET_GROUP_LABELS: Record<PresetGroup, string> = {
    heroes: 'Heroes & openings',
    content: 'Content sections',
    commerce: 'Commerce',
};

export const groupBlockPresets = (
    presets: BlockPreset[],
): Array<{ key: PresetGroup; label: string; presets: BlockPreset[] }> =>
    PRESET_GROUP_ORDER.map((key) => ({
        key,
        label: PRESET_GROUP_LABELS[key],
        presets: presets.filter((preset) => preset.group === key),
    })).filter((group) => group.presets.length > 0);

/**
 * Insertable section presets for the Block library.
 * Hero openings are SectionBlock pattern trees (Change layout preserves content).
 * Near-duplicate "Hero with CTA" was removed in favor of the split-right pattern.
 */
export const blockPresets: BlockPreset[] = [
    ...heroPatternPresets,
    {
        key: 'features-row',
        label: 'Features grid',
        description: 'Three highlights in a clean row.',
        group: 'content',
        preview: 'features',
        category: 'sections',
        blocks: [
            {
                type: 'LayoutGrid',
                id: '__preset__',
                props: {
                    columns: 3,
                    gap: '1.5rem',
                    padding: 40,
                    backgroundColor: 'transparent',
                },
                children: [
                    {
                        type: 'LayoutColumn',
                        id: '__preset__',
                        props: {
                            padding: 10,
                            backgroundColor: 'transparent',
                        },
                        children: [
                            {
                                type: 'FeatureBlock',
                                id: '__preset__',
                                props: {
                                    title: 'Thoughtful detail',
                                    body: 'A short supporting line that explains the value clearly.',
                                    padding: 20,
                                    backgroundColor: 'transparent',
                                },
                            },
                        ],
                    },
                    {
                        type: 'LayoutColumn',
                        id: '__preset__',
                        props: {
                            padding: 10,
                            backgroundColor: 'transparent',
                        },
                        children: [
                            {
                                type: 'FeatureBlock',
                                id: '__preset__',
                                props: {
                                    title: 'Easy to refine',
                                    body: 'Edit titles and body copy directly on the canvas.',
                                    padding: 20,
                                    backgroundColor: 'transparent',
                                },
                            },
                        ],
                    },
                    {
                        type: 'LayoutColumn',
                        id: '__preset__',
                        props: {
                            padding: 10,
                            backgroundColor: 'transparent',
                        },
                        children: [
                            {
                                type: 'FeatureBlock',
                                id: '__preset__',
                                props: {
                                    title: 'Theme-aware',
                                    body: 'Colors and fonts follow your global theme tokens.',
                                    padding: 20,
                                    backgroundColor: 'transparent',
                                },
                            },
                        ],
                    },
                ],
            },
        ],
    },
    {
        key: 'faq-section',
        label: 'FAQ accordion',
        description: 'Heading plus expandable answers.',
        group: 'content',
        preview: 'faq',
        category: 'sections',
        blocks: [
            {
                type: 'LayoutGrid',
                id: '__preset__',
                props: {
                    columns: 1,
                    gap: '1.5rem',
                    padding: 40,
                    backgroundColor: 'transparent',
                },
                children: [
                    {
                        type: 'AtomicText',
                        id: '__preset__',
                        props: {
                            content: 'Frequently asked questions',
                            fontSize: 'clamp(1.75rem, 4vw, 2.5rem)',
                            color: '--theme-text',
                            fontFamily: 'heading',
                            fontWeight: '700',
                            padding: 0,
                            backgroundColor: 'transparent',
                        },
                    },
                    {
                        type: 'FAQBlock',
                        id: '__preset__',
                        props: {
                            items: [
                                {
                                    question: 'How do I publish my page?',
                                    answer: 'Save your draft, then click Publish to copy it to the live site.',
                                },
                                {
                                    question: 'Can I change colors later?',
                                    answer: 'Yes. Update the Theme workspace and every block inherits the new palette.',
                                },
                                {
                                    question: 'How do I replace images?',
                                    answer: 'Select an image block and choose a new file from the media library.',
                                },
                            ],
                            padding: 20,
                            backgroundColor: 'transparent',
                        },
                    },
                ],
            },
        ],
    },
    {
        key: 'menu-section',
        label: 'Menu section',
        description: 'Grouped dishes with prices, ready to edit.',
        group: 'content',
        preview: 'menu',
        category: 'sections',
        blocks: [
            {
                type: 'MenuBlock',
                id: '__preset__',
                props: {
                    heading: 'Our Menu',
                    subheading: 'Freshly prepared, served with care',
                    columns: 2,
                    items: [
                        {
                            category: 'Starters',
                            name: 'Heirloom Tomato Salad',
                            description: 'Basil, aged balsamic, sea salt.',
                            price: '$12',
                        },
                        {
                            category: 'Starters',
                            name: 'Charred Sourdough',
                            description: 'Cultured butter, rosemary.',
                            price: '$8',
                        },
                        {
                            category: 'Mains',
                            name: 'Wood-Fired Ribeye',
                            description: 'Roasted garlic, seasonal greens.',
                            price: '$34',
                        },
                        {
                            category: 'Mains',
                            name: 'Handmade Pappardelle',
                            description: 'Slow-braised ragù, pecorino.',
                            price: '$24',
                        },
                        {
                            category: 'Desserts',
                            name: 'Dark Chocolate Tart',
                            description: 'Sea salt, crème fraîche.',
                            price: '$11',
                        },
                    ],
                    padding: 40,
                    backgroundColor: 'transparent',
                },
            },
        ],
    },
    {
        key: 'collections-row',
        label: 'Collections',
        description: 'Three shoppable collection tiles.',
        group: 'commerce',
        preview: 'collections',
        category: 'sections',
        blocks: [
            {
                type: 'CollectionListBlock',
                id: '__preset__',
                props: {
                    eyebrow: 'Shop by collection',
                    heading: 'Find your everyday favorites',
                    bindingVersion: 1,
                    sourceKey: 'featured',
                    collections: [
                        {
                            title: 'Home',
                            subtitle: 'Objects for considered rooms',
                            imageSrc: '',
                            imageAlt: 'Home collection',
                            url: '/shop',
                        },
                        {
                            title: 'Wear',
                            subtitle: 'Useful pieces made to last',
                            imageSrc: '',
                            imageAlt: 'Wear collection',
                            url: '/shop',
                        },
                        {
                            title: 'Gift',
                            subtitle: 'Thoughtful finds for giving',
                            imageSrc: '',
                            imageAlt: 'Gift collection',
                            url: '/shop',
                        },
                    ],
                    padding: 40,
                    backgroundColor: 'transparent',
                },
            },
        ],
    },
    {
        key: 'products-grid',
        label: 'Product grid',
        description: 'A four-up product edit for the shop.',
        group: 'commerce',
        preview: 'products',
        category: 'sections',
        blocks: [
            {
                type: 'ProductGridBlock',
                id: '__preset__',
                props: {
                    eyebrow: 'Curated selection',
                    heading: 'The current edit',
                    bindingVersion: 1,
                    sourceKey: 'featured',
                    limit: 4,
                    columns: 4,
                    viewAllLabel: 'View all',
                    viewAllUrl: '/shop',
                    products: [
                        {
                            key: 'linen-throw',
                            title: 'Linen throw',
                            priceLabel: '$48.00',
                            compareAtLabel: '',
                            badge: 'New',
                            imageSrc: '',
                            imageAlt: 'Linen throw',
                            url: '/product',
                        },
                        {
                            key: 'stoneware-cup',
                            title: 'Stoneware cup',
                            priceLabel: '$24.00',
                            compareAtLabel: '',
                            badge: '',
                            imageSrc: '',
                            imageAlt: 'Stoneware cup',
                            url: '/product',
                        },
                        {
                            key: 'canvas-tote',
                            title: 'Canvas tote',
                            priceLabel: '$36.00',
                            compareAtLabel: '',
                            badge: '',
                            imageSrc: '',
                            imageAlt: 'Canvas tote',
                            url: '/product',
                        },
                        {
                            key: 'cedar-candle',
                            title: 'Cedar candle',
                            priceLabel: '$32.00',
                            compareAtLabel: '$40.00',
                            badge: 'Sale',
                            imageSrc: '',
                            imageAlt: 'Cedar candle',
                            url: '/product',
                        },
                    ],
                    padding: 40,
                    backgroundColor: 'transparent',
                },
            },
        ],
    },
];
