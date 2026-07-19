<!-- eslint-disable vue/block-lang -->
<script setup>
/* eslint-disable vue/no-mutating-props */
import { computed } from 'vue';
import InlineText from './InlineText.vue';

const props = defineProps({
    nodeId: { type: String },
    blockProps: { type: Object, default: () => ({}) },
});

const resolveColor = (value, fallback) => {
    if (!value) {
        return fallback;
    }

    return String(value).startsWith('--') ? `var(${value})` : value;
};

const headingFamily = computed(() =>
    props.blockProps?.fontFamily === 'body'
        ? 'var(--theme-font-body)'
        : 'var(--theme-font-heading)',
);

const alignClass = computed(() => {
    const align = props.blockProps?.align || 'center';

    if (align === 'left') {
        return 'text-left';
    }

    if (align === 'right') {
        return 'text-right';
    }

    return 'text-center';
});

const headlineStyle = computed(() => ({
    color: resolveColor(props.blockProps?.headlineColor, 'var(--theme-text)'),
    fontFamily: headingFamily.value,
}));

const subheadlineStyle = computed(() => ({
    color: resolveColor(
        props.blockProps?.subheadlineColor,
        'color-mix(in srgb, var(--theme-text) 60%, transparent)',
    ),
}));
</script>

<template>
    <div :class="alignClass">
        <InlineText
            tag="h1"
            class="text-4xl font-extrabold tracking-tight @md:text-6xl"
            :style="headlineStyle"
            :value="blockProps.headline"
            placeholder="Click to edit headline"
            aria-label="Hero headline"
            @update:value="blockProps.headline = $event"
        />
        <InlineText
            tag="p"
            class="mt-4 text-xl"
            :style="subheadlineStyle"
            :value="blockProps.subheadline"
            placeholder="Click to edit subheadline"
            aria-label="Hero subheadline"
            multiline
            @update:value="blockProps.subheadline = $event"
        />
    </div>
</template>
