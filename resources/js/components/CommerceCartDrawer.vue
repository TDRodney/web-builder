<script setup lang="ts">
import { ShoppingBag, X } from '@lucide/vue';
import { useCommerceCart } from '@/lib/commerceCart';

defineProps<{ themeVars?: Record<string, string> }>();
const commerceCart = useCommerceCart();
</script>

<template>
    <Teleport to="body">
        <div
            v-if="commerceCart?.isOpen.value"
            class="cart-layer"
            :style="themeVars"
        >
            <button
                type="button"
                class="cart-backdrop"
                aria-label="Close cart"
                @click="commerceCart.close"
            ></button>
            <aside
                class="cart-drawer"
                role="dialog"
                aria-modal="true"
                aria-labelledby="cart-drawer-title"
            >
                <header>
                    <div>
                        <span>Shopping bag</span>
                        <h2 id="cart-drawer-title">
                            {{ commerceCart.itemCount.value }} items
                        </h2>
                    </div>
                    <button
                        type="button"
                        class="close-button"
                        aria-label="Close cart"
                        @click="commerceCart.close"
                    >
                        <X :size="20" />
                    </button>
                </header>

                <div
                    v-if="commerceCart.error.value"
                    class="cart-error"
                    role="alert"
                >
                    {{ commerceCart.error.value }}
                </div>

                <div
                    v-if="!commerceCart.cart.value?.lines.length"
                    class="empty-cart"
                >
                    <ShoppingBag :size="28" />
                    <p>Your bag is ready for something considered.</p>
                </div>

                <div v-else class="cart-lines">
                    <article
                        v-for="line in commerceCart.cart.value.lines"
                        :key="line.id"
                        class="cart-line"
                    >
                        <div class="line-image">
                            <img
                                v-if="line.image?.src"
                                :src="line.image.src"
                                :alt="line.image.alt"
                            />
                        </div>
                        <div>
                            <h3>{{ line.title }}</h3>
                            <p>{{ line.variantTitle }}</p>
                            <div class="quantity-row">
                                <button
                                    type="button"
                                    :disabled="
                                        line.quantity <= 1 ||
                                        commerceCart.isBusy.value
                                    "
                                    :aria-label="`Decrease ${line.title} quantity`"
                                    @click="
                                        commerceCart.update(
                                            line.variantId,
                                            line.quantity - 1,
                                        )
                                    "
                                >
                                    −
                                </button>
                                <span>{{ line.quantity }}</span>
                                <button
                                    type="button"
                                    :disabled="commerceCart.isBusy.value"
                                    :aria-label="`Increase ${line.title} quantity`"
                                    @click="
                                        commerceCart.update(
                                            line.variantId,
                                            line.quantity + 1,
                                        )
                                    "
                                >
                                    +
                                </button>
                                <button
                                    type="button"
                                    class="remove-line"
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

                <footer v-if="commerceCart.cart.value?.lines.length">
                    <div>
                        <span>Subtotal</span>
                        <strong>{{
                            commerceCart.cart.value.subtotal.formatted
                        }}</strong>
                    </div>
                    <p>Shipping and taxes are confirmed by the store.</p>
                    <button
                        type="button"
                        class="checkout-button"
                        :disabled="
                            commerceCart.isBusy.value ||
                            !commerceCart.cart.value.checkoutAvailable
                        "
                        @click="commerceCart.checkout"
                    >
                        {{
                            commerceCart.isBusy.value
                                ? 'Working…'
                                : 'Continue to fixture checkout'
                        }}
                    </button>
                    <a href="/cart" @click="commerceCart.close"
                        >View full bag</a
                    >
                </footer>
            </aside>
        </div>
    </Teleport>
</template>

<style scoped>
.cart-layer {
    position: fixed;
    z-index: 1000;
    inset: 0;
    font-family: var(--theme-font-body, sans-serif);
}
.cart-backdrop {
    position: absolute;
    width: 100%;
    height: 100%;
    padding: 0;
    border: 0;
    background: rgb(0 0 0 / 42%);
}
.cart-drawer {
    position: absolute;
    right: 0;
    display: flex;
    width: min(100%, 480px);
    height: 100%;
    flex-direction: column;
    padding: 1.5rem;
    color: var(--theme-text, #1c1917);
    background: var(--theme-bg, #fff);
    box-shadow: -20px 0 60px rgb(0 0 0 / 18%);
}
header,
footer > div,
.quantity-row {
    display: flex;
    align-items: center;
}
header {
    justify-content: space-between;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid
        color-mix(in srgb, var(--theme-text) 15%, transparent);
}
header span {
    font-size: 0.7rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}
h2 {
    margin: 0.25rem 0 0;
    font: 600 1.6rem var(--theme-font-heading, serif);
}
.close-button,
.quantity-row button {
    border: 1px solid color-mix(in srgb, var(--theme-text) 18%, transparent);
    color: inherit;
    background: transparent;
    cursor: pointer;
}
.close-button {
    display: grid;
    width: 2.5rem;
    height: 2.5rem;
    place-items: center;
    border-radius: 9999px;
}
.cart-error {
    margin-top: 1rem;
    padding: 0.75rem;
    border: 1px solid currentcolor;
    color: #b91c1c;
    font-size: 0.8rem;
}
.empty-cart {
    display: grid;
    flex: 1;
    place-content: center;
    justify-items: center;
    opacity: 0.65;
}
.cart-lines {
    flex: 1;
    overflow-y: auto;
}
.cart-line {
    display: grid;
    grid-template-columns: 72px 1fr auto;
    padding: 1.25rem 0;
    gap: 1rem;
    border-bottom: 1px solid
        color-mix(in srgb, var(--theme-text) 12%, transparent);
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
.cart-line h3 {
    margin: 0;
    font: 600 1rem var(--theme-font-heading, serif);
}
.cart-line p {
    margin: 0.25rem 0 0.75rem;
    font-size: 0.8rem;
    opacity: 0.6;
}
.quantity-row {
    gap: 0.5rem;
}
.quantity-row button {
    min-width: 1.8rem;
    height: 1.8rem;
}
.quantity-row .remove-line {
    width: auto;
    padding: 0 0.4rem;
    border: 0;
    text-decoration: underline;
}
footer {
    padding-top: 1.25rem;
    border-top: 1px solid color-mix(in srgb, var(--theme-text) 15%, transparent);
}
footer > div {
    justify-content: space-between;
    font-size: 1.05rem;
}
footer p,
footer a {
    font-size: 0.75rem;
    opacity: 0.65;
}
.checkout-button {
    width: 100%;
    margin: 1rem 0;
    padding: 1rem;
    border: 0;
    border-radius: var(--theme-border-radius, 0);
    color: var(--theme-bg, #fff);
    background: var(--theme-primary, #111827);
    cursor: pointer;
}
.checkout-button:disabled {
    cursor: not-allowed;
    opacity: 0.55;
}
footer a {
    display: block;
    color: inherit;
    text-align: center;
}
</style>
