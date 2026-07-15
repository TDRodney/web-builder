<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useCommerceBlock } from '@/lib/commerce';
import type { CommerceProduct } from '@/types/commerce';

type DisplayProduct = CommerceProduct & Record<string, any>;

const props = defineProps<{
    nodeId?: string;
    blockProps: Record<string, any>;
}>();

const hydratedBlock = useCommerceBlock<{ products: CommerceProduct[] }>(
    props.nodeId,
);
const products = computed<DisplayProduct[]>(() => {
    if (hydratedBlock.value?.status === 'ready') {
        return (hydratedBlock.value.data?.products || []) as DisplayProduct[];
    }

    return (props.blockProps.products || []) as DisplayProduct[];
});
const selectedCategory = ref('all');
const selectedSort = ref('featured');
const currentPage = ref(1);
const categories = computed(() => [
    ...new Set(
        products.value
            .map((product: DisplayProduct) => String(product.category || ''))
            .filter(Boolean),
    ),
]);
const filteredProducts = computed(() => {
    const filtered =
        selectedCategory.value === 'all'
            ? [...products.value]
            : products.value.filter(
                  (product: DisplayProduct) =>
                      product.category === selectedCategory.value,
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
const pageSize = computed(() => Math.max(1, props.blockProps.pageSize || 4));
const controlsVisible = computed(
    () =>
        props.blockProps.showControls === true ||
        props.blockProps.showControls === 'yes',
);
const pageCount = computed(() =>
    Math.max(1, Math.ceil(filteredProducts.value.length / pageSize.value)),
);
const visibleProducts = computed(() => {
    if (!controlsVisible.value) {
        return filteredProducts.value;
    }

    const start = (currentPage.value - 1) * pageSize.value;

    return filteredProducts.value.slice(start, start + pageSize.value);
});

watch([selectedCategory, selectedSort], () => {
    currentPage.value = 1;
});

const imageFor = (product: DisplayProduct): string =>
    product.images?.[0]?.src || product.imageSrc || '';
const imageAltFor = (product: DisplayProduct): string =>
    product.images?.[0]?.alt || product.imageAlt || product.title;
const priceFor = (product: DisplayProduct): string =>
    product.price?.formatted || product.priceLabel || '';
const comparePriceFor = (product: DisplayProduct): string =>
    product.compareAtPrice?.formatted || product.compareAtLabel || '';
</script>

<template>
    <section>
        <div
            v-if="hydratedBlock?.status === 'unavailable'"
            class="connection-note"
            role="status"
        >
            {{ hydratedBlock.message }} Showing editable fallback products.
        </div>
        <header>
            <div>
                <p>{{ blockProps.eyebrow }}</p>
                <h2>{{ blockProps.heading }}</h2>
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
                    <option value="title">Title</option>
                </select>
            </label>
            <span>{{ filteredProducts.length }} products</span>
        </div>
        <div class="grid" :style="{ '--columns': blockProps.columns || 4 }">
            <a
                v-for="product in visibleProducts"
                :key="product.id || product.key || product.title"
                :href="product.url || '#'"
                ><div class="media">
                    <span v-if="product.badge" class="badge">{{
                        product.badge
                    }}</span
                    ><img
                        v-if="imageFor(product)"
                        :src="imageFor(product)"
                        :alt="imageAltFor(product)"
                    />
                    <span v-else class="media-placeholder" aria-hidden="true"
                        >Product image</span
                    >
                </div>
                <h3>{{ product.title }}</h3>
                <div class="price">
                    <span>{{ priceFor(product) }}</span
                    ><del v-if="comparePriceFor(product)">{{
                        comparePriceFor(product)
                    }}</del>
                </div>
                <span v-if="product.available === false" class="sold-out"
                    >Unavailable</span
                ></a
            >
        </div>
        <p v-if="!visibleProducts.length" class="empty-results">
            No products match this selection.
        </p>
        <nav
            v-if="controlsVisible && pageCount > 1"
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
section {
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
header p {
    font-size: 0.72rem;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}
h2 {
    margin: 0.4rem 0;
    font: 600 clamp(2rem, 4vw, 3.4rem) / 1.1 var(--theme-font-heading);
}
header a {
    color: var(--theme-text);
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
.grid {
    display: grid;
    grid-template-columns: repeat(var(--columns), minmax(0, 1fr));
    gap: 1.2rem;
}
.grid > a {
    color: var(--theme-text);
    text-decoration: none;
}
.media {
    position: relative;
    display: grid;
    aspect-ratio: 4/5;
    place-items: center;
    overflow: hidden;
    background: color-mix(in srgb, var(--theme-text) 8%, transparent);
}
.media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.35s;
}
.media:hover img {
    transform: scale(1.025);
}
.media-placeholder {
    font-size: 0.72rem;
    letter-spacing: 0.12em;
    opacity: 0.35;
    text-transform: uppercase;
}
.badge {
    position: absolute;
    z-index: 1;
    top: 0.75rem;
    left: 0.75rem;
    padding: 0.3rem 0.55rem;
    background: var(--theme-bg);
    font-size: 0.68rem;
    text-transform: uppercase;
}
h3 {
    margin: 0.9rem 0 0.25rem;
    font: 500 1rem var(--theme-font-heading);
}
.price {
    display: flex;
    gap: 0.5rem;
    font-size: 0.85rem;
}
.price del {
    opacity: 0.5;
}
.sold-out {
    font-size: 0.72rem;
    opacity: 0.6;
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
    .grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@container (max-width: 420px) {
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
}
</style>
