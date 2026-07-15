<script setup lang="ts">
import { ShoppingBag } from '@lucide/vue';
import { inject } from 'vue';
import { useCommerceCart } from '@/lib/commerceCart';

defineProps<{
    nodeId?: string;
    blockProps: Record<string, any>;
}>();

const isEditable = inject('isEditable', false);
const commerceCart = useCommerceCart();
</script>

<template>
    <section>
        <header>
            <p>{{ blockProps.eyebrow }}</p>
            <h1>{{ blockProps.heading }}</h1>
            <span>{{ blockProps.body }}</span>
        </header>

        <div v-if="isEditable" class="editor-note">
            Cart contents are supplied by the active commerce preview on the
            published storefront.
        </div>

        <div
            v-else-if="!commerceCart?.cart.value?.lines.length"
            class="empty-cart"
        >
            <ShoppingBag :size="32" />
            <h2>{{ blockProps.emptyHeading || 'Your bag is empty' }}</h2>
            <p>
                {{
                    blockProps.emptyBody ||
                    'Explore the current collection and find something considered.'
                }}
            </p>
            <a :href="blockProps.continueUrl || '/shop'">Continue shopping</a>
        </div>

        <div v-else class="cart-layout">
            <div class="cart-lines">
                <article
                    v-for="line in commerceCart.cart.value.lines"
                    :key="line.id"
                >
                    <div class="line-image">
                        <img
                            v-if="line.image?.src"
                            :src="line.image.src"
                            :alt="line.image.alt"
                        />
                    </div>
                    <div>
                        <h2>{{ line.title }}</h2>
                        <p>{{ line.variantTitle }}</p>
                        <div class="quantity">
                            <label :for="`quantity-${line.id}`">Quantity</label>
                            <input
                                :id="`quantity-${line.id}`"
                                :value="line.quantity"
                                type="number"
                                min="1"
                                max="99"
                                :disabled="commerceCart.isBusy.value"
                                @change="
                                    commerceCart.update(
                                        line.variantId,
                                        Number(
                                            ($event.target as HTMLInputElement)
                                                .value,
                                        ),
                                    )
                                "
                            />
                            <button
                                type="button"
                                :disabled="commerceCart.isBusy.value"
                                @click="commerceCart.remove(line.variantId)"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                    <strong>{{ line.lineTotal.formatted }}</strong>
                </article>
            </div>

            <aside>
                <h2>Order summary</h2>
                <div>
                    <span>Subtotal</span>
                    <strong>{{
                        commerceCart.cart.value.subtotal.formatted
                    }}</strong>
                </div>
                <p>Shipping and taxes are confirmed by the connected store.</p>
                <div
                    v-if="commerceCart.error.value"
                    class="cart-error"
                    role="alert"
                >
                    {{ commerceCart.error.value }}
                </div>
                <button
                    type="button"
                    class="checkout"
                    :disabled="
                        commerceCart.isBusy.value ||
                        !commerceCart.cart.value.checkoutAvailable
                    "
                    @click="commerceCart.checkout"
                >
                    {{
                        commerceCart.isBusy.value
                            ? 'Working…'
                            : blockProps.checkoutLabel ||
                              'Continue to fixture checkout'
                    }}
                </button>
            </aside>
        </div>
    </section>
</template>

<style scoped>
section {
    width: min(100%, 1180px);
    margin: 0 auto;
    font-family: var(--theme-font-body);
}
header {
    max-width: 48rem;
    margin-bottom: 3rem;
}
header p {
    font-size: 0.72rem;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}
h1 {
    margin: 0.5rem 0 1rem;
    font: 600 clamp(2.6rem, 6vw, 5rem) / 1 var(--theme-font-heading);
}
header span {
    opacity: 0.68;
}
.editor-note,
.empty-cart {
    padding: 3rem;
    border: 1px solid color-mix(in srgb, var(--theme-text) 15%, transparent);
    text-align: center;
}
.empty-cart {
    display: grid;
    min-height: 22rem;
    place-content: center;
    justify-items: center;
}
.empty-cart h2 {
    margin: 1rem 0 0.4rem;
    font: 600 1.8rem var(--theme-font-heading);
}
.empty-cart p {
    max-width: 30rem;
    opacity: 0.65;
}
.empty-cart a {
    margin-top: 1rem;
    color: inherit;
}
.cart-layout {
    display: grid;
    grid-template-columns: 1fr minmax(280px, 0.38fr);
    gap: clamp(2rem, 5vw, 5rem);
}
.cart-lines article {
    display: grid;
    grid-template-columns: 110px 1fr auto;
    padding: 1.25rem 0;
    gap: 1.25rem;
    border-top: 1px solid color-mix(in srgb, var(--theme-text) 15%, transparent);
}
.line-image {
    aspect-ratio: 4/5;
    background: color-mix(in srgb, var(--theme-text) 8%, transparent);
}
.line-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cart-lines h2,
aside h2 {
    margin: 0;
    font: 600 1.2rem var(--theme-font-heading);
}
.cart-lines p,
aside p {
    font-size: 0.8rem;
    opacity: 0.62;
}
.quantity {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.quantity label {
    font-size: 0.75rem;
}
.quantity input {
    width: 4rem;
    padding: 0.4rem;
    border: 1px solid color-mix(in srgb, var(--theme-text) 20%, transparent);
    color: inherit;
    background: transparent;
}
.quantity button {
    border: 0;
    color: inherit;
    background: transparent;
    text-decoration: underline;
    cursor: pointer;
}
aside {
    height: max-content;
    padding: 1.5rem;
    border: 1px solid color-mix(in srgb, var(--theme-text) 15%, transparent);
}
aside > div:not(.cart-error) {
    display: flex;
    justify-content: space-between;
    margin-top: 1.5rem;
}
.cart-error {
    margin-top: 1rem;
    color: #b91c1c;
    font-size: 0.8rem;
}
.checkout {
    width: 100%;
    margin-top: 1.25rem;
    padding: 1rem;
    border: 0;
    border-radius: var(--theme-border-radius);
    color: var(--theme-bg);
    background: var(--theme-primary);
    cursor: pointer;
}
.checkout:disabled {
    cursor: not-allowed;
    opacity: 0.5;
}
@container (max-width: 720px) {
    .cart-layout {
        grid-template-columns: 1fr;
    }
    .cart-lines article {
        grid-template-columns: 80px 1fr;
    }
    .cart-lines article > strong {
        grid-column: 2;
    }
}
</style>
