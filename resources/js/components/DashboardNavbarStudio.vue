<script setup lang="ts">
import { useHttp } from '@inertiajs/vue3';
import {
    ArrowDown,
    ArrowUp,
    Check,
    ChevronRight,
    Copy,
    FileText,
    FolderOpen,
    GripVertical,
    History,
    Image,
    Laptop,
    Loader2,
    Monitor,
    Plus,
    Redo2,
    RotateCcw,
    Save,
    Smartphone,
    Trash2,
    Undo2,
} from '@lucide/vue';
import { computed, nextTick, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

import { update as updateNavigation } from '@/actions/App/Http/Controllers/TenantNavigationController';
import MediaPicker from '@/components/MediaPicker.vue';
import SiteHeader from '@/components/SiteHeader.vue';
import { useTheme } from '@/lib/theme';
import type {
    NavbarPreset,
    NavigationAction,
    NavigationConfig,
    NavigationHeaderConfig,
    NavigationItem,
} from '@/types/navigation';

interface ThemeConfig {
    colors?: {
        primary?: string;
        secondary?: string;
        background?: string;
        text?: string;
    };
    typography?: {
        headingFont?: string;
        bodyFont?: string;
    };
    borderRadius?: string;
}

interface NavbarVariant {
    label: string;
    description: string;
    category: string;
}

type NavbarSurfaceModeKey = 'design' | 'transparent' | 'theme' | 'custom';

interface NavbarSurfaceMode {
    label: string;
    description: string;
}

interface NavigationOption {
    label: string;
    description: string;
}

interface NavigationSaveResponse {
    status?: string;
    message?: string;
    navigation_config?: NavigationConfig;
}

type PreviewMode = 'desktop' | 'tablet' | 'mobile';
type OutlineTargetType =
    | 'brand'
    | 'item'
    | 'buttons'
    | 'design'
    | 'appearance'
    | 'mobile'
    | 'mega';

interface OutlineTarget {
    type: OutlineTargetType;
    id?: string | null;
}

const props = defineProps<{
    tenantName: string;
    navigationConfig?: NavigationConfig | null;
    themeConfig?: ThemeConfig | null;
    defaultVariant: string;
    defaultMenuMode: 'simple' | 'mega';
    variants: Record<string, NavbarVariant>;
    surfaceModes: Record<NavbarSurfaceModeKey, NavbarSurfaceMode>;
    menuModes: Record<'simple' | 'mega', NavigationOption>;
    actionPositions: Record<'start' | 'end', NavigationOption>;
    actionVariants: Record<'primary' | 'outline' | 'text', string>;
    pages: Array<{ slug: string; title: string }>;
}>();

const emit = defineEmits<{
    saved: [navigationConfig: NavigationConfig];
}>();

const sampleItems: NavigationItem[] = [
    { label: 'Home', type: 'internal', slug: 'home' },
    { label: 'Services', type: 'internal', slug: 'services' },
    { label: 'About', type: 'internal', slug: 'about' },
    { label: 'Journal', type: 'internal', slug: 'journal' },
];

const initialNavigation: NavigationConfig = props.navigationConfig
    ? JSON.parse(JSON.stringify(props.navigationConfig))
    : {
          header: {
              variant: props.defaultVariant,
              showLogo: true,
              items: [],
              ctaButton: {
                  show: false,
                  label: 'Contact us',
                  slug: 'contact',
              },
          },
          footer: { copyright: '' },
      };

initialNavigation.header ??= {};
initialNavigation.footer ??= {};
const legacyMegaMenu = initialNavigation.header.variant === 'mega-menu';

if (legacyMegaMenu) {
    initialNavigation.header.variant = 'classic-inline';
}

initialNavigation.header.variant ??= props.defaultVariant;
initialNavigation.header.menu ??= {
    mode: legacyMegaMenu ? 'mega' : props.defaultMenuMode,
    triggerLabel: 'Explore',
};
initialNavigation.header.menu.mode ??= props.defaultMenuMode;
initialNavigation.header.menu.triggerLabel ??= 'Explore';
initialNavigation.header.surface ??= {
    mode: 'design',
    backgroundColor: '#ffffff',
    textColor: '#18181b',
};

if (initialNavigation.header.surface.mode === 'transparent') {
    initialNavigation.header.surface.mode = 'design';
}

initialNavigation.header.surface.mode ??= 'design';
initialNavigation.header.surface.backgroundColor ??= '#ffffff';
initialNavigation.header.surface.textColor ??= '#18181b';
initialNavigation.header.actionPosition ??= 'end';
initialNavigation.header.actionStyle ??= {
    mode: 'theme',
    backgroundColor: props.themeConfig?.colors?.primary ?? '#18181b',
    textColor: props.themeConfig?.colors?.background ?? '#ffffff',
};
initialNavigation.header.actionStyle.mode ??= 'theme';
initialNavigation.header.actionStyle.backgroundColor ??=
    props.themeConfig?.colors?.primary ?? '#18181b';
initialNavigation.header.actionStyle.textColor ??=
    props.themeConfig?.colors?.background ?? '#ffffff';
initialNavigation.header.actionStyle.hoverBackgroundColor ??=
    props.themeConfig?.colors?.secondary ?? '#3f3f46';
initialNavigation.header.actionStyle.hoverTextColor ??=
    props.themeConfig?.colors?.background ?? '#ffffff';
initialNavigation.header.linkStyle ??= {
    mode: 'theme',
    color: props.themeConfig?.colors?.text ?? '#18181b',
    hoverColor: props.themeConfig?.colors?.primary ?? '#18181b',
};
initialNavigation.header.linkStyle.mode ??= 'theme';
initialNavigation.header.linkStyle.color ??=
    props.themeConfig?.colors?.text ?? '#18181b';
initialNavigation.header.linkStyle.hoverColor ??=
    props.themeConfig?.colors?.primary ?? '#18181b';
initialNavigation.header.linkStyle.hoverEffect ??= 'color';
initialNavigation.header.brand ??= {
    type: 'text',
    text: props.tenantName,
    imageUrl: '',
    alt: `${props.tenantName} logo`,
    width: 120,
    mobileWidth: 96,
    hideOnMobile: false,
};
initialNavigation.header.layout ??= {
    contentWidth: 1200,
    height: 72,
    horizontalPadding: 24,
    position: 'static',
    stickyOffset: 0,
    fullWidth: false,
    borderWidth: 1,
    borderColor: '#e4e4e7',
    borderRadius: 16,
    shadow: 'small',
};
initialNavigation.header.responsive ??= {
    breakpoint: 720,
    menuStyle: 'dropdown',
    menuIcon: 'menu',
    alignment: 'left',
    showActions: true,
};
initialNavigation.header.states ??= {
    activeColor: props.themeConfig?.colors?.primary ?? '#18181b',
    focusColor: props.themeConfig?.colors?.primary ?? '#18181b',
    disabledOpacity: 40,
    scrolledBackgroundColor: '#ffffff',
    scrolledTextColor: '#18181b',
};
initialNavigation.header.menu.openOn ??= 'click';
initialNavigation.header.menu.alignment ??= 'center';
initialNavigation.header.menu.width ??= 'wide';
initialNavigation.header.menu.columns ??= 3;
initialNavigation.header.menu.animation ??= 'slide';
initialNavigation.header.menu.groups ??= [
    { id: 'main', label: 'Main navigation', description: '' },
];
initialNavigation.header.menu.featured ??= {
    show: false,
    title: 'Featured',
    description: 'Highlight an important destination.',
    imageUrl: '',
    href: '',
};
initialNavigation.header.actions ??= initialNavigation.header.ctaButton?.show
    ? [
          {
              label:
                  initialNavigation.header.ctaButton.label || 'Start a project',
              type: 'internal',
              slug: initialNavigation.header.ctaButton.slug || 'contact',
              variant: 'primary',
          },
      ]
    : [];
initialNavigation.header.items ??= [];
initialNavigation.presets ??= [];

for (const [index, item] of initialNavigation.header.items.entries()) {
    item.id ??= `item-${Date.now()}-${index}`;
    item.megaGroup ??= 'main';
    item.children ??= [];

    for (const [childIndex, child] of item.children.entries()) {
        child.id ??= `item-${Date.now()}-${index}-${childIndex}`;
        child.type ??= 'internal';
    }
}

for (const [index, action] of initialNavigation.header.actions.entries()) {
    action.id ??= `action-${Date.now()}-${index}`;
    action.type ??= 'internal';
    action.variant ??= 'primary';
    action.icon ??= 'arrow';
    action.iconPosition ??= 'end';
    action.size ??= 'medium';
    action.animation ??= 'lift';
    action.borderRadius ??= 8;
    action.backgroundColor ??=
        initialNavigation.header.actionStyle.backgroundColor;
    action.textColor ??=
        action.variant === 'primary'
            ? initialNavigation.header.actionStyle.textColor
            : (props.themeConfig?.colors?.text ?? '#18181b');
    action.hoverBackgroundColor ??=
        initialNavigation.header.actionStyle.hoverBackgroundColor;
    action.hoverTextColor ??=
        initialNavigation.header.actionStyle.hoverTextColor;
    action.borderColor ??= props.themeConfig?.colors?.primary ?? '#18181b';
}

const navigationHttp = useHttp<
    { navigation_config: NavigationConfig },
    NavigationSaveResponse
>({
    navigation_config: initialNavigation,
});

const outlineSections: Array<{
    type: Exclude<OutlineTargetType, 'item'>;
    label: string;
}> = [
    { type: 'brand', label: 'Brand' },
    { type: 'design', label: 'Design style' },
    { type: 'appearance', label: 'Appearance' },
    { type: 'buttons', label: 'Buttons' },
    { type: 'mega', label: 'Mega menu' },
    { type: 'mobile', label: 'Mobile' },
];
const selectedTarget = ref<OutlineTarget>({
    type: initialNavigation.header.items?.[0]?.id ? 'item' : 'design',
    id: initialNavigation.header.items?.[0]?.id ?? null,
});
const previewMode = ref<PreviewMode>('desktop');
const selectedItemId = ref<string | null>(
    initialNavigation.header.items?.[0]?.id ?? null,
);
const pageToAdd = ref(props.pages[0]?.slug ?? '');
const draggedNavigationItemId = ref<string | null>(null);
const draggedMegaItemId = ref<string | null>(null);
const showAdvancedAppearance = ref(false);
const showAdvancedButtons = ref(false);
const showAdvancedMega = ref(false);
const menuItemsExpanded = ref(true);
const previewWidth = computed(() => {
    if (previewMode.value === 'mobile') {
        return '390px';
    }

    if (previewMode.value === 'tablet') {
        return '760px';
    }

    return '1080px';
});

const variantEntries = computed(() => Object.entries(props.variants));
const surfaceEntries = computed<
    Array<[NavbarSurfaceModeKey, NavbarSurfaceMode]>
>(() =>
    (
        Object.entries(props.surfaceModes) as Array<
            [NavbarSurfaceModeKey, NavbarSurfaceMode]
        >
    ).filter(([key]) => key !== 'transparent'),
);
const actionPositionEntries = computed<
    Array<['start' | 'end', NavigationOption]>
>(
    () =>
        Object.entries(props.actionPositions) as Array<
            ['start' | 'end', NavigationOption]
        >,
);
const actionVariantEntries = computed<
    Array<['primary' | 'outline' | 'text', string]>
>(
    () =>
        Object.entries(props.actionVariants) as Array<
            ['primary' | 'outline' | 'text', string]
        >,
);
const selectedVariant = computed(
    () =>
        navigationHttp.navigation_config.header.variant ?? props.defaultVariant,
);
const selectedSurfaceMode = computed(
    () => navigationHttp.navigation_config.header.surface?.mode ?? 'design',
);
const selectedMenuMode = computed(
    () => navigationHttp.navigation_config.header.menu?.mode ?? 'simple',
);
const selectedItemLocation = computed(() => {
    const items = navigationHttp.navigation_config.header.items ?? [];

    for (const [itemIndex, item] of items.entries()) {
        if (item.id === selectedItemId.value) {
            return { item, itemIndex, parentIndex: null, childIndex: null };
        }

        const childIndex = item.children?.findIndex(
            (child) => child.id === selectedItemId.value,
        );

        if (childIndex !== undefined && childIndex >= 0 && item.children) {
            return {
                item: item.children[childIndex],
                itemIndex,
                parentIndex: itemIndex,
                childIndex,
            };
        }
    }

    return null;
});
const selectedNavigationItem = computed(
    () => selectedItemLocation.value?.item ?? null,
);
const selectedItemIsTopLevel = computed(
    () => selectedItemLocation.value?.parentIndex === null,
);
const actionCount = computed(
    () => navigationHttp.navigation_config.header.actions?.length ?? 0,
);
const canUndo = computed(() => undoStack.value.length > 0);
const canRedo = computed(() => redoStack.value.length > 0);
const showMediaPicker = ref(false);
const mediaTarget = ref<'brand' | 'featured'>('brand');
const undoStack = ref<string[]>([]);
const redoStack = ref<string[]>([]);
const historyPaused = ref(false);
const savedHeader = ref(JSON.stringify(initialNavigation.header));
const compareWithSaved = ref(false);
let previousHeaderSnapshot = JSON.stringify(initialNavigation.header);
let historyTimer: ReturnType<typeof setTimeout> | undefined;
const usesSampleContent = computed(
    () => !navigationHttp.navigation_config.header.items?.length,
);
const previewNavigationConfig = computed<NavigationConfig>(() => {
    const header = compareWithSaved.value
        ? (JSON.parse(savedHeader.value) as NavigationHeaderConfig)
        : navigationHttp.navigation_config.header;

    return {
        ...navigationHttp.navigation_config,
        header: {
            ...header,
            variant: selectedVariant.value,
            showLogo: header.showLogo !== false,
            items: header.items?.length ? header.items : sampleItems,
            ctaButton: header.ctaButton?.show
                ? { ...header.ctaButton, show: false }
                : header.ctaButton,
            actions: header.actions ?? [],
        },
    };
});

const accessibilityWarnings = computed<string[]>(() => {
    const header = navigationHttp.navigation_config.header;
    const warnings: string[] = [];

    if (header.items?.some((item) => !item.label.trim())) {
        warnings.push('Every navigation link needs a visible label.');
    }

    if (
        header.items?.some(
            (item) => item.type === 'external' && !item.href?.trim(),
        )
    ) {
        warnings.push('External navigation links need a valid URL.');
    }

    if (header.actions?.some((action) => action.size === 'small')) {
        warnings.push(
            'Small buttons may not meet the 44px touch-target guideline.',
        );
    }

    if (header.brand?.type === 'image' && !header.brand.alt?.trim()) {
        warnings.push('Image logos need alternative text.');
    }

    if (
        header.actions?.some(
            (action) =>
                action.variant === 'primary' &&
                colorContrast(
                    action.backgroundColor ??
                        header.actionStyle?.backgroundColor ??
                        '#18181b',
                    action.textColor ??
                        header.actionStyle?.textColor ??
                        '#ffffff',
                ) < 4.5,
        )
    ) {
        warnings.push('A filled button has text contrast below 4.5:1.');
    }

    if (
        header.surface?.mode === 'custom' &&
        colorContrast(
            header.surface.backgroundColor ?? '#ffffff',
            header.surface.textColor ?? '#18181b',
        ) < 4.5
    ) {
        warnings.push('Navbar text contrast is below the recommended 4.5:1.');
    }

    return warnings;
});

const { cssVars } = useTheme(() => props.themeConfig);

function selectVariant(variant: string): void {
    navigationHttp.navigation_config.header.variant = variant;
}

function selectSurfaceMode(mode: NavbarSurfaceModeKey): void {
    if (!navigationHttp.navigation_config.header.surface) {
        return;
    }

    navigationHttp.navigation_config.header.surface.mode = mode;
}

function selectMenuMode(mode: 'simple' | 'mega'): void {
    if (!navigationHttp.navigation_config.header.menu) {
        return;
    }

    navigationHttp.navigation_config.header.menu.mode = mode;
}

function addAction(): void {
    const actions = navigationHttp.navigation_config.header.actions;

    if (!actions || actions.length >= 3) {
        return;
    }

    const isPrimary = actions.length === 0;

    actions.push({
        id: `action-${Date.now()}`,
        label: `Action ${actions.length + 1}`,
        type: 'internal',
        slug: 'home',
        variant: isPrimary ? 'primary' : 'outline',
        icon: 'arrow',
        iconPosition: 'end',
        size: 'medium',
        animation: 'lift',
        borderRadius: 8,
        backgroundColor:
            navigationHttp.navigation_config.header.actionStyle
                ?.backgroundColor ?? '#18181b',
        textColor: isPrimary
            ? (navigationHttp.navigation_config.header.actionStyle?.textColor ??
              '#ffffff')
            : (props.themeConfig?.colors?.text ?? '#18181b'),
        hoverBackgroundColor:
            navigationHttp.navigation_config.header.actionStyle
                ?.hoverBackgroundColor ?? '#3f3f46',
        hoverTextColor:
            navigationHttp.navigation_config.header.actionStyle
                ?.hoverTextColor ?? '#ffffff',
        borderColor: props.themeConfig?.colors?.primary ?? '#18181b',
    });
}

function addNavigationItem(parentIndex?: number): void {
    const item: NavigationItem = {
        id: `item-${Date.now()}`,
        label: parentIndex === undefined ? 'New link' : 'Nested link',
        type: 'internal',
        slug: props.pages[0]?.slug ?? 'home',
        megaGroup: 'main',
        children: [],
    };

    if (parentIndex === undefined) {
        navigationHttp.navigation_config.header.items?.push(item);
    } else {
        navigationHttp.navigation_config.header.items?.[
            parentIndex
        ].children?.push(item);
    }

    selectedItemId.value = item.id ?? null;
    selectedTarget.value = { type: 'item', id: item.id ?? null };
}

function addExistingPage(): void {
    const page = props.pages.find(
        (candidate) => candidate.slug === pageToAdd.value,
    );

    if (!page) {
        return;
    }

    const item: NavigationItem = {
        id: `item-${Date.now()}-${page.slug}`,
        label: page.title,
        type: 'internal',
        slug: page.slug,
        megaGroup:
            navigationHttp.navigation_config.header.menu?.groups?.[0]?.id ??
            'main',
        children: [],
    };

    navigationHttp.navigation_config.header.items?.push(item);
    selectedItemId.value = item.id ?? null;
    selectedTarget.value = { type: 'item', id: item.id ?? null };
}

function addDropdownItem(): void {
    const item: NavigationItem = {
        id: `item-${Date.now()}-dropdown`,
        label: 'Dropdown',
        type: 'internal',
        slug: props.pages[0]?.slug ?? 'home',
        megaGroup:
            navigationHttp.navigation_config.header.menu?.groups?.[0]?.id ??
            'main',
        children: [
            {
                id: `item-${Date.now()}-dropdown-child`,
                label: props.pages[0]?.title ?? 'New page',
                type: 'internal',
                slug: props.pages[0]?.slug ?? 'home',
            },
        ],
    };

    navigationHttp.navigation_config.header.items?.push(item);
    selectedItemId.value = item.id ?? null;
    selectedTarget.value = { type: 'item', id: item.id ?? null };
}

function selectNavigationItem(item: NavigationItem): void {
    selectedItemId.value = item.id ?? null;
    selectedTarget.value = { type: 'item', id: item.id ?? null };
}

function selectOutlineTarget(
    type: OutlineTargetType,
    id?: string | null,
): void {
    selectedTarget.value = { type, id: id ?? null };

    if (type === 'item' && id) {
        selectedItemId.value = id;
    }
}

function handlePreviewSelect(target: { type: string; id?: string }): void {
    if (target.type === 'brand') {
        selectOutlineTarget('brand');
    } else if (target.type === 'item') {
        selectOutlineTarget('item', target.id ?? null);
    } else if (target.type === 'button') {
        selectOutlineTarget('buttons', target.id ?? null);
    } else if (target.type === 'mobile') {
        selectOutlineTarget('mobile');
    } else if (target.type === 'mega') {
        selectOutlineTarget('mega');
    }
}

function nestSelectedItem(): void {
    const location = selectedItemLocation.value;
    const items = navigationHttp.navigation_config.header.items;

    if (!location || location.parentIndex !== null || !items) {
        return;
    }

    if (location.itemIndex === 0) {
        return;
    }

    const [item] = items.splice(location.itemIndex, 1);
    const parent = items[location.itemIndex - 1];

    parent.children ??= [];
    parent.children.push(item);
    selectedItemId.value = item.id ?? null;
    selectedTarget.value = { type: 'item', id: item.id ?? null };
}

function unnestSelectedItem(): void {
    const location = selectedItemLocation.value;
    const items = navigationHttp.navigation_config.header.items;

    if (
        !location ||
        location.parentIndex === null ||
        location.childIndex === null ||
        !items
    ) {
        return;
    }

    const parent = items[location.parentIndex];
    const [child] = parent.children?.splice(location.childIndex, 1) ?? [];

    if (!child) {
        return;
    }

    items.splice(location.parentIndex + 1, 0, child);
    selectedItemId.value = child.id ?? null;
    selectedTarget.value = { type: 'item', id: child.id ?? null };
}

function setSelectedItemKind(kind: 'link' | 'dropdown'): void {
    const location = selectedItemLocation.value;

    if (!location || location.parentIndex !== null) {
        return;
    }

    if (kind === 'dropdown' && !location.item.children?.length) {
        location.item.children = [
            {
                id: `item-${Date.now()}-child`,
                label: props.pages[0]?.title ?? 'New page',
                type: 'internal',
                slug: props.pages[0]?.slug ?? 'home',
            },
        ];
    } else if (kind === 'link') {
        location.item.children = [];
    }
}

function removeSelectedNavigationItem(): void {
    const location = selectedItemLocation.value;
    const items = navigationHttp.navigation_config.header.items;

    if (!location || !items) {
        return;
    }

    if (location.parentIndex === null) {
        items.splice(location.itemIndex, 1);
    } else if (location.childIndex !== null) {
        items[location.parentIndex]?.children?.splice(location.childIndex, 1);
    }

    selectedItemId.value = items[0]?.id ?? null;
}

function moveSelectedNavigationItem(offset: -1 | 1): void {
    const location = selectedItemLocation.value;

    if (!location || location.parentIndex !== null) {
        return;
    }

    moveItem(
        navigationHttp.navigation_config.header.items,
        location.itemIndex,
        offset,
    );
}

function startNavigationDrag(event: DragEvent, itemId: string): void {
    draggedNavigationItemId.value = itemId;

    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', itemId);
    }
}

