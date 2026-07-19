<script setup lang="ts">
import { computed, inject, ref, watch } from 'vue';

type CardContentItem = {
    key: string;
    label: string;
    visible: boolean;
};

const props = defineProps<{
    selectedBlock: {
        props: Record<string, any>;
    };
}>();

const forceSave = inject<(() => void) | null>('forceSave', null);

const allTabs = [
    { key: 'content', label: 'Content', simple: true },
    { key: 'layout', label: 'Layout', simple: true },
    { key: 'card', label: 'Card', simple: true },
    { key: 'style', label: 'Style', simple: false },
    { key: 'responsive', label: 'Responsive', simple: false },
    { key: 'advanced', label: 'Advanced', simple: false },
];

const cardContentDefaults: CardContentItem[] = [
    { key: 'image', label: 'Product image', visible: true },
    { key: 'category', label: 'Category', visible: false },
    { key: 'name', label: 'Product name', visible: true },
    { key: 'price', label: 'Price', visible: true },
    { key: 'description', label: 'Description', visible: false },
    { key: 'availability', label: 'Availability', visible: true },
    { key: 'button', label: 'Add to cart', visible: false },
];

const presets: Record<string, Record<string, unknown>> = {
    minimal: {
        cardSurface: 'transparent',
        cardBorder: 'none',
        cardShadow: 'none',
        cardCorners: 'none',
        cardPadding: 0,
        cardSpacing: 10,
        imageRatio: 'portrait',
        hoverEffect: 'fade',
    },
    editorial: {
        layoutType: 'editorial',
        cardSurface: 'transparent',
        cardBorder: 'none',
        cardShadow: 'none',
        cardCorners: 'none',
        cardPadding: 0,
        cardSpacing: 14,
        imageRatio: 'landscape',
        hoverEffect: 'zoom',
    },
    luxury: {
        cardSurface: 'surface',
        cardBorder: 'subtle',
        cardShadow: 'soft',
        cardCorners: 'theme',
        cardPadding: 18,
        cardSpacing: 12,
        imageRatio: 'portrait',
        hoverEffect: 'lift',
    },
    modern: {
        cardSurface: 'surface',
        cardBorder: 'none',
        cardShadow: 'soft',
        cardCorners: 'large',
        cardPadding: 16,
        cardSpacing: 10,
        imageRatio: 'square',
        hoverEffect: 'lift',
    },
    compact: {
        layoutType: 'list',
        cardSurface: 'transparent',
        cardBorder: 'subtle',
        cardShadow: 'none',
        cardCorners: 'theme',
        cardPadding: 12,
        cardSpacing: 6,
        imageRatio: 'square',
        hoverEffect: 'none',
    },
    restaurant: {
        layoutType: 'list',
        cardSurface: 'transparent',
        cardBorder: 'divider',
        cardShadow: 'none',
        cardCorners: 'none',
        cardPadding: 10,
        cardSpacing: 6,
        imageRatio: 'square',
        hoverEffect: 'none',
    },
    fashion: {
        cardSurface: 'transparent',
        cardBorder: 'none',
        cardShadow: 'none',
        cardCorners: 'none',
        cardPadding: 0,
        cardSpacing: 12,
        imageRatio: 'portrait',
        hoverEffect: 'image-swap',
    },
    marketplace: {
        cardSurface: 'surface',
        cardBorder: 'subtle',
        cardShadow: 'none',
        cardCorners: 'small',
        cardPadding: 12,
        cardSpacing: 8,
        imageRatio: 'square',
        hoverEffect: 'button-reveal',
    },
    electronics: {
        cardSurface: 'surface',
        cardBorder: 'subtle',
        cardShadow: 'soft',
        cardCorners: 'small',
        cardPadding: 14,
        cardSpacing: 8,
        imageRatio: 'square',
        hoverEffect: 'button-reveal',
        buttonStyle: 'primary',
    },
};

const presentationDefaults: Record<string, unknown> = {
    sort: 'featured',
    editingMode: 'simple',
    layoutType: 'grid',
    tabletColumns: 2,
    mobileColumns: 1,
    gap: 24,
    alignment: 'left',
    imageRatio: 'portrait',
    tabletImageRatio: 'portrait',
    mobileImageRatio: 'portrait',
    cardPreset: 'minimal',
    cardSurface: 'transparent',
    cardBorder: 'none',
    cardShadow: 'none',
    cardCorners: 'none',
    cardPadding: 0,
    cardSpacing: 10,
    hoverEffect: 'fade',
    buttonStyle: 'primary',
    buttonLabel: 'Add to cart',
    showBadge: 'yes',
    showComparePrice: 'yes',
};

