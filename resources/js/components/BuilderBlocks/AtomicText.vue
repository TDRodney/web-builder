<script>
export default {
  blueprint: {
    props: { content: 'Atomic Text Element', fontSize: '16px', color: '#0f172a' }
  }
}
</script>

<script setup>
import { computed, inject } from 'vue';

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

const isEditable = inject('isEditable', false);

const computedStyles = computed(() => {
  const styles = {
    fontFamily: 'var(--theme-font-body)',
  };
  
  if (props.blockProps?.fontSize) {
    styles.fontSize = typeof props.blockProps.fontSize === 'number' 
      ? `${props.blockProps.fontSize}px` 
      : props.blockProps.fontSize;
  }
  
  if (props.blockProps?.color) {
    const colorVal = props.blockProps.color;
    styles.color = (colorVal === '#0f172a') 
      ? 'var(--theme-text)' 
      : (String(colorVal).startsWith('--') ? `var(${colorVal})` : colorVal);
  } else {
    styles.color = 'var(--theme-text)';
  }
  
  return styles;
});
</script>

<template>
  <div 
    :tabindex="isEditable ? 0 : undefined"
    :style="computedStyles"
    :class="isEditable 
      ? 'focus:ring-2 focus:ring-indigo-500 focus:outline-none hover:ring-1 hover:ring-indigo-300 rounded p-1 transition-all cursor-pointer' 
      : 'p-1 transition-all'
    "
  >
    {{ props.blockProps?.content ?? '' }}
  </div>
</template>
