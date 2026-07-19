<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ExternalLink,
    FileText,
    Images,
    Loader2,
    Monitor,
    Palette,
    PanelLeft,
    PanelRight,
    PanelTop,
    Redo2,
    Smartphone,
    Tablet,
    Undo2,
    Upload,
} from '@lucide/vue';
import { computed } from 'vue';

interface EditorPageOption {
    id: number;
    slug: string;
    title: string;
    is_homepage?: boolean;
}

const props = withDefaults(
    defineProps<{
        dashboardUrl: string;
        liveUrl: string;
        pageTitle?: string;
        tenantName?: string;
        viewMode: string;
        canUndo?: boolean;
        canRedo?: boolean;
        saveState?: string;
        commercePreview?: { options?: Array<Record<string, string>> };
        pages?: EditorPageOption[];
        currentPageSlug?: string;
        sidebarCollapsed?: boolean;
        inspectorCollapsed?: boolean;
        workspaceMode?: string;
        isPublishing?: boolean;
        isSaving?: boolean;
        saveError?: string;
    }>(),
    {
        pageTitle: 'Untitled page',
        tenantName: 'Workspace',
        canUndo: false,
        canRedo: false,
        saveState: 'saved',
        commercePreview: () => ({}),
        pages: () => [],
        currentPageSlug: '',
        sidebarCollapsed: false,
        inspectorCollapsed: false,
        workspaceMode: 'pages',
        isPublishing: false,
        isSaving: false,
        saveError: '',
    },
);

const emit = defineEmits([
    'toggle-sidebar',
    'toggle-inspector',
    'update:view-mode',
    'undo',
    'redo',
    'update:commerce-preview',
    'switch-page',
    'update:workspace-mode',
    'open-media',
    'publish',
]);

const productPreviewOptions = computed(() =>
    (props.commercePreview?.options || []).filter(
        (option) => option.resource === 'product',
    ),
);

const viewModes = [
    { value: 'desktop', label: 'Desktop view', icon: Monitor },
    { value: 'tablet', label: 'Tablet view', icon: Tablet },
    { value: 'mobile', label: 'Mobile view', icon: Smartphone },
];

const workspaceModes = [
    { value: 'pages', label: 'Pages', icon: FileText },
    { value: 'navigation', label: 'Navigation', icon: PanelTop },
    { value: 'theme', label: 'Theme', icon: Palette },
];
</script>

