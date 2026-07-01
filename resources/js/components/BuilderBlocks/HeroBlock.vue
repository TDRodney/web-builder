<script setup>
defineProps({
  styles: Object,
  content: Object
});
</script>
/**
TODO:
- make this editable in the builder
- add STYLING CONTROL INTERFACES
 */

<template>
  <div class="text-center">
    <h1 class="text-4xl font-extrabold tracking-tight @md:text-6xl text-slate-900">
      {{ content?.headline || 'Click to Edit Headline' }}
    </h1>
    <p class="mt-4 text-xl text-slate-500">
      {{ content?.subheadline || 'Click to edit your subheadline description.' }}
    </p>
  </div>
</template>