<script setup lang="ts">
import {
    ChevronRight,
    ClipboardPaste,
    Copy,
    ExternalLink,
    FileText,
    Group,
    Home,
    MoveDown,
    MoveUp,
    Palette,
    PanelTop,
    Pencil,
    X,
} from '@lucide/vue';
import { computed, inject } from 'vue';

import ContentInspector from '@/components/Editor/ContentInspector.vue';
import { iconForBlock } from '@/lib/blockIcons';
import { getBlockDefinition } from '@/lib/blockRegistry';

type BlockNode = {
    id: string;
    type: string;
    props?: Record<string, unknown>;
    children?: BlockNode[];
};

const props = defineProps({
    selectedBlock: { type: Object, default: null },
    activeBlockDefinition: { type: Object, default: null },
    blocks: { type: Array, required: true },
    page: { type: Object, required: true },
    liveUrl: { type: String, required: true },
});

const emit = defineEmits([
    'open-media-picker',
    'rename-page',
    'change-workspace',
    'close',
]);

const canvasSelection = inject<{
    selectNode: (node: BlockNode | null) => void;
} | null>('canvasSelection', null);
const blockActions = inject<{
    duplicateBlock: (id: string) => void;
    moveBlock: (id: string, direction: 'up' | 'down') => void;
    copyBlock: (id: string) => void;
    pasteBlock: (id: string) => void;
    wrapInContainer: (id: string) => void;
    copiedBlock: { value: BlockNode | null };
} | null>('blockActions', null);

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

const breadcrumb = computed<BlockNode[]>(() => {
    if (!props.selectedBlock) {
        return [];
    }

    return (
        findPath(props.blocks as BlockNode[], props.selectedBlock.id) || [
            props.selectedBlock as BlockNode,
        ]
    );
});

const selectedIcon = computed(() =>
    iconForBlock(props.activeBlockDefinition?.icon),
);

const selectedLabel = computed(
    () =>
        props.activeBlockDefinition?.label ||
        props.selectedBlock?.type ||
        'Block',
);

const crumbLabel = (node: BlockNode) =>
    getBlockDefinition(node.type)?.label || node.type;

const canPaste = computed(() => !!blockActions?.copiedBlock?.value);

const sectionCount = computed(() => props.blocks.length);

const quickActions = computed(() => {
    if (!props.selectedBlock || !blockActions) {
        return [];
    }

    const id = props.selectedBlock.id;

    return [
        {
            key: 'move-up',
            label: 'Move up',
            icon: MoveUp,
            disabled: false,
            run: () => blockActions.moveBlock(id, 'up'),
        },
        {
            key: 'move-down',
            label: 'Move down',
            icon: MoveDown,
            disabled: false,
            run: () => blockActions.moveBlock(id, 'down'),
        },
        {
            key: 'duplicate',
            label: 'Duplicate (Ctrl+D)',
            icon: Copy,
            disabled: false,
            run: () => blockActions.duplicateBlock(id),
        },
        {
            key: 'wrap',
            label: 'Wrap in column',
            icon: Group,
            disabled: false,
            run: () => blockActions.wrapInContainer(id),
        },
        {
            key: 'paste',
            label: canPaste.value ? 'Paste after' : 'Copy a block first',
            icon: ClipboardPaste,
            disabled: !canPaste.value,
            run: () => blockActions.pasteBlock(id),
        },
    ];
});
</script>

