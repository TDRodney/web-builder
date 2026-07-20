import type { BlockPreset, PresetBlockNode } from '@/lib/blockPresets';

export type SectionPatternKey =
    | 'hero-centered'
    | 'hero-split-right'
    | 'hero-split-left'
    | 'hero-editorial'
    | 'hero-minimal'
    | 'hero-stats';

export interface SectionPattern {
    key: SectionPatternKey;
    label: string;
    description: string;
    node: PresetBlockNode;
}

const textNode = (
    role: string,
    content: string,
    options: Record<string, unknown> = {},
): PresetBlockNode => ({
    id: `__${role}__`,
    type: 'AtomicText',
    props: {
        patternRole: role,
        content,
        padding: 0,
        backgroundColor: 'transparent',
        fontSize: '18px',
        color: '--theme-text',
        fontFamily: 'body',
        fontWeight: '400',
        lineHeight: '1.5',
        letterSpacing: '0em',
        textAlign: 'inherit',
        maxWidth: 'none',
        ...options,
    },
    children: [],
});

const buttonNode = (
    role: string,
    label: string,
    variant: 'primary' | 'outline' = 'primary',
    alignment: 'start' | 'center' | 'end' = 'start',
): PresetBlockNode => ({
    id: `__${role}__`,
    type: 'ButtonBlock',
    props: {
        patternRole: role,
        label,
        variant,
        url: '#',
        size: 'md',
        alignment,
        padding: 0,
        backgroundColor: 'transparent',
    },
    children: [],
});

const imageNode = (role: string, alt: string): PresetBlockNode => ({
    id: `__${role}__`,
    type: 'ImageBlock',
    props: {
        patternRole: role,
        src: '',
        alt,
        objectFit: 'cover',
        borderRadius: 'var(--theme-border-radius)',
        width: '100%',
        height: 'clamp(220px, 36cqw, 400px)',
        padding: 0,
        backgroundColor: 'transparent',
    },
    children: [],
});

const columnNode = (
    id: string,
    children: PresetBlockNode[],
    options: Record<string, unknown> = {},
): PresetBlockNode => ({
    id,
    type: 'LayoutColumn',
    props: {
        padding: 0,
        backgroundColor: 'transparent',
        span: 'auto',
        width: 'auto',
        height: 'auto',
        gap: '18px',
        verticalAlign: 'center',
        horizontalAlign: 'stretch',
        ...options,
    },
    children,
});

/**
 * CTAs stack vertically inside the copy column. A 2-column button grid
 * collapses into mid-word wrapping whenever the split copy track is
 * narrower than ~280px (the common case for image-left / wide-image layouts).
 */
const actionsNode = (
    id: string,
    alignment: 'start' | 'center' = 'start',
): PresetBlockNode => ({
    id,
    type: 'LayoutColumn',
    props: {
        padding: 0,
        backgroundColor: 'transparent',
        span: 'auto',
        width: '100%',
        height: 'auto',
        gap: '10px',
        verticalAlign: 'start',
        horizontalAlign: alignment === 'center' ? 'center' : 'start',
    },
    children: [
        buttonNode('primaryAction', 'Explore', 'primary', alignment),
        buttonNode('secondaryAction', 'Learn more', 'outline', alignment),
    ],
});

const statNode = (
    role: string,
    value: string,
    label: string,
): PresetBlockNode =>
    columnNode(
        `__${role}-stat__`,
        [
            textNode(`${role}Value`, value, {
                fontSize: 'clamp(1.6rem, 3.2cqw, 2.5rem)',
                fontFamily: 'heading',
                fontWeight: '700',
                lineHeight: '1.05',
                letterSpacing: '-0.02em',
                color: '--theme-primary',
            }),
            textNode(`${role}Label`, label, {
                fontSize: '14px',
                lineHeight: '1.4',
            }),
        ],
        { gap: '4px', verticalAlign: 'start' },
    );

const heroCopy = (alignment: 'left' | 'center' = 'left') => [
    textNode('eyebrow', 'A thoughtful introduction', {
        fontSize: '13px',
        fontWeight: '700',
        letterSpacing: '0.12em',
        textAlign: alignment,
        maxWidth: alignment === 'center' ? '60ch' : '42rem',
    }),
    textNode('heading', 'A clear statement that earns attention', {
        fontSize: 'clamp(2rem, 5cqw, 4rem)',
        fontFamily: 'heading',
        fontWeight: '700',
        lineHeight: '1.05',
        letterSpacing: '-0.035em',
        textAlign: alignment,
        maxWidth: alignment === 'center' ? '16ch' : '18ch',
    }),
    textNode(
        'body',
        'Support the headline with a concise description that gives visitors a reason to continue.',
        {
            fontSize: '17px',
            lineHeight: '1.55',
            textAlign: alignment,
            maxWidth: alignment === 'center' ? '42ch' : '36ch',
        },
    ),
];

