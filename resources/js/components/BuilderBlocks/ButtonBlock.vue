<!-- eslint-disable vue/block-lang -->
<script setup>
/* eslint-disable vue/no-mutating-props */
import { computed, inject } from 'vue';
import InlineText from './InlineText.vue';

const props = defineProps({
    nodeId: { type: String },
    blockProps: { type: Object, default: () => ({}) },
});
const isEditable = inject('isEditable', false);

const tag = computed(() =>
    !isEditable && props.blockProps?.url ? 'a' : 'button',
);

const openInNewTab = computed(() => Boolean(props.blockProps?.openInNewTab));

const linkTarget = computed(() => {
    if (tag.value !== 'a') {
        return undefined;
    }

    return openInNewTab.value ? '_blank' : undefined;
});

const linkRel = computed(() =>
    tag.value === 'a' && openInNewTab.value ? 'noopener noreferrer' : undefined,
);

const attrs = computed(() => {
    const v = props.blockProps?.variant || 'primary';
    const primary = 'var(--theme-primary)';
    const secondary = 'var(--theme-secondary)';

    let style = {};

    if (v === 'primary') {
        style = { backgroundColor: primary, color: 'white' };
    } else if (v === 'secondary') {
        style = { backgroundColor: secondary, color: 'white' };
    } else if (v === 'outline') {
        style = {
            border: '2px solid ' + primary,
            color: primary,
            backgroundColor: 'transparent',
        };
    }

    return style;
});

const sizeClasses = computed(() => {
    const s = props.blockProps?.size || 'md';

    if (s === 'sm') {
        return 'px-3 py-1.5 text-sm';
    }

    if (s === 'lg') {
        return 'px-7 py-3.5 text-lg';
    }

    return 'px-5 py-2.5 text-base';
});

const alignmentClass = computed(() => {
    if (props.blockProps?.alignment === 'start') {
        return 'justify-start';
    }

    if (props.blockProps?.alignment === 'end') {
        return 'justify-end';
    }

    return 'justify-center';
});
</script>

<template>
    <div :class="['flex items-center py-2', alignmentClass]">
        <component
            :is="tag"
            :href="tag === 'a' ? blockProps.url : undefined"
            :target="linkTarget"
            :rel="linkRel"
            :style="attrs"
            :class="[
                'theme-btn inline-block text-center font-semibold transition-all',
                sizeClasses,
            ]"
        >
            <InlineText
                tag="span"
                :value="blockProps.label"
                placeholder="Button label"
                aria-label="Button label"
                @update:value="blockProps.label = $event"
            />
        </component>
    </div>
</template>

<style scoped>
.theme-btn {
    border-radius: var(--theme-border-radius, 8px);
    transition: all 0.15s ease;
}
.theme-btn:hover {
    filter: brightness(0.9);
}
</style>
