<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Blocks,
    ChevronRight,
    ExternalLink,
    FileText,
    Home,
    ListTree,
    LogOut,
    Pencil,
    Plus,
    Trash2,
} from '@lucide/vue';
import type { PropType } from 'vue';
import { ref } from 'vue';

import BlockLibrary from '@/components/Editor/BlockLibrary.vue';
import EditorLayersTree from '@/components/Editor/EditorLayersTree.vue';

type PageSummary = {
    id: number | string;
    title: string;
    slug: string;
    is_homepage: boolean;
};

const blocks = defineModel('blocks', {
    type: Array,
    required: true,
});

defineProps({
    pages: { type: Array as PropType<PageSummary[]>, required: true },
    currentPage: { type: Object as PropType<PageSummary>, required: true },
    blockDefinitions: { type: Array, required: true },
    blockPresets: { type: Array, required: true },
    dashboardUrl: { type: String, required: true },
    logoutUrl: { type: String, required: true },
    liveUrl: { type: String, required: true },
    saveError: { type: String, default: '' },
    collapsed: { type: Boolean, default: false },
});

const emit = defineEmits([
    'create-page',
    'switch-page',
    'rename-page',
    'set-homepage',
    'delete-page',
    'add-block',
    'add-preset',
    'expand',
]);

const activeSection = ref('structure');
const pagesExpanded = ref(true);

const handleCollapsedTabClick = (section: string) => {
    activeSection.value = section;
    emit('expand');
};

const sections = [
    { value: 'structure', label: 'Structure', icon: ListTree },
    { value: 'insert', label: 'Insert', icon: Blocks },
];
</script>

