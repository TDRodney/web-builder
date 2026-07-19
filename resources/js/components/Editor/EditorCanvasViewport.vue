<script setup lang="ts">
import { computed, inject } from 'vue';
import draggable from 'vuedraggable';

import RenderNode from '@/components/BuilderBlocks/RenderNode.vue';
import SiteFooter from '@/components/SiteFooter.vue';
import SiteHeader from '@/components/SiteHeader.vue';
import type { NavigationConfig } from '@/types/navigation';

type BlockNode = {
    id: string;
    type: string;
    props?: Record<string, unknown>;
    children?: BlockNode[];
};

const blocks = defineModel<BlockNode[]>('blocks', { required: true });

const props = withDefaults(
    defineProps<{
        navigationConfig: NavigationConfig;
        pages: Array<{ id?: number; slug: string; title?: string }>;
        tenantName?: string;
        themeVars: Record<string, string>;
        canvasMaxWidth: string;
        currentPageSlug: string;
        viewMode: string;
    }>(),
    { tenantName: 'My Workspace' },
);

const emit = defineEmits(['drag-start', 'drag-end', 'navigate-page']);
const canvasSelection = inject<{
    selectNode: (node: BlockNode | null) => void;
} | null>('canvasSelection', null);
const isDragging = inject<{ value: boolean } | null>('isDragging', null);
const dragState = inject<{ source: string | null } | null>('dragState', null);
const isCanvasDragActive = computed(
    () => isDragging?.value && dragState?.source !== 'layers',
);

const handleDragStart = (event: { item?: HTMLElement }): void => {
    emit('drag-start', event.item?.dataset?.type || null);
};

const handleListChange = (event: { added?: { element?: BlockNode } }): void => {
    if (event.added?.element) {
        canvasSelection?.selectNode(event.added.element);
    }
};

const handleCanvasLink = (event: MouseEvent) => {
    const target = event.target;

    if (!(target instanceof Element)) {
        return;
    }

    const anchor = target.closest('a');

    if (!anchor || !anchor.href || anchor.target === '_blank') {
        return;
    }

    const url = new URL(anchor.href, window.location.origin);

    if (url.origin !== window.location.origin) {
        return;
    }

    const slug = url.pathname.replace(/^\/+|\/+$/g, '') || 'home';

    if (!props.pages.some((page) => page.slug === slug)) {
        return;
    }

    event.preventDefault();
    event.stopPropagation();
    emit('navigate-page', slug);
};
</script>

<template>
    <main class="canvas-workspace">
        <div class="canvas-stage" :class="`canvas-stage-${viewMode}`">
            <div
                class="canvas-frame"
                :style="{ maxWidth: canvasMaxWidth }"
                :data-preview-mode="viewMode"
            >
                <div
                    class="canvas-runtime"
                    @click.capture="handleCanvasLink"
                    :style="[
                        themeVars,
                        {
                            backgroundColor: themeVars['--theme-bg'],
                            color: themeVars['--theme-text'],
                        },
                    ]"
                >
                    <SiteHeader
                        :navigation-config="navigationConfig"
                        :pages="pages"
                        :tenant-name="tenantName"
                        :is-editable="true"
                        :current-page-slug="currentPageSlug"
                        @navigate-page="emit('navigate-page', $event)"
                    />

                    <draggable
                        v-model="blocks"
                        item-key="id"
                        handle=".drag-handle"
                        ghost-class="drag-ghost"
                        :group="{ name: 'canvas-tree', pull: true, put: true }"
                        class="canvas-blocks canvas-drop-zone"
                        :class="{
                            'canvas-drop-zone-active': isCanvasDragActive,
                            'canvas-drop-zone-empty': blocks.length === 0,
                        }"
                        :data-drop-label="
                            blocks.length === 0
                                ? 'Drop your first block here'
                                : 'Drop block on page'
                        "
                        @start="handleDragStart"
                        @end="emit('drag-end')"
                        @change="handleListChange"
                    >
                        <template #item="{ element }">
                            <RenderNode :node="element" />
                        </template>
                    </draggable>

                    <SiteFooter
                        :navigation-config="navigationConfig"
                        :tenant-name="tenantName"
                    />
                </div>
            </div>
        </div>
    </main>
