<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import draggable from 'vuedraggable';


import HeroBlock from '@/components/BuilderBlocks/HeroBlock.vue';
import FeatureBlock from '@/components/BuilderBlocks/FeatureBlock.vue';

const blockRegistry = {
  HeroBlock,
  FeatureBlock
};

const props = defineProps({
  tenant: Object,
  page: Object
});

// Seed some initial demo data if the draft is empty so you have blocks to see instantly
const blocks = ref(props.page.draft_config || [
  { 
    id: 'hero-' + Date.now(), 
    type: 'HeroBlock', 
    styles: { padding: 40, backgroundColor: '#ffffff' },
    content: { headline: 'Welcome to your Site', subheadline: 'Built with our engine.' }
  },
  { 
    id: 'feat-' + Date.now(), 
    type: 'FeatureBlock', 
    styles: { padding: 20, backgroundColor: '#f8fafc' },
    content: { title: 'Blazing Fast Performance', body: '60fps reactive customization rendering.' }
  }
]);

const selectedBlock = ref(null);


// Track the active network request controller
let currentAbortController = null;

// 2. Debounced, Race-Condition-Safe Auto-Save Engine
let saveTimeout = null;
const queueSave = () => {
  clearTimeout(saveTimeout);
  
  saveTimeout = setTimeout(async () => {
    // If a previous save is still flying over the wire, abort it instantly!
    if (currentAbortController) {
      currentAbortController.abort();
    }
    
    // Create a fresh controller for this new request
    currentAbortController = new AbortController();

    try {
      const response = await fetch(`/editor/save`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
          page_id: props.page.id,
          draft_config: blocks.value
        }),
        signal: currentAbortController.signal
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      console.log('Canvas state synced securely.');
    } catch (error) {
      if (error.name === 'AbortError') {
        console.log('Stale request aborted safely.');
      } else {
        console.error('Save failed:', error);
      }
    }
  }, 400); // Wait for 400ms of user inactivity before sending
};

// Deep watch the layout array. Any drag or slider change triggers a safe save.
watch(blocks, () => {
  queueSave();
}, { deep: true });
</script>

<template>
  <div class="flex h-screen bg-slate-900 text-slate-100 font-sans">
    
    <div class="flex-1 overflow-y-auto p-8 bg-slate-950">
      <div class="max-w-4xl mx-auto bg-white text-slate-900 rounded-xl shadow-2xl min-h-[500px] p-6">
        
        <draggable v-model="blocks" item-key="id" handle=".drag-handle" ghost-class="opacity-40">
          <template #item="{ element }">
            <div 
              @click="selectedBlock = element"
              :style="{ 
                '--block-padding': element.styles.padding + 'px',
                '--block-bg': element.styles.backgroundColor 
              }"
              class="border-2 border-transparent hover:border-indigo-500 rounded-lg p-[var(--block-padding)] bg-[var(--block-bg)] transition-all relative group my-2 cursor-pointer"
            >
              <div class="drag-handle absolute top-2 left-2 opacity-0 group-hover:opacity-100 bg-indigo-600 text-white px-2 py-0.5 rounded text-xs cursor-move z-10">
                ::: Move
              </div>
              
              <component 
                :is="blockRegistry[element.type]" 
                :styles="element.styles"
                :content="element.content"
              />

            </div>
          </template>
        </draggable>

      </div>
    </div>

    <div class="w-80 border-l border-slate-800 p-6 bg-slate-900">
      <h3 class="text-lg font-bold border-b border-slate-800 pb-3">Content Inspector</h3>
      
      <div v-if="selectedBlock" class="mt-4 space-y-4">
        <div>
          <label class="text-xs font-semibold text-slate-400 block mb-1">Padding: {{ selectedBlock.styles.padding }}px</label>
          <input type="range" min="10" max="150" v-model.number="selectedBlock.styles.padding" class="w-full accent-indigo-500"/>
        </div>
        
        <div v-if="selectedBlock.type === 'HeroBlock'">
          <label class="text-xs font-semibold text-slate-400 block mb-1">Headline Text</label>
          <input type="text" v-model="selectedBlock.content.headline" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500"/>
        </div>

        <div v-if="selectedBlock.type === 'FeatureBlock'">
          <label class="text-xs font-semibold text-slate-400 block mb-1">Feature Title</label>
          <input type="text" v-model="selectedBlock.content.title" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500"/>
        </div>
      </div>
      <p v-else class="text-sm text-slate-500 mt-4 italic">Click a block on the canvas to inspect it.</p>
    </div>

  </div>
</template>