<template>
    <aside class="editor-sidebar">
        <div
            class="sidebar-heading"
            :class="{ 'sidebar-heading-collapsed': collapsed }"
        >
            <div v-if="!collapsed">
                <span class="sidebar-eyebrow">Editing</span>
                <h1>{{ currentPage.title }}</h1>
            </div>
            <a
                v-if="!collapsed"
                :href="liveUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="sidebar-live-link"
                title="Open live page"
                aria-label="Open live page"
            >
                <ExternalLink :size="15" />
            </a>
        </div>

        <nav v-if="!collapsed" class="sidebar-tabs" aria-label="Editor panels">
            <button
                v-for="section in sections"
                :key="section.value"
                type="button"
                :class="{
                    'sidebar-tab-active': activeSection === section.value,
                }"
                :aria-current="
                    activeSection === section.value ? 'page' : undefined
                "
                @click="activeSection = section.value"
            >
                <component :is="section.icon" :size="14" />
                <span>{{ section.label }}</span>
            </button>
        </nav>

        <nav v-else class="sidebar-rail" aria-label="Editor panels">
            <button
                v-for="section in sections"
                :key="section.value"
                type="button"
                class="rail-icon"
                :class="{ 'rail-icon-active': activeSection === section.value }"
                :title="section.label"
                :aria-label="section.label"
                @click="handleCollapsedTabClick(section.value)"
            >
                <component :is="section.icon" :size="16" />
            </button>
        </nav>

        <div v-if="!collapsed" class="sidebar-scroll">
            <Transition name="panel-switch" mode="out-in">
                <section
                    v-if="activeSection === 'structure'"
                    key="structure"
                    class="sidebar-panel"
                >
                    <div class="structure-group">
                        <div class="structure-group-heading">
                            <button
                                type="button"
                                class="structure-toggle"
                                :aria-expanded="pagesExpanded"
                                @click="pagesExpanded = !pagesExpanded"
                            >
                                <ChevronRight
                                    :size="12"
                                    class="structure-chevron"
                                    :class="{
                                        'structure-chevron-open': pagesExpanded,
                                    }"
                                />
                                <span class="panel-kicker">Pages</span>
                                <span class="structure-count">{{
                                    pages.length
                                }}</span>
                            </button>
                            <button
                                type="button"
                                class="heading-action heading-action-small"
                                title="Create page"
                                aria-label="Create page"
                                @click="emit('create-page')"
                            >
                                <Plus :size="14" />
                            </button>
                        </div>

                        <div v-if="pagesExpanded" class="structure-group-body">
                            <TransitionGroup
                                name="list-item"
                                tag="div"
                                class="page-list"
                            >
                                <article
                                    v-for="page in pages"
                                    :key="page.id"
                                    class="page-row hover-lift"
                                    :class="{
                                        'page-row-active':
                                            currentPage.slug === page.slug,
                                    }"
                                >
                                    <button
                                        type="button"
                                        class="page-select"
                                        @click="emit('switch-page', page.slug)"
                                    >
                                        <span class="page-icon">
                                            <Home
                                                v-if="page.is_homepage"
                                                :size="13"
                                            />
                                            <FileText v-else :size="13" />
                                        </span>
                                        <span class="page-copy">
                                            <strong>{{ page.title }}</strong>
                                            <small
                                                >/{{
                                                    page.slug === 'home'
                                                        ? ''
                                                        : page.slug
                                                }}</small
                                            >
                                        </span>
                                        <span
                                            v-if="page.is_homepage"
                                            class="home-badge"
                                            >Home</span
                                        >
                                    </button>

                                    <div class="page-actions">
                                        <button
                                            v-if="!page.is_homepage"
                                            type="button"
                                            title="Set as homepage"
                                            aria-label="Set as homepage"
                                            @click="emit('set-homepage', page)"
                                        >
                                            <Home :size="13" />
                                        </button>
                                        <button
                                            type="button"
                                            title="Rename page"
                                            aria-label="Rename page"
                                            @click="emit('rename-page', page)"
                                        >
                                            <Pencil :size="13" />
                                        </button>
                                        <button
                                            v-if="!page.is_homepage"
                                            type="button"
                                            class="danger-action"
                                            title="Delete page"
                                            aria-label="Delete page"
                                            @click="emit('delete-page', page)"
                                        >
                                            <Trash2 :size="13" />
                                        </button>
                                    </div>
                                </article>
                            </TransitionGroup>

                            <button
                                type="button"
                                class="create-page-button"
                                @click="emit('create-page')"
                            >
                                <Plus :size="14" />
                                <span>Create page</span>
                            </button>
                        </div>
                    </div>

                    <div class="structure-group structure-group-sections">
                        <div class="structure-group-heading">
                            <span class="structure-static-heading">
                                <ListTree :size="12" />
                                <span class="panel-kicker">Sections</span>
                            </span>
                            <button
                                type="button"
                                class="heading-action heading-action-small"
                                title="Insert block"
                                aria-label="Insert block"
                                @click="activeSection = 'insert'"
                            >
                                <Plus :size="14" />
                            </button>
                        </div>

                        <div class="structure-group-body">
                            <EditorLayersTree v-model:blocks="blocks" />
                        </div>
                    </div>
                </section>

                <section v-else key="insert" class="sidebar-panel">
                    <div class="panel-heading">
                        <div>
                            <span class="panel-kicker">Build your page</span>
                            <h2>Block library</h2>
                        </div>
                        <Blocks :size="17" />
                    </div>

                    <BlockLibrary
                        :block-definitions="blockDefinitions"
                        :block-presets="blockPresets"
                        @add-block="emit('add-block', $event)"
                        @add-preset="emit('add-preset', $event)"
                    />
                </section>
            </Transition>
        </div>

        <footer
            class="sidebar-footer"
            :class="{ 'sidebar-footer-collapsed': collapsed }"
        >
            <template v-if="!collapsed">
                <p v-if="saveError" class="save-error" role="alert">
                    {{ saveError }}
                </p>

                <div class="sidebar-account-actions">
                    <Link :href="dashboardUrl">Exit to dashboard</Link>
                    <Link :href="logoutUrl" method="post" as="button">
                        <LogOut :size="12" />
                        <span>Log out</span>
                    </Link>
                </div>
            </template>
        </footer>
    </aside>
</template>

<style scoped>
.editor-sidebar {
    display: flex;
    min-width: 0;
    min-height: 0;
    flex-direction: column;
    color: var(--editor-text);
    background: var(--editor-panel);
    border-right: 1px solid var(--editor-border);
}

.sidebar-heading,
.panel-heading,
.sidebar-account-actions,
.page-select,
.page-actions,
.publish-button,
.create-page-button,
.heading-action,
.sidebar-live-link {
    display: flex;
    align-items: center;
}

.sidebar-heading {
    min-height: 66px;
    padding: 11px 17px;
    justify-content: space-between;
    border-bottom: 1px solid var(--editor-border);
}

.sidebar-eyebrow,
.panel-kicker {
    display: block;
    color: var(--editor-accent);
    font-size: 9px;
    font-weight: 750;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}

.sidebar-heading h1,
.panel-heading h2 {
    margin: 3px 0 0;
    color: var(--editor-text);
    font-size: 13px;
    font-weight: 650;
    line-height: 1.25;
}

.sidebar-live-link,
.heading-action {
    justify-content: center;
    width: 30px;
    height: 30px;
    color: var(--editor-text-muted);
    background: var(--editor-panel-muted);
    border: 1px solid var(--editor-border);
    border-radius: 5px;
    cursor: pointer;
}

.sidebar-live-link:hover,
.heading-action:hover {
    color: var(--editor-accent);
    background: var(--editor-accent-soft);
}