function dropNavigationItem(targetItemId: string): void {
    const items = navigationHttp.navigation_config.header.items;
    const sourceItemId = draggedNavigationItemId.value;

    if (!items || !sourceItemId || sourceItemId === targetItemId) {
        draggedNavigationItemId.value = null;

        return;
    }

    const sourceIndex = items.findIndex((item) => item.id === sourceItemId);
    const targetIndex = items.findIndex((item) => item.id === targetItemId);

    if (sourceIndex < 0 || targetIndex < 0) {
        draggedNavigationItemId.value = null;

        return;
    }

    const [item] = items.splice(sourceIndex, 1);
    items.splice(targetIndex, 0, item);
    draggedNavigationItemId.value = null;
}

function removeNavigationItem(index: number): void {
    if (
        navigationHttp.navigation_config.header.items?.[index]?.id ===
        selectedItemId.value
    ) {
        selectedItemId.value = null;
    }

    navigationHttp.navigation_config.header.items?.splice(index, 1);
}

function moveItem<T>(
    items: T[] | undefined,
    index: number,
    offset: -1 | 1,
): void {
    if (!items) {
        return;
    }

    const destination = index + offset;

    if (destination < 0 || destination >= items.length) {
        return;
    }

    const [item] = items.splice(index, 1);
    items.splice(destination, 0, item);
}

function removeAction(index: number): void {
    navigationHttp.navigation_config.header.actions?.splice(index, 1);
}

function duplicateAction(index: number): void {
    const actions = navigationHttp.navigation_config.header.actions;

    if (!actions || actions.length >= 3) {
        return;
    }

    const action = JSON.parse(
        JSON.stringify(actions[index]),
    ) as NavigationAction;
    action.id = `action-${Date.now()}`;
    action.label = `${action.label} copy`;
    actions.splice(index + 1, 0, action);
}

function addMegaGroup(): void {
    const groups = navigationHttp.navigation_config.header.menu?.groups;

    if (!groups || groups.length >= 4) {
        return;
    }

    groups.push({
        id: `group-${Date.now()}`,
        label: `Group ${groups.length + 1}`,
        description: '',
    });
}

function megaGroupItems(
    groupId: string,
): Array<{ item: NavigationItem; itemIndex: number }> {
    return (navigationHttp.navigation_config.header.items ?? [])
        .map((item, itemIndex) => ({ item, itemIndex }))
        .filter(({ item }) => item.megaGroup === groupId);
}

function addMegaGroupItem(groupId: string): void {
    navigationHttp.navigation_config.header.items?.push({
        id: `item-${Date.now()}-${groupId}`,
        label: 'New link',
        type: 'internal',
        slug: props.pages[0]?.slug ?? 'home',
        megaGroup: groupId,
        children: [],
    });
}

function moveMegaGroupItem(
    groupId: string,
    itemIndex: number,
    offset: -1 | 1,
): void {
    const items = navigationHttp.navigation_config.header.items;

    if (!items) {
        return;
    }

    const groupItemIndexes = items
        .map((item, index) => ({ item, index }))
        .filter(({ item }) => item.megaGroup === groupId)
        .map(({ index }) => index);
    const currentGroupIndex = groupItemIndexes.indexOf(itemIndex);
    const destinationIndex = groupItemIndexes[currentGroupIndex + offset];

    if (currentGroupIndex === -1 || destinationIndex === undefined) {
        return;
    }

    [items[itemIndex], items[destinationIndex]] = [
        items[destinationIndex],
        items[itemIndex],
    ];
}

