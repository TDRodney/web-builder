<script setup>
import { useHttp, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, provide, nextTick } from 'vue';
import draggable from 'vuedraggable';

import RenderNode from '@/components/BuilderBlocks/RenderNode.vue';
import MediaPicker from '@/components/MediaPicker.vue';
import { getBlockDefinition, blockComponents } from '@/lib/blockRegistry';
import { useTheme } from '@/lib/theme';
import { Toaster } from '@/components/ui/sonner';
import { toast } from 'vue-sonner';

provide('blockRegistry', blockComponents);

const props = defineProps({
  tenant: Object,
  page: Object,
  pages: Array,
  urls: Object
});

const { cssVars: themeVars, fontUrl } = useTheme(() => props.tenant?.theme_config);

const page = usePage();
const blockDefinitions = computed(() => {
  const definitions = page.props.blocksConfig?.definitions || {};
  return Array.isArray(definitions) ? definitions : Object.values(definitions);
});

// Seed some initial demo data if the draft is empty so you have blocks to see instantly
const blocks = ref(props.page.draft_config || [
  { 
    id: 'hero-' + Date.now(), 
    type: 'HeroBlock', 
    props: { padding: 40, backgroundColor: '#ffffff', headline: 'Welcome to your Site', subheadline: 'Built with our engine.' },
    children: []
  },
  { 
    id: 'feat-' + Date.now(), 
    type: 'FeatureBlock', 
    props: { padding: 20, backgroundColor: '#f8fafc', title: 'Blazing Fast Performance', body: '60fps reactive customization rendering.' },
    children: []
  }
]);

const selectedNode = ref(null);
const selectedBlock = selectedNode;
provide('selectedBlock', selectedBlock);
provide('canvasSelection', {
  selectedNode,
  selectNode: (node) => {
    if (node && !node.props) {
      node.props = { padding: 20, backgroundColor: '#ffffff' };
    }

    selectedNode.value = node;
  }
});

const isDragging = ref(false);
provide('isDragging', isDragging);
provide('isEditable', true);

const viewMode = ref('desktop');

const canvasMaxWidth = computed(() => {
  switch (viewMode.value) {
    case 'mobile': return '375px';
    case 'tablet': return '768px';
    default: return 'none';
  }
});

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
  const definition = getBlockDefinition(type);

  if (!definition) {
    console.error(`Block type "${type}" is not registered in the block registry.`);

    return;
  }

  const propsClone = JSON.parse(JSON.stringify(definition.defaultProps));

  const newBlock = {
    id: `${type.toLowerCase()}-${Date.now()}`,
    type: type,
    props: propsClone,
    children: []
  };

  if (type === 'LayoutGrid') { // TODO - Create Sections options and remove this hard coding
    newBlock.children = [
      {
        id: `layoutcolumn-${Date.now()}-1`,
        type: 'LayoutColumn',
        props: { padding: 20, backgroundColor: 'transparent', span: 'auto', width: 'auto', height: 'auto', gap: '0px' },
        children: []
      },
      {
        id: `layoutcolumn-${Date.now()}-2`,
        type: 'LayoutColumn',
        props: { padding: 20, backgroundColor: 'transparent', span: 'auto', width: 'auto', height: 'auto', gap: '0px' },
        children: []
      },
      {
        id: `layoutcolumn-${Date.now()}-3`,
        type: 'LayoutColumn',
        props: { padding: 20, backgroundColor: 'transparent', span: 'auto', width: 'auto', height: 'auto', gap: '0px' },
        children: []
      }
    ];
  }

  if (selectedBlock.value && selectedBlock.value.children) {
    if (!Array.isArray(selectedBlock.value.children)) {
      selectedBlock.value.children = [];
    }

    const parentType = selectedBlock.value.type;
    const parentDef = getBlockDefinition(parentType);
    let isAllowed = true;
    const nestingRules = page.props.blocksConfig?.nesting;
    if (nestingRules && typeof nestingRules === 'object') {
      const allowed = nestingRules[parentType];
      isAllowed = Array.isArray(allowed) ? allowed.includes(type) : true;
    } else {
      isAllowed = !parentDef || !parentDef.allowedChildren || parentDef.allowedChildren.includes(type);
    }

    if (isAllowed) {
      selectedBlock.value.children.push(newBlock);
      toast.success(`Added ${definition.label} inside ${parentDef.label}`);
    } else {
      toast.error(`Nesting Error: ${definition.label} is not allowed inside ${parentDef.label}`);
    }
  } else {
    blocks.value.push(newBlock);
    toast.success(`Added ${definition.label}`);
  }
};

