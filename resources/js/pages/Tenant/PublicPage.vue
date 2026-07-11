<script setup>
import { provide } from 'vue';
import RenderPublicNode from '@/components/BuilderBlocks/RenderPublicNode.vue';
import { blockComponents } from '@/lib/blockRegistry';
import { useTheme } from '@/lib/theme';

provide('blockRegistry', blockComponents);
provide('isEditable', false);

const props = defineProps({
  tenant: Object,
  page: Object
});

const { cssVars: themeVars } = useTheme(() => props.tenant?.theme_config);
</script>

<template>
  <div class="min-h-screen font-sans" :style="[themeVars, { backgroundColor: themeVars['--theme-bg'], color: themeVars['--theme-text'] }]">
    <RenderPublicNode
      v-for="block in page.published_config"
      :key="block.id"
      :node="block"
    />
  </div>
</template>