function startMegaItemDrag(event: DragEvent, itemId: string): void {
    draggedMegaItemId.value = itemId;

    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', itemId);
    }
}

function dropMegaItemInGroup(groupId: string): void {
    const item = navigationHttp.navigation_config.header.items?.find(
        (candidate) => candidate.id === draggedMegaItemId.value,
    );

    if (item) {
        item.megaGroup = groupId;
    }

    draggedMegaItemId.value = null;
}

function removeMegaGroup(index: number): void {
    const menu = navigationHttp.navigation_config.header.menu;
    const removedGroup = menu?.groups?.[index];

    if (!menu?.groups || !removedGroup || menu.groups.length === 1) {
        return;
    }

    menu.groups.splice(index, 1);
    const fallbackGroup = menu.groups[0]?.id;

    for (const item of navigationHttp.navigation_config.header.items ?? []) {
        if (item.megaGroup === removedGroup.id) {
            item.megaGroup = fallbackGroup;
        }
    }
}

function openMediaPicker(target: 'brand' | 'featured'): void {
    mediaTarget.value = target;
    showMediaPicker.value = true;
}

function onMediaSelected(item: { url: string }): void {
    const header = navigationHttp.navigation_config.header;

    if (mediaTarget.value === 'brand' && header.brand) {
        header.brand.imageUrl = item.url;
        header.brand.type = 'image';
    } else if (header.menu?.featured) {
        header.menu.featured.imageUrl = item.url;
    }

    showMediaPicker.value = false;
}

function undo(): void {
    const snapshot = undoStack.value.pop();

    if (!snapshot) {
        return;
    }

    redoStack.value.push(
        JSON.stringify(navigationHttp.navigation_config.header),
    );
    restoreHeader(snapshot);
}

function redo(): void {
    const snapshot = redoStack.value.pop();

    if (!snapshot) {
        return;
    }

    undoStack.value.push(
        JSON.stringify(navigationHttp.navigation_config.header),
    );
    restoreHeader(snapshot);
}

function restoreHeader(snapshot: string): void {
    historyPaused.value = true;
    navigationHttp.navigation_config.header = JSON.parse(
        snapshot,
    ) as NavigationHeaderConfig;
    previousHeaderSnapshot = snapshot;
    void nextTick(() => {
        historyPaused.value = false;
    });
}

function savePreset(): void {
    const presets = navigationHttp.navigation_config.presets ?? [];

    if (presets.length >= 10) {
        toast.error('You can save up to ten navbar presets');

        return;
    }

    presets.push({
        id: `preset-${Date.now()}`,
        name: `Navbar preset ${presets.length + 1}`,
        header: JSON.parse(
            JSON.stringify(navigationHttp.navigation_config.header),
        ) as NavigationHeaderConfig,
    });
    navigationHttp.navigation_config.presets = presets;
    toast.success('Navbar preset added');
}

function applyPreset(preset: NavbarPreset): void {
    restoreHeader(JSON.stringify(preset.header));
}

function removePreset(index: number): void {
    navigationHttp.navigation_config.presets?.splice(index, 1);
}

function resetCurrentPanel(): void {
    const header = navigationHttp.navigation_config.header;
    const target = selectedTarget.value.type;

    if (target === 'brand') {
        header.brand = {
            type: 'text',
            text: props.tenantName,
            imageUrl: '',
            alt: `${props.tenantName} logo`,
            width: 120,
            mobileWidth: 96,
            hideOnMobile: false,
        };
    } else if (target === 'buttons') {
        header.actions = [];
    } else if (target === 'item') {
        header.items = [];
        selectedItemId.value = null;
    } else if (target === 'design') {
        header.variant = props.defaultVariant;
    } else if (target === 'appearance') {
        header.layout = {
            contentWidth: 1200,
            height: 72,
            horizontalPadding: 24,
            position: 'static',
            stickyOffset: 0,
            fullWidth: false,
            borderWidth: 1,
            borderColor: '#e4e4e7',
            borderRadius: 16,
            shadow: 'small',
        };

        if (header.surface) {
            header.surface.mode = 'design';
        }
    } else if (target === 'mobile') {
        header.responsive = {
            breakpoint: 720,
            menuStyle: 'dropdown',
            menuIcon: 'menu',
            alignment: 'left',
            showActions: true,
        };
    } else if (target === 'mega' && header.menu) {
        header.menu.mode = 'simple';
    }
}

function colorContrast(background: string, foreground: string): number {
    const luminance = (hex: string): number => {
        const channels = hex
            .replace('#', '')
            .match(/.{2}/g)
            ?.map((channel) => Number.parseInt(channel, 16) / 255);

        if (!channels || channels.some(Number.isNaN)) {
            return 21;
        }

        const [red, green, blue] = channels.map((channel) =>
            channel <= 0.03928
                ? channel / 12.92
                : ((channel + 0.055) / 1.055) ** 2.4,
        );

        return 0.2126 * red + 0.7152 * green + 0.0722 * blue;
    };
    const lighter = Math.max(luminance(background), luminance(foreground));
    const darker = Math.min(luminance(background), luminance(foreground));

    return (lighter + 0.05) / (darker + 0.05);
}

watch(
    () => navigationHttp.navigation_config.header,
    (header) => {
        if (historyPaused.value) {
            return;
        }

        clearTimeout(historyTimer);
        historyTimer = setTimeout(() => {
            const nextSnapshot = JSON.stringify(header);

            if (nextSnapshot !== previousHeaderSnapshot) {
                undoStack.value.push(previousHeaderSnapshot);
                undoStack.value = undoStack.value.slice(-30);
                redoStack.value = [];
                previousHeaderSnapshot = nextSnapshot;
            }
        }, 250);
    },
    { deep: true },
);

function selectActionPosition(position: 'start' | 'end'): void {
    navigationHttp.navigation_config.header.actionPosition = position;
}

function selectActionStyleMode(mode: 'theme' | 'custom'): void {
    if (!navigationHttp.navigation_config.header.actionStyle) {
        return;
    }

    navigationHttp.navigation_config.header.actionStyle.mode = mode;
}

function selectLinkStyleMode(mode: 'theme' | 'custom'): void {
    if (!navigationHttp.navigation_config.header.linkStyle) {
        return;
    }

    navigationHttp.navigation_config.header.linkStyle.mode = mode;
}

function keepPreviewInPlace(event: MouseEvent): void {
    const target = event.target;

    if (target instanceof Element && target.closest('a')) {
        event.preventDefault();
    }
}

async function saveNavbar(): Promise<void> {
    try {
        const response = await navigationHttp.patch(
            updateNavigation.url(props.tenantName),
        );

        if (response?.status === 'success' && response.navigation_config) {
            savedHeader.value = JSON.stringify(
                navigationHttp.navigation_config.header,
            );
            emit('saved', response.navigation_config);
            toast.success('Navbar design saved');

            return;
        }

        toast.error(response?.message || 'Unable to save the navbar design');
    } catch {
        toast.error('Unable to save the navbar design');
    }
}
</script>

