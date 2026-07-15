<script setup lang="ts">
import { computed, inject, ref } from 'vue';
const props = defineProps<{
    nodeId?: string;
    blockProps: Record<string, any>;
}>();
const context = inject<any>('commerceContext', null);
const product = computed(() => context?.product || props.blockProps);
const selected = ref('');
</script>
<template>
    <section>
        <div class="gallery">
            <div
                v-for="image in product.images || []"
                :key="image.src"
                class="media"
            >
                <img
                    v-if="image.src"
                    :src="image.src"
                    :alt="image.alt || product.title"
                />
            </div>
        </div>
        <div class="details">
            <p class="vendor">{{ product.vendor }}</p>
            <h1>{{ product.title }}</h1>
            <p class="price">{{ product.priceLabel }}</p>
            <p class="description">{{ product.description }}</p>
            <label v-if="product.options?.length"
                >Option<select v-model="selected">
                    <option value="">Choose an option</option>
                    <option
                        v-for="option in product.options"
                        :key="option"
                        :value="option"
                    >
                        {{ option }}
                    </option>
                </select></label
            ><button type="button">
                {{ product.buttonLabel || 'Add to cart' }}
            </button>
            <div class="meta">{{ product.meta }}</div>
        </div>
    </section>
</template>
<style scoped>
section {
    display: grid;
    grid-template-columns: 1.25fr 0.75fr;
    gap: clamp(2rem, 6vw, 6rem);
    font-family: var(--theme-font-body);
}
.gallery {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}
.media {
    aspect-ratio: 4/5;
    background: color-mix(in srgb, var(--theme-text) 8%, transparent);
}
img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.details {
    position: sticky;
    top: 2rem;
    height: max-content;
    padding: 2rem 0;
}
.vendor {
    text-transform: uppercase;
    letter-spacing: 0.16em;
    font-size: 0.72rem;
}
h1 {
    margin: 0.6rem 0;
    font: 600 clamp(2.5rem, 5vw, 4.5rem)/1 var(--theme-font-heading);
}
.price {
    font-size: 1.15rem;
}
.description,
.meta {
    margin-top: 1.5rem;
    line-height: 1.7;
    opacity: 0.7;
}
label {
    display: grid;
    gap: 0.5rem;
    margin-top: 1.5rem;
    font-size: 0.8rem;
}
select,
button {
    min-height: 3rem;
    border: 1px solid color-mix(in srgb, var(--theme-text) 25%, transparent);
    padding: 0.8rem;
    background: transparent;
    color: var(--theme-text);
}
button {
    width: 100%;
    margin-top: 1rem;
    background: var(--theme-primary);
    color: white;
    border: 0;
    font-weight: 600;
}
@container (max-width:760px) {
    section {
        grid-template-columns: 1fr;
    }
    .details {
        position: static;
    }
    .gallery {
        gap: 0.4rem;
    }
}
</style>
