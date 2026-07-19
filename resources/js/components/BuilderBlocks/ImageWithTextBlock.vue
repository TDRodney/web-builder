<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { inject } from 'vue';
import InlineText from './InlineText.vue';
defineProps<{ nodeId?: string; blockProps: Record<string, any> }>();
const isEditable = inject('isEditable', false);
</script>
<template>
    <section
        class="image-text"
        :class="{ reverse: blockProps.imagePosition === 'right' }"
    >
        <div class="media" :class="{ 'media-empty': !blockProps.imageSrc }">
            <img
                v-if="blockProps.imageSrc"
                :src="blockProps.imageSrc"
                :alt="blockProps.imageAlt || ''"
            /><span v-else-if="isEditable" class="media-hint"
                >Choose editorial image in the inspector</span
            >
        </div>
        <div class="copy">
            <InlineText
                tag="p"
                class="eyebrow"
                :value="blockProps.eyebrow"
                placeholder="Eyebrow"
                aria-label="Section eyebrow"
                @update:value="blockProps.eyebrow = $event"
            />
            <InlineText
                tag="h2"
                :value="blockProps.heading"
                placeholder="Section heading"
                aria-label="Section heading"
                @update:value="blockProps.heading = $event"
            />
            <InlineText
                tag="p"
                :value="blockProps.body"
                placeholder="Section description"
                aria-label="Section description"
                multiline
                @update:value="blockProps.body = $event"
            />
            <a v-if="blockProps.linkLabel" :href="blockProps.linkUrl || '#'">{{
                blockProps.linkLabel
            }}</a>
        </div>
    </section>
</template>
<style scoped>
.image-text {
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: 28rem;
}
.reverse .media {
    order: 2;
}
.media {
    display: flex;
    align-items: center;
    justify-content: center;
    background: color-mix(in srgb, var(--theme-text) 8%, transparent);
    color: color-mix(in srgb, var(--theme-text) 55%, transparent);
    font-family: var(--theme-font-body);
}
.media-empty {
    background:
        radial-gradient(
            120% 90% at 20% 0%,
            color-mix(in srgb, var(--theme-primary) 14%, transparent) 0%,
            transparent 60%
        ),
        radial-gradient(
            110% 100% at 85% 100%,
            color-mix(in srgb, var(--theme-secondary) 18%, transparent) 0%,
            transparent 55%
        ),
        color-mix(in srgb, var(--theme-text) 5%, transparent);
}
.media-hint {
    padding: 0 1.5rem;
    font-size: 0.75rem;
    letter-spacing: 0.06em;
    text-align: center;
    text-transform: uppercase;
}
img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.copy {
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: clamp(2rem, 6vw, 5rem);
    font-family: var(--theme-font-body);
}
h2 {
    margin: 0.5rem 0 1rem;
    font: 600 clamp(2rem, 4vw, 3.8rem)/1.05 var(--theme-font-heading);
    color: var(--theme-text);
}
p {
    line-height: 1.7;
    color: color-mix(in srgb, var(--theme-text) 70%, transparent);
}
.eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.16em;
    font-size: 0.72rem;
}
a {
    width: max-content;
    margin-top: 1.5rem;
    color: var(--theme-text);
    border-bottom: 1px solid currentColor;
    text-decoration: none;
}
@container (max-width:640px) {
    .image-text {
        grid-template-columns: 1fr;
    }
    .reverse .media {
        order: 0;
    }
    .media {
        min-height: 20rem;
    }
}
</style>