const sectionNode = (
    patternKey: SectionPatternKey,
    children: PresetBlockNode[],
    options: Record<string, unknown> = {},
): PresetBlockNode => ({
    id: `__${patternKey}__`,
    type: 'SectionBlock',
    props: {
        patternKey,
        padding: 0,
        sectionPadding: 72,
        backgroundColor: 'transparent',
        backgroundImage: '',
        contentWidth: 1180,
        minHeight: 560,
        verticalAlign: 'center',
        textAlign: 'left',
        overlayOpacity: 0,
        ...options,
    },
    children,
});

export const sectionPatterns: SectionPattern[] = [
    {
        key: 'hero-centered',
        label: 'Centered statement',
        description: 'Focused copy with two actions and generous space.',
        node: sectionNode(
            'hero-centered',
            [
                columnNode(
                    '__centered-copy__',
                    [
                        ...heroCopy('center'),
                        actionsNode('__centered-actions__', 'center'),
                    ],
                    { horizontalAlign: 'center' },
                ),
            ],
            { textAlign: 'center', contentWidth: 980 },
        ),
    },
    {
        key: 'hero-split-right',
        label: 'Copy left, image right',
        description: 'A conversion-focused split with editable media.',
        node: sectionNode('hero-split-right', [
            {
                id: '__split-right-grid__',
                type: 'LayoutGrid',
                props: {
                    padding: 0,
                    backgroundColor: 'transparent',
                    columns: 2,
                    gap: 'clamp(1.5rem, 4cqw, 3.5rem)',
                    columnTemplate: 'equal',
                    alignItems: 'center',
                },
                children: [
                    columnNode('__split-right-copy__', [
                        ...heroCopy(),
                        actionsNode('__split-right-actions__'),
                    ]),
                    columnNode('__split-right-media__', [
                        imageNode(
                            'media',
                            'Replace with a strong hero image from your media library',
                        ),
                    ]),
                ],
            },
        ]),
    },
    {
        key: 'hero-split-left',
        label: 'Image left, copy right',
        description: 'A visual-first split for places, products, and people.',
        node: sectionNode('hero-split-left', [
            {
                id: '__split-left-grid__',
                type: 'LayoutGrid',
                props: {
                    padding: 0,
                    backgroundColor: 'transparent',
                    columns: 2,
                    gap: 'clamp(1.5rem, 4cqw, 3.5rem)',
                    columnTemplate: 'equal',
                    alignItems: 'center',
                },
                children: [
                    columnNode('__split-left-media__', [
                        imageNode(
                            'media',
                            'Replace with an expressive image from your media library',
                        ),
                    ]),
                    columnNode('__split-left-copy__', [
                        ...heroCopy(),
                        actionsNode('__split-left-actions__'),
                    ]),
                ],
            },
        ]),
    },
    {
        key: 'hero-editorial',
        label: 'Editorial composition',
        description:
            'Oversized typography paired with supporting copy and media.',
        node: sectionNode(
            'hero-editorial',
            [
                columnNode('__editorial-stack__', [
                    textNode('eyebrow', 'Journal / Collection / Season', {
                        fontSize: '12px',
                        fontWeight: '700',
                        letterSpacing: '0.16em',
                    }),
                    textNode(
                        'heading',
                        'Make the first screen unmistakably yours',
                        {
                            fontSize: 'clamp(2.25rem, 6cqw, 4.75rem)',
                            fontFamily: 'heading',
                            fontWeight: '700',
                            lineHeight: '0.95',
                            letterSpacing: '-0.04em',
                            maxWidth: '14ch',
                        },
                    ),
                    {
                        id: '__editorial-lower__',
                        type: 'LayoutGrid',
                        props: {
                            padding: 0,
                            backgroundColor: 'transparent',
                            columns: 2,
                            gap: 'clamp(1.5rem, 4cqw, 3.5rem)',
                            columnTemplate: 'equal',
                            alignItems: 'end',
                        },
                        children: [
                            columnNode('__editorial-copy__', [
                                textNode(
                                    'body',
                                    'Use a bolder editorial rhythm when the brand itself is the story.',
                                    { fontSize: '17px', lineHeight: '1.65' },
                                ),
                                buttonNode(
                                    'primaryAction',
                                    'Discover the story',
                                    'primary',
                                ),
                            ]),
                            columnNode('__editorial-media__', [
                                imageNode(
                                    'media',
                                    'Replace with an editorial campaign image',
                                ),
                            ]),
                        ],
                    },
                ]),
            ],
            { minHeight: 700, verticalAlign: 'start' },
        ),
    },
    {
        key: 'hero-minimal',
        label: 'Minimal introduction',
        description: 'Compact, elegant copy with one clear action.',
        node: sectionNode(
            'hero-minimal',
            [
                columnNode('__minimal-copy__', [
                    textNode('eyebrow', 'Welcome', {
                        fontSize: '12px',
                        fontWeight: '700',
                        letterSpacing: '0.14em',
                    }),
                    textNode('heading', 'Simple, direct, memorable', {
                        fontSize: 'clamp(1.85rem, 4.5cqw, 3.5rem)',
                        fontFamily: 'heading',
                        fontWeight: '700',
                        lineHeight: '1.05',
                        letterSpacing: '-0.035em',
                        maxWidth: '16ch',
                    }),
                    textNode(
                        'body',
                        'A restrained introduction for secondary pages and focused campaigns.',
                        { maxWidth: '48ch' },
                    ),
                    buttonNode('primaryAction', 'Continue', 'primary'),
                ]),
            ],
            { minHeight: 420, contentWidth: 1040 },
        ),
    },
    {
        key: 'hero-stats',
        label: 'Statement with stats',
        description: 'Copy and actions beside three proof-point callouts.',
        node: sectionNode('hero-stats', [
            {
                id: '__stats-grid__',
                type: 'LayoutGrid',
                props: {
                    padding: 0,
                    backgroundColor: 'transparent',
                    columns: 2,
                    gap: 'clamp(1.5rem, 4cqw, 3.5rem)',
                    columnTemplate: 'equal',
                    alignItems: 'center',
                },
                children: [
                    columnNode('__stats-copy__', [
                        ...heroCopy(),
                        actionsNode('__stats-actions__'),
                    ]),
                    columnNode(
                        '__stats-callouts__',
                        [
                            statNode('statOne', '10k+', 'Happy customers'),
                            statNode('statTwo', '4.9', 'Average rating'),
                            statNode('statThree', '24/7', 'Support on hand'),
                        ],
                        { gap: '14px', verticalAlign: 'center' },
                    ),
                ],
            },
        ]),
    },
];

