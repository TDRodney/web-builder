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
    <h1
      class="text-4xl font-extrabold tracking-tight @md:text-6xl"
      :style="{ color: 'var(--theme-text)', fontFamily: 'var(--theme-font-heading)' }"
    >
      {{ blockProps.headline || (isEditable ? 'Click to Edit Headline' : '') }}
    </h1>
    <p
      class="mt-4 text-xl"
      :style="{ color: 'color-mix(in srgb, var(--theme-text) 60%, transparent)' }"
    >
      {{ blockProps.subheadline || (isEditable ? 'Click to edit your subheadline description.' : '') }}
    </p>
  </div>
</template>