</template>

<style scoped>
.canvas-workspace {
    min-width: 0;
    min-height: 0;
    overflow: auto;
    background-color: var(--editor-bg);
    background-image:
        linear-gradient(45deg, rgb(24 24 27 / 2.5%) 25%, transparent 25%),
        linear-gradient(-45deg, rgb(24 24 27 / 2.5%) 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, rgb(24 24 27 / 2.5%) 75%),
        linear-gradient(-45deg, transparent 75%, rgb(24 24 27 / 2.5%) 75%);
    background-position:
        0 0,
        0 4px,
        4px -4px,
        -4px 0;
    background-size: 8px 8px;
    scrollbar-color: var(--editor-border-strong) var(--editor-bg);
}

.canvas-stage {
    display: flex;
    width: 100%;
    min-width: min-content;
    min-height: 100%;
    padding: 32px;
    box-sizing: border-box;
    align-items: flex-start;
    justify-content: center;
}

.canvas-stage-desktop {
    padding: 32px;
}

.canvas-frame {
    width: 100%;
    height: calc(100vh - 118px);
    min-height: 420px;
    overflow-x: hidden;
    overflow-y: auto;
    background: #ffffff;
    border: 1px solid var(--editor-border);
    border-radius: 10px;
    box-shadow: var(--editor-shadow);
    scrollbar-color: #71717a #e4e4e7;
    scrollbar-width: thin;
    transition: max-width 220ms ease;
}

.canvas-runtime {
    position: relative;
    display: flex;
    min-height: 100%;
    flex-direction: column;
    box-sizing: border-box;
    container-type: inline-size;
    contain: layout style;
    font-family: var(--theme-font-body, sans-serif);
}

.canvas-blocks {
    position: relative;
    flex: 1 0 auto;
    min-height: 160px;
}

.canvas-drop-zone {
    transition:
        background-color 140ms ease,
        box-shadow 140ms ease;
}

.canvas-drop-zone-active {
    background: color-mix(in srgb, var(--editor-accent) 4%, transparent);
    box-shadow: inset 0 0 0 2px
        color-mix(in srgb, var(--editor-accent) 42%, transparent);
}

.canvas-drop-zone-active::after {
    position: absolute;
    right: 10px;
    bottom: 10px;
    z-index: 40;
    padding: 5px 8px;
    color: var(--editor-accent);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.02em;
    content: attr(data-drop-label);
    pointer-events: none;
    background: var(--editor-panel);
    border: 1px solid color-mix(in srgb, var(--editor-accent) 35%, white);
    border-radius: 5px;
    box-shadow: var(--editor-shadow);
}

.canvas-drop-zone-empty.canvas-drop-zone-active::after {
    top: 50%;
    right: auto;
    bottom: auto;
    left: 50%;
    transform: translate(-50%, -50%);
}

.canvas-stage-tablet .canvas-frame,
.canvas-stage-mobile .canvas-frame {
    flex: 0 0 auto;
}

.canvas-runtime :deep(.bg-\[var\(--block-bg\)\]) {
    background-color: var(--block-bg) !important;
}

.canvas-runtime :deep(.p-\[var\(--block-padding\)\]) {
    padding: var(--block-padding) !important;
}

@media (prefers-reduced-motion: reduce) {
    .canvas-frame,
    .canvas-drop-zone {
        transition: none;
    }
}

@media (max-width: 820px) {
    .canvas-stage {
        padding: 18px;
    }
    .canvas-frame {
        height: calc(100vh - 90px);
        min-height: calc(100vh - 90px);
    }
}
</style>
