<script setup lang="ts">
import { computed } from 'vue';
import { useCommerceBlock } from '@/lib/commerce';
import type { CommerceProduct } from '@/types/commerce';

const props = defineProps<{
    nodeId?: string;
    blockProps: Record<string, any>;
}>();

const hydratedBlock = useCommerceBlock<{ products: CommerceProduct[] }>(
    props.nodeId,
);
const products = computed(() => {
    if (hydratedBlock.value?.status === 'ready') {
        return hydratedBlock.value.data?.products || [];
    }

    return props.blockProps.products || [];
});

const imageFor = (product: CommerceProduct & Record<string, any>): string =>
    product.images?.[0]?.src || product.imageSrc || '';
const imageAltFor = (product: CommerceProduct & Record<string, any>): string =>
    product.images?.[0]?.alt || product.imageAlt || product.title;
const priceFor = (product: CommerceProduct & Record<string, any>): string =>
    product.price?.formatted || product.priceLabel || '';
const comparePriceFor = (
    product: CommerceProduct & Record<string, any>,
): string => product.compareAtPrice?.formatted || product.compareAtLabel || '';
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
        <div class="grid" :style="{ '--columns': blockProps.columns || 4 }">
            <a
                v-for="product in products"
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
}
</style>
