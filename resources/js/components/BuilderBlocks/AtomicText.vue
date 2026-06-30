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
  props: {
    type: Object,
    default: () => ({})
  }
});

const computedStyles = computed(() => {
  const styles = {};
  
  if (props.props?.fontSize) {
    styles.fontSize = typeof props.props.fontSize === 'number' 
      ? `${props.props.fontSize}px` 
      : props.props.fontSize;
  }
  
  if (props.props?.color) {
    const colorVal = props.props.color;
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
    {{ props.props?.content ?? '' }}
  </div>
</template>
