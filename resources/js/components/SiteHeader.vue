<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    ChevronDown,
    Grid2X2,
    Menu,
    MoreHorizontal,
    Search,
    ShoppingBag,
    X,
} from '@lucide/vue';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

import type {
    NavigationAction,
    NavigationConfig,
    NavigationItem,
} from '@/types/navigation';

const props = withDefaults(
    defineProps<{
        navigationConfig?: NavigationConfig;
        pages?: Array<{ slug?: string; title?: string }>;
        tenantName?: string;
        isEditable?: boolean;
        showCart?: boolean;
        cartCount?: number;
        currentPageSlug?: string;
        previewResponsiveMode?: 'auto' | 'desktop' | 'tablet' | 'mobile';
    }>(),
    {
        navigationConfig: () => ({ header: {}, footer: {} }),
        pages: () => [],
        tenantName: 'My Workspace',
        isEditable: false,
        showCart: false,
        cartCount: 0,
        currentPageSlug: '',
        previewResponsiveMode: 'auto',
    },
);

export type NavbarPreviewSelectTarget =
    | { type: 'brand' }
    | { type: 'item'; id?: string }
    | { type: 'button'; id?: string }
    | { type: 'mobile' }
    | { type: 'mega' };

const emit = defineEmits<{
    'open-cart': [];
    'navigate-page': [slug: string];
    select: [target: NavbarPreviewSelectTarget];
}>();
const page = usePage<{ page?: { slug?: string } }>();
const mobileMenuOpen = ref(false);
const megaMenuOpen = ref(false);
const isScrolled = ref(false);
const headerElement = ref<HTMLElement | null>(null);
const isResponsiveMobile = ref(false);
let resizeObserver: ResizeObserver | undefined;
const shouldUseMobileLayout = computed(() => {
    if (props.previewResponsiveMode === 'mobile') {
        return true;
    }

    // Tablet preview is width-only; layout follows desktop chrome.
    if (
        props.previewResponsiveMode === 'desktop' ||
        props.previewResponsiveMode === 'tablet'
    ) {
        return false;
    }

    return isResponsiveMobile.value;
});

function emitSelect(target: NavbarPreviewSelectTarget): void {
    if (props.isEditable) {
        emit('select', target);
    }
}

const supportedVariants = [
    'classic-inline',
    'floating-island',
    'centered-brand',
    'mega-menu',
];
const supportedSurfaceModes = ['design', 'transparent', 'theme', 'custom'];

const headerItems = computed(() => props.navigationConfig?.header?.items || []);
const logoText = computed(() => props.tenantName);
const brand = computed(() => props.navigationConfig?.header?.brand);
const showLogo = computed(
    () => props.navigationConfig?.header?.showLogo !== false,
);
const ctaButton = computed(
    () => props.navigationConfig?.header?.ctaButton || {},
);
const headerActions = computed<NavigationAction[]>(() => {
    const actions = props.navigationConfig?.header?.actions;

    if (actions?.length) {
        return actions;
    }

    if (ctaButton.value.show) {
        return [
            {
                label: ctaButton.value.label || 'Continue',
                type: 'internal',
                slug: ctaButton.value.slug,
                variant: 'primary',
            },
        ];
    }

    return [];
});
const variant = computed(() => {
    const configuredVariant = props.navigationConfig?.header?.variant;

    if (configuredVariant === 'mega-menu') {
        return 'classic-inline';
    }

    return typeof configuredVariant === 'string' &&
        supportedVariants.includes(configuredVariant)
        ? configuredVariant
        : 'floating-island';
});
const menuMode = computed(() => {
    const configuredMode = props.navigationConfig?.header?.menu?.mode;

    if (configuredMode === 'simple' || configuredMode === 'mega') {
        return configuredMode;
    }

    return props.navigationConfig?.header?.variant === 'mega-menu'
        ? 'mega'
        : 'simple';
});
const megaMenuLabel = computed(
    () => props.navigationConfig?.header?.menu?.triggerLabel || 'Explore',
);
const actionPosition = computed(() =>
    props.navigationConfig?.header?.actionPosition === 'start'
        ? 'start'
        : 'end',
);
const surfaceMode = computed(() => {
    const configuredMode = props.navigationConfig?.header?.surface?.mode;

    return typeof configuredMode === 'string' &&
        supportedSurfaceModes.includes(configuredMode)
        ? configuredMode
        : 'design';
});
const surfaceStyle = computed<Record<string, string>>(() => ({
    '--navbar-custom-background':
        props.navigationConfig?.header?.surface?.backgroundColor || '#ffffff',
    '--navbar-custom-text':
        props.navigationConfig?.header?.surface?.textColor || '#18181b',
    '--navbar-action-background':
        props.navigationConfig?.header?.actionStyle?.mode === 'custom'
            ? props.navigationConfig.header.actionStyle.backgroundColor ||
              '#18181b'
            : 'var(--theme-primary, #4f46e5)',
    '--navbar-action-text':
        props.navigationConfig?.header?.actionStyle?.mode === 'custom'
            ? props.navigationConfig.header.actionStyle.textColor || '#ffffff'
            : 'var(--theme-bg, #ffffff)',
    '--navbar-action-hover-background':
        props.navigationConfig?.header?.actionStyle?.mode === 'custom'
            ? props.navigationConfig.header.actionStyle.hoverBackgroundColor ||
              '#3f3f46'
            : 'color-mix(in srgb, var(--theme-primary, #4f46e5) 82%, black)',
    '--navbar-action-hover-text':
        props.navigationConfig?.header?.actionStyle?.mode === 'custom'
            ? props.navigationConfig.header.actionStyle.hoverTextColor ||
              '#ffffff'
            : 'var(--theme-bg, #ffffff)',
    '--navbar-link-color':
        props.navigationConfig?.header?.linkStyle?.color || '#18181b',
    '--navbar-link-hover-color':
        props.navigationConfig?.header?.linkStyle?.mode === 'custom'
            ? props.navigationConfig.header.linkStyle.hoverColor || '#18181b'
            : 'var(--theme-primary, #4f46e5)',
    '--navbar-active-color':
        props.navigationConfig?.header?.states?.activeColor ||
        'var(--theme-primary, #4f46e5)',
    '--navbar-focus-color':
        props.navigationConfig?.header?.states?.focusColor ||
        'var(--theme-primary, #4f46e5)',
    '--navbar-disabled-opacity': `${
        (props.navigationConfig?.header?.states?.disabledOpacity ?? 40) / 100
    }`,
    '--navbar-scrolled-background':
        props.navigationConfig?.header?.states?.scrolledBackgroundColor ||
        'var(--theme-bg, #ffffff)',
    '--navbar-scrolled-text':
        props.navigationConfig?.header?.states?.scrolledTextColor ||
        'var(--theme-text, #18181b)',
    '--navbar-content-width': `${
        props.navigationConfig?.header?.layout?.contentWidth ?? 1200
    }px`,
    '--navbar-height': `${props.navigationConfig?.header?.layout?.height ?? 72}px`,
    '--navbar-horizontal-padding': `${
        props.navigationConfig?.header?.layout?.horizontalPadding ?? 24
    }px`,
    '--navbar-layout-radius': `${
        props.navigationConfig?.header?.layout?.borderRadius ?? 16
    }px`,
    '--navbar-layout-border-width': `${
        props.navigationConfig?.header?.layout?.borderWidth ?? 1
    }px`,
    '--navbar-layout-border-color':
        props.navigationConfig?.header?.layout?.borderColor || '#e4e4e7',
    '--navbar-sticky-offset': `${
        props.navigationConfig?.header?.layout?.stickyOffset ?? 0
    }px`,
    '--navbar-brand-width': `${brand.value?.width ?? 120}px`,
    '--navbar-brand-mobile-width': `${brand.value?.mobileWidth ?? 96}px`,
}));

