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

const computedStyles = computed(() => {
  const styles = {};
  
  if (props.propsData?.fontSize) {
    styles.fontSize = typeof props.propsData.fontSize === 'number' 
      ? `${props.propsData.fontSize}px` 
      : props.propsData.fontSize;
  }
  
  if (props.propsData?.color) {
    const colorVal = props.propsData.color;
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
    {{ propsData?.content ?? '' }}
  </div>
</template>
