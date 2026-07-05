<script setup>
import { inject, ref, onErrorCaptured } from 'vue';
import draggable from 'vuedraggable';
import RenderNode from './RenderNode.vue';

const props = defineProps({
  node: {
    type: Object,
    required: true
  }
});

const blockRegistry = inject('blockRegistry');
const selectedBlock = inject('selectedBlock');
const canvasSelection = inject('canvasSelection', null);
const isDragging = inject('isDragging', null);
const forceSave = inject('forceSave', null);

const hasError = ref(false);
const errorMessage = ref('');

onErrorCaptured((err) => {
  hasError.value = true;
  errorMessage.value = err.message || 'Rendering failed';
  console.error(`[Block Render Error] Node ID: ${props.node.id}, Type: ${props.node.type}`, err);
  return false;
});

const selectBlock = (node) => {
  if (canvasSelection) {
    canvasSelection.selectNode(node);
  } else if (selectedBlock) {
    selectedBlock.value = node;
  }
};
const handleDragStart = () => {
  if (isDragging) {
    isDragging.value = true;
  }
};

const handleDragEnd = () => {
  if (isDragging) {
    isDragging.value = false;
  }

  if (forceSave) {
    forceSave();
  }
};
</script>

<template>
  <div 
    @click.stop="selectBlock(node)"
    :style="{ 
      padding: (node.props?.padding ?? 0) + 'px',
      backgroundColor: node.props?.backgroundColor ?? 'transparent'
    }"
    class="border-2 border-transparent hover:border-indigo-500 rounded-lg relative group my-2 cursor-pointer transition-[border-color,background-color]"
  >
    <div class="drag-handle absolute top-2 left-2 opacity-0 group-hover:opacity-100 bg-indigo-600 text-white px-2 py-0.5 rounded text-xs cursor-move z-10">
      ::: Move
    </div>
    
    <div v-if="hasError" class="p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-lg text-xs flex flex-col gap-1 my-2">
      <div class="font-bold flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        Failed to render block: {{ node.type }}
      </div>
      <div class="font-mono text-[10px] opacity-80">{{ errorMessage }}</div>
    </div>

    <component 
      v-else
      :is="blockRegistry[node.type]" 
      :node-id="node.id"
      :block-props="node.props"
    >
      <draggable 
        v-if="node.children"
        v-model="node.children" 
        item-key="id" 
        handle=".drag-handle" 
        ghost-class="drag-ghost"
        :group="{ name: 'canvas-tree', pull: true, put: true }"
        @start="handleDragStart"
        @end="handleDragEnd"
        class="min-h-[50px] w-full"
      >
        <template #item="{ element }">
          <RenderNode :node="element" />
        </template>
      </draggable>
    </component>
  </div>
</template>
