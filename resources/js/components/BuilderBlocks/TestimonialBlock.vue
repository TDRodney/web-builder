<!-- eslint-disable vue/block-lang -->
<script setup>
/* eslint-disable vue/no-mutating-props */
import { inject } from 'vue';
import InlineText from './InlineText.vue';

defineProps({
    nodeId: { type: String, required: true },
    blockProps: { type: Object, default: () => ({}) },
});

const isEditable = inject('isEditable', false);
</script>

<template>
    <div
        class="testimonial-card"
        :style="{
            borderRadius: 'var(--theme-border-radius, 8px)',
            color: 'var(--theme-text, #0f172a)',
        }"
    >
        <!-- Big Quotes Mark decoration -->
        <span
            class="quote-mark"
            :style="{ color: 'var(--theme-primary, #6366f1)' }"
            >“</span
        >

        <InlineText
            tag="blockquote"
            class="testimonial-quote"
            :value="blockProps.quote"
            placeholder="Click to edit testimonial"
            aria-label="Testimonial quote"
            multiline
            @update:value="blockProps.quote = $event"
        />

        <div class="testimonial-author">
            <div v-if="blockProps.avatarSrc" class="avatar-container">
                <img
                    :src="blockProps.avatarSrc"
                    alt="Avatar"
                    class="avatar-img"
                />
            </div>
            <div v-else-if="isEditable" class="avatar-placeholder">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="avatar-icon"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                    />
                </svg>
            </div>

            <div class="author-details">
                <InlineText
                    tag="h5"
                    class="author-name"
                    :style="{ color: 'var(--theme-primary, #6366f1)' }"
                    :value="blockProps.authorName"
                    placeholder="Author name"
                    aria-label="Testimonial author"
                    @update:value="blockProps.authorName = $event"
                />
                <InlineText
                    class="author-role"
                    :value="blockProps.authorRole"
                    placeholder="Author role"
                    aria-label="Testimonial author role"
                    @update:value="blockProps.authorRole = $event"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>
.testimonial-card {
    position: relative;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.06);
    padding: 2.25rem 2rem;
    box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.15);
    overflow: hidden;
}

.quote-mark {
    position: absolute;
    top: -0.5rem;
    left: 1rem;
    font-size: 5rem;
    font-family: serif;
    opacity: 0.15;
    line-height: 1;
}

.testimonial-quote {
    font-family: var(--theme-font-body, sans-serif);
    font-size: 1.1rem;
    font-style: italic;
    line-height: 1.6;
    margin: 0 0 1.5rem 0;
    position: relative;
    z-index: 1;
}

.testimonial-author {
    display: flex;
    align-items: center;
    gap: 0.875rem;
}

.avatar-container {
    width: 3rem;
    height: 3rem;
    border-radius: 9999px;
    overflow: hidden;
    border: 2px solid var(--theme-primary, #6366f1);
    flex-shrink: 0;
}

.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 3rem;
    height: 3rem;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.06);
    border: 2px dashed rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.avatar-icon {
    width: 1.25rem;
    height: 1.25rem;
    color: var(--theme-text);
    opacity: 0.4;
}

.author-details {
    display: flex;
    flex-direction: column;
}

.author-name {
    font-family: var(--theme-font-heading, sans-serif);
    font-size: 0.95rem;
    font-weight: 700;
    margin: 0 0 0.15rem 0;
}

.author-role {
    font-family: var(--theme-font-body, sans-serif);
    font-size: 0.8125rem;
    opacity: 0.6;
}
</style>