.sidebar-tabs {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    border-bottom: 1px solid var(--editor-border);
}

.sidebar-tabs button {
    display: flex;
    min-width: 0;
    height: 46px;
    align-items: center;
    justify-content: center;
    gap: 5px;
    color: var(--editor-text-muted);
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    background: transparent;
    border: 0;
    border-right: 1px solid var(--editor-border);
    border-bottom: 2px solid transparent;
    cursor: pointer;
}

.sidebar-tabs button:last-child {
    border-right: 0;
}

.sidebar-tabs button:hover {
    color: var(--editor-text);
    background: var(--editor-panel-muted);
}

.sidebar-tabs .sidebar-tab-active {
    color: var(--editor-accent);
    background: var(--editor-accent-soft);
    border-bottom-color: var(--editor-accent);
}

.sidebar-scroll {
    min-height: 0;
    flex: 1;
    overflow-y: auto;
    scrollbar-color: var(--editor-border-strong) transparent;
    scrollbar-width: thin;
}

.sidebar-panel {
    padding: 17px;
}

.structure-group + .structure-group {
    margin-top: 16px;
    padding-top: 14px;
    border-top: 1px solid var(--editor-border);
}

.structure-group-heading {
    display: flex;
    min-height: 28px;
    margin-bottom: 8px;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
}

.structure-toggle {
    display: inline-flex;
    min-width: 0;
    padding: 2px 4px 2px 0;
    align-items: center;
    gap: 5px;
    background: transparent;
    border: 0;
    border-radius: 4px;
    cursor: pointer;
}

.structure-toggle:hover .panel-kicker {
    color: var(--editor-text);
}

.structure-chevron {
    color: var(--editor-text-muted);
    transition: transform 140ms ease;
}

.structure-chevron-open {
    transform: rotate(90deg);
}

.structure-count {
    padding: 1px 6px;
    color: var(--editor-text-muted);
    font-size: 8.5px;
    font-weight: 700;
    background: var(--editor-panel-muted);
    border: 1px solid var(--editor-border);
    border-radius: 9999px;
}

.structure-static-heading {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: var(--editor-text-muted);
}

.heading-action-small {
    width: 25px;
    height: 25px;
}

.panel-heading {
    min-height: 39px;
    margin-bottom: 17px;
    padding-bottom: 11px;
    justify-content: space-between;
    color: var(--editor-text-muted);
    border-bottom: 1px solid var(--editor-border);
}

.selection-badge,
.home-badge {
    color: var(--editor-accent);
    font-size: 8px;
    font-weight: 750;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    background: rgb(97 121 158 / 12%);
    border: 1px solid rgb(125 151 192 / 22%);
    border-radius: 9999px;
}

.selection-badge {
    padding: 3px 7px;
}
.home-badge {
    padding: 2px 5px;
}

.page-list {
    display: flex;
    flex-direction: column;
    gap: 7px;
}

.page-row {
    display: flex;
    min-width: 0;
    align-items: center;
    background: var(--editor-panel);
    border: 1px solid var(--editor-border);
    border-radius: 5px;
}

.page-row:hover {
    border-color: var(--editor-border-strong);
}
.page-row-active {
    background: var(--editor-accent-soft);
    border-color: color-mix(in srgb, var(--editor-accent) 35%, white);
}

.page-select {
    min-width: 0;
    min-height: 48px;
    flex: 1;
    padding: 7px 8px;
    gap: 8px;
    color: inherit;
    text-align: left;
    background: transparent;
    border: 0;
    cursor: pointer;
}

.page-icon {
    display: inline-flex;
    color: var(--editor-accent);
}

.page-copy {
    display: flex;
    min-width: 0;
    flex: 1;
    flex-direction: column;
}

