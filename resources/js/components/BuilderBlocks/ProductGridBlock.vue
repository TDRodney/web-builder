<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { computed, inject, ref, watch } from 'vue';
import { useCommerceBlock } from '@/lib/commerce';
import { useCommerceCart } from '@/lib/commerceCart';
import type { CommerceProduct } from '@/types/commerce';
import InlineText from './InlineText.vue';

type DisplayProduct = CommerceProduct & Record<string, any>;
type CardContentItem = {
    key: string;
    label?: string;
    visible: boolean;
};

const props = defineProps<{
    nodeId?: string;
    blockProps: Record<string, any>;
}>();

const hydratedBlock = useCommerceBlock<{ products: CommerceProduct[] }>(
    props.nodeId,
);
const commerceCart = useCommerceCart();
const isEditable = inject('isEditable', false);
const selectedCategory = ref('all');
const selectedSort = ref(String(props.blockProps.sort || 'featured'));
const currentPage = ref(1);
const addStatuses = ref<Record<string, string>>({});

const defaultCardContent: CardContentItem[] = [
    { key: 'image', visible: true },
    { key: 'category', visible: false },
    { key: 'name', visible: true },
    { key: 'price', visible: true },
    { key: 'description', visible: false },
    { key: 'availability', visible: true },
    { key: 'button', visible: false },
];

const products = computed<DisplayProduct[]>(() => {
    if (hydratedBlock.value?.status === 'ready') {
        return (hydratedBlock.value.data?.products || []) as DisplayProduct[];
    }

    return (props.blockProps.products || []) as DisplayProduct[];
});
const cardContent = computed<CardContentItem[]>(() => {
    const configured = props.blockProps.cardContent;

    return Array.isArray(configured) && configured.length > 0
        ? configured.filter(
              (item): item is CardContentItem =>
                  typeof item?.key === 'string' && item.visible !== false,
          )
        : defaultCardContent.filter((item) => item.visible);
});
const categories = computed(() => [
    ...new Set(
        products.value
            .map((product) => String(product.category || ''))
            .filter(Boolean),
    ),
]);
const filteredProducts = computed(() => {
    const filtered =
        selectedCategory.value === 'all'
            ? [...products.value]
            : products.value.filter(
                  (product) => product.category === selectedCategory.value,
              );

    if (selectedSort.value === 'price-low') {
        return filtered.sort(
            (first, second) =>
                (first.price?.amountMinor || 0) -
                (second.price?.amountMinor || 0),
        );
    }

    if (selectedSort.value === 'price-high') {
        return filtered.sort(
            (first, second) =>
                (second.price?.amountMinor || 0) -
                (first.price?.amountMinor || 0),
        );
    }

    if (selectedSort.value === 'title') {
        return filtered.sort((first, second) =>
            String(first.title).localeCompare(String(second.title)),
        );
    }

    return filtered;
});
const pageSize = computed(() =>
    Math.max(1, Number(props.blockProps.pageSize || 4)),
);
const controlsVisible = computed(
    () =>
        props.blockProps.showControls === true ||
        props.blockProps.showControls === 'yes',
);
const paginationEnabled = computed(
    () =>
        props.blockProps.paginationEnabled === true ||
        props.blockProps.paginationEnabled === 'yes' ||
        (props.blockProps.paginationEnabled === undefined &&
            controlsVisible.value),
);
const pageCount = computed(() =>
    Math.max(1, Math.ceil(filteredProducts.value.length / pageSize.value)),
);
const visibleProducts = computed(() => {
    if (!paginationEnabled.value) {
        return filteredProducts.value;
    }

    const start = (currentPage.value - 1) * pageSize.value;

    return filteredProducts.value.slice(start, start + pageSize.value);
});
const layoutType = computed(() =>
    ['grid', 'carousel', 'list', 'editorial'].includes(
        props.blockProps.layoutType,
    )
        ? props.blockProps.layoutType
        : 'grid',
);
const resolvedImageRatio = (ratio: unknown): string => {
    const ratios: Record<string, string> = {
        square: '1 / 1',
        portrait: '4 / 5',
        landscape: '16 / 10',
        original: 'auto',
    };

    return ratios[String(ratio)] || ratios.portrait;
};
const gridStyle = computed(() => ({
    '--desktop-columns': String(
        Math.max(
            1,
            Number(
                props.blockProps.desktopColumns ||
                    props.blockProps.columns ||
                    4,
            ),
        ),
    ),
    '--tablet-columns': String(
        Math.max(1, Number(props.blockProps.tabletColumns || 2)),
    ),
    '--mobile-columns': String(
        Math.max(1, Number(props.blockProps.mobileColumns || 1)),
    ),
    '--product-gap': `${Math.max(0, Number(props.blockProps.gap ?? 24))}px`,
    '--desktop-image-ratio': resolvedImageRatio(props.blockProps.imageRatio),
    '--tablet-image-ratio': resolvedImageRatio(
        props.blockProps.tabletImageRatio || props.blockProps.imageRatio,
    ),
    '--mobile-image-ratio': resolvedImageRatio(
        props.blockProps.mobileImageRatio || props.blockProps.imageRatio,
    ),
}));
const cardStyle = computed(() => ({
    '--card-padding': `${Math.max(0, Number(props.blockProps.cardPadding ?? 0))}px`,
    '--card-spacing': `${Math.max(0, Number(props.blockProps.cardSpacing ?? 10))}px`,
}));
watch([selectedCategory, selectedSort], () => {
    currentPage.value = 1;
});
watch(
    () => props.blockProps.sort,
    (sort) => {
        selectedSort.value = String(sort || 'featured');
    },
);
watch(pageCount, (lastPage) => {
    currentPage.value = Math.min(currentPage.value, lastPage);
});

