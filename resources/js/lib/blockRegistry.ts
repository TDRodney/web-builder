import AtomicText from '@/components/BuilderBlocks/AtomicText.vue';
import FeatureBlock from '@/components/BuilderBlocks/FeatureBlock.vue';
import HeroBlock from '@/components/BuilderBlocks/HeroBlock.vue';
import LayoutColumn from '@/components/BuilderBlocks/LayoutColumn.vue';
import LayoutGrid from '@/components/BuilderBlocks/LayoutGrid.vue';

export const blockComponents: Record<string, any> = {
  HeroBlock,
  FeatureBlock,
  LayoutGrid,
  LayoutColumn,
  AtomicText
};

export interface InspectorField {
  key: string;
  label: string;
  type: 'text' | 'number' | 'color' | 'range';
  min?: number;
  max?: number;
  placeholder?: string;
}

export interface BlockDefinition {
  type: string;
  label: string;
  category: 'content' | 'layout' | 'media' | 'interactive';
  icon: string;
  defaultProps: Record<string, any>;
  inspectorFields: InspectorField[];
  allowedChildren?: string[];
}

export const blockDefinitions: BlockDefinition[] = [
  {
    type: 'HeroBlock',
    label: 'Hero Section',
    category: 'content',
    icon: 'layout',
    defaultProps: {
      padding: 40,
      backgroundColor: '#ffffff',
      headline: 'Welcome to your Site',
      subheadline: 'Built with our engine.'
    },
    inspectorFields: [
      { key: 'padding', label: 'Padding (px)', type: 'range', min: 10, max: 150 },
      { key: 'backgroundColor', label: 'Background Color', type: 'color' },
      { key: 'headline', label: 'Headline Text', type: 'text', placeholder: 'Enter headline' },
      { key: 'subheadline', label: 'Subheadline Text', type: 'text', placeholder: 'Enter subheadline' }
    ]
  },
  {
    type: 'FeatureBlock',
    label: 'Feature Block',
    category: 'content',
    icon: 'star',
    defaultProps: {
      padding: 20,
      backgroundColor: '#f8fafc',
      title: 'Feature Item',
      body: 'Feature description details go here.'
    },
    inspectorFields: [
      { key: 'padding', label: 'Padding (px)', type: 'range', min: 10, max: 150 },
      { key: 'backgroundColor', label: 'Background Color', type: 'color' },
      { key: 'title', label: 'Feature Title', type: 'text', placeholder: 'Enter title' },
      { key: 'body', label: 'Feature Body', type: 'text', placeholder: 'Enter description' }
    ]
  },
  {
    type: 'AtomicText',
    label: 'Atomic Text',
    category: 'content',
    icon: 'type',
    defaultProps: {
      padding: 20,
      backgroundColor: '#ffffff',
      content: 'Atomic Text Element',
      fontSize: '16px',
      color: '#0f172a'
    },
    inspectorFields: [
      { key: 'padding', label: 'Padding (px)', type: 'range', min: 10, max: 150 },
      { key: 'backgroundColor', label: 'Background Color', type: 'color' },
      { key: 'content', label: 'Text Content', type: 'text', placeholder: 'Enter text' },
      { key: 'fontSize', label: 'Font Size (e.g. 16px, 1.25rem)', type: 'text' },
      { key: 'color', label: 'Text Color', type: 'text' }
    ]
  },
  {
    type: 'LayoutGrid',
    label: 'Layout Grid',
    category: 'layout',
    icon: 'grid',
    defaultProps: {
      padding: 20,
      backgroundColor: 'transparent',
      columns: 3,
      gap: '1rem'
    },
    inspectorFields: [
      { key: 'padding', label: 'Padding (px)', type: 'range', min: 10, max: 150 },
      { key: 'backgroundColor', label: 'Background Color', type: 'color' },
      { key: 'columns', label: 'Columns Count', type: 'number', min: 1, max: 12 },
      { key: 'gap', label: 'Gap Size (e.g. 1rem, 16px)', type: 'text' }
    ],
    allowedChildren: ['LayoutColumn']
  },
  {
    type: 'LayoutColumn',
    label: 'Layout Column',
    category: 'layout',
    icon: 'columns',
    defaultProps: {
      padding: 20,
      backgroundColor: 'transparent',
      span: 'auto',
      width: 'auto',
      height: 'auto',
      gap: '0px'
    },
    inspectorFields: [
      { key: 'padding', label: 'Padding (px)', type: 'range', min: 10, max: 150 },
      { key: 'backgroundColor', label: 'Background Color', type: 'color' },
      { key: 'span', label: 'Grid Column Span (1-12) or Flex Basis', type: 'text' },
      { key: 'width', label: 'Width (e.g. auto, 100%)', type: 'text' },
      { key: 'height', label: 'Height (e.g. auto, 200px)', type: 'text' },
      { key: 'gap', label: 'Gap', type: 'text' }
    ],
    allowedChildren: ['HeroBlock', 'FeatureBlock', 'LayoutGrid', 'LayoutColumn', 'AtomicText']
  }
];

export const getBlockDefinition = (type: string): BlockDefinition | undefined => {
  return blockDefinitions.find(def => def.type === type);
};
