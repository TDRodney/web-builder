<!-- eslint-disable vue/block-lang -->
<script>
export default {
    blueprint: {
        props: {
            content: 'Atomic Text Element',
            fontSize: '16px',
            color: '--theme-text',
        },
    },
};
</script>

<!-- eslint-disable vue/block-lang -->
<script setup>
/* eslint-disable vue/no-mutating-props */
import { computed, inject } from 'vue';
import InlineText from './InlineText.vue';

const props = defineProps({
    nodeId: {
        type: String,
        required: true,
    },
    blockProps: {
        type: Object,
        default: () => ({}),
    },
});

const isEditable = inject('isEditable', false);

const computedStyles = computed(() => {
    const styles = {
        fontFamily:
            props.blockProps?.fontFamily === 'heading'
                ? 'var(--theme-font-heading)'
                : 'var(--theme-font-body)',
        fontWeight: props.blockProps?.fontWeight || '400',
        lineHeight: props.blockProps?.lineHeight || '1.4',
        letterSpacing: props.blockProps?.letterSpacing || '0em',
        textAlign: props.blockProps?.textAlign || 'inherit',
        maxWidth: props.blockProps?.maxWidth || 'none',
    };

    if (props.blockProps?.fontSize) {
        styles.fontSize =
            typeof props.blockProps.fontSize === 'number'
                ? `${props.blockProps.fontSize}px`
                : props.blockProps.fontSize;
    }

    if (props.blockProps?.color) {
        const colorVal = props.blockProps.color;
        styles.color =
            colorVal === '#0f172a'
                ? 'var(--theme-text)'
                : String(colorVal).startsWith('--')
                  ? `var(${colorVal})`
                  : colorVal;
    } else {
        styles.color = 'var(--theme-text)';
    }

    return styles;
});
</script>

<template>
    <InlineText
        tag="div"
        :tabindex="isEditable ? 0 : undefined"
        :style="computedStyles"
        :class="
            isEditable
                ? 'cursor-pointer rounded p-1 transition-all hover:ring-1 hover:ring-indigo-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none'
                : 'p-1 transition-all'
        "
        :value="props.blockProps?.content"
        placeholder="Click to edit text"
        aria-label="Text content"
        multiline
        @update:value="props.blockProps.content = $event"
    />
</template>
