export interface PresetBlockNode {
  type: string;
  props: Record<string, any>;
  children?: PresetBlockNode[];
  id?: string;
}

export interface BlockPreset {
  key: string;
  label: string;
  description: string;
  category: 'layout' | 'sections' | 'content';
  blocks: PresetBlockNode[];
}

export const blockPresets: BlockPreset[] = [
  {
    key: 'hero-cta',
    label: 'Hero with CTA',
    description: 'Split section with headline, CTA button, and hero image',
    category: 'sections',
    blocks: [
      {
        type: 'LayoutGrid',
        id: '__preset__',
        props: {
          columns: 2,
          gap: '2rem',
          padding: 60,
          backgroundColor: 'transparent',
        },
        children: [
          {
            type: 'LayoutColumn',
            id: '__preset__',
            props: {
              padding: 20,
              backgroundColor: 'transparent',
              span: 'auto',
              width: 'auto',
              height: 'auto',
              gap: '0px',
            },
            children: [
              {
                type: 'HeroBlock',
                id: '__preset__',
                props: {
                  headline: 'Build Beautiful Storefronts Instantly',
                  subheadline: 'Launch your project with our high-converting theme engine and simple controls.',
                  padding: 20,
                  backgroundColor: 'transparent',
                },
              },
              {
                type: 'ButtonBlock',
                id: '__preset__',
                props: {
                  label: 'Get Started Now',
                  variant: 'primary',
                  size: 'md',
                  url: '#',
                },
              },
            ],
          },
          {
            type: 'LayoutColumn',
            id: '__preset__',
            props: {
              padding: 20,
              backgroundColor: 'transparent',
              span: 'auto',
              width: 'auto',
              height: 'auto',
              gap: '0px',
            },
            children: [
              {
                type: 'ImageBlock',
                id: '__preset__',
                props: {
                  src: '',
                  alt: 'Hero illustration',
                  objectFit: 'cover',
                  borderRadius: 'var(--theme-border-radius)',
                  width: '100%',
                  height: '350px',
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
    key: 'features-row',
    label: 'Features Grid',
    description: 'Three columns displaying key features or services',
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
            props: { padding: 10, backgroundColor: 'transparent' },
            children: [
              {
                type: 'FeatureBlock',
                id: '__preset__',
                props: {
                  title: 'Lightning Fast Page Speeds',
                  body: 'Fully optimized code delivery ensures instant response times and higher SEO scores.',
                  padding: 20,
                  backgroundColor: 'transparent',
                },
              },
            ],
          },
          {
            type: 'LayoutColumn',
            id: '__preset__',
            props: { padding: 10, backgroundColor: 'transparent' },
            children: [
              {
                type: 'FeatureBlock',
                id: '__preset__',
                props: {
                  title: 'Custom CSS variable Cascade',
                  body: 'Change global colors or fonts and watch them automatically propagate to every block.',
                  padding: 20,
                  backgroundColor: 'transparent',
                },
              },
            ],
          },
          {
            type: 'LayoutColumn',
            id: '__preset__',
            props: { padding: 10, backgroundColor: 'transparent' },
            children: [
              {
                type: 'FeatureBlock',
                id: '__preset__',
                props: {
                  title: 'Responsive Layout Builders',
                  body: 'Modify preview width settings to inspect and preview tablet and mobile device views.',
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
    label: 'FAQ Accordion Row',
    description: 'Headline text followed by an accordion question cards list',
    category: 'sections',
    blocks: [
      {
        type: 'LayoutGrid',
        id: '__preset__',
        props: {
          columns: 1,
          gap: '2rem',
          padding: 40,
          backgroundColor: 'transparent',
        },
        children: [
          {
            type: 'HeroBlock',
            id: '__preset__',
            props: {
              headline: 'Frequently Asked Questions',
              subheadline: 'Find quick answers to common questions about our platform and services.',
              padding: 20,
              backgroundColor: 'transparent',
            },
          },
          {
            type: 'FAQBlock',
            id: '__preset__',
            props: {
              items: [
                { question: 'How do I export my layout?', answer: 'Simply click Publish Draft to copy configuration configurations instantly to the public site.' },
                { question: 'Can I use custom hex colors?', answer: 'Yes! Global palettes offer quick defaults, but the color pickers let you customize values completely.' },
                { question: 'Are assets optimized?', answer: 'Every uploaded image is parsed, resized, and optimized to 150px thumbnails automatically.' },
              ],
              padding: 20,
              backgroundColor: 'transparent',
            },
          },
        ],
      },
    ],
  },
];
