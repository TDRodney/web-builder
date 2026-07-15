<script setup lang="ts">
import { computed, inject } from 'vue';
const props = defineProps<{
    nodeId?: string;
    blockProps: Record<string, any>;
}>();
const commerceContext = inject<any>('commerceContext', null);
const products = computed(() =>
    commerceContext?.products?.length
        ? commerceContext.products
        : props.blockProps.products || [],
);
</script>
<template>
    <section>
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
                :key="product.key || product.title"
                :href="product.url || '#'"
                ><div class="media">
                    <span v-if="product.badge" class="badge">{{
                        product.badge
                    }}</span
                    ><img
                        v-if="product.imageSrc"
                        :src="product.imageSrc"
                        :alt="product.imageAlt || product.title"
                    />
                </div>
                <h3>{{ product.title }}</h3>
                <div class="price">
                    <span>{{ product.priceLabel }}</span
                    ><del v-if="product.compareAtLabel">{{
                        product.compareAtLabel
                    }}</del>
                </div></a
            >
        </div>
    </section>
</template>
<style scoped>
section {
    font-family: var(--theme-font-body);
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
    font: 600 clamp(2rem, 4vw, 3.4rem)/1.1 var(--theme-font-heading);
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
    aspect-ratio: 4/5;
    overflow: hidden;
    background: color-mix(in srgb, var(--theme-text) 8%, transparent);
}
img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.35s;
}
.media:hover img {
    transform: scale(1.025);
}
.badge {
    position: absolute;
    z-index: 1;
    top: 0.7rem;
    left: 0.7rem;
    padding: 0.35rem 0.55rem;
    background: var(--theme-bg);
    font-size: 0.68rem;
}
h3 {
    margin: 0.8rem 0 0.35rem;
    font: 500 1rem var(--theme-font-heading);
}
.price {
    display: flex;
    gap: 0.6rem;
    font-size: 0.86rem;
}
.price del {
    opacity: 0.45;
}
@container (max-width:780px) {
    .grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@container (max-width:420px) {
    header {
        align-items: start;
        gap: 1rem;
    }
    .grid {
        gap: 0.7rem;
    }
}
</style>
