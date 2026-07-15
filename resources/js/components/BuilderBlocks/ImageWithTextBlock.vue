<script setup lang="ts">
import { inject } from 'vue';
defineProps<{ nodeId?: string; blockProps: Record<string, any> }>();
const isEditable = inject('isEditable', false);
</script>
<template>
    <section
        class="image-text"
        :class="{ reverse: blockProps.imagePosition === 'right' }"
    >
        <div class="media">
            <img
                v-if="blockProps.imageSrc"
                :src="blockProps.imageSrc"
                :alt="blockProps.imageAlt || ''"
            /><span v-else-if="isEditable"
                >Choose editorial image in the inspector</span
            >
        </div>
        <div class="copy">
            <p class="eyebrow">{{ blockProps.eyebrow }}</p>
            <h2>{{ blockProps.heading }}</h2>
            <p>{{ blockProps.body }}</p>
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
