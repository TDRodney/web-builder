<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { computed } from 'vue';

import InlineText from './InlineText.vue';

const props = defineProps<{
    nodeId?: string;
    blockProps: Record<string, unknown>;
}>();

const resolveColor = (value: unknown, fallback: string): string => {
    if (typeof value !== 'string' || value === '') {
        return fallback;
    }

    return value.startsWith('--') ? `var(${value})` : value;
};

const announcementStyles = computed(() => ({
    backgroundColor: resolveColor(
        props.blockProps.barColor,
        'var(--theme-primary, #111827)',
    ),
    color: resolveColor(props.blockProps.textColor, 'var(--theme-bg, #ffffff)'),
}));
</script>
<template>
    <InlineText
        tag="div"
        class="announcement"
        :style="announcementStyles"
        :value="blockProps.text"
        placeholder="Announcement text"
        aria-label="Announcement text"
        @update:value="blockProps.text = $event"
    />
</template>
<style scoped>
.announcement {
    padding: 0.7rem 1rem;
    text-align: center;
    font-family: var(--theme-font-body);
    font-size: 0.82rem;
    letter-spacing: 0.08em;
}
</style>
