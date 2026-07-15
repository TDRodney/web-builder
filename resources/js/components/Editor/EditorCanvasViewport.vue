<script setup lang="ts">
import draggable from 'vuedraggable';

import RenderNode from '@/components/BuilderBlocks/RenderNode.vue';
import SiteFooter from '@/components/SiteFooter.vue';
import SiteHeader from '@/components/SiteHeader.vue';

const blocks = defineModel('blocks', {
    type: Array,
    required: true,
});

const props = defineProps({
    navigationConfig: { type: Object, required: true },
    pages: { type: Array, required: true },
    tenantName: { type: String, default: 'My Workspace' },
    themeVars: { type: Object, required: true },
    canvasMaxWidth: { type: String, required: true },
    viewMode: { type: String, required: true },
});

const emit = defineEmits(['drag-start', 'drag-end', 'navigate-page']);

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
                        @navigate-page="emit('navigate-page', $event)"
                    />

                    <draggable
                        v-model="blocks"
                        item-key="id"
                        handle=".drag-handle"
                        ghost-class="drag-ghost"
                        :group="{ name: 'canvas-tree', pull: true, put: true }"
                        class="canvas-blocks"
                        @start="emit('drag-start')"
                        @end="emit('drag-end')"
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
    background-color: #111113;
    background-image:
        linear-gradient(45deg, rgb(255 255 255 / 2.2%) 25%, transparent 25%),
        linear-gradient(-45deg, rgb(255 255 255 / 2.2%) 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, rgb(255 255 255 / 2.2%) 75%),
        linear-gradient(-45deg, transparent 75%, rgb(255 255 255 / 2.2%) 75%);
    background-position:
        0 0,
        0 4px,
        4px -4px,
        -4px 0;
    background-size: 8px 8px;
    scrollbar-color: #52525b #171719;
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
    border: 1px solid #303033;
    border-radius: 7px;
    box-shadow: 0 18px 60px rgb(0 0 0 / 52%);
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
    flex: 1 0 auto;
    min-height: 160px;
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
    .canvas-frame {
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