<template>
    <section
        id="navbar-studio"
        class="navbar-studio-shell flex h-full min-h-0 flex-col overflow-hidden rounded-[7px] border border-editor-border bg-editor-panel"
    >
        <div
            class="flex shrink-0 flex-col gap-3 border-b border-editor-border px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5"
        >
            <div>
                <span
                    class="text-[9px] font-bold tracking-[0.14em] text-editor-text-muted uppercase"
                >
                    Site navigation
                </span>
                <h2 class="mt-1 text-base font-semibold text-editor-text">
                    Navigation
                </h2>
                <p
                    class="mt-1 max-w-2xl text-xs leading-5 text-editor-text-muted"
                >
                    Click the outline or the live preview to edit. Switching
                    designs keeps your links.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-1.5">
                <button
                    type="button"
                    class="studio-tool-button"
                    :disabled="!canUndo"
                    title="Undo"
                    @click="undo"
                >
                    <Undo2 :size="12" /> Undo
                </button>
                <button
                    type="button"
                    class="studio-tool-button"
                    :disabled="!canRedo"
                    title="Redo"
                    @click="redo"
                >
                    <Redo2 :size="12" /> Redo
                </button>
                <button
                    type="button"
                    class="studio-tool-button"
                    @click="resetCurrentPanel"
                >
                    <RotateCcw :size="12" /> Reset
                </button>
                <button
                    type="button"
                    class="studio-tool-button"
                    :aria-pressed="compareWithSaved"
                    @click="compareWithSaved = !compareWithSaved"
                >
                    <History :size="12" />
                    {{ compareWithSaved ? 'Show edits' : 'Compare' }}
                </button>
            </div>
        </div>

        <div
            class="grid min-h-0 flex-1 grid-cols-1 gap-px overflow-hidden bg-editor-border lg:grid-cols-[240px_minmax(0,1fr)_300px]"
        >
            <aside
                class="order-1 min-h-0 min-w-0 overflow-y-auto bg-editor-panel p-4 lg:order-none lg:col-start-1 lg:row-start-1"
                aria-label="Navbar outline"
            >
                <span class="studio-legend">Outline</span>
                <p class="studio-help">
                    Select a part to edit. Drag menu items to reorder.
                </p>

                <nav class="mt-4 grid gap-0.5" aria-label="Navbar parts">
                    <button
                        v-for="section in outlineSections.filter(
                            (entry) => entry.type !== 'mega',
                        )"
                        :key="section.type"
                        type="button"
                        class="flex min-h-9 w-full items-center gap-2 rounded-[5px] border px-2.5 text-left text-[10px] font-semibold transition"
                        :class="
                            selectedTarget.type === section.type
                                ? 'border-editor-border-strong bg-editor-panel-muted text-editor-text'
                                : 'border-transparent text-editor-text-muted hover:border-editor-border hover:bg-editor-panel-muted hover:text-editor-text'
                        "
                        @click="selectOutlineTarget(section.type)"
                    >
                        {{ section.label }}
                    </button>
                    <button
                        type="button"
                        class="flex min-h-9 w-full items-center gap-2 rounded-[5px] border px-2.5 text-left text-[10px] font-semibold transition"
                        :class="
                            selectedTarget.type === 'mega'
                                ? 'border-editor-border-strong bg-editor-panel-muted text-editor-text'
                                : 'border-transparent text-editor-text-muted hover:border-editor-border hover:bg-editor-panel-muted hover:text-editor-text'
                        "
                        @click="selectOutlineTarget('mega')"
                    >
                        Mega menu
                        <span
                            class="ml-auto rounded-full bg-editor-panel-muted px-1.5 py-0.5 text-[8px] font-bold text-editor-text-muted uppercase"
                        >
                            {{ selectedMenuMode }}
                        </span>
                    </button>
                </nav>

                <div class="mt-5 border-t border-editor-border pt-4">
                    <div class="flex items-center justify-between gap-2">
                        <button
                            type="button"
                            class="studio-legend flex items-center gap-1"
                            @click="menuItemsExpanded = !menuItemsExpanded"
                        >
                            Menu items
                            <ChevronRight
                                :size="12"
                                class="transition"
                                :class="menuItemsExpanded ? 'rotate-90' : ''"
                            />
                        </button>
                        <span
                            class="rounded-full bg-editor-panel-muted px-2 py-0.5 text-[9px] font-bold text-editor-text-muted"
                        >
                            {{
                                navigationHttp.navigation_config.header.items
                                    ?.length ?? 0
                            }}
                        </span>
                    </div>

                    <div v-if="menuItemsExpanded" class="mt-2 grid gap-1">
                        <TransitionGroup
                            name="list-item"
                            tag="div"
                            class="grid gap-1"
                            role="tree"
                        >
                            <div
                                v-for="item in navigationHttp.navigation_config
                                    .header.items"
                                :key="item.id"
                                class="grid gap-1"
                                role="treeitem"
                                :aria-selected="
                                    selectedTarget.type === 'item' &&
                                    selectedItemId === item.id
                                "
                                draggable="true"
                                @dragstart="
                                    startNavigationDrag($event, item.id || '')
                                "
                                @dragend="draggedNavigationItemId = null"
                                @dragover.prevent
                                @drop.prevent="
                                    dropNavigationItem(item.id || '')
                                "
                            >
                                <button
                                    type="button"
                                    class="group flex min-h-9 w-full items-center gap-2 rounded-[5px] border px-2 text-left transition"
                                    :class="
                                        selectedTarget.type === 'item' &&
                                        selectedItemId === item.id
                                            ? 'border-editor-border-strong bg-editor-panel-muted text-editor-text'
                                            : 'border-transparent text-editor-text-muted hover:border-editor-border hover:bg-editor-panel-muted hover:text-editor-text'
                                    "
                                    @click="selectNavigationItem(item)"
                                >
                                    <GripVertical
                                        :size="13"
                                        class="shrink-0 cursor-grab opacity-45 group-hover:opacity-80"
                                    />
                                    <FolderOpen
                                        v-if="item.children?.length"
                                        :size="13"
                                        class="shrink-0"
                                    />
                                    <FileText
                                        v-else
                                        :size="13"
                                        class="shrink-0"
                                    />
                                    <span
                                        class="min-w-0 flex-1 truncate text-[10px] font-semibold"
                                    >
                                        {{ item.label || 'Untitled item' }}
                                    </span>
                                </button>

                                <div
                                    v-if="item.children?.length"
                                    class="ml-4 grid gap-0.5 border-l border-editor-border pl-2"
                                    role="group"
                                >
                                    <button
                                        v-for="child in item.children"
                                        :key="child.id"
                                        type="button"
                                        role="treeitem"
                                        class="flex min-h-8 items-center gap-2 rounded-[4px] px-2 text-left text-[9px] transition"
                                        :class="
                                            selectedTarget.type === 'item' &&
                                            selectedItemId === child.id
                                                ? 'bg-editor-panel-muted font-semibold text-editor-text'
                                                : 'text-editor-text-muted hover:bg-editor-panel-muted hover:text-editor-text'
                                        "
                                        @click="selectNavigationItem(child)"
                                    >
                                        <FileText :size="11" class="shrink-0" />
                                        <span class="truncate">
                                            {{ child.label || 'Untitled page' }}
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </TransitionGroup>

                        <button
                            v-if="
                                !navigationHttp.navigation_config.header.items
                                    ?.length
                            "
                            type="button"
                            class="studio-empty-button"
                            @click="addNavigationItem()"
                        >
                            <Plus :size="14" /> Add your first item
                        </button>

                        <div class="mt-2 grid gap-1.5">
                            <select
                                v-model="pageToAdd"
                                class="studio-select"
                                aria-label="Choose an existing page"
                            >
                                <option
                                    v-for="pageOption in pages"
                                    :key="pageOption.slug"
                                    :value="pageOption.slug"
                                >
                                    {{ pageOption.title }}
                                </option>
                            </select>
                            <button
                                type="button"
                                class="studio-tool-button w-full"
                                :disabled="!pageToAdd"
                                @click="addExistingPage"
                            >
                                <Plus :size="12" /> Add page
                            </button>
                            <button
                                type="button"
                                class="studio-tool-button w-full"
                                @click="addDropdownItem"
                            >
                                <FolderOpen :size="12" /> Create dropdown
                            </button>
                        </div>
                    </div>
                </div>
            </aside>

            <div
                class="order-3 min-h-0 min-w-0 overflow-y-auto bg-editor-panel p-4 sm:p-5 lg:order-none lg:col-start-3 lg:row-start-1"
                aria-label="Navbar inspector"
            >
                <div class="mb-4 flex items-center justify-between gap-2">
                    <div>
                        <span class="studio-legend">Inspector</span>
                        <p class="studio-help capitalize">
                            {{
                                selectedTarget.type === 'item'
                                    ? 'Menu item'
                                    : selectedTarget.type
                            }}
                        </p>
                    </div>
                </div>

                <fieldset
                    v-if="
                        selectedTarget.type === 'brand' &&
                        navigationHttp.navigation_config.header.brand
                    "
                    id="navbar-settings-panel-brand"
                    role="tabpanel"
                    aria-labelledby="navbar-settings-tab-brand"
                    tabindex="0"
                >
                    <legend class="studio-legend">Logo and brand</legend>
                    <p class="studio-help">
                        Use a text wordmark or select an image from the media
                        library.
                    </p>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <button
                            v-for="type in ['text', 'image'] as const"
                            :key="type"
                            type="button"
                            class="studio-choice"
                            :class="
                                navigationHttp.navigation_config.header.brand
                                    .type === type
                                    ? 'studio-choice-active'
                                    : ''
                            "
                            @click="
                                navigationHttp.navigation_config.header.brand.type =
                                    type
                            "
                        >
                            {{ type === 'text' ? 'Text logo' : 'Image logo' }}
                        </button>
                    </div>
                    <label
                        v-if="
                            navigationHttp.navigation_config.header.brand
                                .type === 'text'
                        "
                        class="studio-field mt-3"
                    >
                        <span>Brand text</span>
                        <input
                            v-model="
                                navigationHttp.navigation_config.header.brand
                                    .text
                            "
                            type="text"
                            maxlength="60"
                        />
                    </label>
                    <div v-else class="mt-3 grid gap-2">
                        <button
                            type="button"
                            class="studio-media-button"
                            @click="openMediaPicker('brand')"
                        >
                            <Image :size="14" /> Choose logo image
                        </button>
                        <label class="studio-field">
                            <span>Alternative text</span>
                            <input
                                v-model="
                                    navigationHttp.navigation_config.header
                                        .brand.alt
                                "
                                type="text"
                                maxlength="100"
                            />
                        </label>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <label class="studio-field">
                            <span>Desktop width</span>
                            <input
                                v-model.number="
                                    navigationHttp.navigation_config.header
                                        .brand.width
                                "
                                type="number"
                                min="40"
                                max="280"
                            />
                        </label>
                        <label class="studio-field">
                            <span>Mobile width</span>
                            <input
                                v-model.number="
                                    navigationHttp.navigation_config.header
                                        .brand.mobileWidth
                                "
                                type="number"
                                min="32"
                                max="220"
                            />
                        </label>
                    </div>
                    <label class="studio-check mt-3">
                        <input
                            v-model="
                                navigationHttp.navigation_config.header.brand
                                    .hideOnMobile
                            "
                            type="checkbox"
                        />
                        Hide logo on mobile
                    </label>
                </fieldset>

                <fieldset
                    v-if="selectedTarget.type === 'appearance'"
                    id="navbar-settings-panel-surface"
                    role="tabpanel"
                    aria-labelledby="navbar-settings-tab-surface"
                    tabindex="0"
                >
                    <legend
                        class="text-[9px] font-bold tracking-[0.12em] text-editor-text-muted uppercase"
                    >
                        Navbar background
                    </legend>
                    <p
                        class="mt-1 text-[10px] leading-4 text-editor-text-muted"
                    >
                        {{ surfaceModes[selectedSurfaceMode].description }}
                    </p>

                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <button
                            v-for="[key, surface] in surfaceEntries"
                            :key="key"
                            type="button"
                            class="min-h-9 rounded-[5px] border px-2.5 text-[9px] font-semibold transition motion-reduce:transition-none"
                            :class="
                                selectedSurfaceMode === key
                                    ? 'border-editor-text bg-editor-text text-white'
                                    : 'border-editor-border bg-editor-panel text-editor-text-muted hover:bg-editor-panel-muted hover:text-editor-text'
                            "
                            :aria-pressed="selectedSurfaceMode === key"
                            @click="selectSurfaceMode(key)"
                        >
                            {{ surface.label }}
                        </button>
                    </div>

                    <div
                        v-if="
                            selectedSurfaceMode === 'custom' &&
                            navigationHttp.navigation_config.header.surface
                        "
                        class="mt-3 grid grid-cols-2 gap-2"
                    >
                        <label
                            class="rounded-[5px] border border-editor-border bg-editor-panel p-2.5"
                        >
                            <span
                                class="block text-[8px] font-bold tracking-[0.1em] text-editor-text-muted uppercase"
                            >
                                Background
                            </span>
                            <span class="mt-2 flex items-center gap-2">
                                <input
                                    v-model="
                                        navigationHttp.navigation_config.header
                                            .surface.backgroundColor
                                    "
                                    type="color"
                                    class="size-7 cursor-pointer rounded border border-editor-border bg-transparent p-0.5"
                                />
                                <code class="text-[9px] text-editor-text">
                                    {{
                                        navigationHttp.navigation_config.header
                                            .surface.backgroundColor
                                    }}
                                </code>
                            </span>
                        </label>
                        <label
                            class="rounded-[5px] border border-editor-border bg-editor-panel p-2.5"
                        >
                            <span
                                class="block text-[8px] font-bold tracking-[0.1em] text-editor-text-muted uppercase"
                            >
                                Text
                            </span>
                            <span class="mt-2 flex items-center gap-2">
                                <input
                                    v-model="
                                        navigationHttp.navigation_config.header
                                            .surface.textColor
                                    "
                                    type="color"
                                    class="size-7 cursor-pointer rounded border border-editor-border bg-transparent p-0.5"
                                />
                                <code class="text-[9px] text-editor-text">
                                    {{
                                        navigationHttp.navigation_config.header
                                            .surface.textColor
                                    }}
                                </code>
                            </span>
                        </label>
                    </div>
                </fieldset>

                <div v-if="selectedTarget.type === 'appearance'" class="mt-4">
                    <button
                        type="button"
                        class="studio-tool-button w-full"
                        :aria-expanded="showAdvancedAppearance"
                        @click="
                            showAdvancedAppearance = !showAdvancedAppearance
                        "
                    >
                        {{ showAdvancedAppearance ? 'Hide' : 'Show' }} advanced
                        & presets
                    </button>
                    <div v-if="showAdvancedAppearance" class="mt-4">
                        <div class="border-t border-editor-border pt-4">
                            <legend
                                class="text-[9px] font-bold tracking-[0.12em] text-editor-text-muted uppercase"
                            >
                                Link appearance
                            </legend>
                            <p
                                class="mt-1 text-[10px] leading-4 text-editor-text-muted"
                            >
                                Set the shared visual treatment for navigation
                                text.
                            </p>

                            <div
                                v-if="
                                    navigationHttp.navigation_config.header
                                        .linkStyle
                                "
                                class="mt-3"
                            >
                                <span
                                    class="text-[8px] font-bold tracking-[0.1em] text-editor-text-muted uppercase"
                                >
                                    Navigation text colors
                                </span>
                                <p
                                    class="mt-1 text-[10px] leading-4 text-editor-text-muted"
                                >
                                    Set how links look normally and when a
                                    visitor hovers.
                                </p>
                                <div class="mt-2 grid grid-cols-2 gap-2">
                                    <button
                                        v-for="mode in [
                                            'theme',
                                            'custom',
                                        ] as const"
                                        :key="mode"
                                        type="button"
                                        class="min-h-9 rounded-[5px] border px-2.5 text-[9px] font-semibold capitalize transition"
                                        :class="
                                            navigationHttp.navigation_config
                                                .header.linkStyle.mode === mode
                                                ? 'border-editor-text bg-editor-text text-white'
                                                : 'border-editor-border bg-editor-panel text-editor-text-muted hover:bg-editor-panel-muted hover:text-editor-text'
                                        "
                                        :aria-pressed="
                                            navigationHttp.navigation_config
                                                .header.linkStyle.mode === mode
                                        "
                                        @click="selectLinkStyleMode(mode)"
                                    >
                                        {{
                                            mode === 'theme'
                                                ? 'Site theme'
                                                : mode
                                        }}
                                    </button>
                                </div>
                                <div
                                    v-if="
                                        navigationHttp.navigation_config.header
                                            .linkStyle.mode === 'custom'
                                    "
                                    class="mt-2 grid grid-cols-2 gap-2"
                                >
                                    <label
                                        class="rounded-[5px] border border-editor-border bg-editor-panel p-2.5"
                                    >
                                        <span
                                            class="block text-[8px] font-bold tracking-[0.1em] text-editor-text-muted uppercase"
                                        >
                                            Normal text
                                        </span>
                                        <input
                                            v-model="
                                                navigationHttp.navigation_config
                                                    .header.linkStyle.color
                                            "
                                            type="color"
                                            class="mt-2 h-7 w-full cursor-pointer rounded border border-editor-border bg-transparent p-0.5"
                                        />
                                    </label>
                                    <label
                                        class="rounded-[5px] border border-editor-border bg-editor-panel p-2.5"
                                    >
                                        <span
                                            class="block text-[8px] font-bold tracking-[0.1em] text-editor-text-muted uppercase"
                                        >
                                            Hover text
                                        </span>
                                        <input
                                            v-model="
                                                navigationHttp.navigation_config
                                                    .header.linkStyle.hoverColor
                                            "
                                            type="color"
                                            class="mt-2 h-7 w-full cursor-pointer rounded border border-editor-border bg-transparent p-0.5"
                                        />
                                    </label>
                                </div>
                                <label class="studio-field mt-2">
                                    <span>Hover effect</span>
                                    <select
                                        v-model="
                                            navigationHttp.navigation_config
                                                .header.linkStyle.hoverEffect
                                        "
                                    >
                                        <option value="color">
                                            Color change
                                        </option>
                                        <option value="underline">
                                            Underline slide
                                        </option>
                                        <option value="background">
                                            Background fill
                                        </option>
                                        <option value="lift">Lift</option>
                                        <option value="none">No effect</option>
                                    </select>
                                </label>
                                <div
                                    v-if="
                                        navigationHttp.navigation_config.header
                                            .states
                                    "
                                    class="mt-2 grid grid-cols-2 gap-2"
                                >
                                    <label class="studio-field">
                                        <span>Active page</span>
                                        <input
                                            v-model="
                                                navigationHttp.navigation_config
                                                    .header.states.activeColor
                                            "
                                            type="color"
                                        />
                                    </label>
                                    <label class="studio-field">
                                        <span>Keyboard focus</span>
                                        <input
                                            v-model="
                                                navigationHttp.navigation_config
                                                    .header.states.focusColor
                                            "
                                            type="color"
                                        />
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 border-t border-editor-border pt-4">
                            <div
                                class="flex items-center justify-between gap-2"
                            >
                                <span class="studio-legend">Saved presets</span>
                                <button
                                    type="button"
                                    class="studio-tool-button"
                                    @click="savePreset"
                                >
                                    <Plus :size="12" /> Save current
                                </button>
                            </div>
                            <TransitionGroup
                                name="list-item"
                                tag="div"
                                class="mt-2 grid gap-2"
                            >
                                <div
                                    v-for="(
                                        preset, presetIndex
                                    ) in navigationHttp.navigation_config
                                        .presets"
                                    :key="preset.id"
                                    class="hover-lift flex items-center gap-1 rounded-[5px] border border-editor-border bg-editor-panel-muted p-2"
                                >
                                    <input
                                        v-model="preset.name"
                                        type="text"
                                        maxlength="60"
                                        class="studio-inline-input"
                                    />
                                    <button
                                        type="button"
                                        class="studio-tool-button"
                                        @click="applyPreset(preset)"
                                    >
                                        Apply
                                    </button>
                                    <button
                                        type="button"
                                        class="studio-icon-button hover:text-red-600"
                                        aria-label="Delete preset"
                                        @click="removePreset(presetIndex)"
                                    >
                                        <Trash2 :size="12" />
                                    </button>
                                </div>
                            </TransitionGroup>
                            <p
                                v-if="
                                    !navigationHttp.navigation_config.presets
                                        ?.length
                                "
                                class="studio-help mt-2"
                            >
                                Save up to ten reusable navbar configurations.
                            </p>
                        </div>
                    </div>
                </div>

                <fieldset
                    v-if="
                        selectedTarget.type === 'item' &&
                        navigationHttp.navigation_config.header.menu
                    "
                    id="navbar-settings-panel-menu"
                    role="tabpanel"
                    aria-labelledby="navbar-settings-tab-menu"
                    tabindex="0"
                >
                    <div v-if="selectedNavigationItem">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="studio-legend">Item editor</span>
                                <p class="studio-help">
                                    Edit only the selected navigation item.
                                </p>
                            </div>
                            <span
                                class="rounded-full border border-editor-border bg-editor-panel-muted px-2 py-1 text-[8px] font-bold text-editor-text-muted uppercase"
                            >
                                {{
                                    selectedItemIsTopLevel
                                        ? selectedNavigationItem.children
                                              ?.length
                                            ? 'Dropdown'
                                            : 'Link'
                                        : 'Nested link'
                                }}
                            </span>
                        </div>

                        <label class="studio-field mt-3">
                            <span>Title</span>
                            <input
                                v-model="selectedNavigationItem.label"
                                type="text"
                                maxlength="40"
                            />
                        </label>

                        <div
                            v-if="selectedItemIsTopLevel"
                            class="mt-3 grid grid-cols-2 gap-2"
                        >
                            <button
                                type="button"
                                class="studio-choice"
                                :class="
                                    !selectedNavigationItem.children?.length
                                        ? 'studio-choice-active'
                                        : ''
                                "
                                @click="setSelectedItemKind('link')"
                            >
                                <FileText :size="12" /> Link
                            </button>
                            <button
                                type="button"
                                class="studio-choice"
                                :class="
                                    selectedNavigationItem.children?.length
                                        ? 'studio-choice-active'
                                        : ''
                                "
                                @click="setSelectedItemKind('dropdown')"
                            >
                                <FolderOpen :size="12" /> Dropdown
                            </button>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <label class="studio-field">
                                <span>Destination type</span>
                                <select v-model="selectedNavigationItem.type">
                                    <option value="internal">
                                        Internal page
                                    </option>
                                    <option value="external">
                                        External link
                                    </option>
                                </select>
                            </label>
                            <label
                                v-if="
                                    selectedNavigationItem.type !== 'external'
                                "
                                class="studio-field"
                            >
                                <span>Page</span>
                                <select v-model="selectedNavigationItem.slug">
                                    <option
                                        v-for="pageOption in pages"
                                        :key="pageOption.slug"
                                        :value="pageOption.slug"
                                    >
                                        {{ pageOption.title }}
                                    </option>
                                </select>
                            </label>
                            <label v-else class="studio-field">
                                <span>URL</span>
                                <input
                                    v-model="selectedNavigationItem.href"
                                    type="url"
                                    placeholder="https://example.com"
                                />
                            </label>
                        </div>

                        <div
                            v-if="
                                selectedItemIsTopLevel &&
                                selectedNavigationItem.children?.length
                            "
                            class="mt-4 border-t border-editor-border pt-4"
                        >
                            <div
                                class="flex items-center justify-between gap-2"
                            >
                                <span class="studio-legend">
                                    Dropdown pages
                                </span>
                                <button
                                    type="button"
                                    class="studio-icon-button"
                                    aria-label="Add page to dropdown"
                                    @click="
                                        addNavigationItem(
                                            selectedItemLocation?.itemIndex,
                                        )
                                    "
                                >
                                    <Plus :size="12" />
                                </button>
                            </div>
                            <div class="mt-2 grid gap-1.5">
                                <button
                                    v-for="child in selectedNavigationItem.children"
                                    :key="child.id"
                                    type="button"
                                    class="flex min-h-9 items-center gap-2 rounded-[5px] border border-editor-border bg-editor-panel-muted px-2 text-left text-[9px] text-editor-text transition hover:border-editor-border-strong"
                                    @click="selectNavigationItem(child)"
                                >
                                    <FileText :size="12" />
                                    <span class="min-w-0 flex-1 truncate">
                                        {{ child.label }}
                                    </span>
                                    <ChevronRight :size="12" />
                                </button>
                            </div>
                        </div>

                        <div
                            class="mt-4 flex items-center justify-between gap-2 border-t border-editor-border pt-4"
                        >
                            <label class="studio-check">
                                <input
                                    v-model="selectedNavigationItem.disabled"
                                    type="checkbox"
                                />
                                Disabled
                            </label>
                            <div class="flex items-center gap-1">
                                <button
                                    v-if="
                                        selectedItemIsTopLevel &&
                                        (selectedItemLocation?.itemIndex ?? 0) >
                                            0
                                    "
                                    type="button"
                                    class="studio-tool-button"
                                    aria-label="Nest under previous item"
                                    @click="nestSelectedItem"
                                >
                                    Nest
                                </button>
                                <button
                                    v-if="!selectedItemIsTopLevel"
                                    type="button"
                                    class="studio-tool-button"
                                    aria-label="Move out of dropdown"
                                    @click="unnestSelectedItem"
                                >
                                    Unnest
                                </button>
                                <button
                                    v-if="selectedItemIsTopLevel"
                                    type="button"
                                    class="studio-icon-button"
                                    :disabled="
                                        selectedItemLocation?.itemIndex === 0
                                    "
                                    aria-label="Move selected item up"
                                    @click="moveSelectedNavigationItem(-1)"
                                >
                                    <ArrowUp :size="12" />
                                </button>
                                <button
                                    v-if="selectedItemIsTopLevel"
                                    type="button"
                                    class="studio-icon-button"
                                    :disabled="
                                        selectedItemLocation?.itemIndex ===
                                        (navigationHttp.navigation_config.header
                                            .items?.length ?? 0) -
                                            1
                                    "
                                    aria-label="Move selected item down"
                                    @click="moveSelectedNavigationItem(1)"
                                >
                                    <ArrowDown :size="12" />
                                </button>
                                <button
                                    type="button"
                                    class="studio-icon-button hover:text-red-600"
                                    aria-label="Delete selected item"
                                    @click="removeSelectedNavigationItem"
                                >
                                    <Trash2 :size="12" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="rounded-[6px] border border-dashed border-editor-border bg-editor-panel-muted p-4 text-center"
                    >
                        <strong class="text-[10px] text-editor-text">
                            Select a navigation item
                        </strong>
                        <p class="studio-help">
                            Choose a menu item from the outline to edit it.
                        </p>
                    </div>
                </fieldset>

                <fieldset
                    v-if="
                        selectedTarget.type === 'mega' &&
                        navigationHttp.navigation_config.header.menu
                    "
                    id="navbar-settings-panel-mega"
                    role="tabpanel"
                    aria-labelledby="navbar-settings-tab-mega"
                    tabindex="0"
                >
                    <div
                        class="flex items-center justify-between gap-3 rounded-[6px] border border-editor-border bg-editor-panel-muted p-3"
                    >
                        <div>
                            <strong class="block text-[11px] text-editor-text">
                                Mega menu
                            </strong>
                            <p class="studio-help">
                                Show navigation links inside a large panel.
                            </p>
                        </div>
                        <button
                            type="button"
                            role="switch"
                            class="relative h-6 w-11 shrink-0 rounded-full border transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-zinc-500 motion-reduce:transition-none"
                            :class="
                                selectedMenuMode === 'mega'
                                    ? 'border-editor-text bg-editor-text'
                                    : 'border-editor-border-strong bg-editor-panel'
                            "
                            :aria-checked="selectedMenuMode === 'mega'"
                            aria-label="Enable mega menu"
                            @click="
                                selectMenuMode(
                                    selectedMenuMode === 'mega'
                                        ? 'simple'
                                        : 'mega',
                                )
                            "
                        >
                            <span
                                class="absolute top-0.5 size-[18px] rounded-full bg-white shadow-sm transition-transform motion-reduce:transition-none"
                                :class="
                                    selectedMenuMode === 'mega'
                                        ? 'translate-x-[20px]'
                                        : 'translate-x-0.5'
                                "
                            ></span>
                        </button>
                    </div>

                    <div v-if="selectedMenuMode === 'mega'">
                        <label class="studio-field mt-4">
                            <span>Trigger label</span>
                            <input
                                v-model="
                                    navigationHttp.navigation_config.header.menu
                                        .triggerLabel
                                "
                                type="text"
                                maxlength="40"
                            />
                        </label>

                        <button
                            type="button"
                            class="studio-tool-button mt-3 w-full"
                            :aria-expanded="showAdvancedMega"
                            @click="showAdvancedMega = !showAdvancedMega"
                        >
                            {{ showAdvancedMega ? 'Hide' : 'Show' }} layout
                            &amp; featured
                        </button>

                        <div v-if="showAdvancedMega" class="mt-4">
                            <div>
                                <span class="studio-legend">Choose layout</span>
                                <div class="mt-2 grid grid-cols-3 gap-2">
                                    <button
                                        v-for="columnCount in [
                                            2, 3, 4,
                                        ] as const"
                                        :key="columnCount"
                                        type="button"
                                        class="rounded-[5px] border p-2 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-zinc-500"
                                        :class="
                                            navigationHttp.navigation_config
                                                .header.menu.columns ===
                                            columnCount
                                                ? 'border-editor-text bg-editor-panel-muted text-editor-text'
                                                : 'border-editor-border bg-editor-panel text-editor-text-muted hover:border-editor-border-strong'
                                        "
                                        :aria-label="`${columnCount} column layout`"
                                        :aria-pressed="
                                            navigationHttp.navigation_config
                                                .header.menu.columns ===
                                            columnCount
                                        "
                                        @click="
                                            navigationHttp.navigation_config.header.menu.columns =
                                                columnCount
                                        "
                                    >
                                        <span
                                            class="grid h-7 gap-1"
                                            :style="{
                                                gridTemplateColumns: `repeat(${columnCount}, minmax(0, 1fr))`,
                                            }"
                                        >
                                            <span
                                                v-for="column in columnCount"
                                                :key="column"
                                                class="rounded-sm bg-current opacity-30"
                                            ></span>
                                        </span>
                                        <span
                                            class="mt-1 block text-[8px] font-bold"
                                        >
                                            {{ columnCount }} columns
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <label class="studio-field">
                                    <span>Open menu</span>
                                    <select
                                        v-model="
                                            navigationHttp.navigation_config
                                                .header.menu.openOn
                                        "
                                    >
                                        <option value="click">On click</option>
                                        <option value="hover">On hover</option>
                                    </select>
                                </label>
                                <label class="studio-field">
                                    <span>Panel width</span>
                                    <select
                                        v-model="
                                            navigationHttp.navigation_config
                                                .header.menu.width
                                        "
                                    >
                                        <option value="content">Content</option>
                                        <option value="wide">Wide</option>
                                        <option value="full">Full width</option>
                                    </select>
                                </label>
                                <label class="studio-field">
                                    <span>Alignment</span>
                                    <select
                                        v-model="
                                            navigationHttp.navigation_config
                                                .header.menu.alignment
                                        "
                                    >
                                        <option value="left">Left</option>
                                        <option value="center">Center</option>
                                        <option value="right">Right</option>
                                    </select>
                                </label>
                                <label class="studio-field col-span-2">
                                    <span>Animation</span>
                                    <select
                                        v-model="
                                            navigationHttp.navigation_config
                                                .header.menu.animation
                                        "
                                    >
                                        <option value="fade">Fade</option>
                                        <option value="slide">Slide</option>
                                        <option value="scale">Scale</option>
                                    </select>
                                </label>
                            </div>
                        </div>

                        <div class="mt-4 border-t border-editor-border pt-4">
                            <div
                                class="flex items-center justify-between gap-2"
                            >
                                <div>
                                    <span class="studio-legend">Sections</span>
                                    <p class="studio-help">
                                        Drag a link into another section to move
                                        it.
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    class="studio-icon-button"
                                    @click="addMegaGroup"
                                >
                                    <Plus :size="13" />
                                </button>
                            </div>
                            <div class="mt-2 grid gap-2">
                                <div
                                    v-for="(group, groupIndex) in navigationHttp
                                        .navigation_config.header.menu.groups"
                                    :key="group.id"
                                    class="overflow-hidden rounded-[6px] border border-editor-border bg-editor-panel"
                                    @dragover.prevent
                                    @drop.prevent="
                                        dropMegaItemInGroup(group.id)
                                    "
                                >
                                    <div class="bg-editor-panel-muted p-3">
                                        <div class="flex items-center gap-1">
                                            <input
                                                v-model="group.label"
                                                type="text"
                                                maxlength="40"
                                                class="studio-inline-input"
                                                aria-label="Mega-menu group name"
                                            />
                                            <button
                                                type="button"
                                                class="studio-icon-button hover:text-red-600"
                                                :disabled="
                                                    (navigationHttp
                                                        .navigation_config
                                                        .header.menu.groups
                                                        ?.length ?? 0) === 1
                                                "
                                                aria-label="Remove mega-menu group"
                                                @click="
                                                    removeMegaGroup(groupIndex)
                                                "
                                            >
                                                <Trash2 :size="12" />
                                            </button>
                                        </div>
                                        <input
                                            v-model="group.description"
                                            type="text"
                                            maxlength="120"
                                            placeholder="Optional description"
                                            class="studio-inline-input mt-2"
                                            aria-label="Mega-menu group description"
                                        />
                                    </div>

                                    <div
                                        class="border-t border-editor-border p-3"
                                    >
                                        <div
                                            class="flex items-center justify-between gap-2"
                                        >
                                            <span class="studio-legend">
                                                Links ·
                                                {{
                                                    megaGroupItems(group.id)
                                                        .length
                                                }}
                                            </span>
                                            <button
                                                type="button"
                                                class="inline-flex items-center gap-1 text-[9px] font-bold text-editor-text-muted transition hover:text-editor-text focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-zinc-500 motion-reduce:transition-none"
                                                @click="
                                                    addMegaGroupItem(group.id)
                                                "
                                            >
                                                <Plus :size="12" /> Add link
                                            </button>
                                        </div>

                                        <div
                                            v-if="
                                                megaGroupItems(group.id).length
                                            "
                                            class="mt-2 grid gap-2"
                                        >
                                            <div
                                                v-for="(
                                                    entry, groupItemIndex
                                                ) in megaGroupItems(group.id)"
                                                :key="entry.item.id"
                                                class="rounded-[5px] border border-editor-border bg-editor-panel-muted p-2 transition-opacity"
                                                :class="
                                                    draggedMegaItemId ===
                                                    entry.item.id
                                                        ? 'opacity-40'
                                                        : ''
                                                "
                                                draggable="true"
                                                @dragstart="
                                                    startMegaItemDrag(
                                                        $event,
                                                        entry.item.id || '',
                                                    )
                                                "
                                                @dragend="
                                                    draggedMegaItemId = null
                                                "
                                            >
                                                <div
                                                    class="grid grid-cols-[20px_minmax(0,1fr)_30px_30px_30px] items-center gap-1"
                                                >
                                                    <GripVertical
                                                        :size="13"
                                                        class="cursor-grab text-editor-text-muted"
                                                        aria-hidden="true"
                                                    />
                                                    <input
                                                        v-model="
                                                            entry.item.label
                                                        "
                                                        type="text"
                                                        maxlength="40"
                                                        class="studio-inline-input"
                                                        aria-label="Mega-menu link label"
                                                    />
                                                    <button
                                                        type="button"
                                                        class="studio-icon-button"
                                                        :disabled="
                                                            groupItemIndex === 0
                                                        "
                                                        aria-label="Move link up"
                                                        @click="
                                                            moveMegaGroupItem(
                                                                group.id,
                                                                entry.itemIndex,
                                                                -1,
                                                            )
                                                        "
                                                    >
                                                        <ArrowUp :size="11" />
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="studio-icon-button"
                                                        :disabled="
                                                            groupItemIndex ===
                                                            megaGroupItems(
                                                                group.id,
                                                            ).length -
                                                                1
                                                        "
                                                        aria-label="Move link down"
                                                        @click="
                                                            moveMegaGroupItem(
                                                                group.id,
                                                                entry.itemIndex,
                                                                1,
                                                            )
                                                        "
                                                    >
                                                        <ArrowDown :size="11" />
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="studio-icon-button hover:text-red-600"
                                                        aria-label="Remove mega-menu link"
                                                        @click="
                                                            removeNavigationItem(
                                                                entry.itemIndex,
                                                            )
                                                        "
                                                    >
                                                        <Trash2 :size="11" />
                                                    </button>
                                                </div>

                                                <div
                                                    class="mt-2 grid grid-cols-2 gap-1"
                                                >
                                                    <select
                                                        v-model="
                                                            entry.item.type
                                                        "
                                                        class="studio-select"
                                                        aria-label="Link type"
                                                    >
                                                        <option
                                                            value="internal"
                                                        >
                                                            Internal page
                                                        </option>
                                                        <option
                                                            value="external"
                                                        >
                                                            External URL
                                                        </option>
                                                    </select>
                                                    <select
                                                        v-if="
                                                            entry.item.type !==
                                                            'external'
                                                        "
                                                        v-model="
                                                            entry.item.slug
                                                        "
                                                        class="studio-select"
                                                        aria-label="Destination page"
                                                    >
                                                        <option
                                                            v-for="pageOption in pages"
                                                            :key="
                                                                pageOption.slug
                                                            "
                                                            :value="
                                                                pageOption.slug
                                                            "
                                                        >
                                                            {{
                                                                pageOption.title
                                                            }}
                                                        </option>
                                                    </select>
                                                    <input
                                                        v-else
                                                        v-model="
                                                            entry.item.href
                                                        "
                                                        type="url"
                                                        placeholder="https://example.com"
                                                        class="studio-inline-input"
                                                        aria-label="Destination URL"
                                                    />
                                                </div>

                                                <div
                                                    class="mt-2 flex items-center justify-between gap-2"
                                                >
                                                    <span
                                                        class="text-[8px] text-editor-text-muted"
                                                    >
                                                        Drag to move between
                                                        sections
                                                    </span>
                                                    <label class="studio-check">
                                                        <input
                                                            v-model="
                                                                entry.item
                                                                    .disabled
                                                            "
                                                            type="checkbox"
                                                        />
                                                        Disabled
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <button
                                            v-else
                                            type="button"
                                            class="mt-2 flex min-h-16 w-full items-center justify-center rounded-[5px] border border-dashed border-editor-border bg-editor-panel-muted px-3 text-center text-[9px] font-semibold text-editor-text-muted transition hover:border-editor-border-strong hover:text-editor-text focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-zinc-500 motion-reduce:transition-none"
                                            @click="addMegaGroupItem(group.id)"
                                        >
                                            Add the first link to this group
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="
                                showAdvancedMega &&
                                navigationHttp.navigation_config.header.menu
                                    .featured
                            "
                            class="mt-4 border-t border-editor-border pt-4"
                        >
                            <label class="studio-check">
                                <input
                                    v-model="
                                        navigationHttp.navigation_config.header
                                            .menu.featured.show
                                    "
                                    type="checkbox"
                                />
                                Show featured card
                            </label>
                            <div
                                v-if="
                                    navigationHttp.navigation_config.header.menu
                                        .featured.show
                                "
                                class="mt-2 grid gap-2"
                            >
                                <label class="studio-field">
                                    <span>Title</span>
                                    <input
                                        v-model="
                                            navigationHttp.navigation_config
                                                .header.menu.featured.title
                                        "
                                        type="text"
                                        maxlength="60"
                                    />
                                </label>
                                <label class="studio-field">
                                    <span>Description</span>
                                    <input
                                        v-model="
                                            navigationHttp.navigation_config
                                                .header.menu.featured
                                                .description
                                        "
                                        type="text"
                                        maxlength="160"
                                    />
                                </label>
                                <button
                                    type="button"
                                    class="studio-media-button"
                                    @click="openMediaPicker('featured')"
                                >
                                    <Image :size="14" /> Choose featured image
                                </button>
                                <label class="studio-field">
                                    <span>Destination URL</span>
                                    <input
                                        v-model="
                                            navigationHttp.navigation_config
                                                .header.menu.featured.href
                                        "
                                        type="url"
                                        placeholder="https://example.com"
                                    />
                                </label>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div
                    v-if="
                        selectedTarget.type === 'design' ||
                        selectedTarget.type === 'appearance'
                    "
                    id="navbar-settings-panel-layout"
                    class="grid gap-2 sm:grid-cols-2 xl:grid-cols-1"
                    role="tabpanel"
                    aria-labelledby="navbar-settings-tab-layout"
                    tabindex="0"
                >
                    <template v-if="selectedTarget.type === 'design'">
                        <div class="sm:col-span-2 xl:col-span-1">
                            <span class="studio-legend">Design style</span>
                            <p class="studio-help">
                                Pick a structural layout. Your links and content
                                stay the same.
                            </p>
                        </div>
                        <button
                            v-for="([key, variant], index) in variantEntries"
                            :key="key"
                            type="button"
                            class="group relative overflow-hidden rounded-[6px] border p-4 text-left transition duration-200 motion-reduce:transition-none"
                            :class="
                                selectedVariant === key
                                    ? 'border-editor-border-strong bg-editor-accent-soft shadow-[0_12px_30px_rgb(24_24_27/10%)]'
                                    : 'border-editor-border bg-editor-panel hover:border-editor-border-strong hover:bg-editor-panel-muted'
                            "
                            :aria-pressed="selectedVariant === key"
                            @click="selectVariant(key)"
                        >
                            <span
                                class="mb-4 flex h-12 items-center rounded-[4px] border border-editor-border bg-editor-panel-muted px-3"
                                :class="{
                                    'justify-center': key === 'centered-brand',
                                    'mx-2 rounded-full':
                                        key === 'floating-island',
                                }"
                                aria-hidden="true"
                            >
                                <span
                                    class="h-2.5 w-12 rounded-sm bg-zinc-400"
                                ></span>
                                <span
                                    class="ml-auto flex items-center gap-1.5"
                                    :class="{
                                        'absolute left-1/2 -translate-x-1/2':
                                            key === 'centered-brand',
                                    }"
                                >
                                    <span
                                        v-for="line in key === 'mega-menu'
                                            ? 2
                                            : 3"
                                        :key="line"
                                        class="h-1.5 w-5 rounded-full bg-zinc-300"
                                    ></span>
                                </span>
                                <span
                                    v-if="key !== 'centered-brand'"
                                    class="ml-3 h-5 w-8 rounded-sm bg-zinc-700"
                                ></span>
                            </span>

                            <span
                                class="flex items-start justify-between gap-3"
                            >
                                <span>
                                    <span
                                        class="text-[8px] font-bold tracking-[0.12em] text-editor-text-muted uppercase"
                                    >
                                        {{ variant.category }} · 0{{
                                            index + 1
                                        }}
                                    </span>
                                    <strong
                                        class="mt-1 block text-xs font-semibold text-editor-text"
                                    >
                                        {{ variant.label }}
                                    </strong>
                                </span>
                                <span
                                    v-if="selectedVariant === key"
                                    class="flex size-5 shrink-0 items-center justify-center rounded-full bg-editor-text text-white"
                                >
                                    <Check :size="12" :stroke-width="3" />
                                </span>
                            </span>
                            <span
                                class="mt-2 block text-[10px] leading-4 text-editor-text-muted"
                            >
                                {{ variant.description }}
                            </span>
                        </button>
                    </template>

                    <div
                        v-if="
                            selectedTarget.type === 'appearance' &&
                            navigationHttp.navigation_config.header.layout
                        "
                        class="grid gap-3 sm:col-span-2 xl:col-span-1"
                    >
                        <span class="studio-legend"
                            >Dimensions and position</span
                        >
                        <div class="grid grid-cols-2 gap-2">
                            <label class="studio-field">
                                <span>Content width</span>
                                <input
                                    v-model.number="
                                        navigationHttp.navigation_config.header
                                            .layout.contentWidth
                                    "
                                    type="number"
                                    min="720"
                                    max="1800"
                                />
                            </label>
                            <label class="studio-field">
                                <span>Height</span>
                                <input
                                    v-model.number="
                                        navigationHttp.navigation_config.header
                                            .layout.height
                                    "
                                    type="number"
                                    min="48"
                                    max="140"
                                />
                            </label>
                            <label class="studio-field">
                                <span>Side padding</span>
                                <input
                                    v-model.number="
                                        navigationHttp.navigation_config.header
                                            .layout.horizontalPadding
                                    "
                                    type="number"
                                    min="0"
                                    max="96"
                                />
                            </label>
                            <label class="studio-field">
                                <span>Radius</span>
                                <input
                                    v-model.number="
                                        navigationHttp.navigation_config.header
                                            .layout.borderRadius
                                    "
                                    type="number"
                                    min="0"
                                    max="80"
                                />
                            </label>
                            <label class="studio-field">
                                <span>Border width</span>
                                <input
                                    v-model.number="
                                        navigationHttp.navigation_config.header
                                            .layout.borderWidth
                                    "
                                    type="number"
                                    min="0"
                                    max="6"
                                />
                            </label>
                            <label class="studio-field">
                                <span>Border color</span>
                                <input
                                    v-model="
                                        navigationHttp.navigation_config.header
                                            .layout.borderColor
                                    "
                                    type="color"
                                />
                            </label>
                            <label class="studio-field">
                                <span>Position</span>
                                <select
                                    v-model="
                                        navigationHttp.navigation_config.header
                                            .layout.position
                                    "
                                >
                                    <option value="static">Static</option>
                                    <option value="sticky">Sticky</option>
                                </select>
                            </label>
                            <label class="studio-field">
                                <span>Sticky offset</span>
                                <input
                                    v-model.number="
                                        navigationHttp.navigation_config.header
                                            .layout.stickyOffset
                                    "
                                    type="number"
                                    min="0"
                                    max="160"
                                />
                            </label>
                            <label class="studio-field col-span-2">
                                <span>Shadow</span>
                                <select
                                    v-model="
                                        navigationHttp.navigation_config.header
                                            .layout.shadow
                                    "
                                >
                                    <option value="none">None</option>
                                    <option value="small">Small</option>
                                    <option value="medium">Medium</option>
                                    <option value="large">Large</option>
                                </select>
                            </label>
                        </div>
                        <label class="studio-check">
                            <input
                                v-model="
                                    navigationHttp.navigation_config.header
                                        .layout.fullWidth
                                "
                                type="checkbox"
                            />
                            Stretch navbar to full width
                        </label>
                    </div>
                </div>

                <fieldset
                    v-if="selectedTarget.type === 'buttons'"
                    id="navbar-settings-panel-buttons"
                    role="tabpanel"
                    aria-labelledby="navbar-settings-tab-buttons"
                    tabindex="0"
                >
                    <legend
                        class="text-[9px] font-bold tracking-[0.12em] text-editor-text-muted uppercase"
                    >
                        Navbar buttons
                    </legend>
                    <p
                        class="mt-1 text-[10px] leading-4 text-editor-text-muted"
                    >
                        Add up to three actions and control their placement and
                        appearance.
                    </p>

                    <div class="mt-3">
                        <span
                            class="text-[8px] font-bold tracking-[0.1em] text-editor-text-muted uppercase"
                        >
                            Position
                        </span>
                        <div class="mt-1.5 grid grid-cols-2 gap-2">
                            <button
                                v-for="[key, position] in actionPositionEntries"
                                :key="key"
                                type="button"
                                class="min-h-9 rounded-[5px] border px-2.5 text-[9px] font-semibold transition"
                                :class="
                                    navigationHttp.navigation_config.header
                                        .actionPosition === key
                                        ? 'border-editor-text bg-editor-text text-white'
                                        : 'border-editor-border bg-editor-panel text-editor-text-muted hover:bg-editor-panel-muted hover:text-editor-text'
                                "
                                :title="position.description"
                                :aria-pressed="
                                    navigationHttp.navigation_config.header
                                        .actionPosition === key
                                "
                                @click="selectActionPosition(key)"
                            >
                                {{ position.label }}
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="
                            navigationHttp.navigation_config.header.actionStyle
                        "
                        class="mt-3"
                    >
                        <button
                            type="button"
                            class="studio-tool-button mb-2 w-full"
                            :aria-expanded="showAdvancedButtons"
                            @click="showAdvancedButtons = !showAdvancedButtons"
                        >
                            {{ showAdvancedButtons ? 'Hide' : 'Show' }}
                            button colors &amp; style
                        </button>
                        <div v-if="showAdvancedButtons">
                            <span
                                class="text-[8px] font-bold tracking-[0.1em] text-editor-text-muted uppercase"
                            >
                                Filled button colors
                            </span>
                            <div class="mt-1.5 grid grid-cols-2 gap-2">
                                <button
                                    v-for="mode in ['theme', 'custom'] as const"
                                    :key="mode"
                                    type="button"
                                    class="min-h-9 rounded-[5px] border px-2.5 text-[9px] font-semibold capitalize transition"
                                    :class="
                                        navigationHttp.navigation_config.header
                                            .actionStyle.mode === mode
                                            ? 'border-editor-text bg-editor-text text-white'
                                            : 'border-editor-border bg-editor-panel text-editor-text-muted hover:bg-editor-panel-muted hover:text-editor-text'
                                    "
                                    :aria-pressed="
                                        navigationHttp.navigation_config.header
                                            .actionStyle.mode === mode
                                    "
                                    @click="selectActionStyleMode(mode)"
                                >
                                    {{
                                        mode === 'theme' ? 'Site primary' : mode
                                    }}
                                </button>
                            </div>

                            <div
                                v-if="
                                    navigationHttp.navigation_config.header
                                        .actionStyle.mode === 'custom'
                                "
                                class="mt-2 grid grid-cols-2 gap-2"
                            >
                                <label
                                    class="rounded-[5px] border border-editor-border bg-editor-panel p-2.5"
                                >
                                    <span
                                        class="block text-[8px] font-bold tracking-[0.1em] text-editor-text-muted uppercase"
                                    >
                                        Button
                                    </span>
                                    <input
                                        v-model="
                                            navigationHttp.navigation_config
                                                .header.actionStyle
                                                .backgroundColor
                                        "
                                        type="color"
                                        class="mt-2 h-7 w-full cursor-pointer rounded border border-editor-border bg-transparent p-0.5"
                                    />
                                </label>
                                <label
                                    class="rounded-[5px] border border-editor-border bg-editor-panel p-2.5"
                                >
                                    <span
                                        class="block text-[8px] font-bold tracking-[0.1em] text-editor-text-muted uppercase"
                                    >
                                        Button text
                                    </span>
                                    <input
                                        v-model="
                                            navigationHttp.navigation_config
                                                .header.actionStyle.textColor
                                        "
                                        type="color"
                                        class="mt-2 h-7 w-full cursor-pointer rounded border border-editor-border bg-transparent p-0.5"
                                    />
                                </label>
                                <label
                                    class="rounded-[5px] border border-editor-border bg-editor-panel p-2.5"
                                >
                                    <span
                                        class="block text-[8px] font-bold tracking-[0.1em] text-editor-text-muted uppercase"
                                    >
                                        Hover button
                                    </span>
                                    <input
                                        v-model="
                                            navigationHttp.navigation_config
                                                .header.actionStyle
                                                .hoverBackgroundColor
                                        "
                                        type="color"
                                        class="mt-2 h-7 w-full cursor-pointer rounded border border-editor-border bg-transparent p-0.5"
                                    />
                                </label>
                                <label
                                    class="rounded-[5px] border border-editor-border bg-editor-panel p-2.5"
                                >
                                    <span
                                        class="block text-[8px] font-bold tracking-[0.1em] text-editor-text-muted uppercase"
                                    >
                                        Hover text
                                    </span>
                                    <input
                                        v-model="
                                            navigationHttp.navigation_config
                                                .header.actionStyle
                                                .hoverTextColor
                                        "
                                        type="color"
                                        class="mt-2 h-7 w-full cursor-pointer rounded border border-editor-border bg-transparent p-0.5"
                                    />
                                </label>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="navigationHttp.navigation_config.header.actions"
                        class="mt-4 grid gap-2"
                    >
                        <div
                            v-for="(action, index) in navigationHttp
                                .navigation_config.header.actions"
                            :key="index"
                            class="rounded-[6px] border border-editor-border bg-editor-panel-muted p-3"
                        >
                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <strong
                                    class="text-[9px] font-semibold text-editor-text"
                                >
                                    Button {{ index + 1 }}
                                </strong>
                                <button
                                    type="button"
                                    class="text-[9px] font-semibold text-editor-text-muted hover:text-red-600"
                                    @click="removeAction(index)"
                                >
                                    Remove
                                </button>
                            </div>
                            <input
                                v-model="action.label"
                                type="text"
                                maxlength="40"
                                placeholder="Button label"
                                class="mt-2 h-9 w-full rounded-[5px] border border-editor-border bg-editor-panel px-3 text-[10px] text-editor-text outline-none focus:border-editor-border-strong"
                            />
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <select
                                    v-model="action.type"
                                    class="h-9 rounded-[5px] border border-editor-border bg-editor-panel px-2 text-[10px] text-editor-text outline-none"
                                >
                                    <option value="internal">
                                        Internal page
                                    </option>
                                    <option value="external">
                                        External URL
                                    </option>
                                </select>
                                <select
                                    v-model="action.variant"
                                    class="h-9 rounded-[5px] border border-editor-border bg-editor-panel px-2 text-[10px] text-editor-text outline-none"
                                >
                                    <option
                                        v-for="[
                                            key,
                                            label,
                                        ] in actionVariantEntries"
                                        :key="key"
                                        :value="key"
                                    >
                                        {{ label }}
                                    </option>
                                </select>
                            </div>
                            <input
                                v-if="action.type === 'external'"
                                v-model="action.href"
                                type="url"
                                placeholder="https://example.com"
                                class="mt-2 h-9 w-full rounded-[5px] border border-editor-border bg-editor-panel px-3 text-[10px] text-editor-text outline-none focus:border-editor-border-strong"
                            />
                            <input
                                v-else
                                v-model="action.slug"
                                type="text"
                                placeholder="Page slug, e.g. contact"
                                class="mt-2 h-9 w-full rounded-[5px] border border-editor-border bg-editor-panel px-3 text-[10px] text-editor-text outline-none focus:border-editor-border-strong"
                            />
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <label class="studio-field">
                                    <span>Size</span>
                                    <select v-model="action.size">
                                        <option value="small">Small</option>
                                        <option value="medium">Medium</option>
                                        <option value="large">Large</option>
                                    </select>
                                </label>
                                <label class="studio-field">
                                    <span>Hover animation</span>
                                    <select v-model="action.animation">
                                        <option value="color">Color</option>
                                        <option value="lift">Lift</option>
                                        <option value="scale">Scale</option>
                                        <option value="arrow">
                                            Arrow move
                                        </option>
                                        <option value="none">None</option>
                                    </select>
                                </label>
                                <label class="studio-field">
                                    <span>Icon</span>
                                    <select v-model="action.icon">
                                        <option value="none">None</option>
                                        <option value="arrow">Arrow</option>
                                        <option value="bag">Bag</option>
                                    </select>
                                </label>
                                <label class="studio-field">
                                    <span>Icon position</span>
                                    <select v-model="action.iconPosition">
                                        <option value="start">Start</option>
                                        <option value="end">End</option>
                                    </select>
                                </label>
                            </div>
                            <div class="mt-2 grid grid-cols-3 gap-2">
                                <label class="studio-field">
                                    <span>Radius</span>
                                    <input
                                        v-model.number="action.borderRadius"
                                        type="number"
                                        min="0"
                                        max="40"
                                    />
                                </label>
                                <label class="studio-field">
                                    <span>Border</span>
                                    <input
                                        v-model="action.borderColor"
                                        type="color"
                                    />
                                </label>
                                <label class="studio-field">
                                    <span>Button</span>
                                    <input
                                        v-model="action.backgroundColor"
                                        type="color"
                                    />
                                </label>
                                <label class="studio-field">
                                    <span>Text</span>
                                    <input
                                        v-model="action.textColor"
                                        type="color"
                                    />
                                </label>
                                <label class="studio-field">
                                    <span>Hover</span>
                                    <input
                                        v-model="action.hoverBackgroundColor"
                                        type="color"
                                    />
                                </label>
                                <label class="studio-field">
                                    <span>Hover text</span>
                                    <input
                                        v-model="action.hoverTextColor"
                                        type="color"
                                    />
                                </label>
                            </div>
                            <div
                                class="mt-2 flex items-center justify-end gap-1"
                            >
                                <label class="studio-check mr-auto">
                                    <input
                                        v-model="action.disabled"
                                        type="checkbox"
                                    />
                                    Disabled
                                </label>
                                <button
                                    type="button"
                                    class="studio-icon-button"
                                    :disabled="index === 0"
                                    aria-label="Move button up"
                                    @click="
                                        moveItem(
                                            navigationHttp.navigation_config
                                                .header.actions,
                                            index,
                                            -1,
                                        )
                                    "
                                >
                                    <ArrowUp :size="12" />
                                </button>
                                <button
                                    type="button"
                                    class="studio-icon-button"
                                    :disabled="index === actionCount - 1"
                                    aria-label="Move button down"
                                    @click="
                                        moveItem(
                                            navigationHttp.navigation_config
                                                .header.actions,
                                            index,
                                            1,
                                        )
                                    "
                                >
                                    <ArrowDown :size="12" />
                                </button>
                                <button
                                    type="button"
                                    class="studio-icon-button"
                                    :disabled="actionCount >= 3"
                                    aria-label="Duplicate button"
                                    @click="duplicateAction(index)"
                                >
                                    <Copy :size="12" />
                                </button>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="h-9 rounded-[5px] border border-dashed border-editor-border-strong bg-editor-panel text-[9px] font-semibold text-editor-text transition hover:bg-editor-panel-muted disabled:cursor-not-allowed disabled:opacity-45"
                            :disabled="actionCount >= 3"
                            @click="addAction"
                        >
                            {{
                                actionCount >= 3
                                    ? 'Maximum of 3 buttons'
                                    : '+ Add button'
                            }}
                        </button>
                    </div>
                </fieldset>

                <fieldset
                    v-if="
                        selectedTarget.type === 'mobile' &&
                        navigationHttp.navigation_config.header.responsive
                    "
                    id="navbar-settings-panel-responsive"
                    role="tabpanel"
                    aria-labelledby="navbar-settings-tab-responsive"
                    tabindex="0"
                >
                    <legend class="studio-legend">Responsive behavior</legend>
                    <p class="studio-help">
                        Control how the navigation transforms on smaller
                        screens.
                    </p>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <label class="studio-field">
                            <span>Breakpoint</span>
                            <input
                                v-model.number="
                                    navigationHttp.navigation_config.header
                                        .responsive.breakpoint
                                "
                                type="number"
                                min="480"
                                max="1024"
                            />
                        </label>
                        <label class="studio-field">
                            <span>Mobile menu</span>
                            <select
                                v-model="
                                    navigationHttp.navigation_config.header
                                        .responsive.menuStyle
                                "
                            >
                                <option value="dropdown">Dropdown</option>
                                <option value="drawer">Side drawer</option>
                                <option value="fullscreen">Full screen</option>
                            </select>
                        </label>
                        <label class="studio-field">
                            <span>Menu icon</span>
                            <select
                                v-model="
                                    navigationHttp.navigation_config.header
                                        .responsive.menuIcon
                                "
                            >
                                <option value="menu">Menu lines</option>
                                <option value="dots">Dots</option>
                                <option value="grid">Grid</option>
                            </select>
                        </label>
                        <label class="studio-field">
                            <span>Link alignment</span>
                            <select
                                v-model="
                                    navigationHttp.navigation_config.header
                                        .responsive.alignment
                                "
                            >
                                <option value="left">Left</option>
                                <option value="center">Center</option>
                            </select>
                        </label>
                    </div>
                    <label class="studio-check mt-3">
                        <input
                            v-model="
                                navigationHttp.navigation_config.header
                                    .responsive.showActions
                            "
                            type="checkbox"
                        />
                        Show action buttons on mobile
                    </label>

                    <div
                        v-if="navigationHttp.navigation_config.header.states"
                        class="mt-4 border-t border-editor-border pt-4"
                    >
                        <span class="studio-legend"
                            >Sticky and disabled states</span
                        >
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            <label class="studio-field">
                                <span>Scrolled surface</span>
                                <input
                                    v-model="
                                        navigationHttp.navigation_config.header
                                            .states.scrolledBackgroundColor
                                    "
                                    type="color"
                                />
                            </label>
                            <label class="studio-field">
                                <span>Scrolled text</span>
                                <input
                                    v-model="
                                        navigationHttp.navigation_config.header
                                            .states.scrolledTextColor
                                    "
                                    type="color"
                                />
                            </label>
                            <label class="studio-field col-span-2">
                                <span
                                    >Disabled opacity
                                    {{
                                        navigationHttp.navigation_config.header
                                            .states.disabledOpacity
                                    }}%</span
                                >
                                <input
                                    v-model.number="
                                        navigationHttp.navigation_config.header
                                            .states.disabledOpacity
                                    "
                                    type="range"
                                    min="20"
                                    max="70"
                                />
                            </label>
                        </div>
                    </div>
                </fieldset>
            </div>

            <div
                class="order-2 flex min-h-0 min-w-0 flex-col overflow-y-auto bg-editor-panel p-4 sm:p-5 lg:order-none lg:col-start-2 lg:row-start-1"
            >
                <div
                    class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <span
                            class="text-[9px] font-bold tracking-[0.12em] text-editor-text-muted uppercase"
                        >
                            Live preview
                        </span>
                        <p class="mt-1 text-[10px] text-editor-text-muted">
                            <span v-if="usesSampleContent">
                                Sample links are used only for this preview.
                            </span>
                            <span v-else
                                >Your saved navigation content is shown.</span
                            >
                            <template v-if="selectedMenuMode === 'mega'">
                                <template v-if="previewMode === 'mobile'">
                                    Click the menu icon to test the mobile
                                    interaction.
                                </template>
                                <template v-else>
                                    Click
                                    <strong
                                        class="font-semibold text-editor-text"
                                    >
                                        {{
                                            navigationHttp.navigation_config
                                                .header.menu?.triggerLabel ||
                                            'Explore'
                                        }}
                                    </strong>
                                    in the navbar to open the mega menu.
                                </template>
                            </template>
                            <template v-else>
                                Click the logo, links, or buttons in the preview
                                to open their settings.
                            </template>
                        </p>
                    </div>

                    <div
                        class="inline-flex w-fit rounded-[5px] border border-editor-border bg-editor-panel-muted p-1"
                    >
                        <button
                            v-for="mode in [
                                'desktop',
                                'tablet',
                                'mobile',
                            ] as PreviewMode[]"
                            :key="mode"
                            type="button"
                            class="inline-flex h-7 items-center gap-1.5 rounded-[3px] px-2.5 text-[9px] font-semibold capitalize transition motion-reduce:transition-none"
                            :class="
                                previewMode === mode
                                    ? 'bg-editor-text text-white'
                                    : 'text-editor-text-muted hover:bg-editor-panel hover:text-editor-text'
                            "
                            @click="previewMode = mode"
                        >
                            <Monitor v-if="mode === 'desktop'" :size="11" />
                            <Laptop v-else-if="mode === 'tablet'" :size="11" />
                            <Smartphone v-else :size="11" />
                            {{ mode }}
                        </button>
                    </div>
                </div>

                <div
                    v-if="compareWithSaved"
                    class="mb-3 rounded-[5px] border border-blue-200 bg-blue-50 px-3 py-2 text-[10px] text-blue-800"
                >
                    Showing the last saved navbar. Select “Show edits” to return
                    to your working version.
                </div>
                <div
                    v-if="accessibilityWarnings.length"
                    class="mb-3 rounded-[5px] border border-amber-200 bg-amber-50 px-3 py-2"
                    role="status"
                >
                    <strong
                        class="text-[9px] font-bold tracking-[0.08em] text-amber-900 uppercase"
                    >
                        Accessibility checks
                    </strong>
                    <ul class="mt-1 grid gap-1 pl-4 text-[10px] text-amber-800">
                        <li
                            v-for="warning in accessibilityWarnings"
                            :key="warning"
                            class="list-disc"
                        >
                            {{ warning }}
                        </li>
                    </ul>
                </div>

                <div
                    class="navbar-preview-stage min-h-[280px] flex-1 overflow-auto rounded-[7px] border border-editor-border bg-editor-bg p-3 sm:p-4"
                >
                    <div
                        class="navbar-preview-runtime mx-auto min-h-[420px] overflow-visible bg-[var(--theme-bg)] text-[var(--theme-text)] shadow-[0_20px_70px_rgb(0_0_0/50%)] transition-[width] duration-200 motion-reduce:transition-none"
                        :style="[
                            { width: previewWidth, maxWidth: '100%' },
                            cssVars,
                        ]"
                    >
                        <div @click.capture="keepPreviewInPlace">
                            <SiteHeader
                                :navigation-config="previewNavigationConfig"
                                :tenant-name="tenantName"
                                :is-editable="true"
                                :preview-responsive-mode="previewMode"
                                @select="handlePreviewSelect"
                            />
                        </div>
                        <div class="grid gap-3 px-8 py-10 opacity-60">
                            <span
                                class="h-3 w-24 rounded-full bg-[var(--theme-primary)]"
                            ></span>
                            <span
                                class="h-7 w-3/5 rounded bg-[var(--theme-text)]"
                            ></span>
                            <span
                                class="h-2 w-4/5 rounded bg-[var(--theme-text)] opacity-35"
                            ></span>
                            <span
                                class="h-2 w-2/3 rounded bg-[var(--theme-text)] opacity-25"
                            ></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer
            class="flex shrink-0 flex-col gap-3 border-t border-editor-border bg-editor-panel px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5"
        >
            <div>
                <p class="text-[10px] leading-4 text-editor-text-muted">
                    Saving updates the shared navbar in the editor and public
                    site.
                </p>
            </div>
            <button
                type="button"
                class="inline-flex h-10 items-center justify-center gap-2 rounded-[5px] border border-editor-text bg-editor-text px-5 text-[11px] font-bold text-white shadow-[0_8px_20px_rgb(24_24_27/18%)] transition hover:bg-zinc-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-zinc-500 disabled:cursor-not-allowed disabled:opacity-45 motion-reduce:transition-none"
                :disabled="navigationHttp.processing"
                @click="saveNavbar"
            >
                <Loader2
                    v-if="navigationHttp.processing"
                    :size="14"
                    class="animate-spin motion-reduce:animate-none"
                />
                <Save v-else :size="14" />
                {{ navigationHttp.processing ? 'Saving...' : 'Save navbar' }}
            </button>
        </footer>

        <MediaPicker
            v-if="showMediaPicker"
            @select="onMediaSelected"
            @close="showMediaPicker = false"
        />
    </section>