const imageFor = (product: DisplayProduct): string =>
    product.images?.[0]?.src || product.imageSrc || '';
const secondaryImageFor = (product: DisplayProduct): string =>
    product.images?.[1]?.src || '';
const imageAltFor = (product: DisplayProduct): string =>
    product.images?.[0]?.alt || product.imageAlt || product.title;
const priceFor = (product: DisplayProduct): string =>
    product.price?.formatted || product.priceLabel || '';
const comparePriceFor = (product: DisplayProduct): string =>
    product.compareAtPrice?.formatted || product.compareAtLabel || '';
const productKey = (product: DisplayProduct): string =>
    String(product.id || product.key || product.title);
const firstAvailableVariant = (product: DisplayProduct) =>
    product.variants?.find((variant: { available?: boolean }) =>
        Boolean(variant.available),
    );
const canAddProduct = (product: DisplayProduct): boolean =>
    hydratedBlock.value?.status === 'ready' &&
    product.available !== false &&
    Boolean(firstAvailableVariant(product));

const addToCart = async (product: DisplayProduct): Promise<void> => {
    const variant = firstAvailableVariant(product);

    if (isEditable || !commerceCart || !variant) {
        return;
    }

    const key = productKey(product);
    addStatuses.value[key] = '';
    const added = await commerceCart.add(variant.id, 1);
    addStatuses.value[key] = added
        ? 'Added to your bag.'
        : commerceCart.error.value;
};
</script>

