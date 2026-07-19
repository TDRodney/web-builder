<!-- eslint-disable vue/block-lang -->
<script setup>
/* eslint-disable vue/no-mutating-props */
import { usePage } from '@inertiajs/vue3';
import { GripVertical } from '@lucide/vue';
import { inject, ref, onErrorCaptured, computed } from 'vue';
import draggable from 'vuedraggable';
import { getBlockDefinition } from '@/lib/blockRegistry';
import {
    surfaceIsGrid as isGridSurface,
    surfaceStacksOnNarrow as stacksOnNarrow,
    surfaceStylesForBlock,
} from '@/lib/layoutSurface';
import BlockToolbar from './BlockToolbar.vue';
import RenderNode from './RenderNode.vue';

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
const dragState = inject('dragState', null);
const canvasDrag = inject('canvasDrag', null);
const hoveredNodeId = inject('hoveredNodeId', null);
const isEditable = inject('isEditable', false);

const blockDefinition = computed(() => getBlockDefinition(props.node.type));
const blockLabel = computed(
    () => blockDefinition.value?.label || props.node.type,
);
const activeBlockLabel = computed(() => {
    if (!dragState?.activeType) {
        return 'block';
    }

    return getBlockDefinition(dragState.activeType)?.label || 'block';
});
const page = usePage();
const nestingRules = computed(() => page.props.blocksConfig?.nesting);
const isContainer = computed(() => {
    if (!Array.isArray(props.node.children)) {
        return false;
    }

    if (nestingRules.value && typeof nestingRules.value === 'object') {
        return Array.isArray(nestingRules.value[props.node.type]);
    }

    return Array.isArray(blockDefinition.value?.allowedChildren);
});
const isCanvasDragActive = computed(
    () => isDragging?.value && dragState?.source !== 'layers',
);

/** Apply grid/flex to this container's drop zone so children sit side-by-side. */
const surfaceStyles = computed(() =>
    surfaceStylesForBlock(props.node.type, props.node.props, {
        isEditable: Boolean(isEditable),
        isEmpty: !props.node.children?.length,
    }),
);

const surfaceIsGrid = computed(() => isGridSurface(surfaceStyles.value));
const surfaceStacksOnNarrow = computed(() =>
    stacksOnNarrow(surfaceStyles.value),
);

const checkAllowedChild = (parentType, childType) => {
    if (nestingRules.value && typeof nestingRules.value === 'object') {
        const allowed = nestingRules.value[parentType];

        return Array.isArray(allowed) && allowed.includes(childType);
    }

    const def = getBlockDefinition(parentType);

    return Array.isArray(def?.allowedChildren)
        ? def.allowedChildren.includes(childType)
        : false;
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
const isTreeHovered = computed(() => hoveredNodeId?.value === props.node.id);

const selectBlock = (node) => {
    if (canvasSelection) {
        canvasSelection.selectNode(node);
    } else if (selectedBlock) {
        selectedBlock.value = node;
    }
};
const handleDragStart = () => {
    canvasDrag?.start(props.node.type);
};

const handleDragEnd = () => {
    canvasDrag?.end();
};

const isDropAllowed = computed(() => {
    if (!dragState?.activeType) {
        return true;
    }

    return checkAllowedChild(props.node.type, dragState.activeType);
});

const dropLabel = computed(() =>
    isDropAllowed.value
        ? `Drop inside ${blockLabel.value}`
        : `${activeBlockLabel.value} cannot go inside ${blockLabel.value}`,
);

const handleChildrenChange = (event) => {
    if (event.added?.element) {
        selectBlock(event.added.element);
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
        :class="{
            'editor-block-node-active': isHovered || isSelected,
            'editor-block-node-tree-hovered': isTreeHovered,
        }"
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
                v-if="isContainer"
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
                @change="handleChildrenChange"
                class="canvas-child-drop-zone min-h-[50px] w-full"
                :class="{
                    'canvas-child-drop-zone-active': isCanvasDragActive,
                    'canvas-child-drop-zone-valid': isDropAllowed,
                    'canvas-child-drop-zone-invalid': !isDropAllowed,
                    'canvas-child-drop-zone-empty': node.children.length === 0,
                    'layout-surface-grid': surfaceIsGrid,
                    'layout-surface-stack-narrow': surfaceStacksOnNarrow,
                }"
                :style="surfaceStyles || undefined"
                :data-drop-label="dropLabel"
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

.editor-block-node-tree-hovered {
    border-color: color-mix(in srgb, #7890b5 60%, transparent);
    box-shadow: 0 0 0 3px rgb(120 144 181 / 14%);
}

.block-drag-handle {
    color: #e4e4e7;
    font-size: 10px;
    background: rgb(15 15 16 / 94%);
    border-color: #3f4652;
    border-radius: 4px;
}

.canvas-child-drop-zone {
    position: relative;
    border-radius: 4px;
    transition:
        background-color 140ms ease,
        box-shadow 140ms ease;
}

.canvas-child-drop-zone-active {
    box-shadow: inset 0 0 0 2px currentColor;
}

.canvas-child-drop-zone-valid {
    color: var(--editor-accent);
    background: color-mix(in srgb, var(--editor-accent) 6%, transparent);
}

.canvas-child-drop-zone-invalid {
    color: #be123c;
    background: rgb(190 18 60 / 5%);
}

.canvas-child-drop-zone-active::after {
    position: absolute;
    right: 8px;
    bottom: 8px;
    z-index: 35;
    max-width: calc(100% - 16px);
    padding: 4px 7px;
    overflow: hidden;
    color: currentColor;
    font-size: 10px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
    pointer-events: none;
    content: attr(data-drop-label);
    background: color-mix(in srgb, currentColor 12%, white);
    border: 1px solid color-mix(in srgb, currentColor 28%, white);
    border-radius: 4px;
    opacity: 0;
    transition: opacity 120ms ease;
}

.canvas-child-drop-zone-active:hover::after,
.canvas-child-drop-zone-empty.canvas-child-drop-zone-active::after {
    opacity: 1;
}

/* Nested blocks must shrink inside CSS grid tracks. */
.layout-surface-grid > :deep(.editor-block-node) {
    min-width: 0;
}

@container (max-width: 680px) {
    .layout-surface-grid.layout-surface-stack-narrow {
        grid-template-columns: minmax(0, 1fr) !important;
    }
}

@media (prefers-reduced-motion: reduce) {
    .canvas-child-drop-zone,
    .canvas-child-drop-zone-active::after {
        transition: none;
    }
}
</style>
