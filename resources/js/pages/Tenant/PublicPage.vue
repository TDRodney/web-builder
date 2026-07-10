<script setup>
import { provide } from 'vue';
import RenderPublicNode from '@/components/BuilderBlocks/RenderPublicNode.vue';
import { blockComponents } from '@/lib/blockRegistry';

provide('blockRegistry', blockComponents);
provide('isEditable', false);

defineProps({
  tenant: Object,
  page: Object
});
</script>

<template>
  <div class="min-h-screen bg-white text-slate-900 font-sans">
    <RenderPublicNode 
      v-for="block in page.published_config" 
      :key="block.id" 
      :node="block" 
    />
  </div>
</template>
