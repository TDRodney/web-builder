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

const resolveColor = (value) => {
    if (!value) {
        return null;
    }

    return String(value).startsWith('--') ? `var(${value})` : String(value);
};

const variantBase = computed(() => {
    const v = props.blockProps?.variant || 'primary';

    if (v === 'secondary') {
        return {
            bg: 'var(--theme-secondary)',
            text: '#ffffff',
            outline: false,
        };
    }

    if (v === 'outline') {
        return { bg: 'transparent', text: 'var(--theme-primary)', outline: true };
    }

    return { bg: 'var(--theme-primary)', text: '#ffffff', outline: false };
});

const hasCustomHover = computed(
    () =>
        Boolean(props.blockProps?.hoverBackgroundColor) ||
        Boolean(props.blockProps?.hoverTextColor),
);

const attrs = computed(() => {
    const base = variantBase.value;
    const bg = resolveColor(props.blockProps?.backgroundColor) ?? base.bg;
    // A "None" background with the filled variants' white text would be
    // invisible on light pages, so fall back to the primary theme color.
    const transparentFallbackText =
        bg === 'transparent' && !base.outline
            ? 'var(--theme-primary)'
            : base.text;
    const text =
        resolveColor(props.blockProps?.textColor) ?? transparentFallbackText;
    const hoverBg =
        resolveColor(props.blockProps?.hoverBackgroundColor) ?? bg;
    const hoverText =
        resolveColor(props.blockProps?.hoverTextColor) ?? text;

    const style = {
        backgroundColor: bg,
        color: text,
        '--btn-hover-bg': hoverBg,
        '--btn-hover-text': hoverText,
    };

    const radius = props.blockProps?.borderRadius;

    if (radius !== undefined && radius !== null && radius !== '') {
        style.borderRadius =
            typeof radius === 'number' ? `${radius}px` : String(radius);
    }

    if (base.outline) {
        // Outline accent follows the text color so the border stays in sync.
        style.border = '2px solid ' + text;
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
                'theme-btn inline-block max-w-full text-center font-semibold transition-all',
                sizeClasses,
                { 'theme-btn--hover-custom': hasCustomHover },
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
    box-sizing: border-box;
    max-width: 100%;
    border-radius: var(--theme-border-radius, 8px);
    transition: all 0.15s ease;
    /* Wrap at word boundaries only — never split letters inside a narrow cell. */
    overflow-wrap: normal;
    word-break: normal;
}
.theme-btn:hover {
    filter: brightness(0.9);
}
.theme-btn--hover-custom:hover {
    background-color: var(--btn-hover-bg);
    color: var(--btn-hover-text);
    filter: none;
}
</style>