const contentKeysByType: Record<string, string[]> = {
    AtomicText: ['content'],
    ButtonBlock: ['label', 'url'],
    ImageBlock: ['src', 'alt'],
};

const collectRoleContent = (
    node: PresetBlockNode,
    roles = new Map<string, Record<string, unknown>>(),
): Map<string, Record<string, unknown>> => {
    const role = node.props?.patternRole;
    const contentKeys = contentKeysByType[node.type] ?? [];

    if (typeof role === 'string' && contentKeys.length) {
        roles.set(
            role,
            Object.fromEntries(
                contentKeys
                    .filter((key) => node.props[key] !== undefined)
                    .map((key) => [key, node.props[key]]),
            ),
        );
    }

    node.children?.forEach((child) => collectRoleContent(child, roles));

    return roles;
};

const applyRoleContent = (
    node: PresetBlockNode,
    roles: Map<string, Record<string, unknown>>,
): void => {
    const role = node.props?.patternRole;

    if (typeof role === 'string' && roles.has(role)) {
        node.props = { ...node.props, ...roles.get(role) };
    }

    node.children?.forEach((child) => applyRoleContent(child, roles));
};

const assignNewIds = (node: PresetBlockNode): PresetBlockNode => {
    const copy = JSON.parse(JSON.stringify(node)) as PresetBlockNode;
    copy.id = `${copy.type.toLowerCase()}-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
    copy.children = copy.children?.map(assignNewIds) ?? [];

    return copy;
};

export const getSectionPattern = (key: string): SectionPattern | undefined =>
    sectionPatterns.find((pattern) => pattern.key === key);

export const createSectionFromPattern = (
    key: string,
): PresetBlockNode | null => {
    const pattern = getSectionPattern(key);

    return pattern ? assignNewIds(pattern.node) : null;
};

export const changeSectionPattern = (
    section: PresetBlockNode,
    key: string,
): boolean => {
    const nextSection = createSectionFromPattern(key);

    if (!nextSection) {
        return false;
    }

    const roleContent = collectRoleContent(section);
    applyRoleContent(nextSection, roleContent);
    nextSection.id = section.id;
    section.props = nextSection.props;
    section.children = nextSection.children;

    return true;
};

const heroPreviewByKey: Record<SectionPatternKey, BlockPreset['preview']> = {
    'hero-centered': 'centered',
    'hero-split-right': 'split-right',
    'hero-split-left': 'split-left',
    'hero-editorial': 'editorial',
    'hero-minimal': 'minimal',
    'hero-stats': 'stats',
};

export const heroPatternPresets: BlockPreset[] = sectionPatterns.map(
    (pattern) => ({
        key: pattern.key,
        label: pattern.label,
        description: pattern.description,
        group: 'heroes',
        preview: heroPreviewByKey[pattern.key],
        category: 'sections',
        blocks: [pattern.node],
    }),
);
