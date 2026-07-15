<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CheckCircle2, LockKeyhole } from '@lucide/vue';
import { useTheme } from '@/lib/theme';
import type { CommerceCart } from '@/types/commerce';

const props = defineProps<{
    tenant: Record<string, any>;
    cart: CommerceCart;
}>();

const { cssVars: themeVars, fontUrl } = useTheme(
    () => props.tenant?.theme_config,
);
</script>

<template>
    <Head title="Fixture checkout">
        <link v-if="fontUrl" rel="stylesheet" :href="fontUrl" />
    </Head>
    <main :style="themeVars">
        <section>
            <div class="checkout-message">
                <LockKeyhole :size="28" />
                <p>Integration handoff preview</p>
                <h1>Hosted checkout starts here.</h1>
                <span>
                    This fixture confirms the storefront-to-provider handoff. It
                    does not collect payment or place an order.
                </span>
                <a href="/cart">Return to bag</a>
            </div>
            <aside>
                <h2>Order summary</h2>
                <article v-for="line in cart.lines" :key="line.id">
                    <div>
                        <strong>{{ line.title }}</strong>
                        <span
                            >{{ line.variantTitle }} · {{ line.quantity }}</span
                        >
                    </div>
                    <span>{{ line.lineTotal.formatted }}</span>
                </article>
                <div class="total">
                    <span>Total</span>
                    <strong>{{ cart.total.formatted }}</strong>
                </div>
                <div class="provider-note">
                    <CheckCircle2 :size="17" />
                    Totals were returned by the fixture provider.
                </div>
            </aside>
        </section>
    </main>
</template>

<style scoped>
main {
    min-height: 100vh;
    padding: clamp(1.5rem, 6vw, 6rem);
    color: var(--theme-text);
    background: var(--theme-bg);
    font-family: var(--theme-font-body);
}
section {
    display: grid;
    width: min(100%, 1120px);
    margin: 0 auto;
    grid-template-columns: 1fr minmax(320px, 0.7fr);
    gap: clamp(3rem, 8vw, 8rem);
}
.checkout-message {
    padding-top: 2rem;
}
.checkout-message p {
    font-size: 0.72rem;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}
h1 {
    max-width: 13ch;
    margin: 1rem 0;
    font: 600 clamp(3rem, 7vw, 6rem) / 0.95 var(--theme-font-heading);
}
.checkout-message span {
    display: block;
    max-width: 36rem;
    line-height: 1.7;
    opacity: 0.7;
}
.checkout-message a {
    display: inline-block;
    margin-top: 2rem;
    color: inherit;
}
aside {
    padding: 2rem;
    border: 1px solid color-mix(in srgb, var(--theme-text) 15%, transparent);
}
aside h2 {
    margin-top: 0;
    font: 600 1.5rem var(--theme-font-heading);
}
article,
.total {
    display: flex;
    justify-content: space-between;
    padding: 1rem 0;
    gap: 1rem;
    border-top: 1px solid color-mix(in srgb, var(--theme-text) 12%, transparent);
}
article div {
    display: grid;
    gap: 0.25rem;
}
article span {
    font-size: 0.8rem;
    opacity: 0.65;
}
.total {
    margin-top: 1rem;
    font-size: 1.1rem;
}
.provider-note {
    display: flex;
    align-items: center;
    margin-top: 1.5rem;
    gap: 0.5rem;
    font-size: 0.75rem;
    opacity: 0.68;
}
@media (max-width: 760px) {
    section {
        grid-template-columns: 1fr;
    }
}
</style>
