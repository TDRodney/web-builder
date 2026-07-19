<!-- eslint-disable vue/block-lang -->
<script setup>
import { computed, inject, useSlots } from 'vue';

defineProps({
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
const slots = useSlots();

const isEmpty = computed(() => {
    if (!slots.default) {
        return true;
    }

    const children = slots.default().filter((c) => {
        return (
            c.type &&
            c.type.toString() !== 'Symbol(Comment)' &&
            c.type.toString() !== 'Symbol(v-cmt)'
        );
    });

    return children.length === 0;
});
</script>

<template>
    <div
        class="layout-column-shell"
        :class="{ 'layout-column-shell-empty': isEditable && isEmpty }"
    >
        <slot />
    </div>
</template>

<style scoped>
.layout-column-shell {
    display: contents;
}

.layout-column-shell-empty {
    display: block;
    width: 100%;
}
</style>
