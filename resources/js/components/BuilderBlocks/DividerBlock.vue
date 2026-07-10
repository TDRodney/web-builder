<script setup>
import { computed, inject } from 'vue';

const props = defineProps({
  nodeId: { type: String },
  blockProps: { type: Object, default: () => ({}) }
});

const isEditable = inject('isEditable', false);

const dividerStyle = computed(() => ({
  height: (props.blockProps?.thickness ?? 1) + 'px',
  backgroundColor: props.blockProps?.color || '#cbd5e1',
  marginTop: (props.blockProps?.margin ?? 16) + 'px',
  marginBottom: (props.blockProps?.margin ?? 16) + 'px',
}));
</script>

<template>
  <div
    :style="dividerStyle"
    :class="isEditable ? 'cursor-pointer' : ''"
  />
</template>