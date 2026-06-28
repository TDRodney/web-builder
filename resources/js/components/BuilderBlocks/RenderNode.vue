<script setup>
import { inject } from 'vue';
import draggable from 'vuedraggable';
import RenderNode from './RenderNode.vue';

defineProps({
  node: {
    type: Object,
    required: true
  }
});

const blockRegistry = inject('blockRegistry');
const selectedBlock = inject('selectedBlock');
const isDragging = inject('isDragging', null);
const forceSave = inject('forceSave', null);

const selectBlock = (node) => {
  if (selectedBlock) {
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
      '--block-padding': (node.styles?.padding ?? 0) + 'px',
      '--block-bg': node.styles?.backgroundColor ?? 'transparent'
    }"
    class="border-2 border-transparent hover:border-indigo-500 rounded-lg p-[var(--block-padding)] bg-[var(--block-bg)] transition-all relative group my-2 cursor-pointer"
  >
    <div class="drag-handle absolute top-2 left-2 opacity-0 group-hover:opacity-100 bg-indigo-600 text-white px-2 py-0.5 rounded text-xs cursor-move z-10">
      ::: Move
    </div>
    
    <component 
      :is="blockRegistry[node.type]" 
      :node-id="node.id"
      :props-data="node.propsData"
      :styles="node.styles"
      :content="node.content"
    >
      <draggable 
        v-if="node.children"
        v-model="node.children" 
        item-key="id" 
        handle=".drag-handle" 
        ghost-class="opacity-40"
        :group="{ name: 'canvas-tree' }"
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
