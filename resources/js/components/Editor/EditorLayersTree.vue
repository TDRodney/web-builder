<script setup lang="ts">
import { ListTree } from '@lucide/vue';
import { inject, nextTick, onMounted, provide, reactive, watch } from 'vue';
import draggable from 'vuedraggable';

import EditorLayersTreeNode from '@/components/Editor/EditorLayersTreeNode.vue';

type BlockNode = {
    id: string;
    type: string;
    props?: Record<string, unknown>;
    children?: BlockNode[];
};

const blocks = defineModel('blocks', {
    type: Array,
    required: true,
});

const selectedBlock = inject<{ value: BlockNode | null } | null>(
    'selectedBlock',
    null,
);
const canvasDrag = inject<{
    start: (blockType: string | null, source?: string) => void;
    end: () => void;
} | null>('canvasDrag', null);

const expandedIds = reactive(new Set<string>());

provide('layersTree', {
    isExpanded: (id: string) => expandedIds.has(id),
    toggle: (id: string) => {
        if (expandedIds.has(id)) {
            expandedIds.delete(id);
        } else {
            expandedIds.add(id);
        }
    },
    expand: (id: string) => {
        expandedIds.add(id);
    },
});

onMounted(() => {
    for (const node of blocks.value as BlockNode[]) {
        if (node.children?.length) {
            expandedIds.add(node.id);
        }
    }
});

/** Returns node ancestry (root → … → node) for the given id, or null. */
const findPath = (
    nodes: BlockNode[],
    id: string,
    trail: BlockNode[] = [],
): BlockNode[] | null => {
    for (const node of nodes) {
        const nextTrail = [...trail, node];

        if (node.id === id) {
            return nextTrail;
        }

        if (node.children) {
            const found = findPath(node.children, id, nextTrail);

            if (found) {
                return found;
            }
        }
    }

    return null;
};

watch(
    () => selectedBlock?.value?.id,
    async (selectedId) => {
        if (!selectedId) {
            return;
        }

        const path = findPath(blocks.value as BlockNode[], selectedId);

        if (!path) {
            return;
        }

        path.slice(0, -1).forEach((ancestor) => expandedIds.add(ancestor.id));

        await nextTick();
        document
            .querySelector(`[data-layer-id="${selectedId}"]`)
            ?.scrollIntoView({ block: 'nearest' });
    },
);

const handleDragStart = (event: { item?: HTMLElement }) => {
    canvasDrag?.start(event.item?.dataset?.type || null, 'layers');
};

const handleDragEnd = () => {
    canvasDrag?.end();
};
</script>

<template>
    <div class="layers-tree">
        <draggable
            v-if="blocks.length"
            v-model="blocks"
            item-key="id"
            handle=".layer-drag-handle"
            ghost-class="layer-drag-ghost"
            :group="{ name: 'layers-tree', pull: true, put: true }"
            @start="handleDragStart"
            @end="handleDragEnd"
        >
            <template #item="{ element }">
                <EditorLayersTreeNode
                    :node="element"
                    :depth="0"
                    :data-type="element.type"
                />
            </template>
        </draggable>

        <div v-else class="layers-empty">
            <ListTree :size="18" />
            <strong>No sections yet</strong>
            <p>Add blocks from the Insert panel to build this page.</p>
        </div>
    </div>
</template>

<style scoped>
.layers-tree {
    min-width: 0;
}

.layers-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 24px 16px;
    gap: 5px;
    color: var(--editor-text-muted);
    text-align: center;
    background: var(--editor-panel-muted);
    border: 1px dashed var(--editor-border-strong);
    border-radius: 6px;
}

.layers-empty strong {
    color: var(--editor-text);
    font-size: 11px;
    font-weight: 650;
}

.layers-empty p {
    margin: 0;
    font-size: 10px;
    line-height: 1.5;
}
</style>