</template>

<style>
.navbar-preview-runtime {
    container-type: inline-size;
}

.navbar-studio-shell {
    box-shadow: var(--editor-shadow) !important;
}

.navbar-studio-shell fieldset {
    min-width: 0;
    margin: 0;
    padding: 0;
    border: 0;
}

.navbar-studio-shell legend {
    padding: 0;
}

.studio-tool-button,
.studio-icon-button,
.studio-media-button,
.studio-choice,
.studio-empty-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    color: var(--editor-text-muted);
    background: var(--editor-panel);
    border: 1px solid var(--editor-border);
    border-radius: 5px;
    font-size: 9px;
    font-weight: 650;
    transition:
        color 150ms ease,
        background-color 150ms ease,
        border-color 150ms ease;
}

.studio-tool-button {
    min-height: 30px;
    padding: 0 0.55rem;
}

.studio-icon-button {
    width: 30px;
    height: 30px;
    flex: 0 0 auto;
}

.studio-media-button,
.studio-empty-button {
    min-height: 38px;
    padding: 0.5rem 0.75rem;
    border-style: dashed;
}

.studio-empty-button {
    width: 100%;
}

.studio-choice {
    min-height: 36px;
    padding: 0.4rem 0.6rem;
}

.studio-choice-active {
    color: white;
    background: var(--editor-text);
    border-color: var(--editor-text);
}

