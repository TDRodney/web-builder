<script setup lang="ts">
import { computed, inject, useSlots } from 'vue';

const props = defineProps({
    nodeId: { type: String, required: true },
    blockProps: { type: Object, default: () => ({}) },
});

const slots = useSlots();
const isEditable = inject('isEditable', false);

const hasChildren = computed(() => Boolean(slots.default?.().length));
const sectionStyle = computed(() => ({
    '--section-content-width': `${props.blockProps.contentWidth ?? 1180}px`,
    '--section-min-height': `${props.blockProps.minHeight ?? 520}px`,
    '--section-padding': `${props.blockProps.sectionPadding ?? 72}px`,
    '--section-align': props.blockProps.verticalAlign ?? 'center',
    '--section-text-align': props.blockProps.textAlign ?? 'left',
    '--section-overlay-opacity': `${(props.blockProps.overlayOpacity ?? 0) / 100}`,
    backgroundColor: props.blockProps.backgroundColor || 'transparent',
    backgroundImage: props.blockProps.backgroundImage
        ? `url(${props.blockProps.backgroundImage})`
        : undefined,
}));
</script>

<template>
    <section
        class="section-block"
        :class="{ 'section-block-empty': isEditable && !hasChildren }"
        :style="sectionStyle"
    >
        <span
            v-if="blockProps.backgroundImage"
            class="section-overlay"
            aria-hidden="true"
        ></span>
        <div class="section-content">
            <slot />
        </div>
    </section>
</template>

<style scoped>
.section-block {
    position: relative;
    display: flex;
    min-height: var(--section-min-height);
    align-items: var(--section-align);
    overflow: hidden;
    background-repeat: no-repeat;
    background-position: center;
    background-size: cover;
}

.section-overlay {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: var(--theme-text, #0f172a);
    opacity: var(--section-overlay-opacity);
}

.section-content {
    position: relative;
    z-index: 1;
    width: min(100%, var(--section-content-width));
    padding: var(--section-padding) clamp(1.25rem, 4vw, 4rem);
    margin-inline: auto;
    text-align: var(--section-text-align);
}

.section-block-empty {
    border: 1px dashed color-mix(in srgb, var(--theme-primary) 36%, transparent);
}

@container (max-width: 640px) {
    .section-block {
        min-height: min(var(--section-min-height), 680px);
    }

    .section-content {
        padding: clamp(2.5rem, 12vw, 4.5rem) 1.25rem;
    }
}
</style>
