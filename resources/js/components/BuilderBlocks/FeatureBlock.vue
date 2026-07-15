<script setup>
import { inject } from 'vue';

defineProps({
  nodeId: { type: String },
  blockProps: { type: Object, default: () => ({}) }
});

const isEditable = inject('isEditable', false);
</script>

<template>
  <div class="py-4 border-t" :style="{ borderTopColor: 'color-mix(in srgb, var(--theme-text) 15%, transparent)' }">
    <h3
      class="text-lg font-bold"
      :style="{ color: 'var(--theme-text)', fontFamily: 'var(--theme-font-heading)' }"
    >
      {{ blockProps.title || (isEditable ? 'Feature Item' : '') }}
    </h3>
    <p
      class="text-sm mt-1"
      :style="{ color: 'color-mix(in srgb, var(--theme-text) 60%, transparent)' }"
    >
      {{ blockProps.body || (isEditable ? 'Feature description details go here.' : '') }}
    </p>
  </div>
</template>