const activeBlockDefinition = computed(() => {
  if (!selectedBlock.value) {
    return null;
  }

  return getBlockDefinition(selectedBlock.value.type);
});

const findParent = (nodes, targetId) => {
  for (const node of nodes) {
    if (node.children?.some(c => c.id === targetId)) {
      return node;
    }
    if (node.children) {
      const result = findParent(node.children, targetId);
      if (result) return result;
    }
  }
  return null;
};

const findIndexInParent = (nodes, targetId) => {
  for (let i = 0; i < nodes.length; i++) {
    if (nodes[i].id === targetId) return i;
    if (nodes[i].children) {
      const idx = findIndexInParent(nodes[i].children, targetId);
      if (idx !== -1) return idx;
    }
  }
  return -1;
};

const generateNewIds = (node) => {
  const copy = JSON.parse(JSON.stringify(node));
  copy.id = `${copy.type.toLowerCase()}-${Date.now()}-${Math.random().toString(36).slice(2, 6)}`;
  if (copy.children) {
    copy.children = copy.children.map(generateNewIds);
  }
  return copy;
};

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

const deleteBlockById = (nodeId) => {
  removeNode(blocks.value, nodeId);
  if (selectedBlock.value?.id === nodeId) {
    selectedBlock.value = null;
  }
};

const deleteSelectedBlock = () => {
  if (!selectedBlock.value) return;
  deleteBlockById(selectedBlock.value.id);
};

const duplicateBlock = (nodeId) => {
  const parent = findParent(blocks.value, nodeId);
  const list = parent ? parent.children : blocks.value;
  const idx = list.findIndex(n => n.id === nodeId);
  if (idx === -1) return;
  list.splice(idx + 1, 0, generateNewIds(list[idx]));
};

const moveBlock = (nodeId, direction) => {
  const parent = findParent(blocks.value, nodeId);
  const list = parent ? parent.children : blocks.value;
  const idx = list.findIndex(n => n.id === nodeId);
  if (idx === -1) return;
  if (direction === 'up' && idx > 0) {
    const [item] = list.splice(idx, 1);
    list.splice(idx - 1, 0, item);
  } else if (direction === 'down' && idx < list.length - 1) {
    const [item] = list.splice(idx, 1);
    list.splice(idx + 1, 0, item);
  }
};

const copiedBlock = ref(null);

const copyBlock = (nodeId) => {
  const walk = (nodes) => {
    for (const n of nodes) {
      if (n.id === nodeId) { copiedBlock.value = JSON.parse(JSON.stringify(n)); return true; }
      if (n.children && walk(n.children)) return true;
    }
    return false;
  };
  walk(blocks.value);
};

const pasteBlock = (targetId) => {
  if (!copiedBlock.value) return;
  const parent = targetId ? findParent(blocks.value, targetId) : null;
  const list = parent ? parent.children : blocks.value;
  const idx = targetId ? list.findIndex(n => n.id === targetId) : list.length - 1;
  list.splice(idx + 1, 0, generateNewIds(copiedBlock.value));
};

const wrapInContainer = (nodeId, containerType = 'LayoutColumn') => {
  const parent = findParent(blocks.value, nodeId);
  const list = parent ? parent.children : blocks.value;
  const idx = list.findIndex(n => n.id === nodeId);
  if (idx === -1) return;
  const node = list[idx];
  const container = {
    id: `${containerType.toLowerCase()}-${Date.now()}`,
    type: containerType,
    props: { padding: 20, backgroundColor: 'transparent', span: 'auto', width: 'auto', height: 'auto', gap: '0px' },
    children: [node]
  };
  list.splice(idx, 1, container);
};

