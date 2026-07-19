<script setup lang="ts">
import type { PresetPreview } from '@/lib/blockPresets';

defineProps<{
    preview: PresetPreview;
}>();
</script>

<template>
    <div class="preset-thumb" aria-hidden="true">
        <!-- Centered statement -->
        <div v-if="preview === 'centered'" class="thumb-stage thumb-centered">
            <span class="bar bar-xs bar-muted" />
            <span class="bar bar-xl bar-heading" />
            <span class="bar bar-md bar-muted" />
            <span class="bar bar-sm bar-muted" />
            <div class="thumb-actions">
                <span class="chip chip-primary" />
                <span class="chip chip-outline" />
            </div>
        </div>

        <!-- Copy left, image right -->
        <div
            v-else-if="preview === 'split-right'"
            class="thumb-stage thumb-split"
        >
            <div class="thumb-copy">
                <span class="bar bar-xs bar-muted" />
                <span class="bar bar-lg bar-heading" />
                <span class="bar bar-sm bar-muted" />
                <span class="chip chip-primary" />
            </div>
            <div class="thumb-media" />
        </div>

        <!-- Image left, copy right -->
        <div
            v-else-if="preview === 'split-left'"
            class="thumb-stage thumb-split"
        >
            <div class="thumb-media" />
            <div class="thumb-copy">
                <span class="bar bar-xs bar-muted" />
                <span class="bar bar-lg bar-heading" />
                <span class="bar bar-sm bar-muted" />
                <span class="chip chip-primary" />
            </div>
        </div>

        <!-- Editorial -->
        <div
            v-else-if="preview === 'editorial'"
            class="thumb-stage thumb-editorial"
        >
            <span class="bar bar-xs bar-muted" />
            <span class="bar bar-xxl bar-heading" />
            <div class="thumb-editorial-lower">
                <div class="thumb-copy">
                    <span class="bar bar-sm bar-muted" />
                    <span class="chip chip-primary" />
                </div>
                <div class="thumb-media thumb-media-sm" />
            </div>
        </div>

        <!-- Minimal -->
        <div v-else-if="preview === 'minimal'" class="thumb-stage thumb-minimal">
            <span class="bar bar-xs bar-muted" />
            <span class="bar bar-lg bar-heading" />
            <span class="bar bar-sm bar-muted" />
            <span class="chip chip-primary" />
        </div>

        <!-- Statement with stats -->
        <div v-else-if="preview === 'stats'" class="thumb-stage thumb-split">
            <div class="thumb-copy">
                <span class="bar bar-xs bar-muted" />
                <span class="bar bar-lg bar-heading" />
                <span class="bar bar-sm bar-muted" />
                <span class="chip chip-primary" />
            </div>
            <div class="thumb-stats">
                <div v-for="n in 3" :key="n" class="thumb-stat">
                    <span class="bar bar-sm bar-accent" />
                    <span class="bar bar-xs bar-muted" />
                </div>
            </div>
        </div>

        <!-- Logo strip -->
        <div v-else-if="preview === 'logos'" class="thumb-stage thumb-logos">
            <span class="bar bar-xs bar-muted thumb-logos-title" />
            <div class="thumb-logos-row">
                <span v-for="n in 5" :key="n" class="thumb-logo" />
            </div>
        </div>

        <!-- Testimonials -->
        <div
            v-else-if="preview === 'testimonials'"
            class="thumb-stage thumb-testimonials"
        >
            <span class="bar bar-md bar-heading thumb-testimonials-title" />
            <div class="thumb-grid-3-row">
                <div v-for="n in 3" :key="n" class="thumb-quote-card">
                    <span class="thumb-quote-mark">“</span>
                    <span class="bar bar-sm bar-muted" />
                    <span class="bar bar-xs bar-heading" />
                </div>
            </div>
        </div>

        <!-- Menu photo cards -->
        <div
            v-else-if="preview === 'menu-cards'"
            class="thumb-stage thumb-menu-cards"
        >
            <span class="bar bar-md bar-heading thumb-menu-cards-title" />
            <div class="thumb-grid-3-row">
                <div v-for="n in 3" :key="n" class="thumb-menu-card">
                    <div class="thumb-media thumb-media-fill" />
                    <div class="thumb-menu-card-row">
                        <span class="bar bar-xs bar-heading" />
                        <span class="bar bar-xs bar-accent thumb-price" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Promo tiles -->
        <div v-else-if="preview === 'promos'" class="thumb-stage thumb-promos">
            <div class="thumb-promo thumb-promo-primary">
                <span class="bar bar-xs bar-light" />
                <span class="bar bar-md bar-light-strong" />
                <span class="chip chip-light" />
            </div>
            <div class="thumb-promo thumb-promo-secondary">
                <span class="bar bar-xs bar-light" />
                <span class="bar bar-md bar-light-strong" />
                <span class="chip chip-light" />
            </div>
        </div>

        <!-- Features grid -->
        <div v-else-if="preview === 'features'" class="thumb-stage thumb-grid-3">
            <div v-for="n in 3" :key="n" class="thumb-card">
                <span class="bar bar-sm bar-heading" />
                <span class="bar bar-xs bar-muted" />
                <span class="bar bar-xs bar-muted" />
            </div>
        </div>

        <!-- FAQ -->
        <div v-else-if="preview === 'faq'" class="thumb-stage thumb-faq">
            <span class="bar bar-md bar-heading" />
            <div v-for="n in 3" :key="n" class="thumb-faq-row">
                <span class="bar bar-sm bar-muted" />
                <span class="thumb-faq-mark" />
            </div>
        </div>

        <!-- Menu -->
        <div v-else-if="preview === 'menu'" class="thumb-stage thumb-menu">
            <span class="bar bar-md bar-heading thumb-menu-title" />
            <div class="thumb-menu-cols">
                <div v-for="col in 2" :key="col" class="thumb-menu-col">
                    <span class="bar bar-xs bar-accent" />
                    <div v-for="n in 3" :key="n" class="thumb-menu-row">
                        <span class="bar bar-xs bar-heading" />
                        <span class="thumb-menu-dots" />
                        <span class="bar bar-xs bar-accent thumb-menu-price" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Collections -->
        <div
            v-else-if="preview === 'collections'"
            class="thumb-stage thumb-grid-3"
        >
            <div v-for="n in 3" :key="n" class="thumb-collection">
                <div class="thumb-media thumb-media-fill" />
                <span class="bar bar-xs bar-heading" />
            </div>
        </div>

        <!-- Products -->
        <div v-else-if="preview === 'products'" class="thumb-stage thumb-grid-4">
            <div v-for="n in 4" :key="n" class="thumb-product">
                <div class="thumb-media thumb-media-fill" />
                <span class="bar bar-xs bar-heading" />
                <span class="bar bar-xs bar-accent thumb-price" />
            </div>
        </div>
    </div>