<template>
    <section
        class="product-section"
        :data-align="blockProps.alignment || 'left'"
    >
        <div
            v-if="hydratedBlock?.status === 'unavailable'"
            class="connection-note"
            role="status"
        >
            {{ hydratedBlock.message }} Showing editable fallback products.
        </div>
        <header>
            <div>
                <InlineText
                    tag="p"
                    :value="blockProps.eyebrow"
                    placeholder="Section eyebrow"
                    aria-label="Product section eyebrow"
                    @update:value="blockProps.eyebrow = $event"
                />
                <InlineText
                    tag="h2"
                    :value="blockProps.heading"
                    placeholder="Product section heading"
                    aria-label="Product section heading"
                    @update:value="blockProps.heading = $event"
                />
            </div>
            <a
                v-if="blockProps.viewAllLabel"
                :href="blockProps.viewAllUrl || '#'"
                >{{ blockProps.viewAllLabel }}</a
            >
        </header>
        <div v-if="controlsVisible" class="catalog-controls">
            <label>
                Category
                <select v-model="selectedCategory">
                    <option value="all">All categories</option>
                    <option v-for="category in categories" :key="category">
                        {{ category }}
                    </option>
                </select>
            </label>
            <label>
                Sort
                <select v-model="selectedSort">
                    <option value="featured">Featured</option>
                    <option value="price-low">Price: low to high</option>
                    <option value="price-high">Price: high to low</option>
                    <option value="title">Alphabetical</option>
                </select>
            </label>
            <span>{{ filteredProducts.length }} products</span>
        </div>
        <div class="product-list" :data-layout="layoutType" :style="gridStyle">
            <article
                v-for="product in visibleProducts"
                :key="productKey(product)"
                class="product-card"
                :data-preset="blockProps.cardPreset || 'minimal'"
                :data-surface="blockProps.cardSurface || 'transparent'"
                :data-border="blockProps.cardBorder || 'none'"
                :data-shadow="blockProps.cardShadow || 'none'"
                :data-corners="blockProps.cardCorners || 'none'"
                :data-hover="blockProps.hoverEffect || 'none'"
                :style="cardStyle"
            >
                <template v-for="field in cardContent" :key="field.key">
                    <a
                        v-if="field.key === 'image'"
                        class="media"
                        :href="product.url || '#'"
                    >
                        <span
                            v-if="
                                blockProps.showBadge !== 'no' && product.badge
                            "
                            class="badge"
                            >{{ product.badge }}</span
                        >
                        <img
                            v-if="imageFor(product)"
                            class="primary-image"
                            :src="imageFor(product)"
                            :alt="imageAltFor(product)"
                        />
                        <img
                            v-if="secondaryImageFor(product)"
                            class="secondary-image"
                            :src="secondaryImageFor(product)"
                            :alt="imageAltFor(product)"
                        />
                        <span
                            v-else-if="!imageFor(product)"
                            class="media-placeholder"
                            aria-hidden="true"
                            >Product image</span
                        >
                    </a>
                    <p v-else-if="field.key === 'category'" class="category">
                        {{ product.category }}
                    </p>
                    <h3 v-else-if="field.key === 'name'">
                        <a :href="product.url || '#'">{{ product.title }}</a>
                    </h3>
                    <div v-else-if="field.key === 'price'" class="price">
                        <span>{{ priceFor(product) }}</span>
                        <del
                            v-if="
                                blockProps.showComparePrice !== 'no' &&
                                comparePriceFor(product)
                            "
                            >{{ comparePriceFor(product) }}</del
                        >
                    </div>
                    <p
                        v-else-if="field.key === 'description'"
                        class="description"
                    >
                        {{ product.description }}
                    </p>
                    <span
                        v-else-if="
                            field.key === 'availability' &&
                            product.available === false
                        "
                        class="sold-out"
                        >Unavailable</span
                    >
                    <div v-else-if="field.key === 'button'" class="card-action">
                        <button
                            type="button"
                            :data-style="blockProps.buttonStyle || 'primary'"
                            :disabled="
                                !canAddProduct(product) ||
                                isEditable ||
                                commerceCart?.isBusy.value
                            "
                            @click="addToCart(product)"
                        >
                            {{
                                isEditable
                                    ? `${blockProps.buttonLabel || 'Add to cart'} · preview`
                                    : product.available === false
                                      ? 'Unavailable'
                                      : blockProps.buttonLabel || 'Add to cart'
                            }}
                        </button>
                        <span
                            v-if="addStatuses[productKey(product)]"
                            class="add-status"
                            role="status"
                            >{{ addStatuses[productKey(product)] }}</span
                        >
                    </div>
                </template>
            </article>
        </div>
        <p v-if="!visibleProducts.length" class="empty-results">
            No products match this selection.
        </p>
        <nav
            v-if="paginationEnabled && pageCount > 1"
            class="pagination"
            aria-label="Product pages"
        >
            <button
                type="button"
                :disabled="currentPage === 1"
                @click="currentPage--"
            >
                Previous
            </button>
            <span>Page {{ currentPage }} of {{ pageCount }}</span>
            <button
                type="button"
                :disabled="currentPage === pageCount"
                @click="currentPage++"
            >
                Next
            </button>
        </nav>
    </section>
</template>

<style scoped>
.product-section {
    font-family: var(--theme-font-body);
}
.connection-note {
    margin-bottom: 1rem;
    padding: 0.75rem 1rem;
    border: 1px solid color-mix(in srgb, var(--theme-text) 18%, transparent);
    font-size: 0.8rem;
}
header {
    display: flex;
    align-items: end;
    justify-content: space-between;
    margin-bottom: 2rem;
}
header p,
.category {
    font-size: 0.72rem;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}
h2 {
    margin: 0.4rem 0;
    font: 600 clamp(2rem, 4vw, 3.4rem) / 1.1 var(--theme-font-heading);
}
header a,
h3 a {
    color: var(--theme-text);
}
header a {
    font-size: 0.85rem;
}
.catalog-controls {
    display: flex;
    align-items: end;
    margin-bottom: 1.5rem;
    gap: 1rem;
}
.catalog-controls label {
    display: grid;
    gap: 0.35rem;
    font-size: 0.7rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}
