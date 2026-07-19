import { usePage } from '@inertiajs/vue3';
import AnnouncementBlock from '@/components/BuilderBlocks/AnnouncementBlock.vue';
import AtomicText from '@/components/BuilderBlocks/AtomicText.vue';
import ButtonBlock from '@/components/BuilderBlocks/ButtonBlock.vue';
import CartBlock from '@/components/BuilderBlocks/CartBlock.vue';
import CollectionListBlock from '@/components/BuilderBlocks/CollectionListBlock.vue';
import ContactFormBlock from '@/components/BuilderBlocks/ContactFormBlock.vue';
import DividerBlock from '@/components/BuilderBlocks/DividerBlock.vue';
import FAQBlock from '@/components/BuilderBlocks/FAQBlock.vue';
import FeatureBlock from '@/components/BuilderBlocks/FeatureBlock.vue';
import HeroBlock from '@/components/BuilderBlocks/HeroBlock.vue';
import ImageBlock from '@/components/BuilderBlocks/ImageBlock.vue';
import ImageWithTextBlock from '@/components/BuilderBlocks/ImageWithTextBlock.vue';
import LayoutColumn from '@/components/BuilderBlocks/LayoutColumn.vue';
import LayoutGrid from '@/components/BuilderBlocks/LayoutGrid.vue';
import MenuBlock from '@/components/BuilderBlocks/MenuBlock.vue';
import NewsletterBlock from '@/components/BuilderBlocks/NewsletterBlock.vue';
import PricingTableBlock from '@/components/BuilderBlocks/PricingTableBlock.vue';
import ProductDetailBlock from '@/components/BuilderBlocks/ProductDetailBlock.vue';
import ProductGridBlock from '@/components/BuilderBlocks/ProductGridBlock.vue';
import RichTextBlock from '@/components/BuilderBlocks/RichTextBlock.vue';
import SectionBlock from '@/components/BuilderBlocks/SectionBlock.vue';
import SpacerBlock from '@/components/BuilderBlocks/SpacerBlock.vue';
import TestimonialBlock from '@/components/BuilderBlocks/TestimonialBlock.vue';
import TrustValuesBlock from '@/components/BuilderBlocks/TrustValuesBlock.vue';
import VideoEmbedBlock from '@/components/BuilderBlocks/VideoEmbedBlock.vue';

export const blockComponents: Record<string, any> = {
    SectionBlock,
    HeroBlock,
    FeatureBlock,
    LayoutGrid,
    LayoutColumn,
    AtomicText,
    ButtonBlock,
    CartBlock,
    DividerBlock,
    SpacerBlock,
    ImageBlock,
    RichTextBlock,
    VideoEmbedBlock,
    FAQBlock,
    TestimonialBlock,
    PricingTableBlock,
    ContactFormBlock,
    AnnouncementBlock,
    ImageWithTextBlock,
    CollectionListBlock,
    ProductGridBlock,
    ProductDetailBlock,
    NewsletterBlock,
    TrustValuesBlock,
    MenuBlock,
};

export interface InspectorField {
    key: string;
    label: string;
    type:
        | 'text'
        | 'number'
        | 'color'
        | 'theme-color'
        | 'font-size'
        | 'range'
        | 'select'
        | 'media'
        | 'repeater';
    min?: number;
    max?: number;
    placeholder?: string;
    defaultValue?: string;
    customDefault?: string;
    options?: Array<{ label: string; value: string }>;
    subFields?: InspectorField[];
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

interface BlockRegistryPageProps {
    blocksConfig?: {
        definitions?: BlockDefinition[] | Record<string, BlockDefinition>;
    };
    [key: string]: unknown;
}

export const getBlockDefinition = (
    type: string,
): BlockDefinition | undefined => {
    try {
        const page = usePage<BlockRegistryPageProps>();
        const definitions = page.props.blocksConfig?.definitions;

        if (definitions && typeof definitions === 'object') {
            if (Array.isArray(definitions)) {
                return definitions.find((def: any) => def.type === type);
            } else {
                return (definitions as Record<string, BlockDefinition>)[type];
            }
        }
    } catch {}

    return undefined;
};
