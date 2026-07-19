<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { computed } from 'vue';
import { useCommerceBlock } from '@/lib/commerce';
import type { CommerceCollection } from '@/types/commerce';
import InlineText from './InlineText.vue';

const props = defineProps<{
    nodeId?: string;
    blockProps: Record<string, any>;
}>();
const hydratedBlock = useCommerceBlock<{
    collections: CommerceCollection[];
}>(props.nodeId);
const collections = computed(() => {
    if (hydratedBlock.value?.status === 'ready') {
        return hydratedBlock.value.data?.collections || [];
    }

    return props.blockProps.collections || [];
});
</script>
<template>
    <section>
        <div
            v-if="hydratedBlock?.status === 'unavailable'"
            class="connection-note"
            role="status"
        >
            {{ hydratedBlock.message }} Showing editable fallback collections.
        </div>
        <div class="heading">
            <div>
                <InlineText
                    tag="p"
                    :value="blockProps.eyebrow"
                    placeholder="Section eyebrow"
                    aria-label="Collection section eyebrow"
                    @update:value="blockProps.eyebrow = $event"
                />
                <InlineText
                    tag="h2"
                    :value="blockProps.heading"
                    placeholder="Collection section heading"
                    aria-label="Collection section heading"
                    @update:value="blockProps.heading = $event"
                />
            </div>
        </div>
        <div class="grid">
            <a
                v-for="item in collections"
                :key="item.id || item.handle || item.title"
                :href="item.url || '#'"
                ><div class="media" :class="{ 'media-empty': !item.imageSrc }">
                    <img
                        v-if="item.imageSrc"
                        :src="item.imageSrc"
                        :alt="item.imageAlt || item.title"
                    />
                </div>
                <h3>{{ item.title }}</h3>
                <span>{{ item.subtitle }}</span></a
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
.heading {
    margin-bottom: 2rem;
}
p {
    font-size: 0.72rem;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}
h2 {
    margin: 0.4rem 0;
    font: 600 clamp(2rem, 4vw, 3.4rem)/1.1 var(--theme-font-heading);
}
.grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.2rem;
}
a {
    color: var(--theme-text);
    text-decoration: none;
}
.media {
    aspect-ratio: 4/5;
    background: color-mix(in srgb, var(--theme-text) 8%, transparent);
    overflow: hidden;
}
.media-empty {
    background:
        radial-gradient(
            120% 90% at 20% 0%,
            color-mix(in srgb, var(--theme-primary) 12%, transparent) 0%,
            transparent 60%
        ),
        radial-gradient(
            110% 100% at 85% 100%,
            color-mix(in srgb, var(--theme-secondary) 16%, transparent) 0%,
            transparent 55%
        ),
        color-mix(in srgb, var(--theme-text) 6%, transparent);
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
h3 {
    margin: 0.9rem 0 0.2rem;
    font: 600 1.2rem var(--theme-font-heading);
}
span {
    font-size: 0.85rem;
    opacity: 0.65;
}
@container (max-width:640px) {
    .grid {
        grid-template-columns: 1fr 1fr;
    }
    .grid a:last-child {
        grid-column: 1/-1;
    }
}
</style>
