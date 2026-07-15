<script setup>
import { useHttp } from '@inertiajs/vue3';
import { Check, ChevronLeft, FilePlus2, LayoutTemplate, X } from '@lucide/vue';
import { computed, provide, ref } from 'vue';
import { toast } from 'vue-sonner';

import RenderPublicNode from '@/components/BuilderBlocks/RenderPublicNode.vue';
import { useSafeNavigate } from '@/composables/useSafeNavigate';
import { blockComponents } from '@/lib/blockRegistry';

const props = defineProps({
    show: Boolean,
    pageLayouts: { type: Array, default: () => [] },
});

const emit = defineEmits(['close']);

const { safeNavigate } = useSafeNavigate();

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
            await safeNavigate(`/editor?page=${res.page.slug}`);
        }
    } catch (err) {
        const message = extractHttpError(err);
        if (message !== null) {
            pageActionError.value = message;
            toast.error(`Failed to create page: ${message}`, { id: loadingToast });
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
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm"
    >
        <div
            class="w-full max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl"
            :class="step === 'layout' ? 'max-w-3xl' : 'max-w-md space-y-4'"
        >
            <div
                class="flex items-center justify-between border-b border-slate-800 pb-3"
            >
                <div class="flex items-center gap-2">
                    <button
                        v-if="step === 'layout' || step === 'details'"
                        type="button"
                        class="cursor-pointer border-0 bg-transparent p-1 text-slate-400 transition hover:text-white"
                        :aria-label="
                            step === 'layout' ? 'Back to mode' : 'Back'
                        "
                        @click="
                            step === 'layout' ? backToMode() : selectedLayoutKey ? backToLayouts() : backToMode()
                        "
                    >
                        <ChevronLeft :size="18" />
                    </button>
                    <h3 class="text-base font-bold text-white">
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
                    class="cursor-pointer border-0 bg-transparent p-1 text-slate-400 transition hover:text-white"
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
                    class="group flex items-start gap-3 rounded-xl border border-slate-700 bg-slate-800 p-4 text-left transition hover:border-indigo-500 hover:bg-slate-800/60 cursor-pointer"
                    @click="chooseBlank"
                >
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-400"
                    >
                        <FilePlus2 :size="18" />
                    </div>
                    <div>
                        <span class="block text-sm font-semibold text-white"
                            >Start blank</span
                        >
                        <span class="mt-1 block text-xs text-slate-400"
                            >Begin with a single hero block and build your page
                            from scratch.</span
                        >
                    </div>
                </button>

                <button
                    type="button"
                    :disabled="pageLayouts.length === 0"
                    class="group flex items-start gap-3 rounded-xl border border-slate-700 bg-slate-800 p-4 text-left transition hover:border-indigo-500 hover:bg-slate-800/60 disabled:cursor-not-allowed disabled:opacity-50 cursor-pointer"
                    @click="chooseLayouts"
                >
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400"
                    >
                        <LayoutTemplate :size="18" />
                    </div>
                    <div>
                        <span class="block text-sm font-semibold text-white"
                            >Choose a layout</span
                        >
                        <span class="mt-1 block text-xs text-slate-400"
                            >Start from a pre-designed page layout. Everything is
                            fully editable.</span
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
                                ? 'border-indigo-500 bg-indigo-500/10 text-indigo-300'
                                : 'border-slate-700 bg-slate-800 text-slate-400 hover:text-white'
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
                                ? 'border-indigo-500 bg-indigo-500/10 text-indigo-300'
                                : 'border-slate-700 bg-slate-800 text-slate-400 hover:text-white'
                        "
                        @click="activeFilter = pageType"
                    >
                        {{ pageTypeLabel(pageType) }}
                    </button>
                </div>

                <div class="grid max-h-[60vh] gap-3 overflow-y-auto sm:grid-cols-2">
                    <button
                        v-for="layout in filteredLayouts"
                        :key="layout.key"
                        type="button"
                        class="group overflow-hidden rounded-xl border border-slate-700 bg-slate-800 text-left transition hover:border-indigo-500 cursor-pointer"
                        @click="selectLayout(layout.key)"
                    >
                        <div
                            class="flex h-28 items-center justify-center overflow-hidden border-b border-slate-700 bg-slate-950/60"
                        >
                            <div
                                class="pointer-events-none flex w-full max-w-[280px] scale-[0.32] origin-top flex-col gap-1"
                            >
                                <RenderPublicNode
                                    v-for="node in layout.preview_blocks"
                                    :key="node.id"
                                    :node="node"
                                />
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-2 p-3">
                            <div class="min-w-0">
                                <span
                                    class="block truncate text-sm font-semibold text-white"
                                    >{{ layout.label }}</span
                                >
                                <span
                                    class="block text-[10px] font-medium capitalize text-slate-500"
                                    >{{ layout.industry }} ·
                                    {{ pageTypeLabel(layout.page_type) }}</span
                                >
                            </div>
                            <div
                                class="flex size-6 shrink-0 items-center justify-center rounded-full border border-slate-700 text-slate-500 transition group-hover:border-indigo-500 group-hover:text-indigo-400"
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
                    class="flex items-center justify-between gap-3 rounded-lg border border-emerald-500/30 bg-emerald-500/5 p-3"
                >
                    <div class="flex items-center gap-2">
                        <LayoutTemplate :size="14" class="text-emerald-400" />
                        <div>
                            <span
                                class="block text-xs font-semibold text-emerald-300"
                                >Selected layout</span
                            >
                            <span class="block text-xs text-slate-400">{{
                                selectedLayout.label
                            }}</span>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="cursor-pointer border-0 bg-transparent text-xs font-semibold text-indigo-400 hover:text-indigo-300"
                        @click="backToLayouts"
                    >
                        Change
                    </button>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-400"
                        >Page Title</label
                    >
                    <input
                        v-model="createForm.title"
                        type="text"
                        required
                        class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white text-slate-100 focus:border-indigo-500 focus:outline-none"
                        placeholder="e.g. About Us"
                        @input="autoGenerateSlug"
                    />
                    <p v-if="createForm.errors.title" class="mt-1 text-xs text-rose-500">
                        {{ createForm.errors.title }}
                    </p>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-400"
                        >URL Slug</label
                    >
                    <input
                        v-model="createForm.slug"
                        type="text"
                        required
                        class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 font-mono text-sm text-white text-slate-100 focus:border-indigo-500 focus:outline-none"
                        placeholder="e.g. about-us"
                    />
                    <p v-if="createForm.errors.slug" class="mt-1 text-xs text-rose-500">
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
                        class="cursor-pointer rounded-lg border border-slate-700 bg-slate-800 py-2 px-4 text-xs font-semibold text-slate-200 transition hover:bg-slate-700"
                        @click="emit('close')"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="createForm.processing"
                        class="cursor-pointer rounded-lg border-0 bg-indigo-600 py-2 px-4 text-xs font-semibold text-white transition hover:bg-indigo-500 disabled:opacity-50"
                    >
                        {{ createForm.processing ? 'Creating...' : 'Create Page' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
