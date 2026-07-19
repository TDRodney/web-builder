import {
    Box,
    CircleQuestionMark,
    Columns2,
    DollarSign,
    FileText,
    GalleryHorizontal,
    Image,
    LayoutGrid,
    LayoutTemplate,
    Link,
    Mail,
    Megaphone,
    MessageSquare,
    Minus,
    MoveVertical,
    Package,
    PanelLeft,
    Send,
    ShieldCheck,
    ShoppingBag,
    ShoppingCart,
    Star,
    Type,
    Utensils,
    Video,
} from '@lucide/vue';
import type { Component } from 'vue';

/**
 * Maps the `icon` identifiers declared in config/blocks.php to Lucide
 * components so panels (layers tree, block library) render consistent icons.
 */
const blockIconMap: Record<string, Component> = {
    layout: LayoutTemplate,
    star: Star,
    type: Type,
    grid: LayoutGrid,
    columns: Columns2,
    link: Link,
    minus: Minus,
    vertical: MoveVertical,
    image: Image,
    'file-text': FileText,
    video: Video,
    'help-circle': CircleQuestionMark,
    'message-square': MessageSquare,
    'dollar-sign': DollarSign,
    mail: Mail,
    megaphone: Megaphone,
    'panel-left': PanelLeft,
    'gallery-horizontal': GalleryHorizontal,
    'shopping-bag': ShoppingBag,
    'shopping-cart': ShoppingCart,
    package: Package,
    send: Send,
    'shield-check': ShieldCheck,
    utensils: Utensils,
};

export const iconForBlock = (icon?: string | null): Component =>
    (icon && blockIconMap[icon]) || Box;