<template>
    <header class="editor-topbar">
        <div class="topbar-identity">
            <button
                type="button"
                class="sidebar-toggle"
                :aria-label="
                    sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'
                "
                :title="
                    sidebarCollapsed
                        ? 'Expand sidebar (Ctrl+\\)'
                        : 'Collapse sidebar (Ctrl+\\)'
                "
                @click="emit('toggle-sidebar')"
            >
                <PanelLeft
                    :size="16"
                    class="collapse-icon-rotate"
                    :class="{ 'is-collapsed': sidebarCollapsed }"
                />
            </button>

            <Link :href="dashboardUrl" class="back-link">
                <ArrowLeft :size="14" />
                <span>Back</span>
            </Link>

            <div class="builder-title">
                <span class="builder-kicker">Website builder</span>
                <label
                    v-if="workspaceMode === 'pages'"
                    class="builder-page-select"
                >
                    <span class="sr-only">Edit page</span>
                    <select
                        :value="currentPageSlug"
                        aria-label="Edit page"
                        @change="
                            emit(
                                'switch-page',
                                ($event.target as HTMLSelectElement).value,
                            )
                        "
                    >
                        <option
                            v-for="page in pages"
                            :key="page.id"
                            :value="page.slug"
                        >
                            {{ tenantName }} / {{ page.title
                            }}{{ page.is_homepage ? ' · Homepage' : '' }}
                        </option>
                    </select>
                </label>
                <span v-else class="builder-workspace-label">
                    {{ tenantName }} / {{ workspaceMode }}
                </span>
            </div>
        </div>

        <div class="preview-tools">
            <nav class="workspace-switcher" aria-label="Builder workspace">
                <button
                    v-for="workspace in workspaceModes"
                    :key="workspace.value"
                    type="button"
                    class="workspace-button"
                    :class="{
                        'workspace-button-active':
                            workspaceMode === workspace.value,
                    }"
                    :aria-current="
                        workspaceMode === workspace.value ? 'page' : undefined
                    "
                    @click="emit('update:workspace-mode', workspace.value)"
                >
                    <component :is="workspace.icon" :size="13" />
                    <span>{{ workspace.label }}</span>
                </button>
                <button
                    type="button"
                    class="workspace-button"
                    @click="emit('open-media')"
                >
                    <Images :size="13" />
                    <span>Media</span>
                </button>
            </nav>

            <label
                v-if="workspaceMode === 'pages' && productPreviewOptions.length"
                class="resource-preview"
            >
                <span>Fixture product</span>
                <select
                    :value="commercePreview?.selected || ''"
                    @change="
                        emit(
                            'update:commerce-preview',
                            ($event.target as HTMLSelectElement).value,
                        )
                    "
                >
                    <option value="">Page binding</option>
                    <option
                        v-for="option in productPreviewOptions"
                        :key="option.source"
                        :value="option.source"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </label>
            <div
                v-if="workspaceMode === 'pages'"
                class="viewport-switcher"
                aria-label="Preview size"
            >
                <button
                    v-for="mode in viewModes"
                    :key="mode.value"
                    type="button"
                    class="viewport-button"
                    :class="{
                        'viewport-button-active': viewMode === mode.value,
                    }"
                    :aria-pressed="viewMode === mode.value"
                    @click="emit('update:view-mode', mode.value)"
                >
                    <component :is="mode.icon" :size="14" />
                    <span>{{ mode.label }}</span>
                </button>
            </div>
        </div>

        <div class="topbar-actions">
            <div
                class="save-state"
                :class="`save-state-${saveState}`"
                role="status"
                aria-live="polite"
            >
                <span class="save-state-dot"></span>
                <span>{{
                    saveState === 'saving'
                        ? 'Saving'
                        : saveState === 'error'
                          ? 'Save failed'
                          : 'Saved'
                }}</span>
            </div>

            <div class="history-actions">
                <button
                    type="button"
                    class="icon-action"
                    :disabled="!canUndo"
                    title="Undo (Ctrl+Z)"
                    aria-label="Undo"
                    @click="emit('undo')"
                >
                    <Undo2 :size="15" />
                </button>
                <button
                    type="button"
                    class="icon-action"
                    :disabled="!canRedo"
                    title="Redo (Ctrl+Shift+Z)"
                    aria-label="Redo"
                    @click="emit('redo')"
                >
                    <Redo2 :size="15" />
                </button>
                <button
                    v-if="workspaceMode === 'pages'"
                    type="button"
                    class="icon-action"
                    :class="{ 'icon-action-active': !inspectorCollapsed }"
                    :title="
                        inspectorCollapsed ? 'Show inspector' : 'Hide inspector'
                    "
                    :aria-label="
                        inspectorCollapsed ? 'Show inspector' : 'Hide inspector'
                    "
                    @click="emit('toggle-inspector')"
                >
                    <PanelRight :size="15" />
                </button>
            </div>

            <button
                type="button"
                class="publish-action"
                :disabled="isPublishing || isSaving || !!saveError"
                @click="emit('publish')"
            >
                <Loader2
                    v-if="isPublishing"
                    :size="13"
                    class="animate-spin motion-reduce:animate-none"
                />
                <Upload v-else :size="13" />
                <span>{{ isPublishing ? 'Publishing' : 'Publish' }}</span>
            </button>

            <a
                :href="liveUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="live-link"
            >
                <span>Open live link</span>
                <ExternalLink :size="13" />
            </a>
        </div>
    </header>
</template>

<style scoped>
.editor-topbar {
    position: relative;
    z-index: 50;
    display: grid;
    grid-template-columns: minmax(260px, 1fr) auto minmax(260px, 1fr);
    align-items: center;
    min-height: 54px;
    padding: 0 12px 0 10px;
    color: var(--editor-text);
    background: var(--editor-panel);
    border-bottom: 1px solid var(--editor-border);
    box-shadow: 0 1px 3px rgb(24 24 27 / 4%);
}

