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
</script>

<template>
  <div 
    class="layout-grid-container min-h-[50px] transition-all"
    :class="{ 'border-2 border-dashed border-slate-300 rounded-lg p-6 bg-slate-50/50': isEmpty }"
  >
    <slot />
  </div>
</template>

<style scoped>
.layout-grid-container {
  display: grid;
  grid-template-columns: repeat(v-bind('propsData.columns'), minmax(0, 1fr));
  gap: v-bind('propsData.gap');
  padding: v-bind('propsData.padding');
}
</style>
