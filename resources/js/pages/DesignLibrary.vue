<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Check,
    ChevronRight,
    Eye,
    ImageIcon,
    Laptop,
    LockKeyhole,
    Monitor,
    Smartphone,
} from '@lucide/vue';
import { computed, provide, ref } from 'vue';

import RenderPublicNode from '@/components/BuilderBlocks/RenderPublicNode.vue';
import SiteFooter from '@/components/SiteFooter.vue';
import SiteHeader from '@/components/SiteHeader.vue';
import { blockComponents } from '@/lib/blockRegistry';
import { useTheme } from '@/lib/theme';
import { dashboard } from '@/routes';
import { editor } from '@/routes/tenant';

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

interface NavigationConfig {
    header?: {
        showLogo?: boolean;
        items?: Array<{ label: string; slug: string }>;
        ctaButton?: { show?: boolean; label?: string; slug?: string };
    };
    footer?: { copyright?: string };
}

interface CatalogPage {
    title: string;
    slug: string;
    is_homepage: boolean;
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
const selectedKit = computed(
    () =>
        props.kits.find((kit) => kit.key === selectedKitKey.value) ??
        props.kits[0],
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

const { cssVars, fontUrl } = useTheme(() => selectedKit.value?.theme_config);

provide('blockRegistry', blockComponents);
provide('isEditable', false);
provide('showMediaPlaceholders', true);
</script>

<template>
    <Head title="Design library">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin="anonymous"
        />
        <link rel="stylesheet" :href="fontUrl" />
    </Head>

    <div
        class="grid min-h-screen grid-rows-[54px_minmax(0,1fr)] bg-[#0c0c0d] text-zinc-100"
    >
        <header
            class="flex min-w-0 items-center justify-between gap-4 border-b border-[#252527] bg-[#0c0c0d] px-3 sm:px-4"
        >
            <div class="flex min-w-0 items-center gap-3">
                <Link
                    :href="dashboard(props.tenant.subdomain)"
                    class="inline-flex size-8 shrink-0 items-center justify-center rounded-[5px] border border-[#303033] bg-[#171719] text-zinc-400 transition hover:bg-[#29292c] hover:text-white"
                    aria-label="Back to dashboard"
                >
                    <ArrowLeft :size="15" />
                </Link>
                <div class="min-w-0 border-l border-[#2b2b2e] pl-3">
                    <span
                        class="block text-[10px] font-bold tracking-[0.15em] text-[#7c8eae] uppercase"
                        >Design library</span
                    >
                    <span class="block truncate text-[10px] text-zinc-500">{{
                        props.tenant.subdomain
                    }}</span>
                </div>
            </div>

            <Link
                v-if="!props.can_apply_site_kit"
                :href="editor(props.tenant.subdomain)"
                class="inline-flex h-8 items-center justify-center gap-2 rounded-[5px] border border-[#303033] bg-[#f4f4f5] px-3 text-[10px] font-semibold text-zinc-950 transition hover:bg-white"
            >
                Open editor
                <ChevronRight :size="13" />
            </Link>
        </header>

        <div
            class="grid min-h-0 grid-cols-1 lg:grid-cols-[310px_minmax(0,1fr)]"
        >
            <aside
                class="border-b border-[#29292c] bg-[#101011] lg:min-h-0 lg:overflow-y-auto lg:border-r lg:border-b-0"
            >
                <div class="border-b border-[#29292c] p-4 sm:p-5">
                    <span
                        class="text-[9px] font-bold tracking-[0.14em] text-[#7c8eae] uppercase"
                        >Professional site kits</span
                    >
                    <h1
                        class="mt-2 text-lg font-semibold tracking-tight text-zinc-100"
                    >
                        Choose a starting direction
                    </h1>
                    <p class="mt-2 text-xs leading-5 text-zinc-500">
                        Preview the homepage style and included pages before any
                        workspace data is changed.
                    </p>
                </div>

                <div class="grid gap-2 p-3 sm:grid-cols-3 lg:grid-cols-1">
                    <button
                        v-for="kit in props.kits"
                        :key="kit.key"
                        type="button"
                        class="group rounded-[6px] border p-4 text-left transition"
                        :class="
                            selectedKit?.key === kit.key
                                ? 'border-[#596579] bg-[#1b1d21]'
                                : 'border-[#29292c] bg-[#151517] hover:border-[#3c3c41] hover:bg-[#19191c]'
                        "
                        @click="selectedKitKey = kit.key"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span
                                    class="text-[9px] font-bold tracking-[0.12em] text-[#7c8eae] uppercase"
                                    >{{ kit.industry }}</span
                                >
                                <h2
                                    class="mt-1 text-sm font-semibold text-zinc-100"
                                >
                                    {{ kit.label }}
                                </h2>
                            </div>
                            <span
                                class="mt-0.5 flex size-5 items-center justify-center rounded-full border"
                                :class="
                                    selectedKit?.key === kit.key
                                        ? 'border-blue-300/50 bg-blue-300/10 text-blue-200'
                                        : 'border-[#343438] text-zinc-600'
                                "
                            >
                                <Check
                                    v-if="selectedKit?.key === kit.key"
                                    :size="11"
                                />
                            </span>
                        </div>
                        <p class="mt-3 text-[11px] leading-5 text-zinc-500">
                            {{ kit.description }}
                        </p>
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <span
                                v-for="catalogPage in kit.pages"
                                :key="catalogPage.slug"
                                class="rounded-[3px] border border-[#303033] bg-[#111113] px-1.5 py-1 text-[8px] font-semibold tracking-wide text-zinc-500 uppercase"
                            >
                                {{ catalogPage.title }}
                            </span>
                        </div>
                    </button>
                </div>

                <div
                    class="m-3 rounded-[6px] border p-4"
                    :class="
                        props.can_apply_site_kit
                            ? 'border-emerald-400/20 bg-emerald-950/20'
                            : 'border-amber-400/20 bg-amber-950/15'
                    "
                >
                    <div class="flex items-start gap-3">
                        <Check
                            v-if="props.can_apply_site_kit"
                            :size="15"
                            class="mt-0.5 shrink-0 text-emerald-300"
                        />
                        <LockKeyhole
                            v-else
                            :size="15"
                            class="mt-0.5 shrink-0 text-amber-300"
                        />
                        <div>
                            <strong
                                class="block text-[10px] font-semibold"
                                :class="
                                    props.can_apply_site_kit
                                        ? 'text-emerald-200'
                                        : 'text-amber-200'
                                "
                            >
                                {{
                                    props.can_apply_site_kit
                                        ? 'Workspace ready for a kit'
                                        : 'Existing workspace protected'
                                }}
                            </strong>
                            <p class="mt-1 text-[9px] leading-4 text-zinc-500">
                                {{
                                    props.can_apply_site_kit
                                        ? 'Applying the selected kit is the next staged operation.'
                                        : 'You can preview kits, but they cannot replace your current site.'
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </aside>

            <main
                class="design-workspace min-h-0 min-w-0 overflow-y-auto p-3 sm:p-5 lg:p-6"
            >
                <div
                    v-if="selectedKit"
                    class="mx-auto flex w-full max-w-[1320px] flex-col gap-4"
                >
                    <div
                        class="flex flex-col gap-3 rounded-[6px] border border-[#303033] bg-[#111113] p-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <span
                                class="text-[9px] font-bold tracking-[0.13em] text-[#7c8eae] uppercase"
                                >Homepage preview</span
                            >
                            <h2
                                class="mt-1 text-sm font-semibold text-zinc-100"
                            >
                                {{ selectedKit.label }}
                            </h2>
                        </div>

                        <div
                            class="inline-flex w-fit rounded-[5px] border border-[#303033] bg-[#0c0c0d] p-1"
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
                                        ? 'bg-[#303033] text-white'
                                        : 'text-zinc-500 hover:text-zinc-200'
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
                    </div>

                    <div
                        class="overflow-auto rounded-[7px] border border-[#303033] bg-[#080809] p-3 sm:p-5"
                    >
                        <div
                            class="canvas-runtime mx-auto min-h-[720px] overflow-hidden bg-[var(--theme-bg)] text-[var(--theme-text)] shadow-[0_20px_80px_rgb(0_0_0/55%)] transition-[width] duration-200"
                            :style="[
                                { width: previewWidth, maxWidth: '100%' },
                                cssVars,
                            ]"
                        >
                            <div class="pointer-events-none">
                                <SiteHeader
                                    :navigation-config="
                                        selectedKit.navigation_config
                                    "
                                    :pages="selectedKit.pages"
                                    :tenant-name="selectedKit.label"
                                    :is-editable="true"
                                />

                                <div
                                    class="mx-auto min-h-[520px] max-w-[1180px]"
                                >
                                    <RenderPublicNode
                                        v-for="node in selectedKit.preview_blocks"
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
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <div
                            class="rounded-[6px] border border-[#29292c] bg-[#111113] p-4"
                        >
                            <Eye :size="14" class="text-[#8fa3c4]" />
                            <strong class="mt-3 block text-[10px] text-zinc-200"
                                >Existing renderer</strong
                            >
                            <p class="mt-1 text-[9px] leading-4 text-zinc-500">
                                The preview uses the same Vue block components
                                as the public site.
                            </p>
                        </div>
                        <div
                            class="rounded-[6px] border border-[#29292c] bg-[#111113] p-4"
                        >
                            <ImageIcon :size="14" class="text-[#8fa3c4]" />
                            <strong class="mt-3 block text-[10px] text-zinc-200"
                                >Editable media</strong
                            >
                            <p class="mt-1 text-[9px] leading-4 text-zinc-500">
                                Dashed image areas are replaced through the
                                editor’s media picker.
                            </p>
                        </div>
                        <div
                            class="rounded-[6px] border border-[#29292c] bg-[#111113] p-4"
                        >
                            <LockKeyhole :size="14" class="text-[#8fa3c4]" />
                            <strong class="mt-3 block text-[10px] text-zinc-200"
                                >No silent replacement</strong
                            >
                            <p class="mt-1 text-[9px] leading-4 text-zinc-500">
                                Established workspaces remain preview-only and
                                protected.
                            </p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.design-workspace {
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

.canvas-runtime {
    container-type: inline-size;
}

@media (prefers-reduced-motion: reduce) {
    .canvas-runtime {
        transition: none;
    }
}
</style>
