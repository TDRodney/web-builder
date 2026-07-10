<script setup>
import { inject } from 'vue';

defineProps({
  nodeId: { type: String },
  blockProps: { type: Object, default: () => ({}) }
});

const isEditable = inject('isEditable', false);
</script>

<template>
  <div class="text-center">
    <h1 class="text-4xl font-extrabold tracking-tight @md:text-6xl text-slate-900">
      {{ blockProps.headline || (isEditable ? 'Click to Edit Headline' : '') }}
    </h1>
    <p class="mt-4 text-xl text-slate-500">
      {{ blockProps.subheadline || (isEditable ? 'Click to edit your subheadline description.' : '') }}
    </p>
  </div>
</template>