.topbar-identity,
.topbar-actions,
.history-actions,
.viewport-switcher,
.workspace-switcher,
.preview-tools,
.back-link,
.live-link,
.save-state {
    display: flex;
    align-items: center;
}

.topbar-identity {
    min-width: 0;
    gap: 10px;
}

.sidebar-toggle {
    display: none;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    color: var(--editor-text-muted);
    background: var(--editor-panel-muted);
    border: 1px solid var(--editor-border);
    border-radius: 5px;
    cursor: pointer;
}

.back-link {
    height: 30px;
    padding: 0 10px;
    gap: 6px;
    color: var(--editor-text);
    font-size: 11px;
    font-weight: 650;
    letter-spacing: 0.04em;
    text-decoration: none;
    text-transform: uppercase;
    border: 1px solid var(--editor-border-strong);
    border-radius: 6px;
    transition:
        border-color 150ms ease,
        color 150ms ease,
        background 150ms ease;
}

.back-link:hover,
.back-link:focus-visible {
    color: var(--editor-accent);
    background: var(--editor-accent-soft);
    border-color: color-mix(in srgb, var(--editor-accent) 35%, white);
}

.builder-title {
    display: flex;
    min-width: 0;
    flex-direction: column;
    padding-left: 8px;
    border-left: 1px solid var(--editor-border);
}

.builder-kicker {
    color: var(--editor-accent);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.15em;
    text-transform: uppercase;
}

.builder-page-select select {
    max-width: 240px;
    padding: 0;
    color: var(--editor-text-muted);
    background: transparent;
    border: 0;
    font-size: 10px;
    cursor: pointer;
}

.builder-workspace-label {
    color: var(--editor-text-muted);
    font-size: 10px;
    text-transform: capitalize;
}

.builder-page-select select:focus-visible {
    outline: 1px solid #93c5fd;
    outline-offset: 2px;
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.viewport-switcher {
    padding: 3px;
    gap: 2px;
    background: var(--editor-panel-muted);
    border: 1px solid var(--editor-border);
    border-radius: 6px;
}

.workspace-switcher {
    padding: 3px;
    gap: 2px;
    background: var(--editor-panel-muted);
    border: 1px solid var(--editor-border);
    border-radius: 6px;
}

.workspace-button {
    display: inline-flex;
    align-items: center;
    height: 28px;
    padding: 0 9px;
    gap: 5px;
    color: var(--editor-text-muted);
    font-size: 10px;
    font-weight: 650;
    background: transparent;
    border: 0;
    border-radius: 4px;
    cursor: pointer;
}

.workspace-button:hover,
.workspace-button-active {
    color: var(--editor-accent);
    background: var(--editor-panel);
}
.preview-tools {
    justify-self: center;
    gap: 8px;
}
.resource-preview {
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--editor-text-muted);
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}
.resource-preview select {
    max-width: 150px;
    height: 30px;
    padding: 0 8px;
    color: var(--editor-text);
    background: var(--editor-panel);
    border: 1px solid var(--editor-border);
    border-radius: 5px;
    font-size: 10px;
}

.viewport-button {
    display: inline-flex;
    align-items: center;
    height: 28px;
    padding: 0 12px;
    gap: 7px;
    color: var(--editor-text-muted);
    font-size: 11px;
    font-weight: 600;
    background: transparent;
    border: 0;
    border-radius: 4px;
    cursor: pointer;
    transition:
        color 150ms ease,
        background 150ms ease;
}

.viewport-button:hover {
    color: var(--editor-text);
}

.viewport-button-active {
    color: var(--editor-accent);
    background: var(--editor-panel);
    box-shadow:
        inset 0 0 0 1px color-mix(in srgb, var(--editor-accent) 35%, white),
        0 1px 3px rgb(24 24 27 / 8%);
}

.topbar-actions {
    justify-self: end;
    gap: 10px;
}

