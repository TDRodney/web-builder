<script setup>
defineProps({
  nodeId: { type: String },
  props: { type: Object, default: () => ({}) }
});
</script>

<template>
  <div class="text-center">
    <h1 class="text-4xl font-extrabold tracking-tight @md:text-6xl text-slate-900">
      {{ props.props?.headline || 'Click to Edit Headline' }}
    </h1>
    <p class="mt-4 text-xl text-slate-500">
      {{ props.props?.subheadline || 'Click to edit your subheadline description.' }}
    </p>
  </div>
</template>