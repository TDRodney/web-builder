<!-- eslint-disable vue/block-lang -->
<script setup>
import { computed, inject, onErrorCaptured, ref } from 'vue';
import {
    surfaceIsGrid as isGridSurface,
    surfaceStacksOnNarrow as stacksOnNarrow,
    surfaceStylesForBlock,
} from '@/lib/layoutSurface';
import { vReveal } from '@/lib/reveal';
import RenderPublicNode from './RenderPublicNode.vue';

const props = defineProps({
    node: {
        type: Object,
        required: true,
    },
});

const blockRegistry = inject('blockRegistry');

const hasError = ref(false);
const errorMessage = ref('');

onErrorCaptured((err) => {
    hasError.value = true;
    errorMessage.value = err.message || 'Rendering failed';
    console.error(
        `[Public Block Render Error] Node ID: ${props.node.id}, Type: ${props.node.type}`,
        err,
    );

    return false;
});

const hasChildren = computed(
    () => Array.isArray(props.node.children) && props.node.children.length > 0,
);

const surfaceStyles = computed(() =>
    surfaceStylesForBlock(props.node.type, props.node.props),
);

const surfaceIsGrid = computed(() => isGridSurface(surfaceStyles.value));
const surfaceStacksOnNarrow = computed(() =>
    stacksOnNarrow(surfaceStyles.value),
);
</script>

<template>
    <div
        v-reveal="{ type: node.props?.reveal, delay: node.props?.revealDelay }"
        :style="{
            padding: (node.props?.padding ?? 0) + 'px',
            backgroundColor: node.props?.backgroundColor ?? 'transparent',
            marginTop: node.props?.marginTop !== undefined ? node.props.marginTop + 'px' : undefined,
            marginBottom: node.props?.marginBottom !== undefined ? node.props.marginBottom + 'px' : undefined,
            opacity: node.props?.opacity !== undefined ? node.props.opacity / 100 : undefined,
            borderRadius: node.props?.borderRadius ? node.props.borderRadius : undefined,
        }"
        class="public-block-node transition-[background-color]"
    >
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
            :is="blockRegistry[node.type]"
            v-else
            :node-id="node.id"
            :block-props="node.props"
        >
            <div
                v-if="hasChildren"
                class="public-children"
                :class="{
                    'layout-surface-grid': surfaceIsGrid,
                    'layout-surface-stack-narrow': surfaceStacksOnNarrow,
                }"
                :style="surfaceStyles || undefined"
            >
                <RenderPublicNode
                    v-for="child in node.children"
                    :key="child.id"
                    :node="child"
                />
            </div>
        </component>
    </div>
</template>

<style scoped>
.public-children.layout-surface-grid > .public-block-node {
    min-width: 0;
}

@container (max-width: 680px) {
    .public-children.layout-surface-grid.layout-surface-stack-narrow {
        grid-template-columns: minmax(0, 1fr) !important;
    }
}
</style>
