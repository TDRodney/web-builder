<script setup>
import { computed, useSlots } from 'vue';

const props = defineProps({
  nodeId: {
    type: String,
    required: true
  },
  propsData: {
    type: Object,
    default: () => ({
      columns: 3,
      gap: '1rem',
      padding: '1rem'
    })
  }
});

const slots = useSlots();
const isEmpty = computed(() => {
  if (!slots.default) return true;
  const children = slots.default().filter(c => {
    return c.type && c.type.toString() !== 'Symbol(Comment)' && c.type.toString() !== 'Symbol(v-cmt)';
  });
  return children.length === 0;
});

const computedStyles = computed(() => {
  const columns = props.propsData?.columns ?? 3;
  const gapValue = props.propsData?.gap ?? '1rem';
  const paddingValue = props.propsData?.padding ?? '1rem';
  const widthValue = props.propsData?.width;
  const heightValue = props.propsData?.height;

  return {
    '--grid-columns': columns,
    '--grid-gap': typeof gapValue === 'number' ? `${gapValue}px` : gapValue,
    '--grid-padding': typeof paddingValue === 'number' ? `${paddingValue}px` : paddingValue,
    '--grid-width': widthValue !== undefined && widthValue !== null ? (typeof widthValue === 'number' ? `${widthValue}px` : widthValue) : 'auto',
    '--grid-height': heightValue !== undefined && heightValue !== null ? (typeof heightValue === 'number' ? `${heightValue}px` : heightValue) : 'auto',
  };
});
</script>

<template>
  <div 
    class="layout-grid-container min-h-[50px] transition-all"
    :class="{ 'border-2 border-dashed border-slate-300 rounded-lg p-6 bg-slate-50/50': isEmpty }"
    :style="computedStyles"
  >
    <slot />
  </div>
</template>

<style scoped>
.layout-grid-container {
  display: grid;
  grid-template-columns: repeat(var(--grid-columns, 3), minmax(0, 1fr));
  gap: var(--grid-gap, 1rem);
  padding: var(--grid-padding, 1rem);
  width: var(--grid-width, auto);
  height: var(--grid-height, auto);
}
</style>
