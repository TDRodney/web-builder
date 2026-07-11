<script setup>
import { inject } from 'vue';

defineProps({
  nodeId: { type: String },
  blockProps: { type: Object, default: () => ({}) }
});

const isEditable = inject('isEditable', false);
</script>

<template>
  <div class="py-4 border-t border-slate-200/20">
    <h3
      class="text-lg font-bold"
      :style="{ fontFamily: 'var(--theme-font-heading)', color: 'var(--theme-text)' }"
    >
      {{ blockProps.title || (isEditable ? 'Feature Item' : '') }}
    </h3>
    <p
      class="text-sm mt-1 opacity-80"
      :style="{ fontFamily: 'var(--theme-font-body)', color: 'var(--theme-text)' }"
    >
      {{ blockProps.body || (isEditable ? 'Feature description details go here.' : '') }}
    </p>
  </div>
</template>