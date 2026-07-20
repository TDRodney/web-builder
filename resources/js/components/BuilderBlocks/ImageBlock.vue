<script setup lang="ts">
import { inject } from 'vue';

defineProps({
    nodeId: { type: String },
    blockProps: { type: Object, default: () => ({}) },
});

const isEditable = inject('isEditable', false);
const showMediaPlaceholders = inject('showMediaPlaceholders', false);
</script>

<template>
    <div class="image-block-wrapper">
        <!-- Populated image -->
        <img
            v-if="blockProps.src"
            :src="blockProps.src"
            :alt="blockProps.alt || ''"
            :style="{
                objectFit: blockProps.objectFit || 'cover',
                borderRadius:
                    blockProps.borderRadius || 'var(--theme-border-radius)',
                width: blockProps.width || '100%',
                height: blockProps.height || '300px',
                display: 'block',
            }"
        />

        <!-- Designed placeholder when no image set (editor + previews) -->
        <div
            v-else-if="isEditable || showMediaPlaceholders"
            class="image-placeholder"
            :style="{
                height: blockProps.height || '300px',
                borderRadius:
                    blockProps.borderRadius || 'var(--theme-border-radius)',
            }"
        >
            <span class="placeholder-badge">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="placeholder-icon"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                </svg>
            </span>
            <span v-if="blockProps.alt" class="placeholder-caption">
                {{ blockProps.alt }}
            </span>
            <span v-if="isEditable" class="placeholder-hint">
                Choose an image in the inspector
            </span>
        </div>
        <!-- Public: render nothing if no src -->
    </div>
</template>

<style scoped>
.image-block-wrapper {
    width: 100%;
}

/*
 * Empty-image chrome must stay truthful to the live page: no grey wash.
 * Live sites omit the slot entirely; the editor only draws a light dashed
 * outline so the block is still selectable without changing page color.
 */
.image-placeholder {
    position: relative;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.65rem;
    padding: 1.5rem;
    overflow: hidden;
    cursor: default;
    background: transparent;
    border: 1px dashed
        color-mix(in srgb, var(--theme-text, #1e293b) 18%, transparent);
}

.placeholder-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 9999px;
    background: color-mix(
        in srgb,
        var(--theme-text, #1e293b) 4%,
        transparent
    );
    border: 1px solid
        color-mix(in srgb, var(--theme-text, #1e293b) 12%, transparent);
}

.placeholder-icon {
    width: 1.25rem;
    height: 1.25rem;
    color: color-mix(in srgb, var(--theme-text, #334155) 55%, transparent);
}

.placeholder-caption {
    position: relative;
    max-width: 34ch;
    font-size: 0.8rem;
    line-height: 1.45;
    text-align: center;
    font-family: var(--theme-font-body, sans-serif);
    color: color-mix(in srgb, var(--theme-text, #334155) 55%, transparent);
}

.placeholder-hint {
    position: relative;
    font-size: 0.68rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-family: var(--theme-font-body, sans-serif);
    color: color-mix(in srgb, var(--theme-text, #334155) 40%, transparent);
}
</style>
