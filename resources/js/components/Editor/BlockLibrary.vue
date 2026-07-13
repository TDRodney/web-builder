<script setup>
import { ref } from 'vue';

const props = defineProps({
  blockDefinitions: {
    type: Array,
    required: true
  },
  blockPresets: {
    type: Array,
    required: true
  }
});

const emit = defineEmits(['add-block', 'add-preset']);

const activeLibraryTab = ref('blocks');

const addBlock = (type) => {
  emit('add-block', type);
};

const addPreset = (preset) => {
  emit('add-preset', preset);
};
</script>

<template>
  <!-- Block Library -->
  <div class="border-t border-slate-800 pt-4 mt-4">
    <!-- Library Tabs -->
    <div class="flex border-b border-slate-800 mb-3 gap-3">
      <button
        type="button"
        :class="['text-xs font-bold pb-2 border-b-2 cursor-pointer transition-colors bg-transparent border-0', activeLibraryTab === 'blocks' ? 'text-indigo-400 border-indigo-500' : 'text-slate-500 border-transparent hover:text-slate-300']"
        @click="activeLibraryTab = 'blocks'"
      >
        Blocks
      </button>
      <button
        type="button"
        :class="['text-xs font-bold pb-2 border-b-2 cursor-pointer transition-colors bg-transparent border-0', activeLibraryTab === 'presets' ? 'text-indigo-400 border-indigo-500' : 'text-slate-500 border-transparent hover:text-slate-300']"
        @click="activeLibraryTab = 'presets'"
      >
        Presets
      </button>
    </div>

    <!-- Tab Content: Blocks -->
    <div v-if="activeLibraryTab === 'blocks'" class="grid grid-cols-2 gap-2">
      <button 
        v-for="def in blockDefinitions" 
        :key="def.type"
        @click="addBlock(def.type)" 
        :class="def.type === 'AtomicText' ? 'col-span-2' : ''"
        class="bg-indigo-600/10 hover:bg-indigo-600/20 border border-indigo-500/20 text-indigo-300 text-xs py-2 px-2.5 rounded font-medium transition-colors cursor-pointer"
      >
        + {{ def.label }}
      </button>
      <p class="text-[10px] text-slate-500 mt-2 col-span-2 italic">Tip: If a Layout container is selected, the new block is nested inside it.</p>
    </div>

    <!-- Tab Content: Presets -->
    <div v-else-if="activeLibraryTab === 'presets'" class="space-y-2">
      <button
        v-for="preset in blockPresets"
        :key="preset.key"
        @click="addPreset(preset)"
        class="w-full text-left bg-slate-800/40 hover:bg-slate-800/80 border border-slate-700/60 hover:border-slate-600 rounded p-2.5 transition-all cursor-pointer group"
      >
        <div class="font-bold text-xs text-indigo-300 group-hover:text-indigo-200">{{ preset.label }}</div>
        <div class="text-[10px] text-slate-500 group-hover:text-slate-400 mt-0.5 leading-relaxed">{{ preset.description }}</div>
      </button>
    </div>
  </div>
</template>