</template>

<style scoped>
.preset-thumb {
    --thumb-bg: color-mix(in srgb, var(--theme-bg, #fafafa) 88%, white);
    --thumb-text: var(--theme-text, #18181b);
    --thumb-primary: var(--theme-primary, #4f46e5);
    --thumb-secondary: var(--theme-secondary, #0ea5e9);
    --thumb-muted: color-mix(in srgb, var(--theme-text, #18181b) 28%, transparent);
    --thumb-surface: color-mix(in srgb, var(--theme-text, #18181b) 6%, transparent);
    --thumb-media: linear-gradient(
        135deg,
        color-mix(in srgb, var(--theme-primary, #4f46e5) 35%, transparent),
        color-mix(in srgb, var(--theme-secondary, #0ea5e9) 40%, transparent)
    );

    width: 100%;
    aspect-ratio: 16 / 10;
    border-radius: 5px;
    overflow: hidden;
    background: var(--thumb-bg);
    border: 1px solid color-mix(in srgb, var(--theme-text, #18181b) 10%, transparent);
    transition:
        border-color 160ms ease,
        box-shadow 160ms ease,
        transform 160ms ease;
}

.thumb-stage {
    display: flex;
    flex-direction: column;
    height: 100%;
    padding: 10px 11px;
    gap: 5px;
}

.thumb-centered {
    align-items: center;
    justify-content: center;
    text-align: center;
}

.thumb-centered .bar {
    margin-inline: auto;
}

.thumb-split {
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    gap: 8px;
    align-items: center;
}

.thumb-copy {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.thumb-media {
    width: 100%;
    height: 100%;
    min-height: 42px;
    border-radius: 3px;
    background: var(--thumb-media);
}

.thumb-media-sm {
    min-height: 36px;
}

.thumb-media-fill {
    min-height: 28px;
    flex: 1;
}

.thumb-editorial {
    justify-content: flex-start;
    gap: 6px;
}

.thumb-editorial-lower {
    display: grid;
    grid-template-columns: 1fr 0.85fr;
    gap: 8px;
    align-items: end;
    margin-top: auto;
}

.thumb-minimal {
    justify-content: center;
    max-width: 78%;
}

.thumb-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 5px;
    align-content: center;
}

.thumb-grid-4 {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 4px;
    align-content: center;
}

.thumb-card {
    display: flex;
    flex-direction: column;
    gap: 3px;
    padding: 6px 5px;
    border-radius: 3px;
    background: var(--thumb-surface);
    border-top: 2px solid
        color-mix(in srgb, var(--theme-text, #18181b) 18%, transparent);
}

.thumb-faq {
    justify-content: center;
    gap: 6px;
}

.thumb-faq-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    padding: 5px 6px;
    border-radius: 3px;
    background: var(--thumb-surface);
}

.thumb-faq-mark {
    width: 6px;
    height: 6px;
    border-right: 1.5px solid var(--thumb-muted);
    border-bottom: 1.5px solid var(--thumb-muted);
    transform: rotate(45deg) translateY(-1px);
    flex-shrink: 0;
}

.thumb-menu {
    gap: 7px;
    justify-content: center;
}

.thumb-menu-title {
    align-self: center;
}

.thumb-menu-cols {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.thumb-menu-col {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.thumb-menu-row {
    display: flex;
    align-items: center;
    gap: 3px;
}

.thumb-menu-dots {
    flex: 1;
    height: 0;
    border-bottom: 1px dotted var(--thumb-muted);
    min-width: 6px;
}

.thumb-menu-price {
    width: 18% !important;
    flex-shrink: 0;
}

.thumb-collection,
.thumb-product {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-height: 0;
}

.thumb-price {
    width: 40% !important;
}

.thumb-actions {
    display: flex;
    gap: 4px;
    margin-top: 2px;
}

.thumb-stats {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 6px;
}

.thumb-stat {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 4px 6px;
    border-radius: 3px;
    background: var(--thumb-surface);
}

.thumb-stat .bar-sm {
    width: 34%;
}

.thumb-stat .bar-xs {
    width: 58%;
}

.thumb-logos {
    justify-content: center;
    align-items: center;
    gap: 9px;
}

.thumb-logos-title {
    width: 34%;
}

.thumb-logos-row {
    display: flex;
    width: 100%;
    gap: 5px;
}

.thumb-logo {
    flex: 1;
    height: 16px;
    border-radius: 3px;
    background: var(--thumb-surface);
    border: 1px solid
        color-mix(in srgb, var(--theme-text, #18181b) 12%, transparent);
}

.thumb-testimonials {
    justify-content: center;
    gap: 7px;
}

.thumb-testimonials-title {
    align-self: center;
    width: 48%;
}

.thumb-grid-3-row {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 5px;
}

.thumb-quote-card {
    display: flex;
    flex-direction: column;
    gap: 3px;
    padding: 6px 6px 7px;
    border-radius: 3px;
    background: var(--thumb-surface);
}

.thumb-quote-mark {
    font-size: 11px;
    line-height: 0.7;
    font-weight: 700;
    color: var(--thumb-primary);
}

.thumb-quote-card .bar-sm {
    width: 86%;
}

.thumb-quote-card .bar-xs {
    width: 42%;
    margin-top: 2px;
}

.thumb-menu-cards {
    justify-content: center;
    gap: 7px;
}

.thumb-menu-cards-title {
    align-self: center;
    width: 44%;
}

.thumb-menu-card {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-height: 0;
}

.thumb-menu-card .thumb-media-fill {
    min-height: 30px;
}

.thumb-menu-card-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 4px;
}

.thumb-menu-card-row .bar-xs {
    width: 46%;
}

.thumb-menu-card-row .thumb-price {
    width: 18% !important;
}

.thumb-promos {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
    align-content: center;
}

.thumb-promo {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 4px;
    min-height: 58px;
    padding: 8px;
    border-radius: 4px;
}

.thumb-promo-primary {
    background: color-mix(in srgb, var(--thumb-primary) 85%, transparent);
}

.thumb-promo-secondary {
    background: color-mix(in srgb, var(--thumb-secondary) 85%, transparent);
}

.bar-light {
    background: rgb(255 255 255 / 0.55);
}

.bar-light-strong {
    background: rgb(255 255 255 / 0.92);
}

.chip-light {
    background: rgb(255 255 255 / 0.9);
    width: 22px;
    height: 6px;
}

.bar {
    display: block;
    height: 4px;
    border-radius: 999px;
    background: var(--thumb-muted);
}

.bar-xs {
    width: 28%;
    height: 3px;
}

.bar-sm {
    width: 62%;
}

.bar-md {
    width: 74%;
}

.bar-lg {
    width: 88%;
    height: 5px;
}

.bar-xl {
    width: 70%;
    height: 6px;
}

.bar-xxl {
    width: 92%;
    height: 8px;
}

.bar-heading {
    background: var(--thumb-text);
    opacity: 0.88;
}

.bar-muted {
    background: var(--thumb-muted);
}

.bar-accent {
    background: var(--thumb-primary);
    opacity: 0.85;
}

.chip {
    display: inline-block;
    width: 28px;
    height: 8px;
    border-radius: 999px;
}

.chip-primary {
    background: var(--thumb-primary);
}

.chip-outline {
    background: transparent;
    border: 1.5px solid var(--thumb-primary);
}
</style>