.studio-tool-button:hover:not(:disabled),
.studio-icon-button:hover:not(:disabled),
.studio-media-button:hover,
.studio-choice:hover:not(.studio-choice-active),
.studio-empty-button:hover {
    color: var(--editor-text);
    background: var(--editor-panel-muted);
    border-color: var(--editor-border-strong);
}

.studio-tool-button:disabled,
.studio-icon-button:disabled {
    cursor: not-allowed;
    opacity: 0.4;
}

.studio-legend {
    display: block;
    color: var(--editor-text-muted);
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.studio-help {
    margin-top: 0.25rem;
    color: var(--editor-text-muted);
    font-size: 10px;
    line-height: 1rem;
}

.studio-field {
    display: grid;
    gap: 0.35rem;
    color: var(--editor-text-muted);
    font-size: 8px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.studio-field input,
.studio-field select,
.studio-inline-input,
.studio-select {
    width: 100%;
    height: 34px;
    min-width: 0;
    padding: 0 0.55rem;
    color: var(--editor-text);
    background: var(--editor-panel);
    border: 1px solid var(--editor-border);
    border-radius: 5px;
    font-size: 10px;
    font-weight: 500;
    letter-spacing: normal;
    outline: none;
    text-transform: none;
}

.studio-field input[type='color'] {
    padding: 0.25rem;
    cursor: pointer;
}

.studio-field input[type='range'] {
    padding: 0;
}

.studio-field input:focus,
.studio-field select:focus,
.studio-inline-input:focus,
.studio-select:focus {
    border-color: var(--editor-border-strong);
    outline: 2px solid color-mix(in srgb, var(--editor-text) 12%, transparent);
    outline-offset: 1px;
}

.studio-check {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--editor-text-muted);
    font-size: 10px;
    font-weight: 600;
}

.studio-check input {
    accent-color: var(--editor-text);
}
</style>
