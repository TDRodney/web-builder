<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import draggable from 'vuedraggable';

const props = defineProps({
  tenant: Object,
  page: Object
});

// 1. Local reactive source of truth for the workspace canvas
const blocks = ref(props.page.draft_config || []);
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
      await axios.post(`/editor/save`, {
        page_id: props.page.id,
        draft_config: blocks.value
      }, {
        signal: currentAbortController.signal
      });
      console.log('Canvas state synced securely.');
    } catch (error) {
      if (axios.isCancel(error)) {
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
        
        <draggable 
          v-model="blocks" 
          item-key="id" 
          handle=".drag-handle"
          ghost-class="opacity-40"
        >
          <template #item="{ element }">
            <div 
              @click="selectedBlock = element"
              :style="{ 
                '--block-padding': element.styles.padding + 'px',
                '--block-bg': element.styles.backgroundColor 
              }"
              class="border-2 border-transparent hover:border-indigo-500 rounded-lg p-[var(--block-padding)] bg-[var(--block-bg)] transition-all relative group my-2 cursor-pointer"
            >
              <div class="drag-handle absolute top-2 left-2 opacity-0 group-hover:opacity-100 bg-indigo-600 text-white p-1 rounded text-xs cursor-move">
                ::: Move
              </div>
              
              <div v-if="element.type === 'HeroBlock'" class="text-center">
                <h1 class="text-3xl font-extrabold">Dynamic Canvas Engine</h1>
                <p class="text-slate-500 mt-2">Adjust padding or slide order freely.</p>
              </div>
            </div>
          </template>
        </draggable>

      </div>
    </div>

    <div class="w-80 border-l border-slate-800 p-6 bg-slate-900">
      <h3 class="text-lg font-bold border-b border-slate-800 pb-3">Inspector</h3>
      
      <div v-if="selectedBlock" class="mt-4 space-y-4">
        <div>
          <label class="text-xs font-semibold text-slate-400 block mb-1">
            Padding: {{ selectedBlock.styles.padding }}px
          </label>
          <input 
            type="range" min="10" max="150" 
            v-model.number="selectedBlock.styles.padding"
            class="w-full accent-indigo-500"
          />
        </div>
        <div>
          <label class="text-xs font-semibold text-slate-400 block mb-1">Background Color</label>
          <input 
            type="color" 
            v-model="selectedBlock.styles.backgroundColor"
            class="w-full h-10 rounded bg-transparent border-0 cursor-pointer"
          />
        </div>
      </div>
      <p v-else class="text-sm text-slate-500 mt-4 italic">Click a canvas block to edit properties.</p>
    </div>

  </div>
</template>