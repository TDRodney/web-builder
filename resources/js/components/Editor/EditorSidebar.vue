<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Blocks,
    ExternalLink,
    FileText,
    Home,
    LogOut,
    PanelTop,
    Pencil,
    Plus,
    Settings2,
    SlidersHorizontal,
    Trash2,
    Upload,
} from '@lucide/vue';
import type { PropType } from 'vue';
import { computed, ref, watch } from 'vue';

import BlockLibrary from '@/components/Editor/BlockLibrary.vue';
import ContentInspector from '@/components/Editor/ContentInspector.vue';
import NavigationSettings from '@/components/Editor/NavigationSettings.vue';

type PageSummary = {
    id: number | string;
    title: string;
    slug: string;
    is_homepage: boolean;
};

const props = defineProps({
    pages: { type: Array as PropType<PageSummary[]>, required: true },
    currentPage: { type: Object as PropType<PageSummary>, required: true },
    selectedBlock: { type: Object, default: null },
    activeBlockDefinition: { type: Object, default: null },
    navigationConfig: { type: Object, required: true },
    tenant: { type: Object, required: true },
    blockDefinitions: { type: Array, required: true },
    blockPresets: { type: Array, required: true },
    dashboardUrl: { type: String, required: true },
    logoutUrl: { type: String, required: true },
    liveUrl: { type: String, required: true },
    isPublishing: { type: Boolean, default: false },
    isSaving: { type: Boolean, default: false },
    saveError: { type: String, default: '' },
});

const emit = defineEmits([
    'create-page',
    'switch-page',
    'rename-page',
    'set-homepage',
    'delete-page',
    'open-media-picker',
    'add-block',
    'add-preset',
    'publish',
]);

const activeSection = ref('content');

const sections = [
    { value: 'content', label: 'Content', icon: SlidersHorizontal },
    { value: 'blocks', label: 'Blocks', icon: Blocks },
    { value: 'pages', label: 'Pages', icon: FileText },
    { value: 'site', label: 'Site', icon: PanelTop },
];

const selectedBlockLabel = computed(
    () =>
        props.activeBlockDefinition?.label ||
        props.selectedBlock?.type ||
        'Nothing selected',
);

watch(
    () => props.selectedBlock?.id,
    (selectedId) => {
        if (selectedId) {
            activeSection.value = 'content';
        }
    },
);
</script>

<template>
    <aside class="editor-sidebar">
        <div class="sidebar-heading">
            <div>
                <span class="sidebar-eyebrow">Editing</span>
                <h1>{{ currentPage.title }}</h1>
            </div>
            <a
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

        <nav class="sidebar-tabs" aria-label="Editor panels">
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

        <div class="sidebar-scroll">
            <section v-if="activeSection === 'content'" class="sidebar-panel">
                <div class="panel-heading">
                    <div>
                        <span class="panel-kicker">Content inspector</span>
                        <h2>{{ selectedBlockLabel }}</h2>
                    </div>
                    <span v-if="selectedBlock" class="selection-badge"
                        >Selected</span
                    >
                </div>

                <ContentInspector
                    :selected-block="selectedBlock"
                    :active-block-definition="activeBlockDefinition"
                    @open-media-picker="emit('open-media-picker', $event)"
                />
            </section>

            <section
                v-else-if="activeSection === 'blocks'"
                class="sidebar-panel"
            >
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

            <section
                v-else-if="activeSection === 'pages'"
                class="sidebar-panel"
            >
                <div class="panel-heading">
                    <div>
                        <span class="panel-kicker">Site structure</span>
                        <h2>Pages</h2>
                    </div>
                    <button
                        type="button"
                        class="heading-action"
                        title="Create page"
                        aria-label="Create page"
                        @click="emit('create-page')"
                    >
                        <Plus :size="16" />
                    </button>
                </div>

                <div class="page-list">
                    <article
                        v-for="page in pages"
                        :key="page.id"
                        class="page-row"
                        :class="{
                            'page-row-active': currentPage.slug === page.slug,
                        }"
                    >
                        <button
                            type="button"
                            class="page-select"
                            @click="emit('switch-page', page.slug)"
                        >
                            <span class="page-icon">
                                <Home v-if="page.is_homepage" :size="13" />
                                <FileText v-else :size="13" />
                            </span>
                            <span class="page-copy">
                                <strong>{{ page.title }}</strong>
                                <small
                                    >/{{
                                        page.slug === 'home' ? '' : page.slug
                                    }}</small
                                >
                            </span>
                            <span v-if="page.is_homepage" class="home-badge"
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
                </div>

                <button
                    type="button"
                    class="create-page-button"
                    @click="emit('create-page')"
                >
                    <Plus :size="14" />
                    <span>Create page</span>
                </button>
            </section>

            <section v-else class="sidebar-panel">
                <div class="panel-heading">
                    <div>
                        <span class="panel-kicker">Shared site settings</span>
                        <h2>Navigation</h2>
                    </div>
                    <Settings2 :size="17" />
                </div>

                <NavigationSettings
                    :navigation-config="navigationConfig"
                    :pages="pages"
                    :tenant="tenant"
                />
            </section>
        </div>

        <footer class="sidebar-footer">
            <p v-if="saveError" class="save-error" role="alert">
                {{ saveError }}
            </p>

            <button
                type="button"
                class="publish-button"
                :disabled="isPublishing || isSaving || !!saveError"
                @click="emit('publish')"
            >
                <Upload :size="15" />
                <span>{{
                    isPublishing ? 'Publishing…' : 'Publish interface'
                }}</span>
            </button>

            <div class="sidebar-account-actions">
                <Link :href="dashboardUrl">Exit to dashboard</Link>
                <Link :href="logoutUrl" method="post" as="button">
                    <LogOut :size="12" />
                    <span>Log out</span>
                </Link>
            </div>
        </footer>
    </aside>