.page-copy strong {
    overflow: hidden;
    color: var(--editor-text);
    font-size: 11px;
    font-weight: 600;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.page-copy small {
    overflow: hidden;
    color: var(--editor-text-muted);
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 9px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.page-actions {
    padding-right: 6px;
    gap: 2px;
}

.page-actions button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 25px;
    height: 25px;
    color: var(--editor-text-muted);
    background: transparent;
    border: 0;
    border-radius: 4px;
    cursor: pointer;
}

.page-actions button:hover {
    color: var(--editor-accent);
    background: var(--editor-accent-soft);
}
.page-actions .danger-action:hover {
    color: #fca5a5;
    background: rgb(127 29 29 / 25%);
}

.create-page-button {
    width: 100%;
    height: 36px;
    margin-top: 11px;
    justify-content: center;
    gap: 6px;
    color: var(--editor-accent);
    font-size: 10px;
    font-weight: 650;
    background: transparent;
    border: 1px dashed var(--editor-border-strong);
    border-radius: 5px;
    cursor: pointer;
}

.create-page-button:hover {
    color: var(--editor-accent);
    background: var(--editor-accent-soft);
    border-color: color-mix(in srgb, var(--editor-accent) 35%, white);
}

.sidebar-footer {
    padding: 14px 16px 12px;
    background: var(--editor-panel);
    border-top: 1px solid var(--editor-border);
    box-shadow: 0 -8px 24px rgb(24 24 27 / 5%);
}

.save-error {
    margin: 0 0 8px;
    padding: 7px 9px;
    color: #fecaca;
    font-size: 10px;
    line-height: 1.35;
    background: rgb(127 29 29 / 22%);
    border: 1px solid rgb(248 113 113 / 20%);
    border-radius: 4px;
}

.publish-button {
    width: 100%;
    height: 45px;
    justify-content: center;
    gap: 8px;
    color: #ffffff;
    font-size: 12px;
    font-weight: 750;
    background: var(--editor-accent);
    border: 1px solid var(--editor-accent);
    border-radius: 5px;
    cursor: pointer;
    box-shadow: 0 8px 20px rgb(79 70 229 / 18%);
}

.publish-button:hover:not(:disabled) {
    background: color-mix(in srgb, var(--editor-accent) 88%, black);
}
.publish-button:disabled {
    cursor: not-allowed;
    opacity: 0.45;
}

.sidebar-account-actions {
    margin-top: 9px;
    justify-content: space-between;
}

.sidebar-account-actions a,
.sidebar-account-actions button {
    display: inline-flex;
    align-items: center;
    padding: 4px 2px;
    gap: 4px;
    color: var(--editor-text-muted);
    font-size: 9px;
    font-weight: 600;
    text-decoration: none;
    background: transparent;
    border: 0;
    cursor: pointer;
}

.sidebar-account-actions a:hover,
.sidebar-account-actions button:hover {
    color: var(--editor-text);
}

button:focus-visible,
a:focus-visible {
    outline: 2px solid color-mix(in srgb, var(--editor-accent) 45%, white);
    outline-offset: 2px;
}

.editor-sidebar :deep([class*='bg-slate-8']),
.editor-sidebar :deep([class*='bg-slate-9']) {
    background-color: var(--editor-panel-muted) !important;
}

.editor-sidebar :deep([class*='border-slate-6']),
.editor-sidebar :deep([class*='border-slate-7']),
.editor-sidebar :deep([class*='border-slate-8']) {
    border-color: var(--editor-border) !important;
}

.editor-sidebar :deep([class*='text-slate-2']),
.editor-sidebar :deep([class*='text-slate-3']),
.editor-sidebar :deep([class*='text-slate-4']) {
    color: var(--editor-text) !important;
}

.editor-sidebar :deep([class*='text-slate-5']) {
    color: var(--editor-text-muted) !important;
}

.editor-sidebar :deep(input:not([type='checkbox']):not([type='color'])),
.editor-sidebar :deep(select),
.editor-sidebar :deep(textarea) {
    color: var(--editor-text) !important;
    background: var(--editor-panel) !important;
    border-color: var(--editor-border-strong) !important;
    box-shadow: 0 1px 2px rgb(24 24 27 / 4%);
}

.editor-sidebar :deep(input:focus),
.editor-sidebar :deep(select:focus),
.editor-sidebar :deep(textarea:focus) {
    border-color: var(--editor-accent) !important;
    box-shadow: 0 0 0 3px rgb(79 70 229 / 10%);
}

.sidebar-heading-collapsed {
    min-height: 56px;
    justify-content: center;
    padding: 0;
}

.sidebar-rail {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 8px 0;
    border-bottom: 1px solid var(--editor-border);
}

.rail-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    color: var(--editor-text-muted);
    background: transparent;
    border: 0;
    border-radius: 6px;
    cursor: pointer;
    transition:
        color 150ms ease,
        background-color 150ms ease;
}

.rail-icon:hover {
    color: var(--editor-text);
    background: var(--editor-panel-muted);
}

.rail-icon-active {
    color: var(--editor-accent);
    background: var(--editor-accent-soft);
}

.sidebar-footer-collapsed {
    display: flex;
    justify-content: center;
    padding: 10px 8px;
}

.publish-button-compact {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    color: #ffffff;
    background: var(--editor-accent);
    border: 1px solid var(--editor-accent);
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 150ms ease;
}

.publish-button-compact:hover:not(:disabled) {
    background: color-mix(in srgb, var(--editor-accent) 88%, black);
}

.publish-button-compact:disabled {
    cursor: not-allowed;
    opacity: 0.45;
}
</style>