const linkHoverEffect = computed(
    () => props.navigationConfig?.header?.linkStyle?.hoverEffect ?? 'color',
);
const layoutPosition = computed(
    () => props.navigationConfig?.header?.layout?.position ?? 'static',
);
const layoutShadow = computed(
    () => props.navigationConfig?.header?.layout?.shadow ?? 'small',
);
const mobileMenuStyle = computed(
    () => props.navigationConfig?.header?.responsive?.menuStyle ?? 'dropdown',
);
const mobileMenuIcon = computed(
    () => props.navigationConfig?.header?.responsive?.menuIcon ?? 'menu',
);
const megaGroups = computed(() => {
    const configuredGroups = props.navigationConfig?.header?.menu?.groups;
    const groups = configuredGroups?.length
        ? configuredGroups
        : [{ id: 'main', label: 'Explore', description: '' }];

    return groups.map((group) => ({
        ...group,
        items: headerItems.value.filter(
            (item) => (item.megaGroup || groups[0].id) === group.id,
        ),
    }));
});

const actionCssVars = (action: NavigationAction): Record<string, string> => ({
    '--action-background':
        action.backgroundColor ||
        'var(--navbar-action-background, var(--theme-primary, #4f46e5))',
    '--action-text':
        action.textColor ||
        'var(--navbar-action-text, var(--theme-bg, #ffffff))',
    '--action-hover-background':
        action.hoverBackgroundColor ||
        'var(--navbar-action-hover-background, var(--theme-primary, #4f46e5))',
    '--action-hover-text':
        action.hoverTextColor ||
        'var(--navbar-action-hover-text, var(--theme-bg, #ffffff))',
    '--action-border': action.borderColor || 'currentColor',
    '--action-radius': `${action.borderRadius ?? 8}px`,
});

function openMegaMenu(): void {
    megaMenuOpen.value = true;
}

function closeMegaMenu(): void {
    megaMenuOpen.value = false;
}

function handleScroll(): void {
    isScrolled.value = window.scrollY > 8;
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
    resizeObserver = new ResizeObserver(([entry]) => {
        isResponsiveMobile.value =
            entry.contentRect.width <
            (props.navigationConfig?.header?.responsive?.breakpoint ?? 720);
    });

    if (headerElement.value) {
        resizeObserver.observe(headerElement.value);
    }
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    resizeObserver?.disconnect();
});

const getPageUrl = (slug?: string): string | undefined => {
    if (props.isEditable) {
        return undefined;
    }

    return !slug || slug === 'home' ? '/' : `/${slug}`;
};

const navigateInEditor = (slug?: string, itemId?: string): void => {
    mobileMenuOpen.value = false;
    megaMenuOpen.value = false;

    if (props.isEditable) {
        if (itemId) {
            emitSelect({ type: 'item', id: itemId });
        }

        emit('navigate-page', slug || 'home');
    }
};

const navigateAction = (action: NavigationAction): void => {
    mobileMenuOpen.value = false;
    megaMenuOpen.value = false;

    if (props.isEditable) {
        emitSelect({ type: 'button', id: action.id });

        if (action.type !== 'external') {
            emit('navigate-page', action.slug || 'home');
        }
    }
};

const normalizedPath = computed(() => {
    const path = page.url.split('?')[0].replace(/\/$/, '');

    return path || '/';
});

const activePageSlug = computed(() => {
    if (props.currentPageSlug) {
        return props.currentPageSlug;
    }

    if (page.props.page?.slug) {
        return page.props.page.slug;
    }

    return normalizedPath.value === '/'
        ? 'home'
        : normalizedPath.value.replace(/^\//, '');
});

const isItemActive = (item: NavigationItem): boolean => {
    if (item.type === 'external') {
        return false;
    }

    return activePageSlug.value === (item.slug || 'home');
};

const searchEnabled = computed(
    () => props.navigationConfig?.header?.search?.show === true,
);
const searchPlaceholder = computed(
    () =>
        props.navigationConfig?.header?.search?.placeholder ||
        'Search this site…',
);
const searchOpen = ref(false);
const searchQuery = ref('');
const searchInput = ref<HTMLInputElement | null>(null);

const searchablePages = computed(() =>
    props.pages.filter((entry) => entry.slug || entry.title),
);

const searchResults = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();

    if (!query) {
        return searchablePages.value.slice(0, 8);
    }

    return searchablePages.value
        .filter(
            (entry) =>
                (entry.title || '').toLowerCase().includes(query) ||
                (entry.slug || '').toLowerCase().includes(query),
        )
        .slice(0, 8);
});

const toggleSearch = (): void => {
    searchOpen.value = !searchOpen.value;

    if (searchOpen.value) {
        mobileMenuOpen.value = false;
        megaMenuOpen.value = false;
        searchQuery.value = '';
        nextTick(() => searchInput.value?.focus());
    }
};

const closeSearch = (): void => {
    searchOpen.value = false;
    searchQuery.value = '';
};

const selectSearchResult = (slug?: string): void => {
    closeSearch();

    if (props.isEditable) {
        emit('navigate-page', slug || 'home');
    }
};

watch(
    () => page.url,
    () => {
        mobileMenuOpen.value = false;
        megaMenuOpen.value = false;
        searchOpen.value = false;
    },
);
</script>

