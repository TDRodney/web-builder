<script setup lang="ts">
import { Head, Link, router, useHttp } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Check,
    ChevronRight,
    Laptop,
    Loader2,
    LockKeyhole,
    Maximize2,
    Monitor,
    Search,
    Smartphone,
    Sparkles,
} from '@lucide/vue';
import { computed, provide, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

import RenderPublicNode from '@/components/BuilderBlocks/RenderPublicNode.vue';
import SiteFooter from '@/components/SiteFooter.vue';
import SiteHeader from '@/components/SiteHeader.vue';
import { blockComponents } from '@/lib/blockRegistry';
import { useTheme } from '@/lib/theme';
import { dashboard } from '@/routes';
import { editor } from '@/routes/tenant';
import type { NavigationConfig } from '@/types/navigation';

defineOptions({ layout: [] });

interface ThemeConfig {
    colors: {
        primary: string;
        secondary: string;
        background: string;
        text: string;
    };
    typography: {
        headingFont: string;
        bodyFont: string;
    };
    borderRadius: string;
}

interface CatalogPage {
    title: string;
    slug: string;
    is_homepage: boolean;
    preview_blocks: BlockNode[];
}

interface BlockNode {
    id: string;
    type: string;
    props: Record<string, unknown>;
    children?: BlockNode[];
}

interface SiteKit {
    key: string;
    label: string;
    industry: string;
    description: string;
    tier: 'free' | 'premium';
    theme_config: ThemeConfig;
    navigation_config: NavigationConfig;
    pages: CatalogPage[];
    preview_blocks: BlockNode[];
}

const props = defineProps<{
    tenant: { id: number; subdomain: string };
    can_apply_site_kit: boolean;
    kits: SiteKit[];
}>();

type PreviewMode = 'desktop' | 'tablet' | 'mobile';

const selectedKitKey = ref(props.kits[0]?.key ?? '');
const previewMode = ref<PreviewMode>('desktop');
const industryFilter = ref<string>('all');
const kitSearch = ref('');
const selectedPreviewPageSlug = ref('');
const fitPreview = ref(true);

const industries = computed(() => {
    const values = [...new Set(props.kits.map((kit) => kit.industry))];

    return values.sort((a, b) => a.localeCompare(b));
});

const filteredKits = computed(() => {
    const search = kitSearch.value.trim().toLowerCase();

    return props.kits.filter((kit) => {
        const matchesIndustry =
            industryFilter.value === 'all' ||
            kit.industry === industryFilter.value;
        const matchesSearch =
            search === '' ||
            [kit.label, kit.industry, kit.description].some((value) =>
                value.toLowerCase().includes(search),
            );

        return matchesIndustry && matchesSearch;
    });
});

const selectedKit = computed(
    () =>
        filteredKits.value.find((kit) => kit.key === selectedKitKey.value) ??
        filteredKits.value[0],
);
const selectedPreviewPage = computed(
    () =>
        selectedKit.value?.pages.find(
            (page) => page.slug === selectedPreviewPageSlug.value,
        ) ?? selectedKit.value?.pages.find((page) => page.is_homepage),
);

watch(filteredKits, (kits) => {
    if (!kits.some((kit) => kit.key === selectedKitKey.value) && kits[0]) {
        selectedKitKey.value = kits[0].key;
    }
});

watch(
    selectedKit,
    (kit) => {
        selectedPreviewPageSlug.value =
            kit?.pages.find((page) => page.is_homepage)?.slug ??
            kit?.pages[0]?.slug ??
            '';
    },
    { immediate: true },
);

const previewWidth = computed(() => {
    if (previewMode.value === 'mobile') {
        return '375px';
    }

    if (previewMode.value === 'tablet') {
        return '768px';
    }

    return '1180px';
});

const selectedIsPremium = computed(() => selectedKit.value?.tier === 'premium');

const canUseSelectedKit = computed(
    () =>
        props.can_apply_site_kit &&
        Boolean(selectedKit.value) &&
        !selectedIsPremium.value,
);

const { cssVars, fontUrl } = useTheme(() => selectedKit.value?.theme_config);

provide('blockRegistry', blockComponents);
provide('isEditable', false);
provide('showMediaPlaceholders', true);

interface ApplyKitResponse {
    status?: string;
    homepage_slug?: string;
}

interface StartFromScratchResponse {
    status?: string;
}

const applyHttp = useHttp<Record<string, never>, ApplyKitResponse>({});
const scratchHttp = useHttp<Record<string, never>, StartFromScratchResponse>(
    {},
);

const extractHttpError = (error: unknown): string => {
    const err = error as {
        response?: { data?: { message?: string } };
        message?: string;
    };

    return (
        err?.response?.data?.message ||
        err?.message ||
        'An unknown error occurred'
    );
};

const selectKit = (key: string): void => {
    selectedKitKey.value = key;
};

const applyKit = async (): Promise<void> => {
    const kitKey = selectedKitKey.value;

    if (!kitKey || !canUseSelectedKit.value) {
        return;
    }

    const loadingToast = toast.loading('Applying site kit…');

    try {
        const res = await applyHttp.post(`/designs/site-kits/${kitKey}/apply`);

        if (res && res.status === 'success') {
            toast.success('Site kit applied!', { id: loadingToast });
            router.visit(`/editor?page=${res.homepage_slug}`);
        }
    } catch (err) {
        toast.error(extractHttpError(err), { id: loadingToast });
    }
};

const startFromScratch = async (): Promise<void> => {
    const loadingToast = toast.loading('Preparing workspace…');

    try {
        const res = await scratchHttp.post('/designs/start-from-scratch');

        if (res && res.status === 'success') {
            toast.success('Workspace ready — create your first page.', {
                id: loadingToast,
            });
            router.visit('/editor');
        }
    } catch (err) {
        toast.error(extractHttpError(err), { id: loadingToast });
    }
};
</script>

<template>
    <Head title="Choose a starting design">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin="anonymous"
        />
        <link rel="stylesheet" :href="fontUrl" />
    </Head>

    <div
        class="kits-library-shell grid h-dvh grid-rows-[54px_minmax(0,1fr)] overflow-hidden bg-editor-bg font-sans text-editor-text"
    >
        <header
            class="relative z-50 flex min-w-0 items-center justify-between gap-4 border-b border-editor-border bg-editor-panel px-3 sm:px-4"
        >
            <div class="flex min-w-0 items-center gap-3">
                <Link
                    :href="dashboard(tenant.subdomain)"
                    class="flex size-8 shrink-0 items-center justify-center rounded-[5px] border border-editor-border bg-editor-panel-muted text-editor-text-muted transition hover:border-editor-border-strong hover:bg-editor-accent-soft hover:text-editor-text"
                    aria-label="Back to dashboard"
                >
                    <ArrowLeft :size="16" />
                </Link>
                <div class="min-w-0 border-l border-editor-border pl-3">
                    <span
                        class="block text-[10px] font-bold tracking-[0.15em] text-editor-text uppercase"
                    >
                        Starting designs
                    </span>
                    <span
                        class="block truncate text-[10px] text-editor-text-muted"
                    >
                        {{ tenant.subdomain }} · starting designs
                    </span>
                </div>
            </div>

            <Link
                v-if="!can_apply_site_kit"
                :href="editor(tenant.subdomain)"
                class="inline-flex h-[30px] items-center justify-center gap-1.5 rounded-[5px] border border-editor-border-strong bg-editor-text px-2.5 text-[11px] font-semibold text-white transition hover:opacity-90"
            >
                <span class="hidden sm:inline">Open editor</span>
                <ChevronRight :size="13" />
            </Link>
        </header>

        <div
            class="grid min-h-0 grid-cols-1 gap-px overflow-hidden bg-editor-border lg:grid-cols-[300px_minmax(0,1fr)]"
        >
            <aside
                class="flex min-h-0 flex-col overflow-hidden bg-editor-panel"
                aria-label="Kit catalog"
            >
                <div class="shrink-0 border-b border-editor-border px-4 py-4">
                    <span
                        class="text-[9px] font-bold tracking-[0.14em] text-editor-text-muted uppercase"
                    >
                        Catalog
                    </span>
                    <h1 class="mt-1 text-base font-semibold text-editor-text">
                        Choose a starting design
                    </h1>
                    <p class="mt-1 text-xs leading-5 text-editor-text-muted">
                        Pick a look, preview its pages, then make it yours.
                    </p>

                    <label class="relative mt-3 block">
                        <Search
                            :size="13"
                            class="pointer-events-none absolute top-1/2 left-2.5 -translate-y-1/2 text-editor-text-muted"
                        />
                        <span class="sr-only">Search designs</span>
                        <input
                            v-model="kitSearch"
                            type="search"
                            placeholder="Search designs"
                            class="h-8 w-full rounded-[5px] border border-editor-border bg-editor-panel-muted pr-3 pl-8 text-[10px] text-editor-text outline-none placeholder:text-editor-text-muted focus:border-editor-border-strong"
                        />
                    </label>

                    <div class="mt-3 flex flex-wrap gap-1.5">
                        <button
                            type="button"
                            class="rounded-[4px] border px-2 py-1 text-[9px] font-bold tracking-[0.06em] uppercase transition"
                            :class="
                                industryFilter === 'all'
                                    ? 'border-editor-text bg-editor-text text-white'
                                    : 'border-editor-border text-editor-text-muted hover:border-editor-border-strong hover:text-editor-text'
                            "
                            @click="industryFilter = 'all'"
                        >
                            All
                        </button>
                        <button
                            v-for="industry in industries"
                            :key="industry"
                            type="button"
                            class="rounded-[4px] border px-2 py-1 text-[9px] font-bold tracking-[0.06em] uppercase transition"
                            :class="
                                industryFilter === industry
                                    ? 'border-editor-text bg-editor-text text-white'
                                    : 'border-editor-border text-editor-text-muted hover:border-editor-border-strong hover:text-editor-text'
                            "
                            @click="industryFilter = industry"
                        >
                            {{ industry }}
                        </button>
                    </div>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto p-3">
                    <div class="grid gap-2">
                        <button
                            v-for="(kit, kitIndex) in filteredKits"
                            :key="kit.key"
                            type="button"
                            class="kit-card rounded-[7px] border p-2.5 text-left transition"
                            :style="{ '--card-delay': `${kitIndex * 70}ms` }"
                            :class="
                                selectedKit?.key === kit.key
                                    ? 'border-editor-border-strong bg-editor-accent-soft shadow-[0_0_0_1px_rgb(24_24_27/12%)]'
                                    : 'border-editor-border bg-editor-panel hover:border-editor-border-strong hover:bg-editor-panel-muted'
                            "
                            @click="selectKit(kit.key)"
                        >
                            <div
                                class="relative flex h-24 items-end overflow-hidden rounded-[5px] border border-editor-border p-3"
                                :style="{
                                    background: `linear-gradient(135deg, ${kit.theme_config.colors.background} 0%, ${kit.theme_config.colors.background} 55%, ${kit.theme_config.colors.primary}33 100%)`,
                                }"
                            >
                                <span
                                    class="absolute top-2.5 left-2.5 flex gap-1"
                                >
                                    <span
                                        class="size-2.5 rounded-full border border-black/10"
                                        :style="{
                                            backgroundColor:
                                                kit.theme_config.colors.primary,
                                        }"
                                    ></span>
                                    <span
                                        class="size-2.5 rounded-full border border-black/10"
                                        :style="{
                                            backgroundColor:
                                                kit.theme_config.colors
                                                    .secondary,
                                        }"
                                    ></span>
                                    <span
                                        class="size-2.5 rounded-full border border-black/10"
                                        :style="{
                                            backgroundColor:
                                                kit.theme_config.colors.text,
                                        }"
                                    ></span>
                                </span>
                                <strong
                                    class="text-xl leading-none"
                                    :style="{
                                        color: kit.theme_config.colors.text,
                                        fontFamily:
                                            kit.theme_config.typography
                                                .headingFont,
                                    }"
                                >
                                    {{ kit.label }}
                                </strong>
                            </div>

                            <div
                                class="mt-2.5 flex items-center justify-between gap-2"
                            >
                                <div class="min-w-0">
                                    <span
                                        class="text-[9px] font-bold tracking-[0.12em] text-editor-text-muted uppercase"
                                    >
                                        {{ kit.industry }}
                                    </span>
                                </div>
                                <span
                                    class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full border"
                                    :class="
                                        selectedKit?.key === kit.key
                                            ? 'border-zinc-400 bg-editor-text text-white'
                                            : 'border-editor-border text-editor-text-muted'
                                    "
                                >
                                    <Check
                                        v-if="selectedKit?.key === kit.key"
                                        :size="11"
                                    />
                                </span>
                            </div>

                            <span
                                class="mt-2 block text-[9px] text-editor-text-muted"
                            >
                                {{ kit.pages.length }} editable
                                {{ kit.pages.length === 1 ? 'page' : 'pages' }}
                            </span>
                        </button>
                        <div
                            v-if="filteredKits.length === 0"
                            class="rounded-[6px] border border-dashed border-editor-border p-5 text-center"
                        >
                            <strong class="text-[10px] text-editor-text"
                                >No designs found</strong
                            >
                            <p
                                class="mt-1 text-[9px] leading-4 text-editor-text-muted"
                            >
                                Try another search or category.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="shrink-0 border-t border-editor-border p-3">
                    <button
                        v-if="can_apply_site_kit"
                        type="button"
                        :disabled="scratchHttp.processing"
                        class="inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-[5px] text-[9px] font-medium text-editor-text-muted transition hover:bg-editor-panel-muted hover:text-editor-text disabled:cursor-not-allowed disabled:opacity-50"
                        @click="startFromScratch"
                    >
                        <Loader2
                            v-if="scratchHttp.processing"
                            :size="11"
                            class="animate-spin"
                        />
                        {{
                            scratchHttp.processing
                                ? 'Preparing…'
                                : 'Start with a blank site'
                        }}
                    </button>
                    <div
                        v-else
                        class="flex items-start gap-2 rounded-[6px] border border-editor-border bg-editor-panel-muted p-3"
                    >
                        <LockKeyhole
                            :size="13"
                            class="mt-0.5 shrink-0 text-editor-text-muted"
                        />
                        <p class="text-[9px] leading-4 text-editor-text-muted">
                            Preview only. Your existing site will not be
                            changed.
                        </p>
                    </div>
                </div>
            </aside>

            <main
                class="kits-workspace flex min-h-0 min-w-0 flex-col overflow-hidden p-3 sm:p-4"
            >
                <div
                    v-if="selectedKit"
                    class="flex min-h-0 flex-1 flex-col gap-3"
                >
                    <div
                        class="flex shrink-0 flex-wrap items-center gap-3 rounded-[7px] border border-editor-border bg-editor-panel px-3 py-2.5"
                    >
                        <div class="min-w-[150px] flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2
                                    class="text-sm font-semibold text-editor-text"
                                >
                                    {{ selectedKit.label }}
                                </h2>
                                <span
                                    class="rounded-full px-2 py-0.5 text-[8px] font-bold tracking-[0.08em] uppercase"
                                    :class="
                                        selectedKit.tier === 'premium'
                                            ? 'bg-amber-100 text-amber-800'
                                            : 'bg-emerald-100 text-emerald-800'
                                    "
                                >
                                    {{ selectedKit.tier }}
                                </span>
                            </div>
                            <p class="mt-0.5 text-[9px] text-editor-text-muted">
                                {{ selectedKit.pages.length }} editable pages ·
                                Nothing is published automatically
                            </p>
                        </div>

                        <label class="flex items-center gap-2">
                            <span
                                class="text-[9px] font-semibold text-editor-text-muted"
                                >Page</span
                            >
                            <select
                                v-model="selectedPreviewPageSlug"
                                class="h-8 rounded-[5px] border border-editor-border bg-editor-panel-muted px-2 text-[9px] font-semibold text-editor-text outline-none focus:border-editor-border-strong"
                            >
                                <option
                                    v-for="catalogPage in selectedKit.pages"
                                    :key="catalogPage.slug"
                                    :value="catalogPage.slug"
                                >
                                    {{ catalogPage.title }}
                                </option>
                            </select>
                        </label>

                        <button
                            type="button"
                            class="inline-flex h-[30px] items-center gap-1.5 rounded-[5px] border border-editor-border bg-editor-panel-muted px-2 text-[9px] font-semibold text-editor-text-muted transition hover:text-editor-text"
                            :aria-pressed="fitPreview"
                            @click="fitPreview = !fitPreview"
                        >
                            <Maximize2 :size="11" />
                            {{ fitPreview ? 'Fit' : '100%' }}
                        </button>

                        <div
                            class="inline-flex w-fit rounded-[5px] border border-editor-border bg-editor-panel-muted p-1"
                            aria-label="Preview size"
                        >
                            <button
                                v-for="mode in [
                                    'desktop',
                                    'tablet',
                                    'mobile',
                                ] as PreviewMode[]"
                                :key="mode"
                                type="button"
                                class="inline-flex h-7 items-center gap-1.5 rounded-[3px] px-2.5 text-[9px] font-semibold capitalize transition"
                                :class="
                                    previewMode === mode
                                        ? 'bg-editor-text text-white'
                                        : 'text-editor-text-muted hover:text-editor-text'
                                "
                                @click="previewMode = mode"
                            >
                                <Monitor v-if="mode === 'desktop'" :size="11" />
                                <Laptop
                                    v-else-if="mode === 'tablet'"
                                    :size="11"
                                />
                                <Smartphone v-else :size="11" />
                                {{ mode }}
                            </button>
                        </div>

                        <button
                            v-if="can_apply_site_kit"
                            type="button"
                            :disabled="
                                !canUseSelectedKit || applyHttp.processing
                            "
                            class="inline-flex h-9 items-center justify-center gap-2 rounded-[5px] bg-editor-text px-4 text-[10px] font-bold text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-45"
                            @click="applyKit"
                        >
                            <Loader2
                                v-if="applyHttp.processing"
                                :size="13"
                                class="animate-spin"
                            />
                            <Sparkles
                                v-else-if="selectedIsPremium"
                                :size="13"
                            />
                            <Check v-else :size="13" />
                            {{
                                applyHttp.processing
                                    ? 'Applying…'
                                    : selectedIsPremium
                                      ? 'Premium — coming soon'
                                      : 'Use this design'
                            }}
                        </button>
                    </div>

                    <div
                        class="min-h-0 flex-1 overflow-auto rounded-[7px] border border-editor-border bg-editor-panel-muted p-3 sm:p-5"
                    >
                        <div
                            class="canvas-runtime mx-auto min-h-[640px] overflow-hidden bg-[var(--theme-bg)] text-[var(--theme-text)] shadow-[var(--editor-shadow)] transition-[width] duration-200"
                            :style="[
                                {
                                    width: previewWidth,
                                    maxWidth: fitPreview ? '100%' : 'none',
                                },
                                cssVars,
                            ]"
                        >
                            <Transition name="kit-preview" mode="out-in">
                                <div
                                    :key="`${selectedKit.key}:${selectedPreviewPage?.slug}`"
                                    class="pointer-events-none"
                                >
                                    <SiteHeader
                                        :navigation-config="
                                            selectedKit.navigation_config
                                        "
                                        :pages="selectedKit.pages"
                                        :tenant-name="selectedKit.label"
                                        :is-editable="true"
                                        :current-page-slug="
                                            selectedPreviewPage?.slug
                                        "
                                    />

                                    <div
                                        class="mx-auto min-h-[520px] max-w-[1180px]"
                                    >
                                        <RenderPublicNode
                                            v-for="node in selectedPreviewPage?.preview_blocks ||
                                            selectedKit.preview_blocks"
                                            :key="node.id"
                                            :node="node"
                                        />
                                    </div>

                                    <SiteFooter
                                        :navigation-config="
                                            selectedKit.navigation_config
                                        "
                                        :tenant-name="selectedKit.label"
                                    />
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.kits-workspace {
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
}

.canvas-runtime {
    container-type: inline-size;
}

/* Staggered entrance for kit cards in the catalog rail */
@keyframes kit-card-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: none;
    }
}

.kit-card {
    animation: kit-card-in 420ms cubic-bezier(0.22, 1, 0.36, 1) both;
    animation-delay: var(--card-delay, 0ms);
}

/* Crossfade + lift when switching between kits in the live preview */
.kit-preview-enter-active {
    transition:
        opacity 360ms cubic-bezier(0.22, 1, 0.36, 1),
        transform 360ms cubic-bezier(0.22, 1, 0.36, 1);
}

.kit-preview-leave-active {
    transition:
        opacity 160ms ease,
        transform 160ms ease;
}

.kit-preview-enter-from {
    opacity: 0;
    transform: translateY(14px);
}

.kit-preview-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

@media (prefers-reduced-motion: reduce) {
    .canvas-runtime {
        transition: none;
    }

    .kit-card {
        animation: none;
    }

    .kit-preview-enter-active,
    .kit-preview-leave-active {
        transition: none;
    }
}
</style>
