<!-- eslint-disable vue/block-lang -->
<script setup>
import { useHttp, router } from '@inertiajs/vue3';
import { Check, ChevronLeft, FilePlus2, LayoutTemplate, X } from '@lucide/vue';
import { computed, provide, ref } from 'vue';
import { toast } from 'vue-sonner';

import RenderPublicNode from '@/components/BuilderBlocks/RenderPublicNode.vue';
import { blockComponents } from '@/lib/blockRegistry';

const props = defineProps({
    show: Boolean,
    pageLayouts: { type: Array, default: () => [] },
});

const emit = defineEmits(['close']);

// Modal steps: 'mode' (blank vs layout) -> 'layout' (grid) -> 'details' (title/slug)
const step = ref('mode');
const selectedLayoutKey = ref(null);

const createForm = useHttp({
    title: '',
    slug: '',
    layout_key: '',
});

const pageActionError = ref('');

provide('blockRegistry', blockComponents);
provide('isEditable', false);
provide('showMediaPlaceholders', true);

const selectedLayout = computed(() =>
    props.pageLayouts.find((layout) => layout.key === selectedLayoutKey.value),
);

// Unique page types for the filter chips
const pageTypes = computed(() => {
    const types = props.pageLayouts.map((layout) => layout.page_type);

    return [...new Set(types)];
});

const activeFilter = ref('all');

const filteredLayouts = computed(() => {
    if (activeFilter.value === 'all') {
        return props.pageLayouts;
    }

    return props.pageLayouts.filter(
        (layout) => layout.page_type === activeFilter.value,
    );
});

const resetModal = () => {
    step.value = 'mode';
    selectedLayoutKey.value = null;
    activeFilter.value = 'all';
    pageActionError.value = '';
};

const chooseBlank = () => {
    selectedLayoutKey.value = null;
    createForm.layout_key = '';
    step.value = 'details';
};

const chooseLayouts = () => {
    step.value = 'layout';
};

const selectLayout = (layoutKey) => {
    selectedLayoutKey.value = layoutKey;
    createForm.layout_key = layoutKey;
    step.value = 'details';
};

const backToMode = () => {
    step.value = 'mode';
};

const backToLayouts = () => {
    step.value = 'layout';
};

const autoGenerateSlug = () => {
    createForm.slug = createForm.title
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim();
};

const extractHttpError = (error) => {
    if (error?.response?.data?.message) {
        return error.response.data.message;
    }

    if (error?.message) {
        return error.message;
    }

    return 'An unknown error occurred';
};

const submitCreatePage = async () => {
    pageActionError.value = '';
    const loadingToast = toast.loading('Creating page...');

    try {
        const res = await createForm.post('/editor/pages');

        if (res && res.status === 'success') {
            toast.success('Page created successfully', { id: loadingToast });
            createForm.reset();
            resetModal();
            emit('close');
            router.visit(`/editor?page=${res.page.slug}`);
        }
    } catch (err) {
        const message = extractHttpError(err);

        if (message !== null) {
            pageActionError.value = message;
            toast.error(`Failed to create page: ${message}`, {
                id: loadingToast,
            });
        } else {
            toast.dismiss(loadingToast);
        }
    }
};

