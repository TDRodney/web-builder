import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from '@lucide/vue';

export type BreadcrumbItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
};

export type NavItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
};

export type NavbarSurfaceMode = 'design' | 'transparent' | 'theme' | 'custom';

export type NavbarHoverEffect =
    | 'color'
    | 'underline'
    | 'background'
    | 'lift'
    | 'none';

export type NavbarActionAnimation =
    | 'color'
    | 'lift'
    | 'scale'
    | 'arrow'
    | 'none';

export interface NavigationItem {
    id?: string;
    label: string;
    type?: 'internal' | 'external';
    slug?: string;
    href?: string;
    disabled?: boolean;
    megaGroup?: string;
    children?: NavigationItem[];
}

export interface NavigationAction extends NavigationItem {
    variant: 'primary' | 'outline' | 'text';
    icon?: 'none' | 'arrow' | 'bag';
    iconPosition?: 'start' | 'end';
    size?: 'small' | 'medium' | 'large';
    animation?: NavbarActionAnimation;
    backgroundColor?: string;
    textColor?: string;
    hoverBackgroundColor?: string;
    hoverTextColor?: string;
    borderColor?: string;
    borderRadius?: number;
}

export interface MegaMenuGroup {
    id: string;
    label: string;
    description?: string;
}

export interface NavigationHeaderConfig {
    variant?: string;
    menu?: {
        mode?: 'simple' | 'mega';
        triggerLabel?: string;
        openOn?: 'click' | 'hover';
        alignment?: 'left' | 'center' | 'right';
        width?: 'content' | 'wide' | 'full';
        columns?: 2 | 3 | 4;
        animation?: 'fade' | 'slide' | 'scale';
        groups?: MegaMenuGroup[];
        featured?: {
            show?: boolean;
            title?: string;
            description?: string;
            imageUrl?: string;
            href?: string;
        };
    };
    surface?: {
        mode?: NavbarSurfaceMode;
        backgroundColor?: string;
        textColor?: string;
    };
    brand?: {
        type?: 'text' | 'image';
        text?: string;
        imageUrl?: string;
        alt?: string;
        width?: number;
        mobileWidth?: number;
        hideOnMobile?: boolean;
    };
    layout?: {
        contentWidth?: number;
        height?: number;
        horizontalPadding?: number;
        position?: 'static' | 'sticky';
        stickyOffset?: number;
        fullWidth?: boolean;
        borderWidth?: number;
        borderColor?: string;
        borderRadius?: number;
        shadow?: 'none' | 'small' | 'medium' | 'large';
    };
    responsive?: {
        breakpoint?: number;
        menuStyle?: 'dropdown' | 'drawer' | 'fullscreen';
        menuIcon?: 'menu' | 'dots' | 'grid';
        alignment?: 'left' | 'center';
        showActions?: boolean;
    };
    states?: {
        activeColor?: string;
        focusColor?: string;
        disabledOpacity?: number;
        scrolledBackgroundColor?: string;
        scrolledTextColor?: string;
    };
    actionPosition?: 'start' | 'end';
    actionStyle?: {
        mode?: 'theme' | 'custom';
        backgroundColor?: string;
        textColor?: string;
        hoverBackgroundColor?: string;
        hoverTextColor?: string;
    };
    linkStyle?: {
        mode?: 'theme' | 'custom';
        color?: string;
        hoverColor?: string;
        hoverEffect?: NavbarHoverEffect;
    };
    actions?: NavigationAction[];
    showLogo?: boolean;
    items?: NavigationItem[];
    ctaButton?: {
        show?: boolean;
        label?: string;
        slug?: string;
    };
}

export interface NavbarPreset {
    id: string;
    name: string;
    header: NavigationHeaderConfig;
}

export interface FooterLink {
    id?: string;
    label: string;
    type?: 'internal' | 'external';
    slug?: string;
    href?: string;
}

export interface FooterLinkGroup {
    id: string;
    label: string;
    links: FooterLink[];
}

export interface NavigationFooterConfig {
    variant?: 'minimal' | 'columns' | 'newsletter' | 'editorial';
    moduleOrder?: Array<
        'brand' | 'links' | 'newsletter' | 'social' | 'copyright'
    >;
    backgroundMode?: 'theme' | 'contrast' | 'custom';
    backgroundColor?: string;
    textColor?: string;
    contentWidth?: number;
    showLinks?: boolean;
    showSocial?: boolean;
    showCopyright?: boolean;
    brand?: {
        show?: boolean;
        title?: string;
        description?: string;
        imageUrl?: string;
        alt?: string;
    };
    linkGroups?: FooterLinkGroup[];
    newsletter?: {
        show?: boolean;
        eyebrow?: string;
        heading?: string;
        body?: string;
        buttonLabel?: string;
        buttonUrl?: string;
    };
    socialLinks?: Array<{
        id: string;
        label: string;
        href: string;
    }>;
    copyright?: string;
}

export interface NavigationConfig {
    header: NavigationHeaderConfig;
    footer: NavigationFooterConfig;
    presets?: NavbarPreset[];
    commerce?: Record<string, string | number | boolean | null>;
}
