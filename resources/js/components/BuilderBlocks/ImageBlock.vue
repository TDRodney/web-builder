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

        <!-- Editable placeholder when no image set -->
        <div
            v-else-if="isEditable || showMediaPlaceholders"
            class="image-placeholder"
            :style="{
                height: blockProps.height || '300px',
                borderRadius:
                    blockProps.borderRadius || 'var(--theme-border-radius)',
            }"
        >
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
            <span class="placeholder-text">
                {{
                    isEditable
                        ? 'Select image in inspector →'
                        : blockProps.alt || 'Add image in editor'
                }}
            </span>
        </div>
        <!-- Public: render nothing if no src -->
    </div>
</template>

<style scoped>
.image-block-wrapper {
    width: 100%;
}

.image-placeholder {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    background: rgba(255, 255, 255, 0.04);
    border: 2px dashed rgba(255, 255, 255, 0.15);
    cursor: default;
}

.placeholder-icon {
    width: 3rem;
    height: 3rem;
    color: var(--theme-primary, #6366f1);
    opacity: 0.5;
}

.placeholder-text {
    font-size: 0.8125rem;
    font-family: var(--theme-font-body, sans-serif);
    color: var(--theme-text, #94a3b8);
    opacity: 0.6;
}
</style>
