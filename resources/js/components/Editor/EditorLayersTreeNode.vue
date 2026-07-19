<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ChevronRight, Copy, GripVertical, Trash2 } from '@lucide/vue';
import { computed, inject } from 'vue';
import draggable from 'vuedraggable';

import { iconForBlock } from '@/lib/blockIcons';
import { getBlockDefinition } from '@/lib/blockRegistry';

type BlockNode = {
    id: string;
    type: string;
    props?: Record<string, unknown>;
    children?: BlockNode[];
};

const props = defineProps({
    node: { type: Object, required: true },
    depth: { type: Number, default: 0 },
});

const page = usePage();

const selectedBlock = inject<{ value: BlockNode | null } | null>(
    'selectedBlock',
    null,
);
const canvasSelection = inject<{
    selectNode: (node: BlockNode | null) => void;
} | null>('canvasSelection', null);
const hoveredNodeId = inject<{ value: string | null } | null>(
    'hoveredNodeId',
    null,
);
const blockActions = inject<{
    duplicateBlock: (id: string) => void;
    deleteBlockById: (id: string) => void;
} | null>('blockActions', null);
const canvasDrag = inject<{
    start: (blockType: string | null, source?: string) => void;
    end: () => void;
} | null>('canvasDrag', null);
const layersTree = inject<{
    isExpanded: (id: string) => boolean;
    toggle: (id: string) => void;
    expand: (id: string) => void;
} | null>('layersTree', null);

const definition = computed(() => getBlockDefinition(props.node.type));
const label = computed(() => definition.value?.label || props.node.type);
const icon = computed(() => iconForBlock(definition.value?.icon));

const nestingRules = computed(
    () =>
        (
            page.props as {
                blocksConfig?: { nesting?: Record<string, string[]> };
            }
        ).blocksConfig?.nesting,
);
const isContainer = computed(() => {
    if (!Array.isArray(props.node.children)) {
        return false;
    }

    if (nestingRules.value) {
        return Array.isArray(nestingRules.value[props.node.type]);
    }

    return Array.isArray(definition.value?.allowedChildren);
});
const childCount = computed(() => props.node.children?.length ?? 0);
const isExpanded = computed(
    () => layersTree?.isExpanded(props.node.id) ?? false,
);
const isSelected = computed(() => selectedBlock?.value?.id === props.node.id);

const summary = computed(() => {
    const nodeProps = props.node.props || {};
    const candidate = [
        'headline',
        'title',
        'heading',
        'content',
        'label',
        'text',
        'quote',
    ]
        .map((key) => nodeProps[key])
        .find((value) => typeof value === 'string' && value.trim() !== '');

    if (typeof candidate !== 'string') {
        return '';
    }

    return candidate.length > 26 ? `${candidate.slice(0, 26)}…` : candidate;
});

const selectNode = () => {
    canvasSelection?.selectNode(props.node as BlockNode);

    document
        .querySelector(`.canvas-runtime [data-node-id="${props.node.id}"]`)
        ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const setHovered = (hovering: boolean) => {
    if (hoveredNodeId) {
        hoveredNodeId.value = hovering ? props.node.id : null;
    }
};

const checkAllowedChild = (parentType: string, childType: string | null) => {
    if (!childType) {
        return false;
    }

    if (nestingRules.value && typeof nestingRules.value === 'object') {
        const allowed = nestingRules.value[parentType];

        return Array.isArray(allowed) && allowed.includes(childType);
    }

    const def = getBlockDefinition(parentType);

    return Array.isArray(def?.allowedChildren)
        ? def.allowedChildren.includes(childType)
        : false;
};

const handleDragStart = (event: { item?: HTMLElement }) => {
    canvasDrag?.start(event.item?.dataset?.type || null, 'layers');
};

const handleDragEnd = () => {
    canvasDrag?.end();
};
</script>

<template>
    <!-- eslint-disable vue/no-mutating-props — vuedraggable reorders node.children in place; same pattern as RenderNode. -->
    <div class="layer-node" :data-type="node.type">
        <div
            class="layer-row"
            :class="{
                'layer-row-selected': isSelected,
            }"
            :data-layer-id="node.id"
            :style="{ paddingLeft: `${8 + depth * 14}px` }"
            role="button"
            tabindex="0"
            @click.stop="selectNode"
            @keydown.enter.prevent="selectNode"
            @mouseenter="setHovered(true)"
            @mouseleave="setHovered(false)"
        >
            <button
                v-if="isContainer"
                type="button"
                class="layer-chevron"
                :class="{ 'layer-chevron-open': isExpanded }"
                :aria-label="isExpanded ? 'Collapse layer' : 'Expand layer'"
                :aria-expanded="isExpanded"
                @click.stop="layersTree?.toggle(node.id)"
            >
                <ChevronRight :size="12" />
            </button>
            <span v-else class="layer-chevron-spacer"></span>

            <span class="layer-icon">
                <component :is="icon" :size="13" />
            </span>

            <span class="layer-copy">
                <strong>{{ label }}</strong>
                <small v-if="summary">{{ summary }}</small>
            </span>

            <span v-if="isContainer && childCount" class="layer-count">
                {{ childCount }}
            </span>

            <span class="layer-actions">
                <button
                    type="button"
                    title="Duplicate"
                    aria-label="Duplicate block"
                    @click.stop="blockActions?.duplicateBlock(node.id)"
                >
                    <Copy :size="12" />
                </button>
                <button
                    type="button"
                    class="layer-action-danger"
                    title="Delete"
                    aria-label="Delete block"
                    @click.stop="blockActions?.deleteBlockById(node.id)"
                >
                    <Trash2 :size="12" />
                </button>
            </span>

            <span class="layer-drag-handle" title="Drag to reorder" @click.stop>
                <GripVertical :size="12" />
            </span>
        </div>

        <draggable
            v-if="isContainer && isExpanded"
            v-model="node.children"
            item-key="id"
            handle=".layer-drag-handle"
            ghost-class="layer-drag-ghost"
            class="layer-children"
            :class="{ 'layer-children-empty': childCount === 0 }"
            :group="{
                name: 'layers-tree',
                pull: true,
                put: (_to: unknown, _from: unknown, dragEl: HTMLElement) =>
                    checkAllowedChild(
                        node.type,
                        dragEl.getAttribute('data-type'),
                    ),
            }"
            @start="handleDragStart"
            @end="handleDragEnd"
        >
            <template #item="{ element }">
                <EditorLayersTreeNode
                    :node="element"
                    :depth="depth + 1"
                    :data-type="element.type"
                />
            </template>
        </draggable>
    </div>
