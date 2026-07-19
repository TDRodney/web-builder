<script setup lang="ts">
import { inject, nextTick, ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        value?: string | number | null;
        tag?: string;
        placeholder?: string;
        multiline?: boolean;
        ariaLabel?: string;
    }>(),
    {
        value: '',
        tag: 'span',
        placeholder: 'Click to edit',
        multiline: false,
        ariaLabel: 'Editable text',
    },
);

const emit = defineEmits<{
    'update:value': [value: string];
}>();

const isEditable = inject('isEditable', false);
const element = ref<HTMLElement | null>(null);
const isFocused = ref(false);
const cancelled = ref(false);

const valueAsText = (): string => String(props.value ?? '');

const syncElement = async (): Promise<void> => {
    await nextTick();

    if (element.value && !isFocused.value) {
        element.value.textContent = valueAsText();
    }
};

watch(() => props.value, syncElement, { immediate: true });

const focus = (): void => {
    isFocused.value = true;
    cancelled.value = false;
};

const blur = (): void => {
    isFocused.value = false;

    if (!element.value) {
        return;
    }

    if (cancelled.value) {
        element.value.textContent = valueAsText();
        cancelled.value = false;

        return;
    }

    const nextValue = props.multiline
        ? element.value.innerText.replace(/\n{3,}/g, '\n\n').trim()
        : (element.value.textContent ?? '').replace(/\s+/g, ' ').trim();

    element.value.textContent = nextValue;

    if (nextValue !== valueAsText()) {
        emit('update:value', nextValue);
    }
};

const handleKeydown = (event: KeyboardEvent): void => {
    if (event.key === 'Escape') {
        event.preventDefault();
        cancelled.value = true;
        element.value?.blur();

        return;
    }

    if (event.key === 'Enter' && !props.multiline) {
        event.preventDefault();
        element.value?.blur();
    }
};

const pastePlainText = (event: ClipboardEvent): void => {
    if (!isEditable) {
        return;
    }

    event.preventDefault();
    const text = event.clipboardData?.getData('text/plain') ?? '';
    const selection = window.getSelection();

    if (!selection?.rangeCount) {
        return;
    }

    const range = selection.getRangeAt(0);
    range.deleteContents();
    range.insertNode(document.createTextNode(text));
    range.collapse(false);
    selection.removeAllRanges();
    selection.addRange(range);
};
</script>

<template>
    <component
        :is="tag"
        ref="element"
        class="inline-text"
        :class="{ 'inline-text-editable': isEditable }"
        :contenteditable="isEditable ? 'plaintext-only' : undefined"
        :data-placeholder="isEditable ? placeholder : undefined"
        :aria-label="isEditable ? ariaLabel : undefined"
        :role="isEditable ? 'textbox' : undefined"
        :aria-multiline="isEditable ? multiline : undefined"
        :spellcheck="isEditable"
        @focus="focus"
        @blur="blur"
        @keydown="handleKeydown"
        @paste="pastePlainText"
        >{{ valueAsText() }}</component
    >
</template>

<style scoped>
.inline-text {
    min-width: 1ch;
}

.inline-text-editable {
    cursor: text;
    border-radius: 3px;
    outline: 1px dashed transparent;
    outline-offset: 3px;
    transition:
        outline-color 120ms ease,
        background-color 120ms ease;
}

.inline-text-editable:hover {
    outline-color: color-mix(in srgb, var(--theme-primary) 42%, transparent);
}

.inline-text-editable:focus {
    color: var(--editor-text, #18181b) !important;
    caret-color: var(--editor-text, #18181b);
    background: color-mix(
        in srgb,
        var(--editor-panel, #ffffff) 94%,
        var(--editor-bg, #f4f4f5)
    );
    outline: 2px solid
        color-mix(in srgb, var(--editor-text, #18181b) 58%, transparent);
    box-shadow: 0 0 0 3px
        color-mix(in srgb, var(--editor-panel, #ffffff) 72%, transparent);
}

.inline-text-editable:focus::selection {
    color: var(--editor-panel, #ffffff);
    background: var(--editor-text, #18181b);
}

.inline-text-editable:empty::before {
    content: attr(data-placeholder);
    opacity: 0.45;
}

@media (prefers-reduced-motion: reduce) {
    .inline-text-editable {
        transition: none;
    }
}
</style>