const activeTab = ref('content');
const blockProps = computed(() => props.selectedBlock.props);
const editingMode = computed({
    get: () => blockProps.value.editingMode || 'simple',
    set: (value: string) => {
        blockProps.value.editingMode = value;
    },
});

watch(
    () => props.selectedBlock,
    (selectedBlock) => {
        Object.entries(presentationDefaults).forEach(([key, value]) => {
            if (selectedBlock.props[key] === undefined) {
                selectedBlock.props[key] = value;
            }
        });

        selectedBlock.props.desktopColumns ??= selectedBlock.props.columns || 4;
        selectedBlock.props.showControls =
            selectedBlock.props.showControls === true
                ? 'yes'
                : selectedBlock.props.showControls || 'no';
        selectedBlock.props.paginationEnabled ??=
            selectedBlock.props.showControls === 'yes' ? 'yes' : 'no';

        if (
            !Array.isArray(selectedBlock.props.cardContent) ||
            selectedBlock.props.cardContent.length === 0
        ) {
            blockProps.value.cardContent = cardContentDefaults.map((item) => ({
                ...item,
            }));
        }
    },
    { immediate: true },
);

const visibleTabs = computed(() =>
    editingMode.value === 'advanced'
        ? allTabs
        : allTabs.filter((tab) => tab.simple),
);
const cardContent = computed<CardContentItem[]>(() => {
    const configured = blockProps.value.cardContent;

    return Array.isArray(configured) && configured.length > 0
        ? (configured as CardContentItem[])
        : cardContentDefaults;
});

watch(editingMode, () => {
    if (!visibleTabs.value.some((tab) => tab.key === activeTab.value)) {
        activeTab.value = 'content';
    }
});

const applyPreset = (preset: string): void => {
    Object.assign(blockProps.value, presets[preset], { cardPreset: preset });
    forceSave?.();
};

const moveCardContent = (index: number, direction: number): void => {
    const target = index + direction;

    if (target < 0 || target >= cardContent.value.length) {
        return;
    }

    const reordered = [...cardContent.value];
    [reordered[index], reordered[target]] = [
        reordered[target],
        reordered[index],
    ];
    blockProps.value.cardContent = reordered;
    forceSave?.();
};
</script>