</template>

<style scoped>
.layer-row {
    position: relative;
    display: flex;
    align-items: center;
    min-width: 0;
    min-height: 32px;
    padding-right: 6px;
    gap: 5px;
    border-radius: 5px;
    cursor: pointer;
    user-select: none;
}

.layer-row:hover {
    background: var(--editor-panel-muted);
}

.layer-row-selected,
.layer-row-selected:hover {
    background: var(--editor-accent-soft);
    box-shadow: inset 0 0 0 1px
        color-mix(in srgb, var(--editor-accent) 30%, white);
}

.layer-chevron {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    color: var(--editor-text-muted);
    background: transparent;
    border: 0;
    border-radius: 3px;
    cursor: pointer;
    transition: transform 140ms ease;
}

.layer-chevron:hover {
    color: var(--editor-text);
    background: var(--editor-border);
}

.layer-chevron-open {
    transform: rotate(90deg);
}

.layer-chevron-spacer {
    width: 16px;
    flex-shrink: 0;
}

.layer-icon {
    display: inline-flex;
    flex-shrink: 0;
    color: var(--editor-accent);
}

.layer-copy {
    display: flex;
    min-width: 0;
    flex: 1;
    align-items: baseline;
    gap: 6px;
}

.layer-copy strong {
    overflow: hidden;
    color: var(--editor-text);
    font-size: 10.5px;
    font-weight: 600;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.layer-copy small {
    overflow: hidden;
    color: var(--editor-text-muted);
    font-size: 9px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.layer-count {
    flex-shrink: 0;
    padding: 1px 5px;
    color: var(--editor-text-muted);
    font-size: 8.5px;
    font-weight: 700;
    background: var(--editor-panel-muted);
    border: 1px solid var(--editor-border);
    border-radius: 9999px;
}

.layer-actions {
    display: none;
    flex-shrink: 0;
    gap: 1px;
}

.layer-row:hover .layer-actions,
.layer-row-selected .layer-actions {
    display: inline-flex;
}

.layer-actions button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    color: var(--editor-text-muted);
    background: transparent;
    border: 0;
    border-radius: 4px;
    cursor: pointer;
}

.layer-actions button:hover {
    color: var(--editor-accent);
    background: var(--editor-accent-soft);
}

.layer-actions .layer-action-danger:hover {
    color: #dc2626;
    background: rgb(220 38 38 / 10%);
}

.layer-drag-handle {
    display: inline-flex;
    flex-shrink: 0;
    color: var(--editor-border-strong);
    cursor: grab;
}

.layer-drag-handle:active {
    cursor: grabbing;
}

.layer-row:hover .layer-drag-handle {
    color: var(--editor-text-muted);
}

.layer-children-empty {
    min-height: 22px;
    margin: 2px 0 2px 28px;
    border: 1px dashed var(--editor-border-strong);
    border-radius: 4px;
}
</style>

<style>
.layer-drag-ghost {
    opacity: 0.4;
}
</style>
