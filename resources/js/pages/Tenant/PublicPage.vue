<script setup>
import { provide } from 'vue';
import AtomicText from '@/components/BuilderBlocks/AtomicText.vue';
import FeatureBlock from '@/components/BuilderBlocks/FeatureBlock.vue';
import HeroBlock from '@/components/BuilderBlocks/HeroBlock.vue';
import LayoutColumn from '@/components/BuilderBlocks/LayoutColumn.vue';
import LayoutGrid from '@/components/BuilderBlocks/LayoutGrid.vue';
import RenderPublicNode from '@/components/BuilderBlocks/RenderPublicNode.vue';

const blockRegistry = {
  HeroBlock,
  FeatureBlock,
  LayoutGrid,
  LayoutColumn,
  AtomicText
};

provide('blockRegistry', blockRegistry);
provide('isEditable', false);

defineProps({
  tenant: Object,
  page: Object
});
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-900 font-sans">
    <!-- Public Header -->
    <header class="bg-white border-b border-slate-200 py-4 px-6 shadow-sm sticky top-0 z-50">
      <div class="max-w-6xl mx-auto flex items-center justify-between">
        <span class="text-lg font-bold text-slate-800 tracking-tight">{{ tenant.subdomain }}.domain</span>
        <nav class="flex gap-4">
          <!-- Static/Dynamic navigation placeholder -->
        </nav>
      </div>
    </header>

    <!-- Main Content Area -->
    <main class="max-w-6xl mx-auto py-8 px-6">
      <div class="space-y-4">
        <RenderPublicNode 
          v-for="block in page.published_config" 
          :key="block.id" 
          :node="block" 
        />
      </div>
    </main>
  </div>
</template>
