<script setup lang="ts">
import { computed, inject, ref, watch } from 'vue';
import { useCommerceBlock } from '@/lib/commerce';
import { useCommerceCart } from '@/lib/commerceCart';
import type { CommerceProduct, CommerceVariant } from '@/types/commerce';

const props = defineProps<{
    nodeId?: string;
    blockProps: Record<string, any>;
}>();
const hydratedBlock = useCommerceBlock<CommerceProduct>(props.nodeId);
const commerceCart = useCommerceCart();
const isEditable = inject('isEditable', false);
const addStatus = ref('');
const product = computed<Record<string, any>>(() => {
    if (hydratedBlock.value?.status === 'ready' && hydratedBlock.value.data) {
        return hydratedBlock.value.data;
    }

    return props.blockProps;
});
const variants = computed<CommerceVariant[]>(
    () => product.value.variants || [],
);
const selectedVariantId = ref('');
const selectedVariant = computed(() =>
    variants.value.find((variant) => variant.id === selectedVariantId.value),
);
const optionLabels = computed(() =>
    variants.value.length
        ? variants.value.map((variant) => ({
              id: variant.id,
              label: variant.title,
              available: variant.available,
          }))
        : (product.value.options || []).map((option: string) => ({
              id: option,
              label: option,
              available: true,
          })),
);
const displayedPrice = computed(
    () =>
        selectedVariant.value?.price?.formatted ||
        product.value.price?.formatted ||
        product.value.priceLabel ||
        '',
);
const canPurchase = computed(
    () =>
        hydratedBlock.value?.status === 'ready' &&
        product.value.available !== false &&
        (variants.value.length === 0 ||
            selectedVariant.value?.available === true),
);

const addToCart = async (): Promise<void> => {
    if (isEditable || !commerceCart || !selectedVariant.value) {
        return;
    }

    addStatus.value = '';
    const added = await commerceCart.add(selectedVariant.value.id, 1);
    addStatus.value = added ? 'Added to your bag.' : commerceCart.error.value;
};

watch(
    variants,
    (availableVariants) => {
        selectedVariantId.value =
            availableVariants.find((variant) => variant.available)?.id || '';
    },
    { immediate: true },
);
</script>

<template>
    <section>
        <div class="gallery">
            <div
                v-for="(image, index) in product.images || []"
                :key="image.src || index"
                class="media"
            >
                <img
                    v-if="image.src"
                    :src="image.src"
                    :alt="image.alt || product.title"
                />
                <span v-else aria-hidden="true">Product image</span>
            </div>
        </div>
        <div class="details">
            <div
                v-if="hydratedBlock?.status === 'unavailable'"
                class="connection-note"
                role="status"
            >
                {{ hydratedBlock.message }} Purchasing is disabled.
            </div>
            <p class="vendor">{{ product.vendor }}</p>
            <h1>{{ product.title }}</h1>
            <p class="price">{{ displayedPrice }}</p>
            <p class="description">{{ product.description }}</p>
            <label v-if="optionLabels.length"
                >Option<select v-model="selectedVariantId">
                    <option
                        v-for="option in optionLabels"
                        :key="option.id"
                        :value="option.id"
                        :disabled="!option.available"
                    >
                        {{ option.label
                        }}{{ option.available ? '' : ' — Unavailable' }}
                    </option>
                </select></label
            ><button
                type="button"
                :disabled="
                    !canPurchase || isEditable || commerceCart?.isBusy.value
                "
                @click="addToCart"
            >
                {{
                    isEditable
                        ? 'Add to cart · preview only'
                        : product.available === false
                          ? 'Unavailable'
                          : product.buttonLabel ||
                            blockProps.buttonLabel ||
                            'Add to cart'
                }}
            </button>
            <p v-if="addStatus" class="add-status" role="status">
                {{ addStatus }}
            </p>
            <div class="meta">{{ product.meta || blockProps.meta }}</div>
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
    display: grid;
    aspect-ratio: 4/5;
    place-items: center;
    background: color-mix(in srgb, var(--theme-text) 8%, transparent);
}
.media span {
    font-size: 0.72rem;
    letter-spacing: 0.12em;
    opacity: 0.35;
    text-transform: uppercase;
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
.connection-note {
    margin-bottom: 1.25rem;
    padding: 0.75rem 1rem;
    border: 1px solid color-mix(in srgb, var(--theme-text) 18%, transparent);
    font-size: 0.8rem;
}
.vendor {
    font-size: 0.72rem;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}
h1 {
    margin: 0.6rem 0;
    font: 600 clamp(2.5rem, 5vw, 4.5rem) / 1 var(--theme-font-heading);
}
.price {
    font-size: 1.15rem;
}
.description,
.meta,
.add-status {
    margin-top: 1.5rem;
    line-height: 1.7;
    opacity: 0.7;
}
.add-status {
    font-size: 0.8rem;
}
label {
    display: grid;
    gap: 0.5rem;
    margin-top: 1.5rem;
    font-size: 0.8rem;
}
select,
button {
    width: 100%;
    padding: 0.9rem 1rem;
    border: 1px solid color-mix(in srgb, var(--theme-text) 25%, transparent);
    border-radius: var(--theme-border-radius);
    background: transparent;
    color: var(--theme-text);
    font: inherit;
}
button {
    margin-top: 1rem;
    border-color: var(--theme-primary);
    background: var(--theme-primary);
    color: var(--theme-bg);
    cursor: pointer;
}
button:hover:not(:disabled) {
    filter: brightness(0.9);
}
button:disabled {
    cursor: not-allowed;
    filter: grayscale(1);
    opacity: 0.55;
}
@container (max-width: 720px) {
    section {
        grid-template-columns: 1fr;
    }
    .details {
        position: static;
    }
}
</style>