.save-state {
    gap: 6px;
    color: var(--editor-text-muted);
    font-size: 10px;
    font-weight: 650;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.save-state-dot {
    width: 7px;
    height: 7px;
    background: #22c55e;
    border-radius: 9999px;
    box-shadow: 0 0 8px rgb(34 197 94 / 45%);
}

.save-state-saving .save-state-dot {
    background: #f59e0b;
    box-shadow: 0 0 8px rgb(245 158 11 / 45%);
    animation: pulse 1.2s ease-in-out infinite;
}

.save-state-error {
    color: #fca5a5;
}

.save-state-error .save-state-dot {
    background: #ef4444;
    box-shadow: 0 0 8px rgb(239 68 68 / 45%);
}

.history-actions {
    gap: 3px;
}

.icon-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    color: var(--editor-text-muted);
    background: var(--editor-panel-muted);
    border: 1px solid var(--editor-border);
    border-radius: 5px;
    cursor: pointer;
}

.icon-action:hover:not(:disabled) {
    color: var(--editor-accent);
    background: var(--editor-accent-soft);
}

.icon-action:disabled {
    color: var(--editor-border-strong);
    cursor: not-allowed;
    opacity: 0.7;
}

.icon-action-active {
    color: var(--editor-accent);
    background: var(--editor-accent-soft);
    border-color: color-mix(in srgb, var(--editor-accent) 30%, white);
}

.live-link {
    height: 30px;
    padding: 0 11px;
    gap: 6px;
    color: #ffffff;
    font-size: 11px;
    font-weight: 650;
    text-decoration: none;
    background: var(--editor-text);
    border: 1px solid var(--editor-text);
    border-radius: 5px;
    transition:
        background 150ms ease,
        border-color 150ms ease;
}

.publish-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 30px;
    padding: 0 11px;
    gap: 6px;
    color: #ffffff;
    font-size: 11px;
    font-weight: 650;
    background: var(--editor-accent);
    border: 1px solid var(--editor-accent);
    border-radius: 5px;
    cursor: pointer;
}

.publish-action:hover:not(:disabled) {
    filter: brightness(0.9);
}

.publish-action:disabled {
    cursor: not-allowed;
    opacity: 0.45;
}

.live-link:hover,
.live-link:focus-visible {
    background: #27272a;
    border-color: #27272a;
}

button:focus-visible,
a:focus-visible {
    outline: 2px solid color-mix(in srgb, var(--editor-accent) 45%, white);
    outline-offset: 2px;
}

@keyframes pulse {
    50% {
        opacity: 0.45;
    }
}

@media (prefers-reduced-motion: reduce) {
    .save-state-saving .save-state-dot {
        animation: none;
    }
}

@media (max-width: 1180px) {
    .editor-topbar {
        grid-template-columns: minmax(220px, 1fr) auto minmax(220px, 1fr);
    }

    .builder-page-select,
    .builder-workspace-label,
    .save-state {
        display: none;
    }

    .viewport-button span {
        display: none;
    }

    .workspace-button span {
        display: none;
    }

    .workspace-button {
        width: 30px;
        padding: 0;
        justify-content: center;
    }

    .viewport-button {
        width: 32px;
        padding: 0;
        justify-content: center;
    }
}

@media (max-width: 820px) {
    .editor-topbar {
        grid-template-columns: 1fr auto;
    }

    .sidebar-toggle {
        display: inline-flex;
    }

    .builder-title,
    .history-actions {
        display: none;
    }

    .preview-tools {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }

    .viewport-switcher {
        display: none;
    }

    .resource-preview {
        display: none;
    }

    .live-link span {
        display: none;
    }

    .publish-action span {
        display: none;
    }

    .publish-action {
        width: 32px;
        padding: 0;
    }

    .live-link {
        width: 32px;
        padding: 0;
        justify-content: center;
    }
}

@media (max-width: 520px) {
    .back-link span {
        display: none;
    }
    .back-link {
        width: 32px;
        padding: 0;
        justify-content: center;
    }
}
</style>
