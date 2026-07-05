<script setup>
import { inject } from 'vue';

defineProps({
  nodeId: { type: String },
  blockProps: { type: Object, default: () => ({}) }
});

const isEditable = inject('isEditable', false);
</script>

<template>
  <div class="py-4 border-t border-slate-100">
    <h3 class="text-lg font-bold text-slate-800">
      {{ blockProps.title || (isEditable ? 'Feature Item' : '') }}
    </h3>
    <p class="text-sm text-slate-600 mt-1">
      {{ blockProps.body || (isEditable ? 'Feature description details go here.' : '') }}
    </p>
  </div>
</template>