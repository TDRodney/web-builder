<script setup>
import { computed } from 'vue';

const props = defineProps({
  nodeId: { type: String },
  blockProps: { type: Object, default: () => ({}) }
});

const tag = computed(() => props.blockProps?.url ? 'a' : 'button');

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
    style = { border: '2px solid ' + primary, color: primary, backgroundColor: 'transparent' };
  }
  return style;
});

const sizeClasses = computed(() => {
  const s = props.blockProps?.size || 'md';
  if (s === 'sm') return 'px-3 py-1.5 text-sm';
  if (s === 'lg') return 'px-7 py-3.5 text-lg';
  return 'px-5 py-2.5 text-base';
});
</script>

<template>
  <div class="flex justify-center items-center py-4">
    <component
      :is="tag"
      :href="tag === 'a' ? blockProps.url : undefined"
      :target="tag === 'a' ? '_blank' : undefined"
      :rel="tag === 'a' ? 'noopener noreferrer' : undefined"
      :style="attrs"
      :class="['inline-block font-semibold transition-all text-center theme-btn', sizeClasses]"
    >
      {{ blockProps.label || 'Button' }}
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
