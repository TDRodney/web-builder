<script setup>
defineProps({
  styles: Object,
  content: Object
});
</script>

<template>
  <div class="py-4 border-t border-slate-100">
    <h3 class="text-lg font-bold text-slate-800">{{ content?.title || 'Feature Item' }}</h3>
    <p class="text-sm text-slate-600 mt-1">{{ content?.body || 'Feature description details go here.' }}</p>
  </div>
</template>