.catalog-controls select {
    min-width: 11rem;
    padding: 0.65rem 0.8rem;
    border: 1px solid color-mix(in srgb, var(--theme-text) 18%, transparent);
    border-radius: var(--theme-border-radius);
    color: inherit;
    background: var(--theme-bg);
}
.catalog-controls > span {
    margin-left: auto;
    font-size: 0.8rem;
    opacity: 0.6;
}
.product-list {
    display: grid;
    grid-template-columns: repeat(var(--desktop-columns), minmax(0, 1fr));
    gap: var(--product-gap);
}
.product-list[data-layout='carousel'] {
    grid-auto-columns: calc(
        (100% - (var(--desktop-columns) - 1) * var(--product-gap)) /
            var(--desktop-columns)
    );
    grid-template-columns: none;
    grid-auto-flow: column;
    overflow-x: auto;
    overscroll-behavior-inline: contain;
    scroll-snap-type: inline mandatory;
}
.product-list[data-layout='carousel'] .product-card {
    scroll-snap-align: start;
}
.product-list[data-layout='list'] {
    grid-template-columns: 1fr;
}
.product-list[data-layout='editorial'] {
    grid-template-columns: repeat(12, minmax(0, 1fr));
}
.product-list[data-layout='editorial'] .product-card {
    grid-column: span 4;
}
.product-list[data-layout='editorial'] .product-card:first-child {
    grid-column: span 8;
}
.product-card {
    display: flex;
    min-width: 0;
    flex-direction: column;
    gap: var(--card-spacing);
    padding: var(--card-padding);
    color: var(--theme-text);
    text-align: left;
    transition:
        transform 0.25s ease,
        opacity 0.25s ease,
        box-shadow 0.25s ease;
}
.product-section[data-align='center'] .product-card,
.product-section[data-align='center'] header {
    text-align: center;
}
.product-section[data-align='center'] header {
    align-items: center;
    flex-direction: column;
}
.product-section[data-align='center'] .price {
    justify-content: center;
}
.product-card[data-surface='surface'] {
    background: color-mix(in srgb, var(--theme-text) 5%, var(--theme-bg));
}
.product-card[data-border='subtle'] {
    border: 1px solid color-mix(in srgb, var(--theme-text) 14%, transparent);
}
.product-card[data-border='divider'] {
    border-bottom: 1px solid
        color-mix(in srgb, var(--theme-text) 22%, transparent);
}
.product-card[data-shadow='soft'] {
    box-shadow: 0 12px 30px
        color-mix(in srgb, var(--theme-text) 10%, transparent);
}
.product-card[data-shadow='strong'] {
    box-shadow: 0 18px 45px
        color-mix(in srgb, var(--theme-text) 18%, transparent);
}
.product-card[data-corners='small'] {
    border-radius: 0.35rem;
}
.product-card[data-corners='theme'] {
    border-radius: var(--theme-border-radius);
}
.product-card[data-corners='large'] {
    border-radius: 1.25rem;
}
.product-card[data-hover='lift']:hover {
    transform: translateY(-5px);
}
.product-card[data-hover='fade']:hover {
    opacity: 0.82;
}
.product-card[data-hover='button-reveal'] .card-action {
    opacity: 0;
    transform: translateY(4px);
    transition: 0.2s ease;
}
.product-card[data-hover='button-reveal']:hover .card-action,
.product-card[data-hover='button-reveal']:focus-within .card-action {
    opacity: 1;
    transform: translateY(0);
}
.product-list[data-layout='list'] .product-card {
    display: grid;
    grid-template-columns: minmax(110px, 200px) 1fr;
    align-items: center;
}
.product-list[data-layout='list'] .product-card > :not(.media) {
    grid-column: 2;
}
.product-list[data-layout='list'] .media {
    grid-column: 1;
    grid-row: 1 / span 10;
}
.media {
    position: relative;
    display: grid;
    aspect-ratio: var(--desktop-image-ratio);
    place-items: center;
    overflow: hidden;
    color: var(--theme-text);
    background:
        radial-gradient(
            120% 90% at 20% 0%,
            color-mix(in srgb, var(--theme-primary) 10%, transparent) 0%,
            transparent 60%
        ),
        radial-gradient(
            110% 100% at 85% 100%,
            color-mix(in srgb, var(--theme-secondary) 14%, transparent) 0%,
            transparent 55%
        ),
        color-mix(in srgb, var(--theme-text) 6%, transparent);
}
.media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition:
        transform 0.35s,
        opacity 0.35s;
}
.product-card[data-hover='zoom']:hover .primary-image {
    transform: scale(1.04);
}
.secondary-image {
    position: absolute;
    inset: 0;
    opacity: 0;
}
.product-card[data-hover='image-swap']:hover .secondary-image {
    opacity: 1;
}
.product-card[data-hover='image-swap']:hover .primary-image {
    opacity: 0;
}
.media-placeholder {
    font-size: 0.72rem;
    letter-spacing: 0.12em;
    opacity: 0.35;
    text-transform: uppercase;
}
.badge {
    position: absolute;
    z-index: 2;
    top: 0.75rem;
    left: 0.75rem;
    padding: 0.3rem 0.55rem;
    background: var(--theme-bg);
    font-size: 0.68rem;
    text-transform: uppercase;
}
h3 {
    margin: 0;
    font: 500 1rem var(--theme-font-heading);
}
h3 a {
    text-decoration: none;
}
.price {
    display: flex;
    gap: 0.5rem;
    font-size: 0.85rem;
}
.price del {
    opacity: 0.5;
}
.category,
.description,
.sold-out,
.add-status {
    margin: 0;
}
.category,
.sold-out,
.add-status {
    font-size: 0.72rem;
}
.category,
.description,
.sold-out {
    opacity: 0.65;
}
.description {
    display: -webkit-box;
    overflow: hidden;
    font-size: 0.85rem;
    line-height: 1.55;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
}
.card-action {
    display: grid;
    gap: 0.35rem;
}
.card-action button {
    width: 100%;
    padding: 0.7rem 0.9rem;
    border: 1px solid var(--theme-primary);
    border-radius: var(--theme-border-radius);
    background: var(--theme-primary);
    color: var(--theme-bg);
    cursor: pointer;
    font: 600 0.78rem var(--theme-font-body);
}
.card-action button[data-style='outline'] {
    color: var(--theme-primary);
    background: transparent;
}
.card-action button[data-style='text'] {
    padding-inline: 0;
    border-color: transparent;
    color: var(--theme-primary);
    background: transparent;
    text-align: inherit;
}
.card-action button:hover:not(:disabled) {
    filter: brightness(0.9);
}
.card-action button:disabled {
    cursor: not-allowed;
    filter: grayscale(1);
    opacity: 0.55;
}
.empty-results {
    padding: 3rem;
    text-align: center;
    opacity: 0.6;
}
.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 2rem;
    gap: 1rem;
    font-size: 0.8rem;
}
.pagination button {
    padding: 0.6rem 0.9rem;
    border: 1px solid color-mix(in srgb, var(--theme-text) 18%, transparent);
    color: inherit;
    background: transparent;
    cursor: pointer;
}
.pagination button:disabled {
    cursor: not-allowed;
    opacity: 0.4;
}
@container (max-width: 780px) {
    .media {
        aspect-ratio: var(--tablet-image-ratio);
    }
    .product-list {
        grid-template-columns: repeat(var(--tablet-columns), minmax(0, 1fr));
    }
    .product-list[data-layout='carousel'] {
        grid-auto-columns: calc(
            (100% - (var(--tablet-columns) - 1) * var(--product-gap)) /
                var(--tablet-columns)
        );
        grid-template-columns: none;
    }
    .product-list[data-layout='list'] {
        grid-template-columns: 1fr;
    }
    .product-list[data-layout='editorial'] .product-card,
    .product-list[data-layout='editorial'] .product-card:first-child {
        grid-column: span 6;
    }
}
@container (max-width: 420px) {
    .media {
        aspect-ratio: var(--mobile-image-ratio);
    }
    header {
        align-items: start;
        flex-direction: column;
        gap: 0.75rem;
    }
    .catalog-controls {
        align-items: stretch;
        flex-direction: column;
    }
    .catalog-controls > span {
        margin-left: 0;
    }
    .product-list {
        grid-template-columns: repeat(var(--mobile-columns), minmax(0, 1fr));
    }
    .product-list[data-layout='carousel'] {
        grid-auto-columns: calc(
            (100% - (var(--mobile-columns) - 1) * var(--product-gap)) /
                var(--mobile-columns)
        );
        grid-template-columns: none;
    }
    .product-list[data-layout='list'] {
        grid-template-columns: 1fr;
    }
    .product-list[data-layout='editorial'] .product-card,
    .product-list[data-layout='editorial'] .product-card:first-child {
        grid-column: 1 / -1;
    }
}
</style>
