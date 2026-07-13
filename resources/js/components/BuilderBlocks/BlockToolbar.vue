<script setup>
import { inject, ref } from 'vue';
import {
  Trash2,
  Copy,
  ClipboardPaste,
  ChevronUp,
  ChevronDown,
  SquareUserRound,
  ArrowUpFromLine
} from '@lucide/vue';

const props = defineProps({
  nodeId: { type: String, required: true },
  visible: { type: Boolean, default: false }
});

const blockActions = inject('blockActions', null);

const showConfirm = ref(false);
const confirmDelete = () => {
  if (showConfirm.value) {
    blockActions?.deleteBlockById(props.nodeId);
    showConfirm.value = false;
  } else {
    showConfirm.value = true;
    setTimeout(() => { showConfirm.value = false; }, 3000);
  }
};

const handleDuplicate = () => blockActions?.duplicateBlock(props.nodeId);
const handleMoveUp = () => blockActions?.moveBlock(props.nodeId, 'up');
const handleMoveDown = () => blockActions?.moveBlock(props.nodeId, 'down');
const handleCopy = () => blockActions?.copyBlock(props.nodeId);
const handlePaste = () => blockActions?.pasteBlock(props.nodeId);
const handleWrap = () => blockActions?.wrapInContainer(props.nodeId);
</script>

<template>
  <div
    class="absolute top-2 right-2 flex items-center gap-0.5 z-20 bg-slate-900/90 backdrop-blur-sm rounded-lg border border-slate-700/50 shadow-lg p-0.5 transition-opacity"
    :class="visible ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none'"
    @click.stop
  >
    <button
      v-if="blockActions"
      @click="handleMoveUp"
      title="Move up"
      class="text-slate-300 hover:text-white hover:bg-slate-700/60 rounded p-1 transition-colors cursor-pointer border-0 bg-transparent"
    >
      <ChevronUp class="h-3.5 w-3.5" />
    </button>
    <button
      v-if="blockActions"
      @click="handleMoveDown"
      title="Move down"
      class="text-slate-300 hover:text-white hover:bg-slate-700/60 rounded p-1 transition-colors cursor-pointer border-0 bg-transparent"
    >
      <ChevronDown class="h-3.5 w-3.5" />
    </button>
    <div class="w-px h-4 bg-slate-700/50 mx-0.5"></div>
    <button
      v-if="blockActions"
      @click="handleDuplicate"
      title="Duplicate"
      class="text-slate-300 hover:text-white hover:bg-slate-700/60 rounded p-1 transition-colors cursor-pointer border-0 bg-transparent"
    >
      <Copy class="h-3.5 w-3.5" />
    </button>
    <button
      v-if="blockActions"
      @click="handleCopy"
      title="Copy"
      class="text-slate-300 hover:text-white hover:bg-slate-700/60 rounded p-1 transition-colors cursor-pointer border-0 bg-transparent"
    >
      <ArrowUpFromLine class="h-3.5 w-3.5" />
    </button>
    <button
      v-if="blockActions && blockActions.copiedBlock?.value"
      @click="handlePaste"
      title="Paste"
      class="text-emerald-400 hover:text-emerald-300 hover:bg-emerald-700/30 rounded p-1 transition-colors cursor-pointer border-0 bg-transparent"
    >
      <ClipboardPaste class="h-3.5 w-3.5" />
    </button>
    <div class="w-px h-4 bg-slate-700/50 mx-0.5"></div>
    <button
      v-if="blockActions"
      @click="handleWrap"
      title="Wrap in Column"
      class="text-slate-300 hover:text-white hover:bg-slate-700/60 rounded p-1 transition-colors cursor-pointer border-0 bg-transparent"
    >
      <SquareUserRound class="h-3.5 w-3.5" />
    </button>
    <button
      v-if="blockActions"
      @click="confirmDelete"
      :title="showConfirm ? 'Click again to confirm' : 'Delete'"
      class="rounded p-1 transition-colors cursor-pointer border-0 bg-transparent"
      :class="showConfirm ? 'text-rose-300 bg-rose-600/30' : 'text-slate-300 hover:text-rose-400 hover:bg-rose-600/20'"
    >
      <Trash2 class="h-3.5 w-3.5" />
    </button>
  </div>
</template>
