<script setup>
import { computed } from 'vue';

const props = defineProps({
  nodeId: {
    type: String,
    required: true
  },
  propsData: {
    type: Object,
    default: () => ({})
  }
});

const columnStyle = computed(() => {
  const span = props.propsData?.span;
  if (!span) return {};
  
  // If span represents a percentage, fraction or custom size
  if (typeof span === 'string' && (span.includes('%') || span.includes('px') || span.includes('/'))) {
    return { flexBasis: span };
  }
  
  // Otherwise, default to CSS grid column span
  return {
    gridColumn: `span ${span} / span ${span}`
  };
});
</script>

<template>
  <div :style="columnStyle" class="w-full min-h-[50px]">
    <slot />
  </div>
</template>