// Initialize Inertia v3 stand-alone HTTP request hook
const http = useHttp({
  page_id: props.page.id,
  draft_config: blocks.value
});

// 2. Debounced, Race-Condition-Safe Auto-Save Engine
let currentSaveVisit = null;

const saveError = ref('');
let saveErrorTimer = null;

const extractHttpError = (error) => {
  if (!error) {
    return 'Unknown error';
  }

  if (error.name === 'HttpCancelledError' || error.message === 'canceled' || error.code === 'canceled') {
    return null;
  }

  if (error.name === 'HttpNetworkError' || error.message === 'Network Error') {
    return 'Cannot reach the server — check your connection';
  }

  const response = error.response;
  if (!response) {
    return error.message || 'Unknown error';
  }

  const status = response.status;
  let body = null;

  try {
    body = response.data ? JSON.parse(response.data) : null;
  } catch {
    // Response is HTML, not JSON — likely a 419 CSRF page or 500 error page
    if (status === 419) {
      return 'Session expired — please reload the page';
    }
    if (status === 401 || status === 403) {
      return 'Unauthorized — your session may have expired';
    }
    if (status >= 500) {
      return `Server error (${status}) — please try again`;
    }
    return `Request failed (${status})`;
  }

  if (body?.errors) {
    const messages = Object.values(body.errors).flat();
    return messages.join('; ');
  }

  if (body?.error) {
    return body.error;
  }

  if (body?.message) {
    return body.message;
  }

  return `Request failed (${status})`;
};