<template>
    <header
        ref="headerElement"
        :class="[
            'site-header',
            `site-header--${variant}`,
            `site-header--surface-${surfaceMode}`,
            `site-header--menu-${menuMode}`,
            `site-header--actions-${actionPosition}`,
            `site-header--hover-${linkHoverEffect}`,
            `site-header--position-${layoutPosition}`,
            `site-header--shadow-${layoutShadow}`,
            `site-header--mobile-${mobileMenuStyle}`,
            `site-header--mobile-align-${navigationConfig?.header?.responsive?.alignment || 'left'}`,
            {
                'site-header--links-custom':
                    navigationConfig?.header?.linkStyle?.mode === 'custom',
                'site-header--full-width':
                    navigationConfig?.header?.layout?.fullWidth,
                'site-header--scrolled': isScrolled,
                'site-header--brand-hidden-mobile': brand?.hideOnMobile,
                'site-header--hide-mobile-actions':
                    navigationConfig?.header?.responsive?.showActions === false,
                'site-header--responsive-mobile': shouldUseMobileLayout,
            },
        ]"
        :style="surfaceStyle"
    >
        <div class="header-shell">
            <div v-if="showLogo" class="site-logo">
                <component
                    :is="isEditable ? 'button' : Link"
                    :type="isEditable ? 'button' : undefined"
                    :href="isEditable ? undefined : '/'"
                    class="logo-link"
                    @click="
                        () => {
                            emitSelect({ type: 'brand' });
                            navigateInEditor('home');
                        }
                    "
                >
                    <img
                        v-if="brand?.type === 'image' && brand.imageUrl"
                        :src="brand.imageUrl"
                        :alt="brand.alt || `${logoText} logo`"
                        class="logo-image"
                    />
                    <span v-else class="logo-copy">{{
                        brand?.text || logoText
                    }}</span>
                </component>
            </div>

            <nav
                v-if="menuMode !== 'mega'"
                class="header-nav desktop-navigation"
                aria-label="Primary navigation"
            >
                <ul class="nav-list">
                    <li
                        v-for="(item, index) in headerItems"
                        :key="`${item.label}-${index}`"
                        class="nav-item"
                    >
                        <a
                            v-if="item.type === 'external'"
                            :href="item.href || '#'"
                            target="_blank"
                            rel="noopener noreferrer"
                            :class="[
                                'nav-link',
                                { 'nav-link-disabled': item.disabled },
                            ]"
                            :aria-disabled="item.disabled || undefined"
                            @click="item.disabled && $event.preventDefault()"
                        >
                            {{ item.label }}
                        </a>

                        <component
                            :is="isEditable ? 'button' : Link"
                            v-else
                            :type="isEditable ? 'button' : undefined"
                            :href="getPageUrl(item.slug)"
                            :aria-current="
                                isItemActive(item) ? 'page' : undefined
                            "
                            :class="[
                                'nav-link',
                                { 'nav-link-active': isItemActive(item) },
                                { 'nav-link-disabled': item.disabled },
                            ]"
                            :aria-disabled="item.disabled || undefined"
                            @click="
                                item.disabled
                                    ? $event.preventDefault()
                                    : navigateInEditor(item.slug, item.id)
                            "
                        >
                            {{ item.label }}
                            <span
                                v-if="isItemActive(item)"
                                class="active-dot"
                                aria-hidden="true"
                            ></span>
                        </component>
                        <div
                            v-if="item.children?.length"
                            class="simple-dropdown"
                        >
                            <template
                                v-for="child in item.children"
                                :key="child.id || child.label"
                            >
                                <a
                                    v-if="child.type === 'external'"
                                    :href="child.href || '#'"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="simple-dropdown-link"
                                    @click="
                                        isEditable &&
                                        emitSelect({
                                            type: 'item',
                                            id: child.id,
                                        })
                                    "
                                >
                                    {{ child.label }}
                                </a>
                                <component
                                    :is="isEditable ? 'button' : Link"
                                    v-else
                                    :type="isEditable ? 'button' : undefined"
                                    :href="getPageUrl(child.slug)"
                                    :aria-current="
                                        isItemActive(child) ? 'page' : undefined
                                    "
                                    :class="[
                                        'simple-dropdown-link',
                                        {
                                            'simple-dropdown-link-active':
                                                isItemActive(child),
                                        },
                                    ]"
                                    @click="
                                        navigateInEditor(child.slug, child.id)
                                    "
                                >
                                    {{ child.label }}
                                </component>
                            </template>
                        </div>
                    </li>
                </ul>
            </nav>

            <nav
                v-else
                class="mega-navigation desktop-navigation"
                aria-label="Primary navigation"
                @mouseenter="
                    navigationConfig?.header?.menu?.openOn === 'hover' &&
                    openMegaMenu()
                "
                @mouseleave="
                    navigationConfig?.header?.menu?.openOn === 'hover' &&
                    closeMegaMenu()
                "
            >
                <button
                    type="button"
                    class="nav-link mega-trigger"
                    :aria-expanded="megaMenuOpen"
                    @click="
                        () => {
                            emitSelect({ type: 'mega' });
                            megaMenuOpen = !megaMenuOpen;
                        }
                    "
                >
                    {{ megaMenuLabel }}
                    <ChevronDown
                        :size="15"
                        :class="{ 'mega-chevron-open': megaMenuOpen }"
                    />
                </button>

                <div
                    v-show="megaMenuOpen"
                    :class="[
                        'mega-panel',
                        `mega-panel--${navigationConfig?.header?.menu?.width || 'wide'}`,
                        `mega-panel--${navigationConfig?.header?.menu?.alignment || 'center'}`,
                        `mega-panel--${navigationConfig?.header?.menu?.animation || 'slide'}`,
                    ]"
                >
                    <component
                        :is="isEditable ? 'div' : 'a'"
                        v-if="navigationConfig?.header?.menu?.featured?.show"
                        class="mega-featured"
                        :href="
                            isEditable
                                ? undefined
                                : navigationConfig.header.menu.featured.href ||
                                  '#'
                        "
                        :style="
                            navigationConfig.header.menu.featured.imageUrl
                                ? {
                                      backgroundImage: `linear-gradient(rgb(0 0 0 / 28%), rgb(0 0 0 / 68%)), url(${navigationConfig.header.menu.featured.imageUrl})`,
                                  }
                                : undefined
                        "
                    >
                        <span class="mega-kicker">Featured</span>
                        <strong>{{
                            navigationConfig.header.menu.featured.title
                        }}</strong>
                        <p>
                            {{
                                navigationConfig.header.menu.featured
                                    .description
                            }}
                        </p>
                    </component>
                    <div
                        class="mega-groups"
                        :style="{
                            gridTemplateColumns: `repeat(${navigationConfig?.header?.menu?.columns || 3}, minmax(0, 1fr))`,
                        }"
                    >
                        <section
                            v-for="group in megaGroups"
                            :key="group.id"
                            class="mega-group"
                        >
                            <div class="mega-group-heading">
                                <strong>{{ group.label }}</strong>
                                <p v-if="group.description">
                                    {{ group.description }}
                                </p>
                            </div>
                            <template
                                v-for="item in group.items"
                                :key="item.id || item.label"
                            >
                                <a
                                    v-if="item.type === 'external'"
                                    :href="item.href || '#'"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    :class="[
                                        'mega-link',
                                        {
                                            'mega-link-active':
                                                isItemActive(item),
                                        },
                                        { 'nav-link-disabled': item.disabled },
                                    ]"
                                    :aria-current="
                                        isItemActive(item) ? 'page' : undefined
                                    "
                                >
                                    <strong>{{ item.label }}</strong>
                                    <ArrowRight :size="15" />
                                </a>
                                <component
                                    :is="isEditable ? 'button' : Link"
                                    v-else
                                    :type="isEditable ? 'button' : undefined"
                                    :href="getPageUrl(item.slug)"
                                    :class="[
                                        'mega-link',
                                        { 'nav-link-disabled': item.disabled },
                                    ]"
                                    @click="
                                        item.disabled
                                            ? $event.preventDefault()
                                            : navigateInEditor(
                                                  item.slug,
                                                  item.id,
                                              )
                                    "
                                >
                                    <strong>{{ item.label }}</strong>
                                    <ArrowRight :size="15" />
                                </component>
                            </template>
                        </section>
                    </div>
                </div>
            </nav>

            <div class="header-actions">
                <button
                    v-if="searchEnabled"
                    type="button"
                    class="action-button search-action"
                    :aria-expanded="searchOpen"
                    aria-label="Search this site"
                    title="Search"
                    @click="toggleSearch"
                >
                    <X v-if="searchOpen" :size="20" :stroke-width="1.8" />
                    <Search v-else :size="20" :stroke-width="1.8" />
                </button>

                <button
                    v-if="showCart"
                    type="button"
                    class="action-button cart-action"
                    :aria-label="`Open shopping bag with ${cartCount} items`"
                    title="Shopping bag"
                    @click="emit('open-cart')"
                >
                    <ShoppingBag
                        :size="20"
                        :stroke-width="1.8"
                        aria-hidden="true"
                    />
                    <span v-if="cartCount" class="cart-count">{{
                        cartCount
                    }}</span>
                </button>

                <component
                    :is="
                        isEditable
                            ? 'button'
                            : action.type === 'external'
                              ? 'a'
                              : Link
                    "
                    v-for="(action, index) in headerActions"
                    :key="`${action.label}-${index}`"
                    :type="isEditable ? 'button' : undefined"
                    :href="
                        isEditable
                            ? undefined
                            : action.type === 'external'
                              ? action.href || '#'
                              : getPageUrl(action.slug)
                    "
                    :target="
                        !isEditable && action.type === 'external'
                            ? '_blank'
                            : undefined
                    "
                    :rel="
                        !isEditable && action.type === 'external'
                            ? 'noopener noreferrer'
                            : undefined
                    "
                    :class="[
                        'action-button desktop-action',
                        `action-button--${action.variant || 'primary'}`,
                        `action-button--${action.size || 'medium'}`,
                        `action-button--animate-${action.animation || 'lift'}`,
                    ]"
                    :style="actionCssVars(action)"
                    :aria-disabled="action.disabled || undefined"
                    @click="
                        action.disabled
                            ? $event.preventDefault()
                            : navigateAction(action)
                    "
                >
                    <ShoppingBag
                        v-if="
                            action.icon === 'bag' &&
                            action.iconPosition === 'start'
                        "
                        :size="15"
                    />
                    <ArrowRight
                        v-else-if="
                            action.icon === 'arrow' &&
                            action.iconPosition === 'start'
                        "
                        class="action-icon"
                        :size="16"
                    />
                    <span>{{ action.label }}</span>
                    <ShoppingBag
                        v-if="
                            action.icon === 'bag' &&
                            action.iconPosition !== 'start'
                        "
                        :size="15"
                    />
                    <ArrowRight
                        v-else-if="
                            action.icon !== 'none' &&
                            action.iconPosition !== 'start'
                        "
                        class="action-icon"
                        :size="17"
                        :stroke-width="1.8"
                    />
                </component>

                <button
                    type="button"
                    class="mobile-toggle"
                    :aria-expanded="mobileMenuOpen"
                    aria-label="Toggle navigation menu"
                    @click="
                        () => {
                            emitSelect({ type: 'mobile' });
                            mobileMenuOpen = !mobileMenuOpen;
                        }
                    "
                >
                    <X v-if="mobileMenuOpen" :size="20" />
                    <MoreHorizontal
                        v-else-if="mobileMenuIcon === 'dots'"
                        :size="20"
                    />
                    <Grid2X2 v-else-if="mobileMenuIcon === 'grid'" :size="18" />
                    <Menu v-else :size="20" />
                </button>
            </div>

            <div
                v-if="searchEnabled && searchOpen"
                class="search-panel"
                role="search"
                @keydown.esc="closeSearch"
            >
                <input
                    ref="searchInput"
                    v-model="searchQuery"
                    type="search"
                    class="search-input"
                    :placeholder="searchPlaceholder"
                    aria-label="Search pages"
                />
                <ul v-if="searchResults.length" class="search-results">
                    <li
                        v-for="result in searchResults"
                        :key="result.slug || result.title"
                    >
                        <component
                            :is="isEditable ? 'button' : Link"
                            :type="isEditable ? 'button' : undefined"
                            :href="getPageUrl(result.slug)"
                            class="search-result-link"
                            @click="selectSearchResult(result.slug)"
                        >
                            <span class="search-result-title">
                                {{ result.title || result.slug }}
                            </span>
                            <span class="search-result-slug">
                                /{{
                                    result.slug === 'home'
                                        ? ''
                                        : result.slug || ''
                                }}
                            </span>
                        </component>
                    </li>
                </ul>
                <p v-else class="search-empty">
                    No pages match “{{ searchQuery }}”.
                </p>
            </div>

            <div v-show="mobileMenuOpen" class="mobile-panel">
                <nav aria-label="Mobile navigation">
                    <ul class="mobile-list">
                        <li
                            v-for="(item, index) in headerItems"
                            :key="`${item.label}-mobile-${index}`"
                        >
                            <a
                                v-if="item.type === 'external'"
                                :href="item.href || '#'"
                                target="_blank"
                                rel="noopener noreferrer"
                                :class="[
                                    'mobile-link',
                                    {
                                        'mobile-link-active':
                                            isItemActive(item),
                                    },
                                    { 'nav-link-disabled': item.disabled },
                                ]"
                                :aria-current="
                                    isItemActive(item) ? 'page' : undefined
                                "
                                :aria-disabled="item.disabled || undefined"
                                @click="
                                    item.disabled && $event.preventDefault()
                                "
                            >
                                <span>0{{ index + 1 }}</span>
                                {{ item.label }}
                            </a>
                            <component
                                :is="isEditable ? 'button' : Link"
                                v-else
                                :type="isEditable ? 'button' : undefined"
                                :href="getPageUrl(item.slug)"
                                :class="[
                                    'mobile-link',
                                    { 'nav-link-disabled': item.disabled },
                                ]"
                                :aria-disabled="item.disabled || undefined"
                                @click="
                                    item.disabled
                                        ? $event.preventDefault()
                                        : navigateInEditor(item.slug, item.id)
                                "
                            >
                                <span>0{{ index + 1 }}</span>
                                {{ item.label }}
                            </component>
                            <div
                                v-if="item.children?.length"
                                class="mobile-submenu"
                            >
                                <template
                                    v-for="child in item.children"
                                    :key="child.id || child.label"
                                >
                                    <a
                                        v-if="child.type === 'external'"
                                        :href="child.href || '#'"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="mobile-sublink"
                                        @click="
                                            isEditable &&
                                            emitSelect({
                                                type: 'item',
                                                id: child.id,
                                            })
                                        "
                                    >
                                        {{ child.label }}
                                    </a>
                                    <component
                                        :is="isEditable ? 'button' : Link"
                                        v-else
                                        :type="
                                            isEditable ? 'button' : undefined
                                        "
                                        :href="getPageUrl(child.slug)"
                                        :aria-current="
                                            isItemActive(child)
                                                ? 'page'
                                                : undefined
                                        "
                                        :class="[
                                            'mobile-sublink',
                                            {
                                                'mobile-sublink-active':
                                                    isItemActive(child),
                                            },
                                        ]"
                                        @click="
                                            navigateInEditor(
                                                child.slug,
                                                child.id,
                                            )
                                        "
                                    >
                                        {{ child.label }}
                                    </component>
                                </template>
                            </div>
                        </li>
                    </ul>
                </nav>

                <div v-if="headerActions.length" class="mobile-actions">
                    <component
                        :is="
                            isEditable
                                ? 'button'
                                : action.type === 'external'
                                  ? 'a'
                                  : Link
                        "
                        v-for="(action, index) in headerActions"
                        :key="`${action.label}-mobile-action-${index}`"
                        :type="isEditable ? 'button' : undefined"
                        :href="
                            isEditable
                                ? undefined
                                : action.type === 'external'
                                  ? action.href || '#'
                                  : getPageUrl(action.slug)
                        "
                        :target="
                            !isEditable && action.type === 'external'
                                ? '_blank'
                                : undefined
                        "
                        :rel="
                            !isEditable && action.type === 'external'
                                ? 'noopener noreferrer'
                                : undefined
                        "
                        :class="[
                            'mobile-cta',
                            `mobile-cta--${action.variant || 'primary'}`,
                            `action-button--${action.size || 'medium'}`,
                            `action-button--animate-${action.animation || 'lift'}`,
                        ]"
                        :style="actionCssVars(action)"
                        @click="
                            action.disabled
                                ? $event.preventDefault()
                                : navigateAction(action)
                        "
                    >
                        <ShoppingBag
                            v-if="
                                action.icon === 'bag' &&
                                action.iconPosition === 'start'
                            "
                            :size="15"
                        />
                        <ArrowRight
                            v-else-if="
                                action.icon === 'arrow' &&
                                action.iconPosition === 'start'
                            "
                            class="action-icon"
                            :size="16"
                        />
                        <span>{{ action.label }}</span>
                        <ShoppingBag
                            v-if="
                                action.icon === 'bag' &&
                                action.iconPosition !== 'start'
                            "
                            :size="15"
                        />
                        <ArrowRight
                            v-else-if="
                                action.icon !== 'none' &&
                                action.iconPosition !== 'start'
                            "
                            class="action-icon"
                            :size="17"
                        />
                    </component>
                </div>
            </div>
        </div>
    </header>