const pageTypeLabel = (pageType) =>
    pageType.charAt(0).toUpperCase() + pageType.slice(1);
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/35 p-4 backdrop-blur-[2px]"
    >
        <div
            class="max-h-[90vh] w-full overflow-y-auto rounded-[10px] border border-editor-border bg-editor-panel p-5 text-editor-text shadow-[var(--editor-shadow)]"
            :class="step === 'layout' ? 'max-w-3xl' : 'max-w-md space-y-4'"
        >
            <div
                class="flex items-center justify-between border-b border-editor-border pb-3"
            >
                <div class="flex items-center gap-2">
                    <button
                        v-if="step === 'layout' || step === 'details'"
                        type="button"
                        class="cursor-pointer rounded-[4px] border-0 bg-transparent p-1 text-editor-text-muted transition hover:bg-editor-panel-muted hover:text-editor-text"
                        :aria-label="
                            step === 'layout' ? 'Back to mode' : 'Back'
                        "
                        @click="
                            step === 'layout'
                                ? backToMode()
                                : selectedLayoutKey
                                  ? backToLayouts()
                                  : backToMode()
                        "
                    >
                        <ChevronLeft :size="18" />
                    </button>
                    <h3 class="text-base font-bold text-editor-text">
                        {{
                            step === 'mode'
                                ? 'Create New Page'
                                : step === 'layout'
                                  ? 'Choose a Layout'
                                  : 'Page Details'
                        }}
                    </h3>
                </div>
                <button
                    type="button"
                    class="cursor-pointer rounded-[4px] border-0 bg-transparent p-1 text-editor-text-muted transition hover:bg-editor-panel-muted hover:text-editor-text"
                    aria-label="Close"
                    @click="emit('close')"
                >
                    <X :size="18" />
                </button>
            </div>

            <!-- Step 1: Mode selection -->
            <div v-if="step === 'mode'" class="grid gap-3 py-2">
                <button
                    type="button"
                    class="group flex cursor-pointer items-start gap-3 rounded-[7px] border border-editor-border bg-editor-panel-muted p-4 text-left transition hover:border-editor-text hover:bg-editor-panel"
                    @click="chooseBlank"
                >
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-[6px] bg-editor-accent-soft text-editor-text"
                    >
                        <FilePlus2 :size="18" />
                    </div>
                    <div>
                        <span
                            class="block text-sm font-semibold text-editor-text"
                            >Start blank</span
                        >
                        <span class="mt-1 block text-xs text-editor-text-muted"
                            >Begin with a single hero block and build your page
                            from scratch.</span
                        >
                    </div>
                </button>

                <button
                    type="button"
                    :disabled="pageLayouts.length === 0"
                    class="group flex cursor-pointer items-start gap-3 rounded-[7px] border border-editor-border bg-editor-panel-muted p-4 text-left transition hover:border-editor-text hover:bg-editor-panel disabled:cursor-not-allowed disabled:opacity-50"
                    @click="chooseLayouts"
                >
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-[6px] bg-editor-accent-soft text-editor-text"
                    >
                        <LayoutTemplate :size="18" />
                    </div>
                    <div>
                        <span
                            class="block text-sm font-semibold text-editor-text"
                            >Choose a layout</span
                        >
                        <span class="mt-1 block text-xs text-editor-text-muted"
                            >Start from a pre-designed page layout. Everything
                            is fully editable.</span
                        >
                    </div>
                </button>
            </div>

            <!-- Step 2: Layout grid -->
            <div v-else-if="step === 'layout'" class="py-2">
                <div class="mb-4 flex flex-wrap gap-1.5">
                    <button
                        type="button"
                        class="cursor-pointer rounded-full border px-3 py-1 text-xs font-semibold transition"
                        :class="
                            activeFilter === 'all'
                                ? 'border-editor-text bg-editor-text text-white'
                                : 'border-editor-border bg-editor-panel-muted text-editor-text-muted hover:border-editor-border-strong hover:text-editor-text'
                        "
                        @click="activeFilter = 'all'"
                    >
                        All
                    </button>
                    <button
                        v-for="pageType in pageTypes"
                        :key="pageType"
                        type="button"
                        class="cursor-pointer rounded-full border px-3 py-1 text-xs font-semibold capitalize transition"
                        :class="
                            activeFilter === pageType
                                ? 'border-editor-text bg-editor-text text-white'
                                : 'border-editor-border bg-editor-panel-muted text-editor-text-muted hover:border-editor-border-strong hover:text-editor-text'
                        "
                        @click="activeFilter = pageType"
                    >
                        {{ pageTypeLabel(pageType) }}
                    </button>
                </div>

                <div
                    class="grid max-h-[60vh] gap-3 overflow-y-auto sm:grid-cols-2"
                >
                    <button
                        v-for="layout in filteredLayouts"
                        :key="layout.key"
                        type="button"
                        class="group cursor-pointer overflow-hidden rounded-[7px] border border-editor-border bg-editor-panel-muted text-left transition hover:border-editor-text"
                        @click="selectLayout(layout.key)"
                    >
                        <div
                            class="flex h-28 items-center justify-center overflow-hidden border-b border-editor-border bg-editor-bg"
                        >
                            <div
                                class="pointer-events-none flex w-full max-w-[280px] origin-top scale-[0.32] flex-col gap-1"
                            >
                                <RenderPublicNode
                                    v-for="node in layout.preview_blocks"
                                    :key="node.id"
                                    :node="node"
                                />
                            </div>
                        </div>
                        <div
                            class="flex items-center justify-between gap-2 p-3"
                        >
                            <div class="min-w-0">
                                <span
                                    class="block truncate text-sm font-semibold text-editor-text"
                                    >{{ layout.label }}</span
                                >
                                <span
                                    class="block text-[10px] font-medium text-editor-text-muted capitalize"
                                    >{{ layout.industry }} ·
                                    {{ pageTypeLabel(layout.page_type) }}</span
                                >
                            </div>
                            <div
                                class="flex size-6 shrink-0 items-center justify-center rounded-full border border-editor-border text-editor-text-muted transition group-hover:border-editor-text group-hover:bg-editor-text group-hover:text-white"
                            >
                                <Check :size="12" />
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Step 3: Title/slug form -->
            <form v-else @submit.prevent="submitCreatePage" class="space-y-4">
                <div
                    v-if="selectedLayout"
                    class="flex items-center justify-between gap-3 rounded-[6px] border border-editor-border bg-editor-panel-muted p-3"
                >
                    <div class="flex items-center gap-2">
                        <LayoutTemplate :size="14" class="text-editor-text" />
                        <div>
                            <span
                                class="block text-xs font-semibold text-editor-text"
                                >Selected layout</span
                            >
                            <span
                                class="block text-xs text-editor-text-muted"
                                >{{ selectedLayout.label }}</span
                            >
                        </div>
                    </div>
                    <button
                        type="button"
                        class="cursor-pointer border-0 bg-transparent text-xs font-semibold text-editor-text underline-offset-4 hover:underline"
                        @click="backToLayouts"
                    >
                        Change
                    </button>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold text-editor-text-muted"
                        >Page Title</label
                    >
                    <input
                        v-model="createForm.title"
                        type="text"
                        required
                        class="w-full rounded-[6px] border border-editor-border bg-editor-panel px-3 py-2 text-sm text-editor-text outline-none focus:border-editor-text"
                        placeholder="e.g. About Us"
                        @input="autoGenerateSlug"
                    />
                    <p
                        v-if="createForm.errors.title"
                        class="mt-1 text-xs text-rose-500"
                    >
                        {{ createForm.errors.title }}
                    </p>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold text-editor-text-muted"
                        >URL Slug</label
                    >
                    <input
                        v-model="createForm.slug"
                        type="text"
                        required
                        class="w-full rounded-[6px] border border-editor-border bg-editor-panel px-3 py-2 font-mono text-sm text-editor-text outline-none focus:border-editor-text"
                        placeholder="e.g. about-us"
                    />
                    <p
                        v-if="createForm.errors.slug"
                        class="mt-1 text-xs text-rose-500"
                    >
                        {{ createForm.errors.slug }}
                    </p>
                </div>

                <p
                    v-if="pageActionError"
                    class="rounded-lg border border-rose-500/30 bg-rose-500/5 p-2 text-xs text-rose-400"
                >
                    {{ pageActionError }}
                </p>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        class="cursor-pointer rounded-[6px] border border-editor-border bg-editor-panel px-4 py-2 text-xs font-semibold text-editor-text transition hover:bg-editor-panel-muted"
                        @click="emit('close')"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="createForm.processing"
                        class="cursor-pointer rounded-[6px] border border-editor-text bg-editor-text px-4 py-2 text-xs font-semibold text-white transition hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{
                            createForm.processing
                                ? 'Creating...'
                                : 'Create Page'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
