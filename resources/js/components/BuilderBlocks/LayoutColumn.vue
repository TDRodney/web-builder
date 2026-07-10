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
  const span = props.blockProps?.span;
  const paddingValue = props.blockProps?.padding;
  const widthValue = props.blockProps?.width;
  const heightValue = props.blockProps?.height;
  const gapValue = props.blockProps?.gap;
  const styles = {};

  if (span) {
    if (typeof span === 'string' && (span.includes('%') || span.includes('px') || span.includes('/'))) {
      styles['--column-flex-basis'] = span;
      styles['--column-grid-span'] = 'auto';
    } else {
      styles['--column-grid-span'] = `span ${span} / span ${span}`;
      styles['--column-flex-basis'] = 'auto';
    }
  } else {
    styles['--column-grid-span'] = 'auto';
    styles['--column-flex-basis'] = 'auto';
  }

  styles['--column-padding'] = paddingValue !== undefined && paddingValue !== null
    ? (typeof paddingValue === 'number' ? `${paddingValue}px` : paddingValue)
    : '0px';

  styles['--column-width'] = widthValue !== undefined && widthValue !== null
    ? (typeof widthValue === 'number' ? `${widthValue}px` : widthValue)
    : 'auto';

  styles['--column-height'] = heightValue !== undefined && heightValue !== null
    ? (typeof heightValue === 'number' ? `${heightValue}px` : heightValue)
    : 'auto';

  styles['--column-gap'] = gapValue !== undefined && gapValue !== null
    ? (typeof gapValue === 'number' ? `${gapValue}px` : gapValue)
    : '0px';

  return styles;
});
</script>

<template>
  <div class="layout-column-wrapper">
    <div 
      :style="computedStyles" 
      :class="isEditable ? 'min-h-[50px]' : ''"
      class="layout-column-container w-full"
    >
      <slot />
    </div>
  </div>
</template>

<style scoped>
.layout-column-wrapper {
  --column-grid-span: auto !important;
  --column-flex-basis: auto !important;
  --column-padding: 0px !important;
  --column-width: auto !important;
  --column-height: auto !important;
  --column-gap: 0px !important;
}

.layout-column-container {
  grid-column: var(--column-grid-span, auto);
  flex-basis: var(--column-flex-basis, auto);
  padding: var(--column-padding, 0px);
  width: var(--column-width, auto);
  height: var(--column-height, auto);
  gap: var(--column-gap, 0px);
}
</style>
