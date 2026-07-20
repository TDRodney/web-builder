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
    | 'products'
    | 'stats'
    | 'logos'
    | 'testimonials'
    | 'promos'
    | 'menu-cards';

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
                            fontSize: 'clamp(1.5rem, 3.5cqw, 2.25rem)',
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
        key: 'menu-cards',
        label: 'Menu photo cards',
        description: 'Dish photos with names and prices in a row.',
        group: 'content',
        preview: 'menu-cards',
        category: 'sections',
        blocks: [
            {
                type: 'LayoutGrid',
                id: '__preset__',
                props: {
                    columns: 1,
                    gap: '1.75rem',
                    padding: 40,
                    backgroundColor: 'transparent',
                },
                children: [
                    {
                        type: 'AtomicText',
                        id: '__preset__',
                        props: {
                            content: 'Explore our cuisine',
                            fontSize: 'clamp(1.5rem, 3.5cqw, 2.25rem)',
                            color: '--theme-text',
                            fontFamily: 'heading',
                            fontWeight: '700',
                            textAlign: 'center',
                            padding: 0,
                            backgroundColor: 'transparent',
                        },
                    },
                    {
                        type: 'LayoutGrid',
                        id: '__preset__',
                        props: {
                            columns: 3,
                            gap: '1.5rem',
                            padding: 0,
                            backgroundColor: 'transparent',
                            alignItems: 'stretch',
                        },
                        children: [
                            ['Signature grilled chicken', '$14', '15 mins'],
                            ['Margherita pizza', '$12', '45 mins'],
                            ['Shrimp scampi pasta', '$14', '30 mins'],
                        ].map(([name, price, meta]) => ({
                            type: 'LayoutColumn',
                            id: '__preset__',
                            props: {
                                padding: 0,
                                backgroundColor: 'transparent',
                                gap: '8px',
                                verticalAlign: 'start',
                                horizontalAlign: 'stretch',
                            },
                            children: [
                                {
                                    type: 'ImageBlock',
                                    id: '__preset__',
                                    props: {
                                        src: '',
                                        alt: `Replace with a photo of ${name.toLowerCase()}`,
                                        objectFit: 'cover',
                                        borderRadius:
                                            'var(--theme-border-radius)',
                                        width: '100%',
                                        height: '220px',
                                        padding: 0,
                                        backgroundColor: 'transparent',
                                    },
                                },
                                {
                                    type: 'LayoutGrid',
                                    id: '__preset__',
                                    props: {
                                        columns: 2,
                                        gap: '12px',
                                        padding: 0,
                                        backgroundColor: 'transparent',
                                        alignItems: 'center',
                                        stackOnNarrow: false,
                                    },
                                    children: [
                                        {
                                            type: 'AtomicText',
                                            id: '__preset__',
                                            props: {
                                                content: name,
                                                fontSize: '17px',
                                                color: '--theme-text',
                                                fontFamily: 'heading',
                                                fontWeight: '700',
                                                padding: 0,
                                                backgroundColor: 'transparent',
                                            },
                                        },
                                        {
                                            type: 'AtomicText',
                                            id: '__preset__',
                                            props: {
                                                content: price,
                                                fontSize: '17px',
                                                color: '--theme-primary',
                                                fontFamily: 'heading',
                                                fontWeight: '700',
                                                textAlign: 'right',
                                                padding: 0,
                                                backgroundColor: 'transparent',
                                            },
                                        },
                                    ],
                                },
                                {
                                    type: 'AtomicText',
                                    id: '__preset__',
                                    props: {
                                        content: meta,
                                        fontSize: '13px',
                                        color: '--theme-text',
                                        fontFamily: 'body',
                                        fontWeight: '400',
                                        padding: 0,
                                        backgroundColor: 'transparent',
                                    },
                                },
                            ],
                        })),
                    },
                ],
            },
        ],
    },
    {
        key: 'logo-strip',
        label: 'Logo strip',
        description: 'A row of partner or press logos.',
        group: 'content',
        preview: 'logos',
        category: 'sections',
        blocks: [
            {
                type: 'LayoutGrid',
                id: '__preset__',
                props: {
                    columns: 1,
                    gap: '1.75rem',
                    padding: 40,
                    backgroundColor: 'transparent',
                },
                children: [
                    {
                        type: 'AtomicText',
                        id: '__preset__',
                        props: {
                            content: 'Trusted by great brands',
                            fontSize: '13px',
                            color: '--theme-text',
                            fontFamily: 'body',
                            fontWeight: '700',
                            letterSpacing: '0.14em',
                            textAlign: 'center',
                            padding: 0,
                            backgroundColor: 'transparent',
                        },
                    },
                    {
                        type: 'LayoutGrid',
                        id: '__preset__',
                        props: {
                            columns: 6,
                            gap: '1.5rem',
                            padding: 0,
                            backgroundColor: 'transparent',
                            alignItems: 'center',
                        },
                        children: Array.from({ length: 6 }, (_, index) => ({
                            type: 'ImageBlock',
                            id: '__preset__',
                            props: {
                                src: '',
                                alt: `Brand logo ${index + 1} — replace from your media library`,
                                objectFit: 'contain',
                                borderRadius: '0px',
                                width: '100%',
                                height: '48px',
                                padding: 0,
                                backgroundColor: 'transparent',
                            },
                        })),
                    },
                ],
            },
        ],
    },
    {
        key: 'testimonials-row',
        label: 'Testimonials',
        description: 'Three customer quotes side by side.',
        group: 'content',
        preview: 'testimonials',
        category: 'sections',
        blocks: [
            {
                type: 'LayoutGrid',
                id: '__preset__',
                props: {
                    columns: 1,
                    gap: '1.75rem',
                    padding: 40,
                    backgroundColor: 'transparent',
                },
                children: [
                    {
                        type: 'AtomicText',
                        id: '__preset__',
                        props: {
                            content: 'Loved by our customers',
                            fontSize: 'clamp(1.5rem, 3.5cqw, 2.25rem)',
                            color: '--theme-text',
                            fontFamily: 'heading',
                            fontWeight: '700',
                            textAlign: 'center',
                            padding: 0,
                            backgroundColor: 'transparent',
                        },
                    },
                    {
                        type: 'LayoutGrid',
                        id: '__preset__',
                        props: {
                            columns: 3,
                            gap: '1.5rem',
                            padding: 0,
                            backgroundColor: 'transparent',
                            alignItems: 'stretch',
                        },
                        children: [
                            {
                                type: 'TestimonialBlock',
                                id: '__preset__',
                                props: {
                                    quote: 'Beautifully made and incredibly practical. Every detail feels thoughtful.',
                                    authorName: 'Olivia R.',
                                    authorRole: 'Verified customer',
                                    avatarSrc: '',
                                    padding: 10,
                                    backgroundColor: 'transparent',
                                },
                            },
                            {
                                type: 'TestimonialBlock',
                                id: '__preset__',
                                props: {
                                    quote: 'Exactly what I was looking for. The quality has held up wonderfully.',
                                    authorName: 'Daniel M.',
                                    authorRole: 'Verified customer',
                                    avatarSrc: '',
                                    padding: 10,
                                    backgroundColor: 'transparent',
                                },
                            },
                            {
                                type: 'TestimonialBlock',
                                id: '__preset__',
                                props: {
                                    quote: 'Fast, friendly service and a product I recommend to everyone.',
                                    authorName: 'Amara K.',
                                    authorRole: 'Verified customer',
                                    avatarSrc: '',
                                    padding: 10,
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
        key: 'promo-tiles',
        label: 'Promo tiles',
        description: 'Two bold offer tiles with calls to action.',
        group: 'commerce',
        preview: 'promos',
        category: 'sections',
        blocks: [
            {
                type: 'LayoutGrid',
                id: '__preset__',
                props: {
                    columns: 2,
                    gap: '1.5rem',
                    padding: 40,
                    backgroundColor: 'transparent',
                    alignItems: 'stretch',
                },
                children: [
                    {
                        type: 'LayoutColumn',
                        id: '__preset__',
                        props: {
                            padding: 36,
                            backgroundColor: 'var(--theme-primary)',
                            gap: '10px',
                            verticalAlign: 'center',
                            horizontalAlign: 'start',
                        },
                        children: [
                            {
                                type: 'AtomicText',
                                id: '__preset__',
                                props: {
                                    content: 'Limited time',
                                    fontSize: '12px',
                                    color: '#ffffff',
                                    fontFamily: 'body',
                                    fontWeight: '700',
                                    letterSpacing: '0.14em',
                                    padding: 0,
                                    backgroundColor: 'transparent',
                                },
                            },
                            {
                                type: 'AtomicText',
                                id: '__preset__',
                                props: {
                                    content: 'Season sale — up to 15% off',
                                    fontSize: 'clamp(1.4rem, 3cqw, 2.1rem)',
                                    color: '#ffffff',
                                    fontFamily: 'heading',
                                    fontWeight: '700',
                                    lineHeight: '1.1',
                                    padding: 0,
                                    backgroundColor: 'transparent',
                                },
                            },
                            {
                                type: 'ButtonBlock',
                                id: '__preset__',
                                props: {
                                    label: 'Shop the sale',
                                    variant: 'secondary',
                                    url: '/shop',
                                    size: 'md',
                                    alignment: 'start',
                                    padding: 0,
                                    backgroundColor: 'transparent',
                                },
                            },
                        ],
                    },
                    {
                        type: 'LayoutColumn',
                        id: '__preset__',
                        props: {
                            padding: 36,
                            backgroundColor: 'var(--theme-secondary)',
                            gap: '10px',
                            verticalAlign: 'center',
                            horizontalAlign: 'start',
                        },
                        children: [
                            {
                                type: 'AtomicText',
                                id: '__preset__',
                                props: {
                                    content: 'New arrivals',
                                    fontSize: '12px',
                                    color: '#ffffff',
                                    fontFamily: 'body',
                                    fontWeight: '700',
                                    letterSpacing: '0.14em',
                                    padding: 0,
                                    backgroundColor: 'transparent',
                                },
                            },
                            {
                                type: 'AtomicText',
                                id: '__preset__',
                                props: {
                                    content: 'Fresh picks for the season',
                                    fontSize: 'clamp(1.4rem, 3cqw, 2.1rem)',
                                    color: '#ffffff',
                                    fontFamily: 'heading',
                                    fontWeight: '700',
                                    lineHeight: '1.1',
                                    padding: 0,
                                    backgroundColor: 'transparent',
                                },
                            },
                            {
                                type: 'ButtonBlock',
                                id: '__preset__',
                                props: {
                                    label: 'Browse new in',
                                    variant: 'primary',
                                    url: '/shop',
                                    size: 'md',
                                    alignment: 'start',
                                    padding: 0,
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
