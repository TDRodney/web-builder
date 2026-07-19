<!-- eslint-disable vue/block-lang -->
<script setup>
/* eslint-disable vue/no-mutating-props */
import { ref, inject } from 'vue';
import InlineText from './InlineText.vue';

defineProps({
    nodeId: { type: String, required: true },
    blockProps: { type: Object, default: () => ({}) },
});

const isEditable = inject('isEditable', false);

// Form state
const formData = ref({});
const isSubmitting = ref(false);
const showSuccess = ref(false);
const errorMessage = ref('');

const getCsrfToken = () => {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
};

const handleSubmit = async () => {
    if (isEditable) {
        return;
    }

    isSubmitting.value = true;
    errorMessage.value = '';
    showSuccess.value = false;

    try {
        const res = await fetch('/contact', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
                'X-XSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({
                data: formData.value,
            }),
        });

        const body = await res.json().catch(() => ({}));

        if (res.ok) {
            showSuccess.value = true;
            formData.value = {};
        } else {
            errorMessage.value =
                body.message ||
                'Submission failed. Please check inputs and try again.';
        }
    } catch {
        errorMessage.value = 'A network error occurred. Please try again.';
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <div
        class="contact-block-wrapper"
        :style="{ color: 'var(--theme-text, #0f172a)' }"
    >
        <div
            v-if="showSuccess"
            class="success-banner"
            :style="{ borderRadius: 'var(--theme-border-radius, 6px)' }"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="success-icon"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                />
            </svg>
            <p class="success-text">
                {{
                    blockProps.successMessage ||
                    'Thank you! Your message has been sent successfully.'
                }}
            </p>
            <button
                type="button"
                class="reset-btn"
                @click="showSuccess = false"
            >
                Send another message
            </button>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="contact-form">
            <div v-if="errorMessage" class="error-banner">
                {{ errorMessage }}
            </div>

            <div
                v-for="(field, index) in blockProps.fields || []"
                :key="index"
                class="form-group"
            >
                <InlineText
                    v-model:value="field.label"
                    tag="label"
                    class="form-label"
                    placeholder="Field label"
                    aria-label="Form field label"
                />

                <textarea
                    v-if="field.type === 'textarea'"
                    v-model="formData[field.label]"
                    :placeholder="field.placeholder"
                    :required="field.required === 'yes'"
                    :disabled="isSubmitting || isEditable"
                    class="form-textarea"
                    :style="{ borderRadius: 'var(--theme-border-radius, 6px)' }"
                ></textarea>

                <input
                    v-else
                    :type="field.type || 'text'"
                    v-model="formData[field.label]"
                    :placeholder="field.placeholder"
                    :required="field.required === 'yes'"
                    :disabled="isSubmitting || isEditable"
                    class="form-input"
                    :style="{ borderRadius: 'var(--theme-border-radius, 6px)' }"
                />
            </div>

            <button
                type="submit"
                :disabled="isSubmitting"
                class="submit-btn"
                :style="{
                    borderRadius: 'var(--theme-border-radius, 6px)',
                    backgroundColor: 'var(--theme-primary, #6366f1)',
                }"
            >
                <template v-if="isSubmitting">Sending...</template>
                <InlineText
                    v-else
                    v-model:value="blockProps.submitLabel"
                    tag="span"
                    placeholder="Send message"
                    aria-label="Submit button label"
                />
            </button>
        </form>
    </div>
</template>

<style scoped>
.contact-block-wrapper {
    width: 100%;
}

.contact-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.form-label {
    font-family: var(--theme-font-body, sans-serif);
    font-size: 0.8125rem;
    font-weight: 600;
    opacity: 0.85;
}

.form-input,
.form-textarea {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: 0.75rem 1rem;
    font-family: var(--theme-font-body, sans-serif);
    font-size: 0.875rem;
    color: var(--theme-text, #0f172a);
    transition:
        border-color 0.15s ease,
        box-shadow 0.15s ease;
    outline: none;
}

.form-input:focus,
.form-textarea:focus {
    border-color: var(--theme-primary, #6366f1);
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
}

.form-textarea {
    min-height: 120px;
    resize: vertical;
}

.submit-btn {
    border: 0;
    color: #ffffff;
    padding: 0.875rem 1.5rem;
    font-family: var(--theme-font-body, sans-serif);
    font-size: 0.875rem;
    font-weight: 700;
    cursor: pointer;
    transition:
        opacity 0.15s ease,
        filter 0.15s ease;
}

.submit-btn:hover {
    filter: brightness(1.1);
}

.submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.success-banner {
    background: rgba(16, 185, 129, 0.05);
    border: 1px solid rgba(16, 185, 129, 0.2);
    padding: 2.5rem 2rem;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
}

.success-icon {
    width: 3rem;
    height: 3rem;
    color: #10b981;
}

.success-text {
    font-family: var(--theme-font-body, sans-serif);
    font-size: 1rem;
    font-weight: 600;
    margin: 0;
}

.reset-btn {
    background: transparent;
    border: 0;
    color: var(--theme-primary, #6366f1);
    font-family: var(--theme-font-body, sans-serif);
    font-size: 0.8125rem;
    font-weight: 700;
    text-decoration: underline;
    cursor: pointer;
    padding: 0;
    margin-top: 0.5rem;
}

.error-banner {
    background: rgba(239, 68, 68, 0.08);
    border: 1px solid rgba(239, 68, 68, 0.2);
    color: #f87171;
    padding: 0.75rem 1rem;
    border-radius: 6px;
    font-family: var(--theme-font-body, sans-serif);
    font-size: 0.875rem;
}
</style>
