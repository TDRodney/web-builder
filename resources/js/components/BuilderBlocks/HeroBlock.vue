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
      :style="{ fontFamily: 'var(--theme-font-heading)', color: 'var(--theme-text)' }"
    >
      {{ blockProps.headline || (isEditable ? 'Click to Edit Headline' : '') }}
    </h1>
    <p
      class="mt-4 text-xl opacity-85"
      :style="{ fontFamily: 'var(--theme-font-body)', color: 'var(--theme-text)' }"
    >
      {{ blockProps.subheadline || (isEditable ? 'Click to edit your subheadline description.' : '') }}
    </p>
  </div>
</template>