<template>
    <section class="space-y-4" aria-label="Smart Product Grid settings">
        <div class="grid grid-cols-2 gap-1 rounded-lg bg-slate-900 p-1">
            <button
                v-for="mode in ['simple', 'advanced']"
                :key="mode"
                type="button"
                class="rounded-md px-2 py-1.5 text-xs font-semibold capitalize transition"
                :class="
                    editingMode === mode
                        ? 'bg-indigo-500 text-white'
                        : 'text-slate-400 hover:text-white'
                "
                @click="editingMode = mode"
            >
                {{ mode }}
            </button>
        </div>

        <div class="flex gap-1 overflow-x-auto pb-1" role="tablist">
            <button
                v-for="tab in visibleTabs"
                :key="tab.key"
                type="button"
                role="tab"
                class="shrink-0 rounded-md px-2.5 py-1.5 text-[11px] font-semibold transition"
                :class="
                    activeTab === tab.key
                        ? 'bg-slate-700 text-white'
                        : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'
                "
                :aria-selected="activeTab === tab.key"
                @click="activeTab = tab.key"
            >
                {{ tab.label }}
            </button>
        </div>

        <div v-if="activeTab === 'content'" class="space-y-4">
            <label class="inspector-field">
                <span>Product source</span>
                <select v-model="blockProps.sourceKey">
                    <option value="featured">Featured products</option>
                    <option value="all">All products</option>
                    <option value="related">Related products</option>
                </select>
            </label>
            <p class="text-[11px] leading-5 text-slate-500">
                Source selection changes which provider products appear. Product
                records remain read-only in the builder.
            </p>
            <label class="inspector-field">
                <span>Default sort</span>
                <select v-model="blockProps.sort">
                    <option value="featured">Provider order</option>
                    <option value="price-low">Price: low to high</option>
                    <option value="price-high">Price: high to low</option>
                    <option value="title">Alphabetical</option>
                </select>
            </label>
            <label class="inspector-field">
                <span>Product limit</span>
                <input
                    v-model.number="blockProps.limit"
                    type="number"
                    min="1"
                    max="50"
                />
            </label>
            <label class="inspector-field">
                <span>Pagination</span>
                <select v-model="blockProps.paginationEnabled">
                    <option value="no">Disabled</option>
                    <option value="yes">Enabled</option>
                </select>
            </label>
            <label
                v-if="blockProps.paginationEnabled === 'yes'"
                class="inspector-field"
            >
                <span>Products per page</span>
                <input
                    v-model.number="blockProps.pageSize"
                    type="number"
                    min="2"
                    max="24"
                />
            </label>
        </div>

        <div v-else-if="activeTab === 'layout'" class="space-y-4">
            <label class="inspector-field">
                <span>Layout type</span>
                <select v-model="blockProps.layoutType">
                    <option value="grid">Grid</option>
                    <option value="carousel">Carousel</option>
                    <option value="list">List</option>
                    <option value="editorial">Editorial</option>
                </select>
            </label>
            <label class="inspector-field">
                <span>Alignment</span>
                <select v-model="blockProps.alignment">
                    <option value="left">Left</option>
                    <option value="center">Center</option>
                </select>
            </label>
            <label class="inspector-field">
                <span>Image ratio</span>
                <select v-model="blockProps.imageRatio">
                    <option value="square">Square</option>
                    <option value="portrait">Portrait</option>
                    <option value="landscape">Landscape</option>
                    <option value="original">Original</option>
                </select>
            </label>
            <label class="inspector-field">
                <span>Grid gap: {{ blockProps.gap ?? 24 }}px</span>
                <input
                    v-model.number="blockProps.gap"
                    type="range"
                    min="0"
                    max="64"
                />
            </label>
        </div>

        <div v-else-if="activeTab === 'card'" class="space-y-4">
            <div>
                <span class="field-title">Card preset</span>
                <div class="mt-2 grid grid-cols-2 gap-2">
                    <button
                        v-for="preset in Object.keys(presets)"
                        :key="preset"
                        type="button"
                        class="rounded-lg border px-2 py-2 text-left text-xs font-semibold capitalize transition"
                        :class="
                            blockProps.cardPreset === preset
                                ? 'border-indigo-400 bg-indigo-500/15 text-indigo-200'
                                : 'border-slate-700 bg-slate-800 text-slate-300 hover:border-slate-600'
                        "
                        @click="applyPreset(preset)"
                    >
                        {{ preset }}
                    </button>
                </div>
            </div>
            <div>
                <span class="field-title">Card content and order</span>
                <div class="mt-2 space-y-2">
                    <div
                        v-for="(item, index) in cardContent"
                        :key="item.key"
                        class="flex items-center gap-2 rounded-lg border border-slate-700 bg-slate-800 p-2"
                    >
                        <input
                            v-model="item.visible"
                            type="checkbox"
                            class="accent-indigo-500"
                        />
                        <span class="min-w-0 flex-1 text-xs text-slate-300">{{
                            item.label
                        }}</span>
                        <button
                            type="button"
                            class="text-slate-400 disabled:opacity-25"
                            :disabled="index === 0"
                            aria-label="Move field up"
                            @click="moveCardContent(index, -1)"
                        >
                            ↑
                        </button>
                        <button
                            type="button"
                            class="text-slate-400 disabled:opacity-25"
                            :disabled="index === cardContent.length - 1"
                            aria-label="Move field down"
                            @click="moveCardContent(index, 1)"
                        >
                            ↓
                        </button>
                    </div>
                </div>
            </div>
            <label class="inspector-field">
                <span>Compare price</span>
                <select v-model="blockProps.showComparePrice">
                    <option value="yes">Visible</option>
                    <option value="no">Hidden</option>
                </select>
            </label>
            <label class="inspector-field">
                <span>Sale badge</span>
                <select v-model="blockProps.showBadge">
                    <option value="yes">Visible</option>
                    <option value="no">Hidden</option>
                </select>
            </label>
        </div>

        <div v-else-if="activeTab === 'style'" class="space-y-4">
            <label class="inspector-field">
                <span>Card surface</span>
                <select v-model="blockProps.cardSurface">
                    <option value="transparent">Transparent</option>
                    <option value="surface">Theme surface</option>
                </select>
            </label>
            <label class="inspector-field">
                <span>Border</span>
                <select v-model="blockProps.cardBorder">
                    <option value="none">None</option>
                    <option value="subtle">Subtle</option>
                    <option value="divider">Divider</option>
                </select>
            </label>
            <label class="inspector-field">
                <span>Shadow</span>
                <select v-model="blockProps.cardShadow">
                    <option value="none">None</option>
                    <option value="soft">Soft</option>
                    <option value="strong">Strong</option>
                </select>
            </label>
            <label class="inspector-field">
                <span>Corners</span>
                <select v-model="blockProps.cardCorners">
                    <option value="none">Square</option>
                    <option value="small">Small</option>
                    <option value="theme">Theme</option>
                    <option value="large">Large</option>
                </select>
            </label>
            <label class="inspector-field">
                <span>Card padding: {{ blockProps.cardPadding ?? 0 }}px</span>
                <input
                    v-model.number="blockProps.cardPadding"
                    type="range"
                    min="0"
                    max="32"
                />
            </label>
            <label class="inspector-field">
                <span
                    >Content spacing: {{ blockProps.cardSpacing ?? 10 }}px</span
                >
                <input
                    v-model.number="blockProps.cardSpacing"
                    type="range"
                    min="4"
                    max="28"
                />
            </label>
            <label class="inspector-field">
                <span>Hover effect</span>
                <select v-model="blockProps.hoverEffect">
                    <option value="none">None</option>
                    <option value="lift">Lift</option>
                    <option value="zoom">Zoom</option>
                    <option value="fade">Fade</option>
                    <option value="image-swap">Image swap</option>
                    <option value="button-reveal">Button reveal</option>
                </select>
            </label>
            <label class="inspector-field">
                <span>Button style</span>
                <select v-model="blockProps.buttonStyle">
                    <option value="primary">Primary</option>
                    <option value="outline">Outline</option>
                    <option value="text">Text</option>
                </select>
            </label>
        </div>

        <div v-else-if="activeTab === 'responsive'" class="space-y-4">
            <label class="inspector-field">
                <span>Desktop columns</span>
                <input
                    v-model.number="blockProps.desktopColumns"
                    type="number"
                    min="1"
                    max="6"
                />
            </label>
            <label class="inspector-field">
                <span>Tablet columns</span>
                <input
                    v-model.number="blockProps.tabletColumns"
                    type="number"
                    min="1"
                    max="4"
                />
            </label>
            <label class="inspector-field">
                <span>Mobile columns</span>
                <input
                    v-model.number="blockProps.mobileColumns"
                    type="number"
                    min="1"
                    max="2"
                />
            </label>
            <label class="inspector-field">
                <span>Tablet image ratio</span>
                <select v-model="blockProps.tabletImageRatio">
                    <option value="square">Square</option>
                    <option value="portrait">Portrait</option>
                    <option value="landscape">Landscape</option>
                    <option value="original">Original</option>
                </select>
            </label>
            <label class="inspector-field">
                <span>Mobile image ratio</span>
                <select v-model="blockProps.mobileImageRatio">
                    <option value="square">Square</option>
                    <option value="portrait">Portrait</option>
                    <option value="landscape">Landscape</option>
                    <option value="original">Original</option>
                </select>
            </label>
        </div>

        <div v-else class="space-y-4">
            <label class="inspector-field">
                <span>Catalog filters</span>
                <select v-model="blockProps.showControls">
                    <option value="no">Hidden</option>
                    <option value="yes">Visible</option>
                </select>
            </label>
            <label class="inspector-field">
                <span>Section eyebrow</span>
                <input v-model="blockProps.eyebrow" type="text" />
            </label>
            <label class="inspector-field">
                <span>Section heading</span>
                <input v-model="blockProps.heading" type="text" />
            </label>
            <label class="inspector-field">
                <span>View-all label</span>
                <input v-model="blockProps.viewAllLabel" type="text" />
            </label>
            <label class="inspector-field">
                <span>View-all URL</span>
                <input v-model="blockProps.viewAllUrl" type="text" />
            </label>
            <label class="inspector-field">
                <span>Button label</span>
                <input v-model="blockProps.buttonLabel" type="text" />
            </label>
        </div>
    </section>
</template>

<style scoped>
.inspector-field {
    display: grid;
    gap: 0.35rem;
}

.inspector-field > span,
.field-title {
    color: rgb(148 163 184);
    font-size: 0.75rem;
    font-weight: 600;
}

.inspector-field select,
.inspector-field input[type='text'],
.inspector-field input[type='number'] {
    width: 100%;
    border: 1px solid rgb(51 65 85);
    border-radius: 0.5rem;
    background: rgb(30 41 59);
    padding: 0.5rem 0.75rem;
    color: white;
    font-size: 0.875rem;
    outline: none;
}

.inspector-field select:focus,
.inspector-field input:focus {
    border-color: rgb(99 102 241);
}

.inspector-field input[type='range'] {
    width: 100%;
    accent-color: rgb(99 102 241);
}
</style>
