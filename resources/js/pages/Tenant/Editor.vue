<script setup>
import { ref, watch, provide, nextTick } from 'vue';
import { useHttp, Link } from '@inertiajs/vue3';
import draggable from 'vuedraggable';

import HeroBlock from '@/components/BuilderBlocks/HeroBlock.vue';
import FeatureBlock from '@/components/BuilderBlocks/FeatureBlock.vue';

const blockRegistry = {
  HeroBlock,
  FeatureBlock
};

provide('blockRegistry', blockRegistry);

const props = defineProps({
  tenant: Object,
  page: Object,
  urls: Object
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
provide('selectedBlock', selectedBlock);

// History management state
const undoStack = ref([]);
const redoStack = ref([]);
let isTraveling = false;

const snapshotState = () => {
  return JSON.parse(JSON.stringify(blocks.value));
};

let lastSavedState = snapshotState();

const undo = () => {
  if (undoStack.value.length === 0) {
    return;
  }
  isTraveling = true;
  redoStack.value.push(snapshotState());
  const prevState = undoStack.value.pop();
  blocks.value = prevState;
  lastSavedState = JSON.parse(JSON.stringify(prevState));
  http.draft_config = prevState;
  queueSave();
  nextTick(() => {
    isTraveling = false;
  });
};

const redo = () => {
  if (redoStack.value.length === 0) {
    return;
  }
  isTraveling = true;
  undoStack.value.push(snapshotState());
  const nextState = redoStack.value.pop();
  blocks.value = nextState;
  lastSavedState = JSON.parse(JSON.stringify(nextState));
  http.draft_config = nextState;
  queueSave();
  nextTick(() => {
    isTraveling = false;
  });
};

// Initialize Inertia v3 stand-alone HTTP request hook
const http = useHttp({
  page_id: props.page.id,
  draft_config: blocks.value
});

// 2. Debounced, Race-Condition-Safe Auto-Save Engine
const saveCanvasState = async () => {
  try {
    await http.post(`/editor/save`, {
      onHttpException(response) {
        console.warn('Background auto-save failed silently:', response.status);
        return false; // Prevent Inertia from redirecting/crashing the page
      },
      onNetworkError(error) {
        console.warn('Network error during auto-save:', error.message);
        return false;
      }
    });
    console.log('Canvas state synced securely.');
  } catch (error) {
    console.error('Save failed:', error);
  }
};

let saveTimeout = null;
const queueSave = () => {
  clearTimeout(saveTimeout);
  saveTimeout = setTimeout(saveCanvasState, 400); // Wait for 400ms of user inactivity before sending
};

const forceSave = async () => {
  if (saveTimeout) {
    clearTimeout(saveTimeout);
    saveTimeout = null;
  }
  await saveCanvasState();
};

// Deep watch the layout array. Any drag or slider change triggers a safe save.
watch(blocks, (newBlocks) => {
  if (isTraveling) {
    return;
  }
  undoStack.value.push(lastSavedState);
  redoStack.value = [];
  lastSavedState = snapshotState();

  http.draft_config = newBlocks;
  queueSave();
}, { deep: true });

// Atomic Publish Handlers
const isPublishing = ref(false);
const publishMessage = ref('');

const publishHttp = useHttp({
  page_id: props.page.id
});

const publishPage = async () => {
  isPublishing.value = true;
  publishMessage.value = '';
  try {
    // 1. Flush any pending draft changes immediately before publishing
    await forceSave();

    // 2. Perform the publish promotion
    const res = await publishHttp.post(`/editor/publish`);
    if (res && res.status === 'success') {
      publishMessage.value = res.message || 'Site published successfully!';
      setTimeout(() => {
        publishMessage.value = '';
      }, 3000);
    }
  } catch (err) {
    console.error('Publish failed:', err);
  } finally {
    isPublishing.value = false;
  }
};
</script>

<template>
  <div class="flex h-screen bg-slate-900 text-slate-100 font-sans overflow-hidden">
    
    <div class="flex-1 overflow-y-auto p-8 bg-slate-950 h-screen">
      <div class="max-w-4xl mx-auto bg-white text-slate-900 rounded-xl shadow-2xl min-h-[600px] p-6 canvas-runtime">
        
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

    <!-- Inspector Sidebar -->
    <div class="w-80 border-l border-slate-800 p-6 bg-slate-900 flex flex-col justify-between h-screen shrink-0">
      <div class="space-y-6">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
          <h3 class="text-base font-bold text-white">Content Inspector</h3>
          <span class="text-indigo-400 text-[10px] font-semibold uppercase tracking-widest bg-indigo-500/10 px-2 py-0.5 rounded-full border border-indigo-500/20">Canvas</span>
        </div>

        <div class="flex items-center gap-2">
          <button 
            @click="undo" 
            :disabled="undoStack.length === 0"
            class="flex-1 bg-slate-800 hover:bg-slate-700 disabled:opacity-40 disabled:hover:bg-slate-800 text-white text-xs font-semibold py-1.5 px-3 rounded-lg transition-all cursor-pointer border border-slate-700"
          >
            Undo
          </button>
          <button 
            @click="redo" 
            :disabled="redoStack.length === 0"
            class="flex-1 bg-slate-800 hover:bg-slate-700 disabled:opacity-40 disabled:hover:bg-slate-800 text-white text-xs font-semibold py-1.5 px-3 rounded-lg transition-all cursor-pointer border border-slate-700"
          >
            Redo
          </button>
        </div>
        
        <div v-if="selectedBlock" class="space-y-4">
          <div>
            <label class="text-xs font-semibold text-slate-400 block mb-1">Padding: {{ selectedBlock.styles.padding }}px</label>
            <input type="range" min="10" max="150" v-model.number="selectedBlock.styles.padding" class="w-full accent-indigo-500"/>
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-400 block mb-1">Background Color</label>
            <div class="flex items-center gap-2">
              <input type="color" v-model="selectedBlock.styles.backgroundColor" class="h-8 w-12 border border-slate-700 bg-transparent cursor-pointer rounded p-0"/>
              <span class="text-xs font-mono text-slate-300">{{ selectedBlock.styles.backgroundColor }}</span>
            </div>
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

      <!-- Action Panel at the Bottom of Sidebar -->
      <div class="border-t border-slate-800 pt-6 mt-6 space-y-4">
        <!-- Publish Status Alert -->
        <div v-if="publishMessage" class="text-emerald-400 text-center text-xs font-medium bg-emerald-500/10 border border-emerald-500/20 px-3 py-2.5 rounded-lg">
          {{ publishMessage }}
        </div>

        <button 
          @click="publishPage" 
          :disabled="isPublishing || http.processing" 
          class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-semibold py-2.5 px-4 rounded-lg transition-all shadow-md cursor-pointer flex items-center justify-center gap-1.5 border-0"
        >
          {{ isPublishing ? 'Publishing...' : 'Publish Draft' }}
        </button>

        <div class="flex items-center justify-between pt-2 border-t border-slate-800/40">
          <Link 
            :href="urls.dashboard" 
            class="text-xs text-slate-400 hover:text-white transition-colors no-underline py-1.5 px-2 hover:bg-slate-800 rounded-md font-medium"
          >
            Exit to Dashboard
          </Link>

          <Link
            :href="urls.logout"
            method="post"
            as="button"
            class="text-xs text-rose-400 hover:text-rose-300 transition-colors py-1.5 px-2 hover:bg-rose-500/10 rounded-md cursor-pointer font-medium bg-transparent border-0"
          >
            Log Out
          </Link>
        </div>
      </div>

    </div>

  </div>
</template>

<style scoped>
.canvas-runtime {
  position: relative;
  /* Contain styles and prevent overflow bleeding */
  contain: layout style;
  box-sizing: border-box;
}

/* Ensure background color and padding custom properties apply to children */
.canvas-runtime :deep(.bg-\[var\(--block-bg\)\]) {
  background-color: var(--block-bg) !important;
}

.canvas-runtime :deep(.p-\[var\(--block-padding\)\]) {
  padding: var(--block-padding) !important;
}
</style>