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
  const span = props.props?.span;
  const paddingValue = props.props?.padding;
  const widthValue = props.props?.width;
  const heightValue = props.props?.height;
  const gapValue = props.props?.gap;
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
  <div :style="computedStyles" class="layout-column-container w-full min-h-[50px]">
    <slot />
  </div>
</template>

<style scoped>
.layout-column-container {
  grid-column: var(--column-grid-span, auto);
  flex-basis: var(--column-flex-basis, auto);
  padding: var(--column-padding, 0px);
  width: var(--column-width, auto);
  height: var(--column-height, auto);
  gap: var(--column-gap, 0px);
}
</style>
