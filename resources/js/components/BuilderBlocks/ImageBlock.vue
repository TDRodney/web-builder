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
 * Placeholder is built from theme tokens so empty kits still look designed:
 * a soft primary→secondary wash over the theme background plus fine grain.
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
    background:
        radial-gradient(
            120% 90% at 15% 0%,
            color-mix(in srgb, var(--theme-primary, #6366f1) 16%, transparent)
                0%,
            transparent 60%
        ),
        radial-gradient(
            110% 100% at 90% 100%,
            color-mix(
                    in srgb,
                    var(--theme-secondary, #a78bfa) 20%,
                    transparent
                )
                0%,
            transparent 55%
        ),
        color-mix(in srgb, var(--theme-text, #1e293b) 4%, transparent);
}

.image-placeholder::after {
    content: '';
    position: absolute;
    inset: 0;
    pointer-events: none;
    background-image: repeating-linear-gradient(
        -45deg,
        color-mix(in srgb, var(--theme-text, #1e293b) 4%, transparent) 0px,
        color-mix(in srgb, var(--theme-text, #1e293b) 4%, transparent) 1px,
        transparent 1px,
        transparent 9px
    );
}

.placeholder-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    border-radius: 9999px;
    background: color-mix(
        in srgb,
        var(--theme-primary, #6366f1) 12%,
        transparent
    );
    border: 1px solid
        color-mix(in srgb, var(--theme-primary, #6366f1) 24%, transparent);
}

.placeholder-icon {
    width: 1.4rem;
    height: 1.4rem;
    color: var(--theme-primary, #6366f1);
    opacity: 0.85;
}

.placeholder-caption {
    position: relative;
    max-width: 34ch;
    font-size: 0.8rem;
    line-height: 1.45;
    text-align: center;
    font-family: var(--theme-font-body, sans-serif);
    color: color-mix(in srgb, var(--theme-text, #334155) 72%, transparent);
}

.placeholder-hint {
    position: relative;
    font-size: 0.68rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-family: var(--theme-font-body, sans-serif);
    color: color-mix(in srgb, var(--theme-text, #334155) 48%, transparent);
}
</style>