const saveCanvasState = async () => {
  if (currentSaveVisit) {
    currentSaveVisit.cancel();
  }

  try {
    currentSaveVisit = http.post(`/editor/save`, {
      onCancel: () => {
        console.log('Previous background save aborted cleanly.');
      }
    });

    await currentSaveVisit;
    if (saveError.value) {
      toast.success('Draft saved successfully', { id: 'save-error' });
    }
    saveError.value = '';
    if (saveErrorTimer) {
      clearTimeout(saveErrorTimer);
      saveErrorTimer = null;
    }
  } catch (error) {
    const message = extractHttpError(error);

    if (message === null) {
      return;
    }

    console.warn('[Save Error]', { status: error?.response?.status, body: error?.response?.data });

    saveError.value = message;
    toast.error(`Auto-save failed: ${message}`, { id: 'save-error' });
    if (saveErrorTimer) {
      clearTimeout(saveErrorTimer);
    }
    saveErrorTimer = setTimeout(() => { saveError.value = ''; }, 10000);
  } finally {
    currentSaveVisit = null;
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
provide('blockActions', {
  duplicateBlock,
  moveBlock,
  copyBlock,
  pasteBlock,
  wrapInContainer,
  copiedBlock,
  deleteBlockById
});

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

const publishError = ref('');

const publishPage = async () => {
  isPublishing.value = true;
  publishMessage.value = '';
  publishError.value = '';

  try {
    toast.loading('Saving pending draft changes...', { id: 'publish-toast' });
    // 1. Flush any pending draft changes immediately before publishing
    await forceSave();

    if (saveError.value) {
      publishError.value = 'Cannot publish while save is failing: ' + saveError.value;
      toast.error(publishError.value, { id: 'publish-toast' });
      return;
    }

    toast.loading('Publishing site...', { id: 'publish-toast' });

    // 2. Perform the publish promotion
    const res = await publishHttp.post(`/editor/publish`);

    if (res && res.status === 'success') {
      publishMessage.value = res.message || 'Site published successfully!';
      toast.success(publishMessage.value, { id: 'publish-toast' });
      setTimeout(() => {
        publishMessage.value = '';
      }, 3000);
    } else {
      publishError.value = 'Publish returned an unexpected response';
      toast.error(publishError.value, { id: 'publish-toast' });
    }
  } catch (err) {
    const message = extractHttpError(err);

    if (message !== null) {
      publishError.value = message;
      toast.error(publishError.value, { id: 'publish-toast' });
    }
  } finally {
    isPublishing.value = false;
  }
};

// Page Management Functions
const showCreateModal = ref(false);
const showRenameModal = ref(false);
const pageToRename = ref(null);

const createForm = useHttp({
  title: '',
  slug: ''
});

const renameForm = useHttp({
  title: '',
  slug: ''
});

const deleteHttp = useHttp({});
const setHomepageHttp = useHttp({});

const pageActionError = ref('');

const autoGenerateSlug = () => {
  createForm.slug = createForm.title
    .toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .trim();
};

const switchPage = async (pageSlug) => {
  if (pageSlug === props.page.slug) {
    return;
  }

  const loadToast = toast.loading(`Saving draft and switching to page...`);
  try {
    await forceSave();
    toast.success('Draft saved successfully', { id: loadToast });
    router.visit(`/editor?page=${pageSlug}`);
  } catch (err) {
    toast.error('Failed to save draft before switching', { id: loadToast });
  }
};

const submitCreatePage = async () => {
  pageActionError.value = '';
  const loadingToast = toast.loading('Creating page...');
  try {
    const res = await createForm.post('/editor/pages');

    if (res && res.status === 'success') {
      toast.success('Page created successfully', { id: loadingToast });
      showCreateModal.value = false;
      createForm.reset();
      router.visit(`/editor?page=${res.page.slug}`);
    }
  } catch (err) {
    const message = extractHttpError(err);
    if (message !== null) {
      pageActionError.value = message;
      toast.error(`Failed to create page: ${message}`, { id: loadingToast });
    } else {
      toast.dismiss(loadingToast);
    }
  }
};

const openRenameModal = (page) => {
  pageToRename.value = page;
  renameForm.title = page.title;
  renameForm.slug = page.slug;
  renameForm.clearErrors();
  showRenameModal.value = true;
};

const submitRenamePage = async () => {
  pageActionError.value = '';
  const loadingToast = toast.loading('Saving settings...');
  try {
    const res = await renameForm.patch(`/editor/pages/${pageToRename.value.id}`);

    if (res && res.status === 'success') {
      toast.success('Page settings updated', { id: loadingToast });
      showRenameModal.value = false;

      if (pageToRename.value.slug === props.page.slug) {
        router.visit(`/editor?page=${res.page.slug}`);
      } else {
        router.reload({ only: ['pages'] });
      }
    }
  } catch (err) {
    const message = extractHttpError(err);
    if (message !== null) {
      pageActionError.value = message;
      toast.error(`Failed to update page: ${message}`, { id: loadingToast });
    } else {
      toast.dismiss(loadingToast);
    }
  }
};

const handleDeletePage = async (page) => {
  if (page.is_homepage) {
    toast.error('Cannot delete the homepage', { id: 'page-delete' });
    return;
  }

  if (!confirm(`Are you sure you want to delete "${page.title}"? This will delete all of its draft and published configurations.`)) {
    return;
  }
  
  pageActionError.value = '';
  const loadingToast = toast.loading('Deleting page...');
  try {
    const res = await deleteHttp.delete(`/editor/pages/${page.id}`);

    if (res && res.status === 'success') {
      toast.success('Page deleted successfully', { id: loadingToast });
      if (page.slug === props.page.slug) {
        router.visit('/editor');
      } else {
        router.reload({ only: ['pages'] });
      }
    }
  } catch (err) {
    const message = extractHttpError(err);
    if (message !== null) {
      pageActionError.value = message;
      toast.error(`Failed to delete page: ${message}`, { id: loadingToast });
    } else {
      toast.dismiss(loadingToast);
    }
  }
};

const handleSetHomepage = async (page) => {
  pageActionError.value = '';
  const loadingToast = toast.loading('Setting homepage...');
  try {
    const res = await setHomepageHttp.patch(`/editor/pages/${page.id}`, {
      is_homepage: true
    });

    if (res && res.status === 'success') {
      toast.success(`"${page.title}" is now the homepage`, { id: loadingToast });
      router.reload({ only: ['pages', 'page'] });
    }
  } catch (err) {
    const message = extractHttpError(err);
    if (message !== null) {
      pageActionError.value = message;
      toast.error(`Failed to set homepage: ${message}`, { id: loadingToast });
    } else {
      toast.dismiss(loadingToast);
    }
  }
};
// Media picker state
const showMediaPicker = ref(false);
const mediaPickerFieldKey = ref('');

const openMediaPicker = (fieldKey) => {
  mediaPickerFieldKey.value = fieldKey;
  showMediaPicker.value = true;
};

const onMediaSelected = (item) => {
  if (selectedBlock.value && mediaPickerFieldKey.value) {
    selectedBlock.value.props[mediaPickerFieldKey.value] = item.url;
  }
  showMediaPicker.value = false;
};
</script>

<template>
  <Head>
    <link v-if="fontUrl" rel="stylesheet" :href="fontUrl" />
  </Head>
  <div class="flex h-screen bg-slate-900 text-slate-100 font-sans overflow-hidden">
    
    <div class="flex-1 overflow-y-auto p-8 bg-slate-950 h-screen">
      <div class="mx-auto text-slate-900 rounded-xl shadow-2xl min-h-[600px] p-6 canvas-runtime" :style="[themeVars, { maxWidth: canvasMaxWidth, backgroundColor: themeVars['--theme-bg'] }]">
        
        <draggable 
          v-model="blocks" 
          item-key="id" 
          handle=".drag-handle" 
          ghost-class="drag-ghost"
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
    <div class="w-80 border-l border-slate-800 bg-slate-900 flex flex-col h-screen shrink-0">
      <div class="flex-1 overflow-y-auto p-6 space-y-6 min-h-0">
        <!-- Pages Management Panel -->
        <div class="space-y-3">
          <div class="flex items-center justify-between border-b border-slate-800 pb-2">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Pages</h3>
            <button @click="showCreateModal = true" class="bg-indigo-600/10 hover:bg-indigo-600/20 border border-indigo-500/20 text-indigo-300 rounded p-1 transition-colors cursor-pointer border-0 flex items-center justify-center" title="Create Page">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
              </svg>
            </button>
          </div>

          <div class="max-h-48 overflow-y-auto space-y-1 pr-1">
            <div 
              v-for="p in props.pages" 
              :key="p.id" 
              class="group flex items-center justify-between rounded-lg p-2 transition-all animate-fade-in"
              :class="props.page.slug === p.slug ? 'bg-indigo-600/20 border border-indigo-500/20 text-white' : 'hover:bg-slate-800/60 text-slate-400 hover:text-slate-200'"
            >
              <button 
                @click="switchPage(p.slug)"
                class="flex-1 text-left text-xs font-medium truncate bg-transparent border-0 cursor-pointer p-0 text-inherit flex items-center gap-1.5 focus:outline-none"
              >
                <span class="truncate">{{ p.title }}</span>
                <span class="text-[9px] font-mono opacity-50 font-normal">({{ p.slug }})</span>
                <span v-if="p.is_homepage" class="text-[8px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 px-1 py-0.2 rounded-full scale-90 shrink-0">
                  Home
                </span>
              </button>

              <div class="opacity-0 group-hover:opacity-100 flex items-center gap-1 shrink-0 transition-opacity ml-2">
                <!-- Set as homepage action -->
                <button 
                  v-if="!p.is_homepage" 
                  @click="handleSetHomepage(p)"
                  class="text-slate-400 hover:text-emerald-400 bg-transparent border-0 p-0.5 cursor-pointer focus:outline-none" 
                  title="Set as Homepage"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                  </svg>
                </button>
                <!-- Rename action -->
                <button 
                  @click="openRenameModal(p)"
                  class="text-slate-400 hover:text-indigo-400 bg-transparent border-0 p-0.5 cursor-pointer focus:outline-none" 
                  title="Rename Page"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                  </svg>
                </button>
                <!-- Delete action -->
                <button 
                  v-if="!p.is_homepage" 
                  @click="handleDeletePage(p)"
                  class="text-slate-400 hover:text-rose-450 bg-transparent border-0 p-0.5 cursor-pointer focus:outline-none" 
                  title="Delete Page"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
          <h3 class="text-base font-bold text-white">Content Inspector</h3>
          <span class="text-indigo-400 text-[10px] font-semibold uppercase tracking-widest bg-indigo-500/10 px-2 py-0.5 rounded-full border border-indigo-500/20">Canvas</span>
        </div>

        <div class="flex items-center gap-2 bg-slate-800 rounded-lg p-0.5">
          <button v-for="mode in ['desktop', 'tablet', 'mobile']" :key="mode" @click="viewMode = mode" :class="viewMode === mode ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-400 hover:text-white'" class="flex-1 text-xs font-semibold py-1.5 px-3 rounded-md transition-all cursor-pointer border-0 capitalize">
            {{ mode === 'desktop' ? 'Desktop' : mode === 'tablet' ? 'Tablet' : 'Mobile' }}
          </button>
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
        
        <div v-if="selectedBlock" class="space-y-4 animate-fade-in">
          <div v-if="activeBlockDefinition" class="space-y-4">
            <div v-for="field in activeBlockDefinition.inspectorFields" :key="field.key" class="space-y-1">
              <label class="text-xs font-semibold text-slate-400 block">
                {{ field.label }}
                <span v-if="field.type === 'range' && selectedBlock.props[field.key] !== undefined" class="text-slate-300">
                  : {{ selectedBlock.props[field.key] }}px
                </span>
              </label>

              <!-- Range slider -->
              <input
                v-if="field.type === 'range'"
                type="range"
                :min="field.min ?? 10"
                :max="field.max ?? 150"
                v-model.number="selectedBlock.props[field.key]"
                class="w-full accent-indigo-500"
              />

              <!-- Color picker -->
              <div v-else-if="field.type === 'color'" class="flex items-center gap-2">
                <input
                  type="color"
                  v-model="selectedBlock.props[field.key]"
                  class="h-8 w-12 border border-slate-700 bg-transparent cursor-pointer rounded p-0"
                />
                <span class="text-xs font-mono text-slate-300">{{ selectedBlock.props[field.key] }}</span>
              </div>

              <!-- Number input -->
              <input
                v-else-if="field.type === 'number'"
                type="number"
                :min="field.min"
                :max="field.max"
                v-model.number="selectedBlock.props[field.key]"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500"
              />

              <!-- Select dropdown -->
              <select
                v-else-if="field.type === 'select'"
                v-model="selectedBlock.props[field.key]"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500 cursor-pointer"
              >
                <option v-for="opt in field.options" :key="opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>

              <!-- Media picker -->
              <div v-else-if="field.type === 'media'" class="space-y-2">
                <div v-if="selectedBlock.props[field.key]" class="relative rounded overflow-hidden">
                  <img
                    :src="selectedBlock.props[field.key]"
                    alt=""
                    class="w-full h-24 object-cover rounded"
                  />
                  <button
                    @click="selectedBlock.props[field.key] = ''"
                    class="absolute top-1 right-1 bg-slate-900/80 text-rose-400 border-0 rounded p-0.5 cursor-pointer"
                    title="Remove image"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
                <button
                  @click="openMediaPicker(field.key)"
                  class="w-full bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 text-xs font-semibold py-2 px-3 rounded-lg transition-all cursor-pointer"
                >
                  {{ selectedBlock.props[field.key] ? 'Change Image' : 'Choose Image' }}
                </button>
              </div>

              <!-- Text fallback -->
              <input
                v-else
                type="text"
                v-model="selectedBlock.props[field.key]"
                :placeholder="field.placeholder"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500"
              />
            </div>
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
            <button 
              v-for="def in blockDefinitions" 
              :key="def.type"
              @click="addBlock(def.type)" 
              :class="def.type === 'AtomicText' ? 'col-span-2' : ''"
              class="bg-indigo-600/10 hover:bg-indigo-600/20 border border-indigo-500/20 text-indigo-300 text-xs py-2 px-2.5 rounded font-medium transition-colors cursor-pointer"
            >
              + {{ def.label }}
            </button>
          </div>
          <p class="text-[10px] text-slate-500 mt-2 italic">Tip: If a Layout container is selected, the new block is nested inside it.</p>
        </div>
      </div>

      <!-- Action Panel at the Bottom of Sidebar -->
      <div class="border-t border-slate-800 p-6 space-y-4 shrink-0 bg-slate-900">

        <button 
          @click="publishPage" 
          :disabled="isPublishing || http.processing || !!saveError" 
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

    <!-- Create Page Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
      <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
          <h3 class="text-base font-bold text-white">Create New Page</h3>
          <button @click="showCreateModal = false" class="text-slate-400 hover:text-white bg-transparent border-0 cursor-pointer p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitCreatePage" class="space-y-4">
          <div>
            <label class="text-xs font-semibold text-slate-400 block mb-1">Page Title</label>
            <input 
              type="text" 
              v-model="createForm.title" 
              @input="autoGenerateSlug"
              required 
              class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500 text-slate-100"
              placeholder="e.g. About Us"
            />
            <p v-if="createForm.errors.title" class="text-rose-500 text-xs mt-1">{{ createForm.errors.title }}</p>
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-400 block mb-1">URL Slug</label>
            <input 
              type="text" 
              v-model="createForm.slug" 
              required 
              class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500 font-mono text-slate-100"
              placeholder="e.g. about-us"
            />
            <p v-if="createForm.errors.slug" class="text-rose-500 text-xs mt-1">{{ createForm.errors.slug }}</p>
          </div>

          <div class="flex items-center justify-end gap-3 pt-2">
            <button 
              type="button" 
              @click="showCreateModal = false" 
              class="bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold py-2 px-4 rounded-lg transition-all cursor-pointer border border-slate-700"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="createForm.processing"
              class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-xs font-semibold py-2 px-4 rounded-lg transition-all cursor-pointer border-0"
            >
              {{ createForm.processing ? 'Creating...' : 'Create Page' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Rename Page Modal -->
    <div v-if="showRenameModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
      <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
          <h3 class="text-base font-bold text-white">Rename Page Settings</h3>
          <button @click="showRenameModal = false" class="text-slate-400 hover:text-white bg-transparent border-0 cursor-pointer p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitRenamePage" class="space-y-4">
          <div>
            <label class="text-xs font-semibold text-slate-400 block mb-1">Page Title</label>
            <input 
              type="text" 
              v-model="renameForm.title" 
              required 
              class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500 text-slate-100"
            />
            <p v-if="renameForm.errors.title" class="text-rose-500 text-xs mt-1">{{ renameForm.errors.title }}</p>
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-400 block mb-1">URL Slug</label>
            <input 
              type="text" 
              v-model="renameForm.slug" 
              required 
              class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500 font-mono text-slate-100"
            />
            <p v-if="renameForm.errors.slug" class="text-rose-500 text-xs mt-1">{{ renameForm.errors.slug }}</p>
          </div>

          <div class="flex items-center justify-end gap-3 pt-2">
            <button 
              type="button" 
              @click="showRenameModal = false" 
              class="bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold py-2 px-4 rounded-lg transition-all cursor-pointer border border-slate-700"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="renameForm.processing"
              class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-xs font-semibold py-2 px-4 rounded-lg transition-all cursor-pointer border-0"
            >
              {{ renameForm.processing ? 'Saving...' : 'Save Settings' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Toast Container -->
    <Toaster position="top-right" closeButton richColors />

    <!-- Media Picker Modal -->
    <MediaPicker
      v-if="showMediaPicker"
      @select="onMediaSelected"
      @close="showMediaPicker = false"
    />

  </div>
</template>


<style scoped>
.canvas-runtime {
  position: relative;
  container-type: inline-size;
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

<style>
.drag-ghost {
  opacity: 0.4;
  transition: none !important;
}
</style>