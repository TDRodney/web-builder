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
        'inline-block font-semibold rounded-lg transition-all text-center',
        blockProps.size === 'sm' ? 'px-3 py-1.5 text-sm' : '',
        blockProps.size === 'md' || !blockProps.size ? 'px-5 py-2.5 text-base' : '',
        blockProps.size === 'lg' ? 'px-7 py-3.5 text-lg' : '',
        blockProps.variant === 'primary' || !blockProps.variant ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm' : '',
        blockProps.variant === 'secondary' ? 'bg-slate-200 text-slate-800 hover:bg-slate-300' : '',
        blockProps.variant === 'outline' ? 'border-2 border-indigo-600 text-indigo-600 hover:bg-indigo-50' : ''
      ]"
    >
      {{ blockProps.label || 'Button' }}
    </a>
    <button
      v-else
      :class="[
        'font-semibold rounded-lg transition-all',
        blockProps.size === 'sm' ? 'px-3 py-1.5 text-sm' : '',
        blockProps.size === 'md' || !blockProps.size ? 'px-5 py-2.5 text-base' : '',
        blockProps.size === 'lg' ? 'px-7 py-3.5 text-lg' : '',
        blockProps.variant === 'primary' || !blockProps.variant ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm' : '',
        blockProps.variant === 'secondary' ? 'bg-slate-200 text-slate-800 hover:bg-slate-300' : '',
        blockProps.variant === 'outline' ? 'border-2 border-indigo-600 text-indigo-600 hover:bg-indigo-50' : ''
      ]"
    >
      {{ blockProps.label || 'Button' }}
    </button>
  </div>
</template>