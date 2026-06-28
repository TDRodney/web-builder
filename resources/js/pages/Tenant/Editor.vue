<script setup>
import { ref, watch, provide, nextTick } from 'vue';
import { useHttp, Link } from '@inertiajs/vue3';
import draggable from 'vuedraggable';

import HeroBlock from '@/components/BuilderBlocks/HeroBlock.vue';
import FeatureBlock from '@/components/BuilderBlocks/FeatureBlock.vue';
import RenderNode from '@/components/BuilderBlocks/RenderNode.vue';
import LayoutGrid from '@/components/BuilderBlocks/LayoutGrid.vue';
import LayoutColumn from '@/components/BuilderBlocks/LayoutColumn.vue';
import AtomicText from '@/components/BuilderBlocks/AtomicText.vue';

const blockRegistry = {
  HeroBlock,
  FeatureBlock,
  LayoutGrid,
  LayoutColumn,
  AtomicText
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

const selectedNode = ref(null);
const selectedBlock = selectedNode;
provide('selectedBlock', selectedBlock);
provide('canvasSelection', {
  selectedNode,
  selectNode: (node) => {
    if (node && !node.styles) {
      node.styles = { padding: 20, backgroundColor: '#ffffff' };
    }
    selectedNode.value = node;
  }
});

const isDragging = ref(false);
provide('isDragging', isDragging);

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

const addBlock = (type) => {
  const newBlock = {
    id: `${type.toLowerCase()}-${Date.now()}`,
    type: type,
    styles: { padding: 20, backgroundColor: '#ffffff' },
    content: {},
    propsData: {}
  };

  if (type === 'HeroBlock') {
    newBlock.content = { headline: 'New Hero Heading', subheadline: 'Add your description here' };
  } else if (type === 'FeatureBlock') {
    newBlock.content = { title: 'New Feature Item', body: 'Feature description details go here.' };
  } else if (type === 'AtomicText') {
    newBlock.propsData = { content: 'Atomic Text Element', fontSize: '16px', color: '#0f172a' };
  } else if (type === 'LayoutGrid') {
    newBlock.propsData = { columns: 3, gap: '1rem', padding: '1rem' };
    newBlock.children = [
      {
        id: `layoutcolumn-${Date.now()}-1`,
        type: 'LayoutColumn',
        styles: { padding: 20, backgroundColor: '#ffffff' },
        propsData: { span: 1 },
        children: []
      },
      {
        id: `layoutcolumn-${Date.now()}-2`,
        type: 'LayoutColumn',
        styles: { padding: 20, backgroundColor: '#ffffff' },
        propsData: { span: 1 },
        children: []
      },
      {
        id: `layoutcolumn-${Date.now()}-3`,
        type: 'LayoutColumn',
        styles: { padding: 20, backgroundColor: '#ffffff' },
        propsData: { span: 1 },
        children: []
      }
    ];
  } else if (type === 'LayoutColumn') {
    newBlock.propsData = { span: 1 };
    newBlock.children = [];
  }

  // If a block with children is selected, add it as a child. Otherwise add to root blocks.
  if (selectedBlock.value && selectedBlock.value.children) {
    if (!Array.isArray(selectedBlock.value.children)) {
      selectedBlock.value.children = [];
    }
    selectedBlock.value.children.push(newBlock);
  } else {
    blocks.value.push(newBlock);
  }
};

const deleteSelectedBlock = () => {
  if (!selectedBlock.value) return;

  const removeNode = (nodes, id) => {
    for (let i = 0; i < nodes.length; i++) {
      if (nodes[i].id === id) {
        nodes.splice(i, 1);
        return true;
      }
      if (nodes[i].children && removeNode(nodes[i].children, id)) {
        return true;
      }
    }
    return false;
  };

  removeNode(blocks.value, selectedBlock.value.id);
  selectedBlock.value = null;
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
  if (isDragging.value) {
    return;
  }
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
provide('forceSave', forceSave);

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
        
        <draggable 
          v-model="blocks" 
          item-key="id" 
          handle=".drag-handle" 
          ghost-class="opacity-40"
          :group="{ name: 'canvas-tree', pull: true, put: true }"
          @start="isDragging = true"
          @end="isDragging = false; forceSave();"
        >
          <template #item="{ element }">
            <RenderNode :node="element" />
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

          <div v-if="selectedBlock.type === 'AtomicText'">
            <label class="text-xs font-semibold text-slate-400 block mb-1">Text Content</label>
            <input type="text" v-model="selectedBlock.propsData.content" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500"/>
            
            <label class="text-xs font-semibold text-slate-400 block mt-3 mb-1">Font Size (e.g. 18px or 1.25rem)</label>
            <input type="text" v-model="selectedBlock.propsData.fontSize" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500"/>
            
            <label class="text-xs font-semibold text-slate-400 block mt-3 mb-1">Color (Hex or CSS Var like --color-name)</label>
            <input type="text" v-model="selectedBlock.propsData.color" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500"/>
          </div>

          <div v-if="selectedBlock.type === 'LayoutGrid'">
            <label class="text-xs font-semibold text-slate-400 block mb-1">Columns Count</label>
            <input type="number" min="1" max="12" v-model.number="selectedBlock.propsData.columns" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500"/>
            
            <label class="text-xs font-semibold text-slate-400 block mt-3 mb-1">Gap Size (e.g. 1rem or 16px)</label>
            <input type="text" v-model="selectedBlock.propsData.gap" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500"/>
            
            <label class="text-xs font-semibold text-slate-400 block mt-3 mb-1">Padding (e.g. 1rem or 20px)</label>
            <input type="text" v-model="selectedBlock.propsData.padding" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500"/>
          </div>

          <div v-if="selectedBlock.type === 'LayoutColumn'">
            <label class="text-xs font-semibold text-slate-400 block mb-1">Grid Column Span (1-12) or Flex Basis (e.g. 50%)</label>
            <input type="text" v-model="selectedBlock.propsData.span" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500"/>
          </div>

          <button 
            @click="deleteSelectedBlock" 
            class="w-full bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold py-2 px-3 rounded-lg transition-all cursor-pointer border-0 mt-4"
          >
            Delete Block
          </button>
        </div>
        <p v-else class="text-sm text-slate-500 mt-4 italic">Click a block on the canvas to inspect it.</p>

        <!-- Block Library -->
        <div class="border-t border-slate-800 pt-4 mt-4">
          <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Add Block</h4>
          <div class="grid grid-cols-2 gap-2">
            <button @click="addBlock('HeroBlock')" class="bg-indigo-600/10 hover:bg-indigo-600/20 border border-indigo-500/20 text-indigo-300 text-xs py-2 px-2.5 rounded font-medium transition-colors cursor-pointer">
              + Hero
            </button>
            <button @click="addBlock('FeatureBlock')" class="bg-indigo-600/10 hover:bg-indigo-600/20 border border-indigo-500/20 text-indigo-300 text-xs py-2 px-2.5 rounded font-medium transition-colors cursor-pointer">
              + Feature
            </button>
            <button @click="addBlock('LayoutGrid')" class="bg-indigo-600/10 hover:bg-indigo-600/20 border border-indigo-500/20 text-indigo-300 text-xs py-2 px-2.5 rounded font-medium transition-colors cursor-pointer">
              + Grid Layout
            </button>
            <button @click="addBlock('LayoutColumn')" class="bg-indigo-600/10 hover:bg-indigo-600/20 border border-indigo-500/20 text-indigo-300 text-xs py-2 px-2.5 rounded font-medium transition-colors cursor-pointer">
              + Column
            </button>
            <button @click="addBlock('AtomicText')" class="col-span-2 bg-indigo-600/10 hover:bg-indigo-600/20 border border-indigo-500/20 text-indigo-300 text-xs py-2 px-2.5 rounded font-medium transition-colors cursor-pointer">
              + Atomic Text
            </button>
          </div>
          <p class="text-[10px] text-slate-500 mt-2 italic">Tip: If a Layout container is selected, the new block is nested inside it.</p>
        </div>
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