</template>

<style scoped>
.editor-sidebar {
    display: flex;
    min-width: 0;
    min-height: 0;
    flex-direction: column;
    color: #d4d4d8;
    background: #101011;
    border-right: 1px solid #29292c;
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
    border-bottom: 1px solid #252527;
}

.sidebar-eyebrow,
.panel-kicker {
    display: block;
    color: #7c8eae;
    font-size: 9px;
    font-weight: 750;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}

.sidebar-heading h1,
.panel-heading h2 {
    margin: 3px 0 0;
    color: #f4f4f5;
    font-size: 13px;
    font-weight: 650;
    line-height: 1.25;
}

.sidebar-live-link,
.heading-action {
    justify-content: center;
    width: 30px;
    height: 30px;
    color: #a1a1aa;
    background: #18181a;
    border: 1px solid #303033;
    border-radius: 5px;
    cursor: pointer;
}

.sidebar-live-link:hover,
.heading-action:hover {
    color: #ffffff;
    background: #262629;
}

.sidebar-tabs {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    border-bottom: 1px solid #252527;
}

.sidebar-tabs button {
    display: flex;
    min-width: 0;
    height: 46px;
    align-items: center;
    justify-content: center;
    gap: 5px;
    color: #71717a;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    background: transparent;
    border: 0;
    border-right: 1px solid #202022;
    border-bottom: 2px solid transparent;
    cursor: pointer;
}

.sidebar-tabs button:last-child {
    border-right: 0;
}

.sidebar-tabs button:hover {
    color: #d4d4d8;
    background: #151517;
}

.sidebar-tabs .sidebar-tab-active {
    color: #ffffff;
    background: #171719;
    border-bottom-color: #9db3d6;
}

.sidebar-scroll {
    min-height: 0;
    flex: 1;
    overflow-y: auto;
    scrollbar-color: #3f3f46 transparent;
    scrollbar-width: thin;
}

.sidebar-panel {
    padding: 17px;
}

.panel-heading {
    min-height: 39px;
    margin-bottom: 17px;
    padding-bottom: 11px;
    justify-content: space-between;
    color: #71717a;
    border-bottom: 1px solid #29292c;
}

.selection-badge,
.home-badge {
    color: #a9bad5;
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
    background: #171719;
    border: 1px solid #2b2b2e;
    border-radius: 5px;
}

.page-row:hover {
    border-color: #3f3f46;
}
.page-row-active {
    background: #1d222a;
    border-color: #5b6d88;
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
    color: #7c8eae;
}

.page-copy {
    display: flex;
    min-width: 0;
    flex: 1;
    flex-direction: column;
}

.page-copy strong {
    overflow: hidden;
    color: #e4e4e7;
    font-size: 11px;
    font-weight: 600;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.page-copy small {
    overflow: hidden;
    color: #71717a;
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
    color: #71717a;
    background: transparent;
    border: 0;
    border-radius: 4px;
    cursor: pointer;
}

.page-actions button:hover {
    color: #ffffff;
    background: #29292c;
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
    color: #a9bad5;
    font-size: 10px;
    font-weight: 650;
    background: transparent;
    border: 1px dashed #3f4652;
    border-radius: 5px;
    cursor: pointer;
}

.create-page-button:hover {
    color: #ffffff;
    background: #171a1f;
    border-color: #64748b;
}

.sidebar-footer {
    padding: 14px 16px 12px;
    background: #0d0d0e;
    border-top: 1px solid #29292c;
    box-shadow: 0 -12px 30px rgb(0 0 0 / 22%);
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
    color: #101011;
    font-size: 12px;
    font-weight: 750;
    background: #f4f4f5;
    border: 1px solid #ffffff;
    border-radius: 5px;
    cursor: pointer;
    box-shadow: 0 8px 24px rgb(0 0 0 / 28%);
}

.publish-button:hover:not(:disabled) {
    background: #ffffff;
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
    color: #71717a;
    font-size: 9px;
    font-weight: 600;
    text-decoration: none;
    background: transparent;
    border: 0;
    cursor: pointer;
}

.sidebar-account-actions a:hover,
.sidebar-account-actions button:hover {
    color: #d4d4d8;
}

button:focus-visible,
a:focus-visible {
    outline: 2px solid #93c5fd;
    outline-offset: 2px;
}
</style>
