<script setup>
import { useHttp, Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, provide, nextTick } from 'vue';

import MediaPicker from '@/components/MediaPicker.vue';
import CreatePageModal from '@/components/Editor/CreatePageModal.vue';
import RenamePageModal from '@/components/Editor/RenamePageModal.vue';
import EditorCanvasViewport from '@/components/Editor/EditorCanvasViewport.vue';
import EditorSidebar from '@/components/Editor/EditorSidebar.vue';
import EditorTopbar from '@/components/Editor/EditorTopbar.vue';
import { getBlockDefinition, blockComponents } from '@/lib/blockRegistry';
import { blockPresets } from '@/lib/blockPresets';
import { commerceHydrationKey, emptyCommerceHydration } from '@/lib/commerce';
import { useTheme } from '@/lib/theme';
import { Toaster } from '@/components/ui/sonner';
import { toast } from 'vue-sonner';

provide('blockRegistry', blockComponents);

const props = defineProps({
    tenant: Object,
    page: Object,
    pages: Array,
    page_layouts: { type: Array, default: () => [] },
    commerce_hydration: { type: Object, default: () => emptyCommerceHydration },
    commerce_preview: { type: Object, default: () => ({}) },
    urls: Object,
});

provide(
    commerceHydrationKey,
    computed(() => props.commerce_hydration || emptyCommerceHydration),
);

const { cssVars: themeVars, fontUrl } = useTheme(
    () => props.tenant?.theme_config,
);

const page = usePage();
const blockDefinitions = computed(() => {
    const definitions = page.props.blocksConfig?.definitions || {};
    return Array.isArray(definitions)
        ? definitions
        : Object.values(definitions);
});

const addPreset = (preset) => {
    const clonedBlocks = preset.blocks.map(generateNewIds);
    clonedBlocks.forEach((block) => {
        blocks.value.push(block);
    });
    saveCanvasState();
    toast.success(`Preset "${preset.label}" added to page`);
};

// Navigation config state
const navigationConfig = ref(
    props.tenant?.navigation_config || {
        header: {
            showLogo: true,
            items: [],
            ctaButton: { show: false, label: 'Contact', slug: 'home' },
        },
        footer: {
            copyright: 'Â© 2026 ' + (props.tenant?.subdomain || 'My Workspace'),
        },
    },
);

// Seed some initial demo data if the draft is empty so you have blocks to see instantly
const blocks = ref(
    props.page.draft_config || [
        {
            id: 'hero-' + Date.now(),
            type: 'HeroBlock',
            props: {
                padding: 40,
                backgroundColor: '#ffffff',
                headline: 'Welcome to your Site',
                subheadline: 'Built with our engine.',
            },
            children: [],
        },
        {
            id: 'feat-' + Date.now(),
            type: 'FeatureBlock',
            props: {
                padding: 20,
                backgroundColor: '#f8fafc',
                title: 'Blazing Fast Performance',
                body: '60fps reactive customization rendering.',
            },
            children: [],
        },
    ],
);

const selectedNode = ref(null);
const selectedBlock = selectedNode;
provide('selectedBlock', selectedBlock);

provide('canvasSelection', {
    selectedNode,
    selectNode: (node) => {
        if (node && !node.props) {
            node.props = { padding: 20, backgroundColor: '#ffffff' };
        }

        selectedNode.value = node;
    },
});

const isDragging = ref(false);
provide('isDragging', isDragging);
provide('isEditable', true);

const viewMode = ref('desktop');
const sidebarOpen = ref(false);

const canvasMaxWidth = computed(() => {
    switch (viewMode.value) {
        case 'mobile':
            return '375px';
        case 'tablet':
            return '768px';
        default:
            return '100%';
    }
});