</template>

<style scoped>
.site-header {
    position: relative;
    z-index: 30;
    box-sizing: border-box;
    width: 100%;
    color: var(--theme-text, #0f172a);
    background: var(--theme-bg, #ffffff);
    font-family: var(--theme-font-body, sans-serif);
}

.header-shell {
    position: relative;
    display: flex;
    align-items: center;
    box-sizing: border-box;
    width: min(100%, 1240px);
    min-height: 4.75rem;
    margin: 0 auto;
    gap: clamp(1.25rem, 3vw, 3rem);
    animation: header-enter 420ms cubic-bezier(0.22, 1, 0.36, 1) both;
}

.site-logo {
    order: 0;
}

.header-nav,
.mega-navigation {
    order: 1;
}

.header-actions {
    order: 2;
}

.site-header--actions-start:not(.site-header--centered-brand) .header-actions {
    order: 1;
    margin-left: auto;
}

.site-header--actions-start:not(.site-header--centered-brand) .header-nav,
.site-header--actions-start:not(.site-header--centered-brand) .mega-navigation {
    order: 2;
    margin-left: 0;
}

.site-header--classic-inline {
    border-bottom: 1px solid
        color-mix(in srgb, var(--theme-text) 12%, transparent);
}

.site-header--classic-inline .header-shell {
    padding: 0.7rem clamp(1.25rem, 4vw, 3rem);
}

.site-header--floating-island {
    padding: 1.1rem clamp(0.75rem, 3vw, 2rem);
}

.site-header--floating-island .header-shell {
    min-height: 5rem;
    padding: 0.55rem 0.65rem 0.55rem 1.5rem;
    color: var(--theme-bg, #ffffff);
    background: var(--theme-text, #0f172a);
    border: 1px solid color-mix(in srgb, var(--theme-bg) 20%, transparent);
    border-radius: calc(var(--theme-border-radius, 8px) * 2.5);
    box-shadow: 0 18px 50px
        color-mix(in srgb, var(--theme-text) 22%, transparent);
}

.site-header--centered-brand {
    padding: 0.85rem clamp(1rem, 4vw, 3rem);
    border-top: 1px solid color-mix(in srgb, var(--theme-text) 10%, transparent);
    border-bottom: 1px solid
        color-mix(in srgb, var(--theme-text) 10%, transparent);
}

.site-header--centered-brand .header-shell {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
    grid-template-areas: 'navigation logo actions';
}

.site-header--centered-brand.site-header--actions-start .header-shell {
    grid-template-areas: 'actions logo navigation';
}

.site-header--centered-brand .site-logo {
    position: static;
    grid-area: logo;
    justify-self: center;
    transform: none;
}

.site-header--centered-brand .header-nav,
.site-header--centered-brand .mega-navigation {
    grid-area: navigation;
    justify-self: start;
    margin-left: 0;
}

.site-header--centered-brand .header-actions {
    grid-area: actions;
    justify-self: end;
    margin-left: 0;
}

.site-header--centered-brand.site-header--actions-start .header-nav,
.site-header--centered-brand.site-header--actions-start .mega-navigation {
    justify-self: end;
}

.site-header--centered-brand.site-header--actions-start .header-actions {
    justify-self: start;
}

.site-header--mega-menu {
    padding: 0.9rem clamp(0.75rem, 3vw, 2rem);
}

.site-header--mega-menu .header-shell {
    padding: 0.6rem 0.65rem 0.6rem 1.5rem;
    color: var(--theme-bg, #ffffff);
    background: var(--theme-text, #0f172a);
    border-radius: var(--theme-border-radius, 8px);
}

.site-header--surface-design,
.site-header--surface-transparent {
    color: var(--theme-text, #0f172a);
    background: transparent;
    border-color: transparent;
}

.site-header--surface-design .header-shell,
.site-header--surface-transparent .header-shell {
    color: inherit;
    background: transparent;
    border-color: color-mix(in srgb, currentColor 12%, transparent);
    box-shadow: none;
}

.site-header--surface-theme {
    color: var(--theme-text, #0f172a);
    background: var(--theme-bg, #ffffff);
}

.site-header--surface-theme .header-shell {
    color: inherit;
    background: var(--theme-bg, #ffffff);
    border-color: color-mix(in srgb, currentColor 12%, transparent);
}

.site-header--surface-custom {
    color: var(--navbar-custom-text, #18181b);
    background: var(--navbar-custom-background, #ffffff);
}

.site-header--surface-custom .header-shell {
    color: inherit;
    background: var(--navbar-custom-background, #ffffff);
    border-color: color-mix(in srgb, currentColor 14%, transparent);
}

.site-logo {
    z-index: 2;
    flex: 0 0 auto;
    min-width: 0;
    max-width: 15rem;
    overflow: hidden;
    font-family: var(--theme-font-heading, sans-serif);
}

.logo-link {
    display: inline-flex;
    align-items: center;
    max-width: 100%;
    padding: 0;
    gap: 0.65rem;
    color: inherit;
    background: transparent;
    border: 0;
    font: inherit;
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: -0.025em;
    text-decoration: none;
    cursor: pointer;
}

.logo-copy {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.header-nav {
    min-width: 0;
    margin-left: auto;
}

.nav-list {
    display: flex;
    align-items: center;
    padding: 0;
    margin: 0;
    gap: clamp(1.1rem, 2.3vw, 2.35rem);
    list-style: none;
}

.nav-item {
    display: flex;
    align-items: center;
}

.nav-link {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 1rem 0;
    gap: 0.35rem;
    color: inherit;
    opacity: 0.64;
    background: transparent;
    border: 0;
    font: inherit;
    font-size: 0.9rem;
    font-weight: 550;
    line-height: 1;
    text-decoration: none;
    white-space: nowrap;
    cursor: pointer;
    transition:
        color 180ms ease,
        opacity 180ms ease,
        transform 180ms ease;
}

.site-header--links-custom .nav-link,
.site-header--links-custom .mobile-link {
    color: var(--navbar-link-color);
}

.nav-link:hover,
.nav-link-active,
.mega-trigger[aria-expanded='true'] {
    color: var(--navbar-link-hover-color, var(--theme-primary, #4f46e5));
    opacity: 1;
    transform: translateY(-1px);
}

.active-dot {
    position: absolute;
    bottom: 0.4rem;
    left: 50%;
    width: 0.3rem;
    height: 0.3rem;
    background: var(--navbar-active-color, var(--theme-primary, #4f46e5));
    border-radius: 9999px;
    transform: translateX(-50%);
}

.header-actions {
    z-index: 2;
    display: flex;
    flex: 0 0 auto;
    align-items: center;
    gap: 0.6rem;
}

.action-button,
.mobile-toggle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    color: inherit;
    background: transparent;
    border: 1px solid color-mix(in srgb, currentColor 18%, transparent);
    border-radius: var(--theme-border-radius, 8px);
    cursor: pointer;
    text-decoration: none;
    transition:
        filter 180ms ease,
        transform 180ms ease,
        background-color 180ms ease;
}

.action-button:hover,
.mobile-toggle:hover {
    filter: brightness(0.94);
    transform: translateY(-1px);
}

.cart-action,
.search-action,
.mobile-toggle {
    position: relative;
    width: 2.75rem;
    height: 2.75rem;
}

.search-panel {
    position: absolute;
    top: calc(100% + 0.55rem);
    right: 0;
    z-index: 60;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    width: min(100%, 22rem);
    padding: 0.75rem;
    font-family: var(--theme-font-body, sans-serif);
    color: var(--theme-text, #0f172a);
    background: var(--theme-bg, #ffffff);
    border: 1px solid color-mix(in srgb, var(--theme-text) 12%, transparent);
    border-radius: calc(var(--theme-border-radius, 8px) * 1.5);
    box-shadow: 0 20px 50px
        color-mix(in srgb, var(--theme-text) 18%, transparent);
}

.search-input {
    box-sizing: border-box;
    width: 100%;
    padding: 0.6rem 0.8rem;
    font: inherit;
    font-size: 0.9rem;
    color: inherit;
    background: color-mix(in srgb, var(--theme-text) 5%, transparent);
    border: 1px solid color-mix(in srgb, var(--theme-text) 14%, transparent);
    border-radius: calc(var(--theme-border-radius, 8px) * 0.9);
}

.search-input:focus {
    outline: 2px solid var(--navbar-focus-color, var(--theme-primary));
    outline-offset: 1px;
}

.search-results {
    display: grid;
    max-height: 16rem;
    padding: 0;
    margin: 0;
    overflow-y: auto;
    gap: 2px;
    list-style: none;
}

.search-result-link {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    box-sizing: border-box;
    width: 100%;
    padding: 0.5rem 0.65rem;
    font: inherit;
    font-size: 0.88rem;
    color: inherit;
    text-align: left;
    text-decoration: none;
    cursor: pointer;
    background: transparent;
    border: 0;
    border-radius: calc(var(--theme-border-radius, 8px) * 0.75);
    gap: 0.75rem;
    transition: background-color 140ms ease;
}

.search-result-link:hover,
.search-result-link:focus-visible {
    background: color-mix(in srgb, var(--theme-primary) 12%, transparent);
}

.search-result-title {
    overflow: hidden;
    font-weight: 600;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.search-result-slug {
    flex-shrink: 0;
    font-size: 0.75rem;
    opacity: 0.6;
}

.search-empty {
    padding: 0.4rem 0.65rem;
    margin: 0;
    font-size: 0.85rem;
    opacity: 0.7;
}

.cart-count {
    position: absolute;
    top: -0.3rem;
    right: -0.3rem;
    display: grid;
    min-width: 1.2rem;
    height: 1.2rem;
    padding: 0 0.2rem;
    place-items: center;
    color: var(--theme-bg, #ffffff);
    background: var(--theme-primary, #4f46e5);
    border-radius: 9999px;
    font-size: 0.62rem;
}

.action-button--primary,
.action-button--outline,
.action-button--text {
    min-height: 2.85rem;
    padding: 0 1.05rem;
    gap: 0.55rem;
    font-size: 0.8rem;
    font-weight: 700;
}

.action-button--primary {
    color: var(--navbar-action-text, var(--theme-bg, #ffffff));
    background: var(--navbar-action-background, var(--theme-primary, #4f46e5));
    border-color: color-mix(
        in srgb,
        var(--navbar-action-background, var(--theme-primary)) 70%,
        transparent
    );
}

.action-button--primary:hover {
    color: var(--navbar-action-hover-text, var(--theme-bg, #ffffff));
    background: var(
        --navbar-action-hover-background,
        var(--theme-primary, #4f46e5)
    );
    filter: none;
}

.action-button--outline {
    color: inherit;
    background: transparent;
    border-color: color-mix(in srgb, currentColor 25%, transparent);
}

.action-button--outline:hover {
    color: var(--action-hover-text, var(--navbar-link-hover-color));
    background: var(--action-hover-background, transparent);
}

.action-button--text {
    min-height: auto;
    padding-inline: 0.45rem;
    color: inherit;
    background: transparent;
    border-color: transparent;
}

.action-button--outline:hover,
.action-button--text:hover {
    color: var(--navbar-link-hover-color, var(--theme-primary, #4f46e5));
}

.mobile-toggle,
.mobile-panel {
    display: none;
}

.mega-navigation {
    position: static;
}

.mega-chevron-open {
    transform: rotate(180deg);
}

.mega-panel {
    position: absolute;
    top: calc(100% + 0.7rem);
    right: 0;
    left: 0;
    z-index: 50;
    display: grid;
    grid-template-columns: minmax(13rem, 0.8fr) minmax(0, 2fr);
    padding: 1rem;
    gap: 1rem;
    color: var(--theme-text, #0f172a);
    background: var(--theme-bg, #ffffff);
    border: 1px solid color-mix(in srgb, var(--theme-text) 14%, transparent);
    border-radius: calc(var(--theme-border-radius, 8px) * 1.5);
    box-shadow: 0 24px 70px
        color-mix(in srgb, var(--theme-text) 25%, transparent);
    animation: panel-enter 220ms cubic-bezier(0.22, 1, 0.36, 1) both;
}

.mega-intro {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    min-height: 10rem;
    padding: 1.2rem;
    gap: 0.45rem;
    color: var(--theme-bg, #ffffff);
    background: var(--theme-primary, #4f46e5);
    border-radius: var(--theme-border-radius, 8px);
}

.mega-kicker {
    font-size: 0.64rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    opacity: 0.7;
    text-transform: uppercase;
}

.mega-intro strong {
    font-family: var(--theme-font-heading, sans-serif);
    font-size: 1.15rem;
}

.mega-intro p {
    max-width: 18rem;
    margin: 0;
    font-size: 0.72rem;
    line-height: 1.5;
    opacity: 0.72;
}

.mega-links {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.55rem;
}

.mega-link {
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    min-height: 4.6rem;
    padding: 0.8rem 1rem;
    gap: 0.8rem;
    color: inherit;
    background: color-mix(in srgb, var(--theme-text) 4%, transparent);
    border: 1px solid color-mix(in srgb, var(--theme-text) 8%, transparent);
    border-radius: var(--theme-border-radius, 8px);
    font: inherit;
    text-align: left;
    text-decoration: none;
    cursor: pointer;
    transition:
        background-color 180ms ease,
        transform 180ms ease;
}

.mega-link:hover {
    background: color-mix(in srgb, var(--theme-primary) 10%, transparent);
    transform: translateY(-2px);
}

.mega-link > span {
    color: var(--theme-primary, #4f46e5);
    font-size: 0.64rem;
    font-weight: 700;
}

.mega-link strong {
    font-size: 0.82rem;
}

@keyframes header-enter {
    from {
        opacity: 0;
        transform: translateY(-0.55rem);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes panel-enter {
    from {
        opacity: 0;
        transform: translateY(-0.45rem) scale(0.985);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.mobile-actions {
    display: grid;
    gap: 0.5rem;
}

.mobile-cta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.9rem 1rem;
    color: inherit;
    background: transparent;
    border: 1px solid transparent;
    border-radius: var(--theme-border-radius, 8px);
    font: inherit;
    font-size: 0.8rem;
    font-weight: 700;
    text-decoration: none;
}

.mobile-cta--primary {
    color: var(--navbar-action-text, var(--theme-bg, #ffffff));
    background: var(--navbar-action-background, var(--theme-primary, #4f46e5));
}

.mobile-cta--primary:hover {
    color: var(--navbar-action-hover-text, var(--theme-bg, #ffffff));
    background: var(
        --navbar-action-hover-background,
        var(--theme-primary, #4f46e5)
    );
}

.mobile-cta--outline {
    border-color: color-mix(in srgb, currentColor 24%, transparent);
}

.mobile-cta--text {
    justify-content: flex-start;
    padding-inline: 0.35rem;
}

@media (max-width: 720px) {
    .site-header,
    .site-header--floating-island,
    .site-header--centered-brand,
    .site-header--mega-menu {
        padding: 0.7rem;
    }

    .header-shell,
    .site-header--classic-inline .header-shell,
    .site-header--floating-island .header-shell,
    .site-header--mega-menu .header-shell {
        display: flex;
        min-height: 4.25rem;
        padding: 0.5rem 0.55rem 0.5rem 1rem;
        border-radius: calc(var(--theme-border-radius, 8px) * 1.5);
    }

    .site-header--classic-inline,
    .site-header--centered-brand {
        border: 0;
    }

    .site-header--classic-inline .header-shell,
    .site-header--centered-brand .header-shell {
        border: 1px solid color-mix(in srgb, var(--theme-text) 12%, transparent);
    }

    .site-header--centered-brand .site-logo {
        position: static;
        transform: none;
    }

    .desktop-navigation,
    .desktop-action {
        display: none;
    }

    .header-actions {
        order: 2;
        margin-left: auto;
    }

    .mobile-toggle {
        display: inline-flex;
    }

    .mobile-panel {
        position: absolute;
        top: calc(100% + 0.55rem);
        right: 0;
        left: 0;
        z-index: 50;
        padding: 0.75rem;
        gap: 0.75rem;
        color: var(--theme-text, #0f172a);
        background: var(--theme-bg, #ffffff);
        border: 1px solid color-mix(in srgb, var(--theme-text) 12%, transparent);
        border-radius: calc(var(--theme-border-radius, 8px) * 1.5);
        box-shadow: 0 20px 50px
            color-mix(in srgb, var(--theme-text) 22%, transparent);
        animation: panel-enter 220ms cubic-bezier(0.22, 1, 0.36, 1) both;
    }

    .mobile-list {
        display: grid;
        padding: 0;
        margin: 0;
        gap: 0.25rem;
        list-style: none;
    }

    .mobile-link {
        display: flex;
        width: 100%;
        align-items: center;
        padding: 0.9rem;
        gap: 0.85rem;
        color: inherit;
        background: transparent;
        border: 0;
        border-radius: var(--theme-border-radius, 8px);
        font: inherit;
        font-size: 0.9rem;
        font-weight: 650;
        text-align: left;
        text-decoration: none;
    }

    .mobile-link:hover {
        color: var(--navbar-link-hover-color, var(--theme-primary, #4f46e5));
        background: color-mix(in srgb, var(--theme-primary) 9%, transparent);
    }

    .mobile-link span {
        color: var(--theme-primary, #4f46e5);
        font-size: 0.62rem;
    }
}

.site-header--position-sticky {
    position: sticky;
    top: var(--navbar-sticky-offset);
}

.site-header--scrolled {
    color: var(--navbar-scrolled-text);
    background: var(--navbar-scrolled-background);
    box-shadow: 0 8px 30px rgb(15 23 42 / 10%);
}

.header-shell {
    width: min(100%, var(--navbar-content-width));
    min-height: var(--navbar-height);
    padding-right: var(--navbar-horizontal-padding);
    padding-left: var(--navbar-horizontal-padding);
    border-width: var(--navbar-layout-border-width);
    border-color: var(--navbar-layout-border-color);
    border-radius: var(--navbar-layout-radius);
}

.site-header--full-width .header-shell {
    max-width: none;
}

.site-header--shadow-none .header-shell {
    box-shadow: none;
}

.site-header--shadow-small .header-shell {
    box-shadow: 0 6px 20px rgb(15 23 42 / 7%);
}

.site-header--shadow-medium .header-shell {
    box-shadow: 0 14px 40px rgb(15 23 42 / 12%);
}

.site-header--shadow-large .header-shell {
    box-shadow: 0 24px 70px rgb(15 23 42 / 18%);
}

.logo-image {
    display: block;
    width: var(--navbar-brand-width);
    max-width: 100%;
    height: auto;
    max-height: calc(var(--navbar-height) - 20px);
    object-fit: contain;
}

.nav-link-active {
    color: var(--navbar-active-color);
}

.simple-dropdown-link-active,
.mega-link-active,
.mobile-link-active,
.mobile-sublink-active {
    color: var(--navbar-active-color);
    background: color-mix(in srgb, var(--navbar-active-color) 10%, transparent);
    opacity: 1;
}

.nav-link:focus-visible,
.action-button:focus-visible,
.mobile-toggle:focus-visible,
.mobile-link:focus-visible,
.mega-link:focus-visible {
    outline: 2px solid var(--navbar-focus-color);
    outline-offset: 3px;
}

.nav-link-disabled,
.action-button[aria-disabled='true'] {
    pointer-events: none;
    cursor: not-allowed;
    opacity: var(--navbar-disabled-opacity);
}

.site-header--hover-none .nav-link:hover {
    color: inherit;
    transform: none;
}

.site-header--hover-underline .nav-link::after {
    position: absolute;
    right: 0;
    bottom: 0.45rem;
    left: 0;
    height: 2px;
    content: '';
    background: var(--navbar-link-hover-color);
    transform: scaleX(0);
    transform-origin: right;
    transition: transform 180ms ease;
}

.site-header--hover-underline .nav-link:hover::after,
.site-header--hover-underline .nav-link-active::after {
    transform: scaleX(1);
    transform-origin: left;
}

.site-header--hover-background .nav-link {
    padding-inline: 0.7rem;
    border-radius: calc(var(--navbar-layout-radius) * 0.55);
}

.site-header--hover-background .nav-link:hover {
    background: color-mix(
        in srgb,
        var(--navbar-link-hover-color) 12%,
        transparent
    );
}

.site-header--hover-lift .nav-link:hover {
    transform: translateY(-3px);
}

.nav-item {
    position: relative;
}

.simple-dropdown {
    position: absolute;
    top: calc(100% - 0.25rem);
    left: 50%;
    z-index: 60;
    display: none;
    min-width: 12rem;
    padding: 0.45rem;
    color: var(--theme-text, #18181b);
    background: var(--theme-bg, #ffffff);
    border: 1px solid color-mix(in srgb, var(--theme-text) 12%, transparent);
    border-radius: var(--navbar-layout-radius);
    box-shadow: 0 18px 45px rgb(15 23 42 / 16%);
    transform: translateX(-50%);
}

.nav-item:hover .simple-dropdown,
.nav-item:focus-within .simple-dropdown {
    display: grid;
}

.simple-dropdown-link {
    padding: 0.7rem 0.8rem;
    color: inherit;
    background: transparent;
    border: 0;
    border-radius: calc(var(--navbar-layout-radius) * 0.65);
    font: inherit;
    font-size: 0.78rem;
    text-align: left;
    text-decoration: none;
}

.simple-dropdown-link:hover {
    color: var(--navbar-link-hover-color);
    background: color-mix(in srgb, var(--theme-primary) 8%, transparent);
}

.action-button,
.mobile-cta {
    border-radius: var(--action-radius, var(--theme-border-radius, 8px));
}

.action-button--primary,
.mobile-cta--primary {
    color: var(--action-text);
    background: var(--action-background);
    border-color: var(--action-border);
}

.action-button--primary:hover,
.mobile-cta--primary:hover {
    color: var(--action-hover-text);
    background: var(--action-hover-background);
}

.action-button--outline {
    color: var(--action-text, inherit);
    border-color: var(--action-border, currentColor);
}

.action-button--small {
    min-height: 2.35rem;
    padding-inline: 0.8rem;
    font-size: 0.72rem;
}

.action-button--medium {
    min-height: 2.85rem;
}

.action-button--large {
    min-height: 3.25rem;
    padding-inline: 1.35rem;
    font-size: 0.88rem;
}

.action-button--animate-none:hover {
    filter: none;
    transform: none;
}

.action-button--animate-color:hover {
    filter: none;
    transform: none;
}

.action-button--animate-scale:hover {
    filter: none;
    transform: scale(1.04);
}

.action-button--animate-arrow:hover .action-icon {
    transform: translateX(0.25rem);
}

.action-icon {
    transition: transform 180ms ease;
}

.mega-panel {
    grid-template-columns: auto minmax(0, 1fr);
}

.mega-panel--content {
    right: auto;
    left: 50%;
    width: min(760px, calc(100vw - 2rem));
    transform: translateX(-50%);
}

.mega-panel--wide {
    right: 0;
    left: 0;
}

.mega-panel--full {
    right: 50%;
    left: 50%;
    width: 100vw;
    transform: translateX(-50%);
    border-radius: 0;
}

.mega-panel--left {
    right: auto;
    left: 0;
}

.mega-panel--right {
    right: 0;
    left: auto;
}

.mega-panel--fade {
    animation-name: panel-fade;
}

.mega-panel--scale {
    animation-name: panel-scale;
}

.mega-featured {
    display: flex;
    min-width: 13rem;
    min-height: 11rem;
    flex-direction: column;
    justify-content: flex-end;
    padding: 1.1rem;
    gap: 0.4rem;
    color: white;
    background-color: var(--theme-primary, #4f46e5);
    background-position: center;
    background-size: cover;
    border-radius: var(--theme-border-radius, 8px);
    text-decoration: none;
}

.mega-featured strong {
    font-family: var(--theme-font-heading, sans-serif);
}

.mega-featured p,
.mega-group-heading p {
    margin: 0;
    font-size: 0.7rem;
    line-height: 1.45;
    opacity: 0.72;
}

.mega-groups {
    display: grid;
    min-width: 0;
    gap: 0.75rem;
}

.mega-group {
    display: flex;
    min-width: 0;
    flex-direction: column;
    gap: 0.45rem;
}

.mega-group-heading {
    min-height: 2.75rem;
    padding: 0.25rem 0.45rem;
}

.mega-group-heading strong {
    font-size: 0.72rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.mega-link {
    grid-template-columns: 1fr auto;
    min-height: 3.2rem;
}

.site-header--responsive-mobile {
    padding: 0.7rem;
}

.site-header--responsive-mobile .header-shell {
    display: flex;
    min-height: 4.25rem;
    padding: 0.5rem 0.55rem 0.5rem 1rem;
}

.site-header--responsive-mobile .desktop-navigation,
.site-header--responsive-mobile .desktop-action {
    display: none;
}

.site-header--responsive-mobile .header-actions {
    order: 2;
    margin-left: auto;
}

.site-header--responsive-mobile .mobile-toggle {
    position: relative;
    z-index: 70;
    display: inline-flex;
}

.site-header--responsive-mobile .mobile-panel {
    position: absolute;
    top: calc(100% + 0.55rem);
    right: 0;
    left: 0;
    z-index: 50;
    padding: 0.75rem;
    gap: 0.75rem;
    color: var(--theme-text, #0f172a);
    background: var(--theme-bg, #ffffff);
    border: 1px solid color-mix(in srgb, var(--theme-text) 12%, transparent);
    border-radius: var(--navbar-layout-radius);
    box-shadow: 0 20px 50px rgb(15 23 42 / 18%);
}

.site-header--mobile-drawer.site-header--responsive-mobile .mobile-panel {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    left: auto;
    width: min(88vw, 24rem);
    padding: 5rem 1rem 1rem;
    border-radius: 0;
}

.site-header--mobile-fullscreen.site-header--responsive-mobile .mobile-panel {
    position: fixed;
    inset: 0;
    padding: 5rem 1.5rem 1.5rem;
    border-radius: 0;
}

.site-header--responsive-mobile .mobile-list {
    display: grid;
    padding: 0;
    margin: 0;
    gap: 0.25rem;
    list-style: none;
}

.mobile-submenu {
    display: grid;
    padding: 0 0.75rem 0.5rem 2.6rem;
    gap: 0.2rem;
}

.mobile-sublink {
    padding: 0.55rem 0.7rem;
    color: inherit;
    background: transparent;
    border: 0;
    border-left: 1px solid color-mix(in srgb, currentColor 18%, transparent);
    font: inherit;
    font-size: 0.78rem;
    text-align: left;
    text-decoration: none;
    opacity: 0.72;
}

.mobile-sublink:hover {
    color: var(--navbar-link-hover-color);
    opacity: 1;
}

.site-header--responsive-mobile .mobile-link {
    display: flex;
    width: 100%;
    align-items: center;
    padding: 0.9rem;
    gap: 0.85rem;
    color: inherit;
    background: transparent;
    border: 0;
    border-radius: var(--theme-border-radius, 8px);
    font: inherit;
    font-size: 0.9rem;
    font-weight: 650;
    text-align: left;
    text-decoration: none;
}

.site-header--responsive-mobile .mobile-actions {
    display: grid;
}

.site-header--hide-mobile-actions .mobile-actions,
.site-header--brand-hidden-mobile.site-header--responsive-mobile .site-logo {
    display: none;
}

.site-header--responsive-mobile .logo-image {
    width: var(--navbar-brand-mobile-width);
}

.site-header--responsive-mobile:has(.mobile-panel) .mobile-list {
    text-align: left;
}

.site-header--mobile-align-center .mobile-link,
.site-header--mobile-align-center .mobile-cta {
    justify-content: center;
    text-align: center;
}

.site-header--responsive-mobile .mobile-panel[style*='display: none'] {
    display: none !important;
}

@keyframes panel-fade {
    from {
        opacity: 0;
    }
}

@keyframes panel-scale {
    from {
        opacity: 0;
        transform: scale(0.94);
    }
}

@media (prefers-reduced-motion: reduce) {
    .header-shell,
    .mega-panel,
    .mobile-panel {
        animation: none;
    }

    .nav-link,
    .action-button,
    .mobile-toggle,
    .mega-link {
        transition: none;
    }
}
</style>