<template>
    <aside class="inspector-panel" aria-label="Inspector">
        <div class="inspector-heading">
            <div class="inspector-heading-copy">
                <span class="inspector-kicker">Inspector</span>
                <h2 v-if="selectedBlock" class="inspector-title">
                    <component :is="selectedIcon" :size="14" />
                    <span>{{ selectedLabel }}</span>
                </h2>
                <h2 v-else class="inspector-title">
                    <FileText :size="14" />
                    <span>{{ page.title }}</span>
                </h2>
            </div>

            <button
                type="button"
                class="inspector-close"
                title="Close inspector"
                aria-label="Close inspector"
                @click="emit('close')"
            >
                <X :size="15" />
            </button>
        </div>

        <nav
            v-if="breadcrumb.length > 1"
            class="inspector-breadcrumb"
            aria-label="Block ancestry"
        >
            <template v-for="(node, index) in breadcrumb" :key="node.id">
                <ChevronRight
                    v-if="index > 0"
                    :size="10"
                    class="crumb-separator"
                />
                <button
                    type="button"
                    class="crumb"
                    :class="{
                        'crumb-current': index === breadcrumb.length - 1,
                    }"
                    :disabled="index === breadcrumb.length - 1"
                    @click="canvasSelection?.selectNode(node)"
                >
                    {{ crumbLabel(node) }}
                </button>
            </template>
        </nav>

        <div class="inspector-scroll">
            <template v-if="selectedBlock">
                <div class="inspector-quick-actions" role="toolbar">
                    <button
                        v-for="action in quickActions"
                        :key="action.key"
                        type="button"
                        :title="action.label"
                        :aria-label="action.label"
                        :disabled="action.disabled"
                        @click="action.run"
                    >
                        <component :is="action.icon" :size="14" />
                    </button>
                </div>

                <ContentInspector
                    :selected-block="selectedBlock"
                    :active-block-definition="activeBlockDefinition"
                    @open-media-picker="emit('open-media-picker', $event)"
                />
            </template>

            <template v-else>
                <section class="page-card">
                    <div class="page-card-heading">
                        <span class="page-card-kicker">Current page</span>
                        <span v-if="page.is_homepage" class="page-card-badge">
                            <Home :size="9" />
                            Homepage
                        </span>
                    </div>

                    <h3>{{ page.title }}</h3>
                    <code>/{{ page.slug === 'home' ? '' : page.slug }}</code>

                    <dl class="page-card-stats">
                        <div>
                            <dt>Sections</dt>
                            <dd>{{ sectionCount }}</dd>
                        </div>
                    </dl>

                    <div class="page-card-actions">
                        <button
                            type="button"
                            @click="emit('rename-page', page)"
                        >
                            <Pencil :size="12" />
                            <span>Rename</span>
                        </button>
                        <a
                            :href="liveUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <ExternalLink :size="12" />
                            <span>Open live</span>
                        </a>
                    </div>
                </section>

                <section class="inspector-shortcuts">
                    <span class="inspector-kicker">Site styles</span>
                    <button
                        type="button"
                        @click="emit('change-workspace', 'theme')"
                    >
                        <Palette :size="13" />
                        <span>
                            <strong>Theme</strong>
                            <small>Colors, typography, corners</small>
                        </span>
                        <ChevronRight :size="12" />
                    </button>
                    <button
                        type="button"
                        @click="emit('change-workspace', 'navigation')"
                    >
                        <PanelTop :size="13" />
                        <span>
                            <strong>Navigation</strong>
                            <small>Header, menu, footer</small>
                        </span>
                        <ChevronRight :size="12" />
                    </button>
                </section>

                <p class="inspector-hint">
                    Select any block on the canvas or in the Sections tree to
                    edit its content and style here.
                </p>
            </template>
        </div>
    </aside>
</template>

<style scoped>
.inspector-panel {
    display: flex;
    min-width: 0;
    min-height: 0;
    flex-direction: column;
    color: var(--editor-text);
    background: var(--editor-panel);
    border-left: 1px solid var(--editor-border);
}

.inspector-heading {
    display: flex;
    min-height: 56px;
    padding: 10px 14px;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    border-bottom: 1px solid var(--editor-border);
}

.inspector-heading-copy {
    min-width: 0;
}

.inspector-kicker {
    display: block;
    color: var(--editor-accent);
    font-size: 9px;
    font-weight: 750;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}

.inspector-title {
    display: flex;
    margin: 3px 0 0;
    align-items: center;
    gap: 6px;
    color: var(--editor-text);
    font-size: 13px;
    font-weight: 650;
    line-height: 1.25;
}

.inspector-title span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.inspector-close {
    display: none;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    flex-shrink: 0;
    color: var(--editor-text-muted);
    background: var(--editor-panel-muted);
    border: 1px solid var(--editor-border);
    border-radius: 5px;
    cursor: pointer;
}

.inspector-close:hover {
    color: var(--editor-text);
    background: var(--editor-accent-soft);
}

.inspector-breadcrumb {
    display: flex;
    padding: 8px 14px;
    align-items: center;
    flex-wrap: wrap;
    gap: 3px;
    border-bottom: 1px solid var(--editor-border);
}

.crumb {
    padding: 2px 4px;
    color: var(--editor-text-muted);
    font-size: 9.5px;
    font-weight: 600;
    background: transparent;
    border: 0;
    border-radius: 3px;
    cursor: pointer;
}

.crumb:hover:not(:disabled) {
    color: var(--editor-accent);
    background: var(--editor-accent-soft);
}

.crumb-current {
    color: var(--editor-text);
    cursor: default;
}

