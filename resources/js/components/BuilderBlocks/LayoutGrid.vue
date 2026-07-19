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
    <!--
      Layout styles are applied to the children wrapper in RenderNode /
      RenderPublicNode so nested blocks become real CSS grid items.
    -->
    <div
        class="layout-grid-shell"
        :class="{ 'layout-grid-shell-empty': isEditable && isEmpty }"
    >
        <slot />
    </div>
</template>

<style scoped>
.layout-grid-shell {
    display: contents;
}

.layout-grid-shell-empty {
    display: block;
    width: 100%;
}
</style>