const updateCommercePreview = (source) => {
    router.get(
        window.location.pathname,
        {
            page: props.page.slug,
            commerce_preview: source || undefined,
        },
        {
            only: ['commerce_hydration', 'commerce_preview'],
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

// History management state
const undoStack = ref([]);
const redoStack = ref([]);
let isTraveling = false;

const snapshotState = () => {
    return JSON.parse(JSON.stringify(blocks.value));
};

let lastSavedState = snapshotState();

const undo = () => {
    if (undoStack.value.length === 0) {
        return;
    }

    isTraveling = true;
    redoStack.value.push(snapshotState());
    const prevState = undoStack.value.pop();
    blocks.value = prevState;
    lastSavedState = JSON.parse(JSON.stringify(prevState));
    http.draft_config = prevState;
    queueSave();
    nextTick(() => {
        isTraveling = false;
    });
};

const redo = () => {
    if (redoStack.value.length === 0) {
        return;
    }

    isTraveling = true;
    undoStack.value.push(snapshotState());
    const nextState = redoStack.value.pop();
    blocks.value = nextState;
    lastSavedState = JSON.parse(JSON.stringify(nextState));
    http.draft_config = nextState;
    queueSave();
    nextTick(() => {
        isTraveling = false;
    });
};

const addBlock = (type) => {
    const definition = getBlockDefinition(type);

    if (!definition) {
        console.error(
            `Block type "${type}" is not registered in the block registry.`,
        );

        return;
    }

    const propsClone = JSON.parse(JSON.stringify(definition.defaultProps));

    const newBlock = {
        id: `${type.toLowerCase()}-${Date.now()}`,
        type: type,
        props: propsClone,
        children: [],
    };

    if (type === 'LayoutGrid') {
        // TODO - Create Sections options and remove this hard coding
        newBlock.children = [
            {
                id: `layoutcolumn-${Date.now()}-1`,
                type: 'LayoutColumn',
                props: {
                    padding: 20,
                    backgroundColor: 'transparent',
                    span: 'auto',
                    width: 'auto',
                    height: 'auto',
                    gap: '0px',
                },
                children: [],
            },
            {
                id: `layoutcolumn-${Date.now()}-2`,
                type: 'LayoutColumn',
                props: {
                    padding: 20,
                    backgroundColor: 'transparent',
                    span: 'auto',
                    width: 'auto',
                    height: 'auto',
                    gap: '0px',
                },
                children: [],
            },
            {
                id: `layoutcolumn-${Date.now()}-3`,
                type: 'LayoutColumn',
                props: {
                    padding: 20,
                    backgroundColor: 'transparent',
                    span: 'auto',
                    width: 'auto',
                    height: 'auto',
                    gap: '0px',
                },
                children: [],
            },
        ];
    }

    if (selectedBlock.value && selectedBlock.value.children) {
        if (!Array.isArray(selectedBlock.value.children)) {
            selectedBlock.value.children = [];
        }

        const parentType = selectedBlock.value.type;
        const parentDef = getBlockDefinition(parentType);
        let isAllowed = true;
        const nestingRules = page.props.blocksConfig?.nesting;
        if (nestingRules && typeof nestingRules === 'object') {
            const allowed = nestingRules[parentType];
            isAllowed = Array.isArray(allowed) ? allowed.includes(type) : true;
        } else {
            isAllowed =
                !parentDef ||
                !parentDef.allowedChildren ||
                parentDef.allowedChildren.includes(type);
        }

        if (isAllowed) {
            selectedBlock.value.children.push(newBlock);
            toast.success(
                `Added ${definition.label} inside ${parentDef.label}`,
            );
        } else {
            toast.error(
                `Nesting Error: ${definition.label} is not allowed inside ${parentDef.label}`,
            );
        }
    } else {
        blocks.value.push(newBlock);
        toast.success(`Added ${definition.label}`);
    }
};

const activeBlockDefinition = computed(() => {
    if (!selectedBlock.value) {
        return null;
    }

    return getBlockDefinition(selectedBlock.value.type);
});

const findParent = (nodes, targetId) => {
    for (const node of nodes) {
        if (node.children?.some((c) => c.id === targetId)) {
            return node;
        }
        if (node.children) {
            const result = findParent(node.children, targetId);
            if (result) return result;
        }
    }
    return null;
};

const findIndexInParent = (nodes, targetId) => {
    for (let i = 0; i < nodes.length; i++) {
        if (nodes[i].id === targetId) return i;
        if (nodes[i].children) {
            const idx = findIndexInParent(nodes[i].children, targetId);
            if (idx !== -1) return idx;
        }
    }
    return -1;
};

const generateNewIds = (node) => {
    const copy = JSON.parse(JSON.stringify(node));
    copy.id = `${copy.type.toLowerCase()}-${Date.now()}-${Math.random().toString(36).slice(2, 6)}`;
    if (copy.children) {
        copy.children = copy.children.map(generateNewIds);
    }
    return copy;
};

const removeNode = (nodes, id) => {
    for (let i = 0; i < nodes.length; i++) {
        if (nodes[i].id === id) {
            nodes.splice(i, 1);
            return true;
        }
        if (nodes[i].children && removeNode(nodes[i].children, id)) {
            return true;
        }
    }
    return false;
};

const deleteBlockById = (nodeId) => {
    removeNode(blocks.value, nodeId);
    if (selectedBlock.value?.id === nodeId) {
        selectedBlock.value = null;
    }
};

const deleteSelectedBlock = () => {
    if (!selectedBlock.value) return;
    deleteBlockById(selectedBlock.value.id);
};

const duplicateBlock = (nodeId) => {
    const parent = findParent(blocks.value, nodeId);
    const list = parent ? parent.children : blocks.value;
    const idx = list.findIndex((n) => n.id === nodeId);
    if (idx === -1) return;
    list.splice(idx + 1, 0, generateNewIds(list[idx]));
};

const moveBlock = (nodeId, direction) => {
    const parent = findParent(blocks.value, nodeId);
    const list = parent ? parent.children : blocks.value;
    const idx = list.findIndex((n) => n.id === nodeId);
    if (idx === -1) return;
    if (direction === 'up' && idx > 0) {
        const [item] = list.splice(idx, 1);
        list.splice(idx - 1, 0, item);
    } else if (direction === 'down' && idx < list.length - 1) {
        const [item] = list.splice(idx, 1);
        list.splice(idx + 1, 0, item);
    }
};

const copiedBlock = ref(null);

const copyBlock = (nodeId) => {
    const walk = (nodes) => {
        for (const n of nodes) {
            if (n.id === nodeId) {
                copiedBlock.value = JSON.parse(JSON.stringify(n));
                return true;
            }
            if (n.children && walk(n.children)) return true;
        }
        return false;
    };
    walk(blocks.value);
};

const pasteBlock = (targetId) => {
    if (!copiedBlock.value) return;
    const parent = targetId ? findParent(blocks.value, targetId) : null;
    const list = parent ? parent.children : blocks.value;
    const idx = targetId
        ? list.findIndex((n) => n.id === targetId)
        : list.length - 1;
    list.splice(idx + 1, 0, generateNewIds(copiedBlock.value));
};

const wrapInContainer = (nodeId, containerType = 'LayoutColumn') => {
    const parent = findParent(blocks.value, nodeId);
    const list = parent ? parent.children : blocks.value;
    const idx = list.findIndex((n) => n.id === nodeId);
    if (idx === -1) return;
    const node = list[idx];
    const container = {
        id: `${containerType.toLowerCase()}-${Date.now()}`,
        type: containerType,
        props: {
            padding: 20,
            backgroundColor: 'transparent',
            span: 'auto',
            width: 'auto',
            height: 'auto',
            gap: '0px',
        },
        children: [node],
    };
    list.splice(idx, 1, container);
};

// Initialize Inertia v3 stand-alone HTTP request hook
const http = useHttp({
    page_id: props.page.id,
    draft_config: blocks.value,
});

// 2. Debounced, Race-Condition-Safe Auto-Save Engine
let currentSaveVisit = null;

const saveError = ref('');
const saveState = ref('saved');

const extractHttpError = (error) => {
    if (!error) {
        return 'Unknown error';
    }

    if (
        error.name === 'HttpCancelledError' ||
        error.message === 'canceled' ||
        error.code === 'canceled'
    ) {
        return null;
    }

    if (
        error.name === 'HttpNetworkError' ||
        error.message === 'Network Error'
    ) {
        return 'Cannot reach the server â€” check your connection';
    }

    const response = error.response;
    if (!response) {
        return error.message || 'Unknown error';
    }

    const status = response.status;
    let body = null;

    try {
        body = response.data ? JSON.parse(response.data) : null;
    } catch {
        // Response is HTML, not JSON â€” likely a 419 CSRF page or 500 error page
        if (status === 419) {
            return 'Session expired â€” please reload the page';
        }
        if (status === 401 || status === 403) {
            return 'Unauthorized â€” your session may have expired';
        }
        if (status >= 500) {
            return `Server error (${status}) â€” please try again`;
        }
        return `Request failed (${status})`;
    }

    if (body?.errors) {
        const messages = Object.values(body.errors).flat();
        return messages.join('; ');
    }

    if (body?.error) {
        return body.error;
    }

    if (body?.message) {
        return body.message;
    }

    return `Request failed (${status})`;
};

const saveCanvasState = async () => {
    saveState.value = 'saving';

    if (currentSaveVisit) {
        currentSaveVisit.cancel();
    }

    try {
        currentSaveVisit = http.post(`/editor/save`, {
            onCancel: () => {
                console.log('Previous background save aborted cleanly.');
            },
        });

        await currentSaveVisit;
        if (saveError.value) {
            toast.success('Draft saved successfully', { id: 'save-error' });
        }
        saveError.value = '';
        saveState.value = 'saved';
    } catch (error) {
        const message = extractHttpError(error);

        if (message === null) {
            return;
        }

        console.warn('[Save Error]', {
            status: error?.response?.status,
            body: error?.response?.data,
        });

        saveError.value = message;
        saveState.value = 'error';
        toast.error(`Auto-save failed: ${message}`, { id: 'save-error' });
    } finally {
        currentSaveVisit = null;
    }
};

let saveTimeout = null;
const queueSave = () => {
    if (isDragging.value) {
        return;
    }

    clearTimeout(saveTimeout);
    saveState.value = 'saving';
    saveTimeout = setTimeout(saveCanvasState, 400); // Wait for 400ms of user inactivity before sending
};

const forceSave = async () => {
    if (saveTimeout) {
        clearTimeout(saveTimeout);
        saveTimeout = null;
    }

    await saveCanvasState();
};
provide('forceSave', forceSave);
provide('blockActions', {
    duplicateBlock,
    moveBlock,
    copyBlock,
    pasteBlock,
    wrapInContainer,
    copiedBlock,
    deleteBlockById,
});

// Deep watch the layout array. Any drag or slider change triggers a safe save.
watch(
    blocks,
    (newBlocks) => {
        if (isTraveling) {
            return;
        }

        undoStack.value.push(lastSavedState);
        redoStack.value = [];
        lastSavedState = snapshotState();

        http.draft_config = newBlocks;
        queueSave();
    },
    { deep: true },
);

// Atomic Publish Handlers
const isPublishing = ref(false);
const publishMessage = ref('');

const publishHttp = useHttp({
    page_id: props.page.id,
});

const publishError = ref('');

const publishPage = async () => {
    isPublishing.value = true;
    publishMessage.value = '';
    publishError.value = '';

    try {
        toast.loading('Saving pending draft changes...', {
            id: 'publish-toast',
        });
        // 1. Flush any pending draft changes immediately before publishing
        await forceSave();

        if (saveError.value) {
            publishError.value =
                'Cannot publish while save is failing: ' + saveError.value;
            toast.error(publishError.value, { id: 'publish-toast' });
            return;
        }

        toast.loading('Publishing site...', { id: 'publish-toast' });

        // 2. Perform the publish promotion
        const res = await publishHttp.post(`/editor/publish`);

        if (res && res.status === 'success') {
            publishMessage.value =
                res.message || 'Site published successfully!';
            toast.success(publishMessage.value, { id: 'publish-toast' });
            setTimeout(() => {
                publishMessage.value = '';
            }, 3000);
        } else {
            publishError.value = 'Publish returned an unexpected response';
            toast.error(publishError.value, { id: 'publish-toast' });
        }
    } catch (err) {
        const message = extractHttpError(err);

        if (message !== null) {
            publishError.value = message;
            toast.error(publishError.value, { id: 'publish-toast' });
        }
    } finally {
        isPublishing.value = false;
    }
};

const isPublishingAll = ref(false);
const publishAllHttp = useHttp({});

const publishAll = async () => {
    isPublishingAll.value = true;

    try {
        toast.loading('Publishing all pages...', { id: 'publish-all-toast' });

        const res = await publishAllHttp.post(`/editor/publish-all`);

        if (res && res.status === 'success') {
            toast.success(
                res.message || 'All pages published.',
                { id: 'publish-all-toast' },
            );
            router.reload({ only: ['pages', 'page'] });
        } else {
            toast.error('Publish all returned an unexpected response', {
                id: 'publish-all-toast',
            });
        }
    } catch (err) {
        const message = extractHttpError(err);

        if (message !== null) {
            toast.error(`Failed to publish all pages: ${message}`, {
                id: 'publish-all-toast',
            });
        }
    } finally {
        isPublishingAll.value = false;
    }
};

// Page Management Functions
const showCreateModal = ref(false);
const showRenameModal = ref(false);
const pageToRename = ref(null);

const deleteHttp = useHttp({});
const setHomepageHttp = useHttp({ is_homepage: true });
const visibilityHttp = useHttp({ is_published: false });

const pageActionError = ref('');

const switchPage = async (pageSlug) => {
    if (pageSlug === props.page.slug) {
        return;
    }

    const loadToast = toast.loading(`Saving draft and switching to page...`);
    try {
        await forceSave();
        toast.success('Draft saved successfully', { id: loadToast });
        router.visit(`/editor?page=${pageSlug}`);
    } catch (err) {
        toast.error('Failed to save draft before switching', { id: loadToast });
    }
};

const openRenameModal = (page) => {
    pageToRename.value = page;
    showRenameModal.value = true;
};

const handleDeletePage = async (page) => {
    if (page.is_homepage) {
        toast.error('Cannot delete the homepage', { id: 'page-delete' });
        return;
    }

    if (
        !confirm(
            `Are you sure you want to delete "${page.title}"? This will delete all of its draft and published configurations.`,
        )
    ) {
        return;
    }

    pageActionError.value = '';
    const loadingToast = toast.loading('Deleting page...');
    try {
        const res = await deleteHttp.delete(`/editor/pages/${page.id}`);

        if (res && res.status === 'success') {
            toast.success('Page deleted successfully', { id: loadingToast });
            if (page.slug === props.page.slug) {
                router.visit('/editor');
            } else {
                router.reload({ only: ['pages'] });
            }
        }
    } catch (err) {
        const message = extractHttpError(err);
        if (message !== null) {
            pageActionError.value = message;
            toast.error(`Failed to delete page: ${message}`, {
                id: loadingToast,
            });
        } else {
            toast.dismiss(loadingToast);
        }
    }
};

const handleSetHomepage = async (page) => {
    pageActionError.value = '';
    const loadingToast = toast.loading('Setting homepage...');
    try {
        setHomepageHttp.is_homepage = true;
        const res = await setHomepageHttp.patch(`/editor/pages/${page.id}`);

        if (res && res.status === 'success') {
            toast.success(`"${page.title}" is now the homepage`, {
                id: loadingToast,
            });
            router.reload({ only: ['pages', 'page'] });
        }
    } catch (err) {
        const message = extractHttpError(err);
        if (message !== null) {
            pageActionError.value = message;
            toast.error(`Failed to set homepage: ${message}`, {
                id: loadingToast,
            });
        } else {
            toast.dismiss(loadingToast);
        }
    }
};

const handleToggleVisibility = async (page) => {
    pageActionError.value = '';
    const next = !page.is_published;
    const loadingToast = toast.loading(
        next ? 'Re-listing page...' : 'Unlisting page...',
    );
    try {
        visibilityHttp.is_published = next;
        const res = await visibilityHttp.patch(
            `/editor/pages/${page.id}/visibility`,
        );

        if (res && res.status === 'success') {
            toast.success(res.message || 'Page visibility updated', {
                id: loadingToast,
            });
            router.reload({ only: ['pages', 'page'] });
        }
    } catch (err) {
        const message = extractHttpError(err);
        if (message !== null) {
            pageActionError.value = message;
            toast.error(`Failed to update visibility: ${message}`, {
                id: loadingToast,
            });
        } else {
            toast.dismiss(loadingToast);
        }
    }
};
// Media picker state
const showMediaPicker = ref(false);
const mediaPickerFieldKey = ref('');

const openMediaPicker = (fieldKey) => {
    mediaPickerFieldKey.value = fieldKey;
    showMediaPicker.value = true;
};

const onMediaSelected = (item) => {
    if (selectedBlock.value && mediaPickerFieldKey.value) {
        selectedBlock.value.props[mediaPickerFieldKey.value] = item.url;
    }
    showMediaPicker.value = false;
};
</script>

<template>
    <Head>
        <link v-if="fontUrl" rel="stylesheet" :href="fontUrl" />
    </Head>

    <div class="editor-root">
        <EditorTopbar
            :dashboard-url="urls.dashboard"
            :live-url="urls.live"
            :page-title="props.page.title"
            :tenant-name="props.tenant?.subdomain"
            :view-mode="viewMode"
            :can-undo="undoStack.length > 0"
            :can-redo="redoStack.length > 0"
            :save-state="saveState"
            :commerce-preview="props.commerce_preview"
            :pages="props.pages"
            :current-page-slug="props.page.slug"
            @toggle-sidebar="sidebarOpen = !sidebarOpen"
            @update:view-mode="viewMode = $event"
            @undo="undo"
            @redo="redo"
            @update:commerce-preview="updateCommercePreview"
            @switch-page="switchPage"
        />

        <div class="editor-body">
            <button
                v-if="sidebarOpen"
                type="button"
                class="sidebar-backdrop"
                aria-label="Close editor sidebar"
                @click="sidebarOpen = false"
            ></button>

            <div
                class="sidebar-drawer"
                :class="{ 'sidebar-drawer-open': sidebarOpen }"
            >
                <EditorSidebar
                    :pages="props.pages"
                    :current-page="props.page"
                    :selected-block="selectedBlock"
                    :active-block-definition="activeBlockDefinition"
                    :navigation-config="navigationConfig"
                    :tenant="props.tenant"
                    :block-definitions="blockDefinitions"
                    :block-presets="blockPresets"
                    :dashboard-url="urls.dashboard"
                    :logout-url="urls.logout"
                    :live-url="urls.live"
                    :is-publishing="isPublishing"
                    :is-publishing-all="isPublishingAll"
                    :is-saving="http.processing"
                    :save-error="saveError"
                    @create-page="showCreateModal = true"
                    @switch-page="switchPage"
                    @rename-page="openRenameModal"
                    @set-homepage="handleSetHomepage"
                    @delete-page="handleDeletePage"
                    @toggle-visibility="handleToggleVisibility"
                    @open-media-picker="openMediaPicker"
                    @add-block="addBlock"
                    @add-preset="addPreset"
                    @publish="publishPage"
                    @publish-all="publishAll"
                />
            </div>

            <EditorCanvasViewport
                v-model:blocks="blocks"
                :navigation-config="navigationConfig"
                :pages="props.pages"
                :tenant-name="props.tenant?.subdomain"
                :theme-vars="themeVars"
                :canvas-max-width="canvasMaxWidth"
                :view-mode="viewMode"
                @drag-start="isDragging = true"
                @drag-end="
                    isDragging = false;
                    forceSave();
                "
                @navigate-page="switchPage"
            />
        </div>

        <CreatePageModal
            :show="showCreateModal"
            :page-layouts="props.page_layouts"
            @close="showCreateModal = false"
        />

        <RenamePageModal
            :show="showRenameModal"
            :page="pageToRename"
            :current-page-slug="props.page.slug"
            @close="showRenameModal = false"
        />

        <Toaster position="top-right" close-button rich-colors />

        <MediaPicker
            v-if="showMediaPicker"
            @select="onMediaSelected"
            @close="showMediaPicker = false"
        />
    </div>
</template>

<style scoped>
.editor-root {
    display: grid;
    width: 100vw;
    height: 100vh;
    min-width: 0;
    grid-template-rows: 54px minmax(0, 1fr);
    overflow: hidden;
    color: #f4f4f5;
    background: #0c0c0d;
    font-family:
        Inter,
        ui-sans-serif,
        system-ui,
        -apple-system,
        BlinkMacSystemFont,
        'Segoe UI',
        sans-serif;
}

.editor-body {
    position: relative;
    display: grid;
    min-width: 0;
    min-height: 0;
    grid-template-columns: 380px minmax(0, 1fr);
    overflow: hidden;
}

.sidebar-drawer {
    min-width: 0;
    min-height: 0;
    z-index: 40;
}

.sidebar-drawer > :deep(*) {
    height: 100%;
}

.sidebar-backdrop {
    display: none;
}

@media (max-width: 820px) {
    .editor-body {
        grid-template-columns: minmax(0, 1fr);
    }

    .sidebar-drawer {
        position: absolute;
        inset: 0 auto 0 0;
        width: min(380px, calc(100vw - 42px));
        transform: translateX(-102%);
        transition: transform 180ms ease;
    }

    .sidebar-drawer-open {
        transform: translateX(0);
    }

    .sidebar-backdrop {
        position: absolute;
        inset: 0;
        z-index: 30;
        display: block;
        background: rgb(0 0 0 / 62%);
        border: 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .sidebar-drawer {
        transition: none;
    }
}
</style>

<style>
.drag-ghost {
    opacity: 0.4;
    transition: none !important;
}
</style>
