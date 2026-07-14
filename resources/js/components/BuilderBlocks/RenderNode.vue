<script setup>
import { inject, ref, onErrorCaptured, computed } from 'vue';
import draggable from 'vuedraggable';
import { usePage } from '@inertiajs/vue3';
import { GripVertical } from '@lucide/vue';
import { getBlockDefinition } from '@/lib/blockRegistry';
import RenderNode from './RenderNode.vue';
import BlockToolbar from './BlockToolbar.vue';

const props = defineProps({
    node: {
        type: Object,
        required: true,
    },
});

const blockRegistry = inject('blockRegistry');
const selectedBlock = inject('selectedBlock', null);
const canvasSelection = inject('canvasSelection', null);
const isDragging = inject('isDragging', null);
const forceSave = inject('forceSave', null);

const blockDefinition = computed(() => getBlockDefinition(props.node.type));
const blockLabel = computed(
    () => blockDefinition.value?.label || props.node.type,
);

const checkAllowedChild = (parentType, childType) => {
    const page = usePage();
    const nestingRules = page.props.blocksConfig?.nesting;

    if (nestingRules && typeof nestingRules === 'object') {
        const allowed = nestingRules[parentType];
        return Array.isArray(allowed) ? allowed.includes(childType) : true;
    }

    const def = getBlockDefinition(parentType);

    if (!def || !def.allowedChildren) {
        return true;
    }

    return def.allowedChildren.includes(childType);
};

const hasError = ref(false);
const errorMessage = ref('');

onErrorCaptured((err) => {
    hasError.value = true;
    errorMessage.value = err.message || 'Rendering failed';
    console.error(
        `[Block Render Error] Node ID: ${props.node.id}, Type: ${props.node.type}`,
        err,
    );

    return false;
});

const isHovered = ref(false);
const isSelected = computed(() => selectedBlock?.value?.id === props.node.id);

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

const resolvedBgColor = computed(() => {
    const bg = props.node.props?.backgroundColor;
    if (!bg || bg === '#ffffff' || bg === '#f8fafc' || bg === 'transparent') {
        return 'transparent';
    }
    return bg;
});
</script>

<template>
    <div
        :data-type="node.type"
        :data-node-id="node.id"
        :data-selected="isSelected ? 'true' : 'false'"
        @click.stop="selectBlock(node)"
        @mouseenter="isHovered = true"
        @mouseleave="isHovered = false"
        :style="{
            padding: (node.props?.padding ?? 0) + 'px',
            backgroundColor: resolvedBgColor,
        }"
        class="editor-block-node relative my-2 cursor-pointer border-2 border-transparent transition-[border-color,background-color]"
        :class="{ 'editor-block-node-active': isHovered || isSelected }"
    >
        <!-- Dedicated drag handle displaying block label and grip icon -->
        <div
            class="block-drag-handle drag-handle absolute top-2 left-2 z-20 flex cursor-grab items-center gap-1.5 border border-slate-700/50 bg-slate-900/90 px-2 py-1 text-xs font-semibold text-slate-200 shadow-lg backdrop-blur-sm transition-opacity select-none active:cursor-grabbing"
            :class="
                isHovered
                    ? 'pointer-events-auto opacity-100'
                    : 'pointer-events-none opacity-0'
            "
        >
            <GripVertical class="h-3.5 w-3.5 text-slate-400" />
            <span>{{ blockLabel }}</span>
        </div>

        <!-- Action buttons toolbar -->
        <BlockToolbar :node-id="node.id" :visible="isHovered" />

        <div
            v-if="hasError"
            class="my-2 flex flex-col gap-1 rounded-lg border border-rose-500/20 bg-rose-500/10 p-4 text-xs text-rose-400"
        >
            <div class="flex items-center gap-1 font-bold">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2.5"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                    />
                </svg>
                Failed to render block: {{ node.type }}
            </div>
            <div class="font-mono text-[10px] opacity-80">
                {{ errorMessage }}
            </div>
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
                :group="{
                    name: 'canvas-tree',
                    pull: true,
                    put: (to, from, dragEl) =>
                        checkAllowedChild(
                            node.type,
                            dragEl.getAttribute('data-type'),
                        ),
                }"
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

<style scoped>
.editor-block-node {
    border-radius: 3px;
}

.editor-block-node-active {
    border-color: #7890b5;
    box-shadow: 0 0 0 1px rgb(120 144 181 / 18%);
}

.block-drag-handle {
    color: #e4e4e7;
    font-size: 10px;
    background: rgb(15 15 16 / 94%);
    border-color: #3f4652;
    border-radius: 4px;
}
</style>