.crumb-separator {
    color: var(--editor-border-strong);
}

.inspector-scroll {
    min-height: 0;
    flex: 1;
    padding: 14px;
    overflow-y: auto;
    scrollbar-color: var(--editor-border-strong) transparent;
    scrollbar-width: thin;
}

.inspector-quick-actions {
    display: grid;
    margin-bottom: 14px;
    padding: 3px;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 2px;
    background: var(--editor-panel-muted);
    border: 1px solid var(--editor-border);
    border-radius: 6px;
}

.inspector-quick-actions button {
    display: inline-flex;
    height: 30px;
    align-items: center;
    justify-content: center;
    color: var(--editor-text-muted);
    background: transparent;
    border: 0;
    border-radius: 4px;
    cursor: pointer;
}

.inspector-quick-actions button:hover:not(:disabled) {
    color: var(--editor-accent);
    background: var(--editor-panel);
    box-shadow: 0 1px 3px rgb(24 24 27 / 8%);
}

.inspector-quick-actions button:disabled {
    cursor: not-allowed;
    opacity: 0.35;
}

.page-card {
    padding: 14px;
    background: var(--editor-panel-muted);
    border: 1px solid var(--editor-border);
    border-radius: 7px;
}

.page-card-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
}

.page-card-kicker {
    color: var(--editor-text-muted);
    font-size: 9px;
    font-weight: 750;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.page-card-badge {
    display: inline-flex;
    padding: 2px 7px;
    align-items: center;
    gap: 3px;
    color: var(--editor-accent);
    font-size: 8px;
    font-weight: 750;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    background: var(--editor-accent-soft);
    border: 1px solid var(--editor-border);
    border-radius: 9999px;
}

.page-card h3 {
    margin: 8px 0 0;
    color: var(--editor-text);
    font-size: 14px;
    font-weight: 650;
}

.page-card code {
    display: block;
    margin-top: 2px;
    color: var(--editor-text-muted);
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 10px;
}

.page-card-stats {
    display: flex;
    margin: 12px 0 0;
    gap: 14px;
}

.page-card-stats dt {
    color: var(--editor-text-muted);
    font-size: 8.5px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.page-card-stats dd {
    margin: 1px 0 0;
    color: var(--editor-text);
    font-size: 15px;
    font-weight: 700;
}

.page-card-actions {
    display: flex;
    margin-top: 12px;
    gap: 6px;
}

.page-card-actions button,
.page-card-actions a {
    display: inline-flex;
    height: 30px;
    padding: 0 10px;
    flex: 1;
    align-items: center;
    justify-content: center;
    gap: 5px;
    color: var(--editor-text);
    font-size: 10px;
    font-weight: 650;
    text-decoration: none;
    background: var(--editor-panel);
    border: 1px solid var(--editor-border);
    border-radius: 5px;
    cursor: pointer;
}

.page-card-actions button:hover,
.page-card-actions a:hover {
    color: var(--editor-accent);
    background: var(--editor-accent-soft);
    border-color: color-mix(in srgb, var(--editor-accent) 30%, white);
}

.inspector-shortcuts {
    display: flex;
    margin-top: 14px;
    flex-direction: column;
    gap: 6px;
}

.inspector-shortcuts > .inspector-kicker {
    margin-bottom: 2px;
}

.inspector-shortcuts button {
    display: flex;
    min-height: 44px;
    padding: 8px 10px;
    align-items: center;
    gap: 9px;
    color: var(--editor-accent);
    text-align: left;
    background: var(--editor-panel);
    border: 1px solid var(--editor-border);
    border-radius: 6px;
    cursor: pointer;
}

.inspector-shortcuts button:hover {
    background: var(--editor-accent-soft);
    border-color: var(--editor-border-strong);
}

.inspector-shortcuts button > span {
    display: flex;
    min-width: 0;
    flex: 1;
    flex-direction: column;
}

.inspector-shortcuts strong {
    color: var(--editor-text);
    font-size: 11px;
    font-weight: 650;
}

.inspector-shortcuts small {
    color: var(--editor-text-muted);
    font-size: 9px;
}

.inspector-hint {
    margin: 14px 2px 0;
    color: var(--editor-text-muted);
    font-size: 10px;
    line-height: 1.55;
}

button:focus-visible,
a:focus-visible {
    outline: 2px solid color-mix(in srgb, var(--editor-accent) 45%, white);
    outline-offset: 2px;
}

@media (max-width: 1100px) {
    .inspector-close {
        display: inline-flex;
    }
}
</style>
