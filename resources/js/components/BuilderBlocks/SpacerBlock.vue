<script setup>
const props = defineProps({
  nodeId: { type: String },
  blockProps: { type: Object, default: () => ({}) }
});
</script>

<template>
  <div
    class="w-full"
    :style="{ height: (blockProps?.height ?? 24) + 'px' }"
  />
</template>