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

const titleStyle = computed(() => ({
    color: resolveColor(props.blockProps?.titleColor, 'var(--theme-text)'),
    fontFamily:
        props.blockProps?.fontFamily === 'body'
            ? 'var(--theme-font-body)'
            : 'var(--theme-font-heading)',
}));

const bodyStyle = computed(() => ({
    color: resolveColor(
        props.blockProps?.bodyColor,
        'color-mix(in srgb, var(--theme-text) 60%, transparent)',
    ),
}));
</script>

<template>
    <div
        class="border-t py-4"
        :style="{
            borderTopColor:
                'color-mix(in srgb, var(--theme-text) 15%, transparent)',
        }"
    >
        <InlineText
            tag="h3"
            class="text-lg font-bold"
            :style="titleStyle"
            :value="blockProps.title"
            placeholder="Feature title"
            aria-label="Feature title"
            @update:value="blockProps.title = $event"
        />
        <InlineText
            tag="p"
            class="mt-1 text-sm"
            :style="bodyStyle"
            :value="blockProps.body"
            placeholder="Feature description"
            aria-label="Feature description"
            multiline
            @update:value="blockProps.body = $event"
        />
    </div>
</template>
