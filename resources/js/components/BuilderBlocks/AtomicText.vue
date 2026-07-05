<script>
export default {
  blueprint: {
    props: { content: 'Atomic Text Element', fontSize: '16px', color: '#0f172a' }
  }
}
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  nodeId: {
    type: String,
    required: true
  },
  blockProps: {
    type: Object,
    default: () => ({})
  }
});

const computedStyles = computed(() => {
  const styles = {};
  
  if (props.blockProps?.fontSize) {
    styles.fontSize = typeof props.blockProps.fontSize === 'number' 
      ? `${props.blockProps.fontSize}px` 
      : props.blockProps.fontSize;
  }
  
  if (props.blockProps?.color) {
    const colorVal = props.blockProps.color;
    styles.color = String(colorVal).startsWith('--') ? `var(${colorVal})` : colorVal;
  }
  
  return styles;
});
</script>

<template>
  <div 
    tabindex="0"
    :style="computedStyles"
    class="focus:ring-2 focus:ring-indigo-500 focus:outline-none hover:ring-1 hover:ring-indigo-300 rounded p-1 transition-all cursor-pointer"
  >
    {{ props.blockProps?.content ?? '' }}
  </div>
</template>
