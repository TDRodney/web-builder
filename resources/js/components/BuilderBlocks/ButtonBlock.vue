<script setup>
const props = defineProps({
  nodeId: { type: String },
  blockProps: { type: Object, default: () => ({}) }
});
</script>

<template>
  <div class="flex justify-center items-center py-4">
    <a
      v-if="blockProps.url"
      :href="blockProps.url"
      target="_blank"
      rel="noopener noreferrer"
      :class="[
        'inline-block font-semibold transition-all text-center theme-btn',
        blockProps.size === 'sm' ? 'px-3 py-1.5 text-sm' : '',
        blockProps.size === 'md' || !blockProps.size ? 'px-5 py-2.5 text-base' : '',
        blockProps.size === 'lg' ? 'px-7 py-3.5 text-lg' : '',
        blockProps.variant === 'outline' ? 'border-2' : 'shadow-sm'
      ]"
      :style="{
        borderRadius: 'var(--theme-border-radius)',
        fontFamily: 'var(--theme-font-body)',
        backgroundColor: (blockProps.variant === 'primary' || !blockProps.variant) ? 'var(--theme-primary)' :
                        (blockProps.variant === 'secondary' ? 'var(--theme-secondary)' : 'transparent'),
        color: (blockProps.variant === 'primary' || !blockProps.variant) ? 'var(--theme-bg)' :
               (blockProps.variant === 'secondary' ? 'var(--theme-bg)' : 'var(--theme-primary)'),
        borderColor: blockProps.variant === 'outline' ? 'var(--theme-primary)' : 'transparent'
      }"
    >
      {{ blockProps.label || 'Button' }}
    </a>
    <button
      v-else
      :class="[
        'font-semibold transition-all theme-btn',
        blockProps.size === 'sm' ? 'px-3 py-1.5 text-sm' : '',
        blockProps.size === 'md' || !blockProps.size ? 'px-5 py-2.5 text-base' : '',
        blockProps.size === 'lg' ? 'px-7 py-3.5 text-lg' : '',
        blockProps.variant === 'outline' ? 'border-2' : 'shadow-sm'
      ]"
      :style="{
        borderRadius: 'var(--theme-border-radius)',
        fontFamily: 'var(--theme-font-body)',
        backgroundColor: (blockProps.variant === 'primary' || !blockProps.variant) ? 'var(--theme-primary)' :
                        (blockProps.variant === 'secondary' ? 'var(--theme-secondary)' : 'transparent'),
        color: (blockProps.variant === 'primary' || !blockProps.variant) ? 'var(--theme-bg)' :
               (blockProps.variant === 'secondary' ? 'var(--theme-bg)' : 'var(--theme-primary)'),
        borderColor: blockProps.variant === 'outline' ? 'var(--theme-primary)' : 'transparent'
      }"
    >
      {{ blockProps.label || 'Button' }}
    </button>
  </div>
</template>

<style scoped>
.theme-btn:hover {
  filter: brightness(0.9);
}
</style>