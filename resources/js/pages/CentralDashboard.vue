<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    ArrowUpRight,
    Check,
    ChevronRight,
    Circle,
    ExternalLink,
    FileText,
    Home,
    Images,
    LayoutDashboard,
    LayoutTemplate,
    LogOut,
    PanelTop,
    Pencil,
    Settings,
    Sparkles,
} from '@lucide/vue';
import { computed } from 'vue';

import { editor } from '@/routes/tenant';
import { index as designLibrary } from '@/routes/tenant/designs';

defineOptions({ layout: [] });

interface ThemeConfig {
    colors?: {
        primary?: string;
        secondary?: string;
        background?: string;
        text?: string;
    };
    typography?: {
        headingFont?: string;
        bodyFont?: string;
    };
    borderRadius?: string;
}

interface NavigationConfig {
    header?: {
        items?: Array<{ label: string; slug: string }>;
        ctaButton?: { show?: boolean; label?: string };
    };
}

interface SiteSummary {
    page_count: number;
    published_page_count: number;
    last_edited_at?: string | null;
}

interface PageSummary {
    id: number;
    title: string;
    slug: string;
    is_homepage: boolean;
    is_published: boolean;
    updated_at?: string | null;
}

const props = defineProps<{
    tenant?: {
        id: number;
        subdomain: string;
    } | null;
    public_url?: string | null;
    theme_config?: ThemeConfig | null;
    navigation_config?: NavigationConfig | null;
    can_apply_site_kit?: boolean;
    site_summary?: SiteSummary;
    pages?: PageSummary[];
    central_navigation: {
        account_settings_url: string;
        logout_url: string;
        csrf_token: string;
    };
}>();

const inertiaPage = usePage();
const user = computed(() => inertiaPage.props.auth.user);

const greeting = computed(() => {
    const hour = new Date().getHours();

    if (hour < 12) {
        return 'Good morning';
    }

    if (hour < 18) {
        return 'Good afternoon';
    }

    return 'Good evening';
});

const summary = computed<SiteSummary>(
    () =>
        props.site_summary ?? {
            page_count: 0,
            published_page_count: 0,
            last_edited_at: null,
        },
);

const pageList = computed<PageSummary[]>(() => props.pages ?? []);
const visiblePages = computed(() => pageList.value.slice(0, 6));
const hiddenPageCount = computed(
    () => Math.max(0, pageList.value.length - visiblePages.value.length),
);

const hasPublishedSite = computed(() => summary.value.published_page_count > 0);

const tenantPublicUrl = computed(() => {
    if (props.public_url) {
        return props.public_url;
    }

    // Client-only fallback for older payloads; avoid reading window during SSR.
    if (props.tenant && typeof window !== 'undefined') {
        return window.location.origin;
    }

    return '';
});

const workspaceHost = computed(() => {
    if (!tenantPublicUrl.value) {
        return 'No workspace assigned';
    }

    try {
        return new URL(tenantPublicUrl.value).host;
    } catch {
        return tenantPublicUrl.value.replace(/^https?:\/\//, '');
    }
});

const primaryWorkspaceUrl = computed(() => {
    if (!props.tenant) {
        return '';
    }

    return props.can_apply_site_kit
        ? designLibrary.url(props.tenant.subdomain)
        : editor.url(props.tenant.subdomain);
});

const relativeTime = (iso?: string | null) => {
    if (!iso) {
        return '';
    }

    const diffMs = Date.now() - new Date(iso).getTime();
    const minutes = Math.floor(diffMs / 60000);

    if (minutes < 1) {
        return 'just now';
    }

    if (minutes < 60) {
        return `${minutes}m ago`;
    }

    const hours = Math.floor(minutes / 60);

    if (hours < 24) {
        return `${hours}h ago`;
    }

    const days = Math.floor(hours / 24);

    if (days < 30) {
        return `${days}d ago`;
    }

    return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(
        new Date(iso),
    );
};

const themeColors = computed(() => ({
    primary: props.theme_config?.colors?.primary ?? '#4f46e5',
    secondary: props.theme_config?.colors?.secondary ?? '#0ea5e9',
    background: props.theme_config?.colors?.background ?? '#ffffff',
    text: props.theme_config?.colors?.text ?? '#0f172a',
}));

const themeFonts = computed(() => ({
    heading: props.theme_config?.typography?.headingFont ?? 'Instrument Sans',
    body: props.theme_config?.typography?.bodyFont ?? 'Inter',
}));

const previewNavItems = computed(() => {
    const items = props.navigation_config?.header?.items ?? [];

    if (items.length) {
        return items.slice(0, 4).map((item) => item.label);
    }

    return pageList.value.slice(0, 4).map((page) => page.title);
});

const previewCta = computed(() => {
    const cta = props.navigation_config?.header?.ctaButton;

    return cta?.show ? cta.label || 'Contact' : '';
});

const setupSteps = computed(() => [
    {
        key: 'design',
        label: 'Pick a starting design',
        description: 'Apply a business kit or start from scratch.',
        done: !props.can_apply_site_kit,
        href: props.tenant ? designLibrary.url(props.tenant.subdomain) : '',
    },
    {
        key: 'pages',
        label: 'Build your pages',
        description: 'Edit sections, content, and navigation in the builder.',
        done: summary.value.page_count > 0 && !!summary.value.last_edited_at,
        href: props.tenant ? editor.url(props.tenant.subdomain) : '',
    },
    {
        key: 'publish',
        label: 'Publish your website',
        description: 'Push your draft live so customers can find you.',
        done: hasPublishedSite.value,
        href: props.tenant ? editor.url(props.tenant.subdomain) : '',
    },
]);

const completedSteps = computed(
    () => setupSteps.value.filter((step) => step.done).length,
);
const setupComplete = computed(
    () => completedSteps.value === setupSteps.value.length,
);
const setupProgress = computed(() =>
    Math.round((completedSteps.value / setupSteps.value.length) * 100),
);
</script>

<template>
    <Head title="Dashboard" />

    <div class="min-h-screen bg-editor-bg font-sans text-editor-text">
        <header
            class="sticky top-0 z-40 flex h-[54px] items-center justify-between gap-4 border-b border-editor-border bg-editor-panel px-3 sm:px-5"
        >
            <div class="flex min-w-0 items-center gap-3">
                <span
                    class="flex size-8 shrink-0 items-center justify-center rounded-[6px] bg-editor-text text-white"
                >
                    <LayoutDashboard :size="15" />
                </span>
                <div class="min-w-0">
                    <strong
                        class="block text-[11px] leading-4 font-bold tracking-[0.02em]"
                    >
                        Nexura
                    </strong>
                    <span
                        class="block truncate text-[10px] leading-3 text-editor-text-muted"
                    >
                        {{ props.tenant?.subdomain || 'Account' }} workspace
                    </span>
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <Link
                    v-if="tenant && !can_apply_site_kit"
                    :href="editor(tenant.subdomain)"
                    class="inline-flex h-8 items-center gap-1.5 rounded-[6px] bg-editor-text px-3 text-[11px] font-bold text-white transition hover:brightness-90"
                >
                    <Pencil :size="12" />
                    <span class="hidden sm:inline">Edit website</span>
                </Link>
                <a
                    :href="central_navigation.account_settings_url"
                    class="inline-flex h-8 items-center gap-1.5 rounded-[6px] border border-editor-border bg-editor-panel px-2.5 text-[11px] font-semibold text-editor-text-muted transition hover:border-editor-border-strong hover:text-editor-text"
                >
                    <Settings :size="13" />
                    <span class="hidden sm:inline">Account</span>
                </a>
                <form :action="central_navigation.logout_url" method="post">
                    <input
                        type="hidden"
                        name="_token"
                        :value="central_navigation.csrf_token"
                    />
                    <button
                        type="submit"
                        class="inline-flex size-8 items-center justify-center rounded-[6px] border border-editor-border bg-editor-panel text-editor-text-muted transition hover:border-editor-border-strong hover:text-editor-text"
                        title="Log out"
                        aria-label="Log out"
                    >
                        <LogOut :size="14" />
                    </button>
                </form>
            </div>
        </header>

        <main class="mx-auto w-full max-w-[1160px] p-4 sm:p-6 lg:p-8">
            <template v-if="tenant">
                <!-- Hero: greeting + status + live site preview -->
                <section
                    class="grid items-center gap-8 py-4 lg:grid-cols-[minmax(0,1fr)_minmax(320px,420px)] lg:gap-12 lg:py-6"
                >
                    <div>
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold tracking-[0.04em] uppercase"
                                :class="
                                    hasPublishedSite
                                        ? 'bg-emerald-100 text-emerald-800'
                                        : 'bg-amber-100 text-amber-800'
                                "
                            >
                                <span
                                    class="size-1.5 rounded-full"
                                    :class="
                                        hasPublishedSite
                                            ? 'bg-emerald-500'
                                            : 'bg-amber-500'
                                    "
                                ></span>
                                {{ hasPublishedSite ? 'Live' : 'Draft' }}
                            </span>
                            <span class="text-[11px] text-editor-text-muted">
                                {{ summary.published_page_count }} of
                                {{ summary.page_count }} pages published
                            </span>
                        </div>

                        <h1
                            class="mt-3 text-[26px] leading-tight font-semibold tracking-tight sm:text-[30px]"
                        >
                            {{ greeting }}, {{ user?.name }}
                        </h1>
                        <p
                            class="mt-2 max-w-md text-[13px] leading-6 text-editor-text-muted"
                        >
                            <template v-if="can_apply_site_kit">
                                Your workspace is ready. Pick a business kit to
                                start with pages, theme, and navigation done
                                for you.
                            </template>
                            <template v-else-if="!hasPublishedSite">
                                Your draft is taking shape. Publish when you're
                                ready to go live at
                                <strong class="font-semibold text-editor-text">{{
                                    workspaceHost
                                }}</strong
                                >.
                            </template>
                            <template v-else>
                                Your website is live at
                                <strong class="font-semibold text-editor-text">{{
                                    workspaceHost
                                }}</strong
                                >. Keep it fresh — edits stay in draft until
                                you publish.
                            </template>
                        </p>

                        <div class="mt-5 flex flex-wrap items-center gap-2.5">
                            <Link
                                :href="primaryWorkspaceUrl"
                                class="inline-flex h-10 items-center gap-2 rounded-[7px] bg-editor-text px-4.5 text-[12px] font-bold text-white shadow-[0_10px_28px_rgb(24_24_27/18%)] transition hover:brightness-90"
                            >
                                <Sparkles
                                    v-if="can_apply_site_kit"
                                    :size="14"
                                />
                                <Pencil v-else :size="13" />
                                {{
                                    can_apply_site_kit
                                        ? 'Choose a business kit'
                                        : 'Edit website'
                                }}
                            </Link>
                            <a
                                :href="tenantPublicUrl"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex h-10 items-center gap-1.5 rounded-[7px] border border-editor-border-strong bg-editor-panel px-4 text-[12px] font-semibold transition hover:border-editor-text"
                            >
                                View live site
                                <ArrowUpRight :size="13" />
                            </a>
                        </div>
                    </div>

                    <!-- Miniature site preview rendered from the tenant theme -->
                    <a
                        :href="tenantPublicUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group block"
                        aria-label="Open live site preview"
                    >
                        <div
                            class="overflow-hidden rounded-[10px] border border-editor-border bg-editor-panel shadow-[0_24px_60px_rgb(24_24_27/12%)] transition duration-200 group-hover:-translate-y-0.5 group-hover:shadow-[0_30px_70px_rgb(24_24_27/18%)]"
                        >
                            <div
                                class="flex items-center gap-2 border-b border-editor-border bg-editor-panel-muted px-3 py-2"
                            >
                                <span class="flex gap-1.5">
                                    <span
                                        class="size-2 rounded-full bg-[#f87171]"
                                    ></span>
                                    <span
                                        class="size-2 rounded-full bg-[#fbbf24]"
                                    ></span>
                                    <span
                                        class="size-2 rounded-full bg-[#34d399]"
                                    ></span>
                                </span>
                                <span
                                    class="flex-1 truncate rounded-[4px] border border-editor-border bg-editor-panel px-2 py-0.5 text-center text-[9px] text-editor-text-muted"
                                >
                                    {{ workspaceHost }}
                                </span>
                                <ExternalLink
                                    :size="11"
                                    class="text-editor-text-muted transition group-hover:text-editor-text"
                                />
                            </div>

                            <div
                                class="min-h-[190px] px-5 pt-4 pb-5"
                                :style="{
                                    backgroundColor: themeColors.background,
                                    color: themeColors.text,
                                }"
                            >
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <strong
                                        class="text-[11px] font-bold"
                                        :style="{
                                            fontFamily: `'${themeFonts.heading}', serif`,
                                        }"
                                    >
                                        {{ tenant.subdomain }}
                                    </strong>
                                    <span
                                        class="flex items-center gap-2.5 text-[7.5px] opacity-70"
                                    >
                                        <span
                                            v-for="item in previewNavItems"
                                            :key="item"
                                            >{{ item }}</span
                                        >
                                    </span>
                                    <span
                                        v-if="previewCta"
                                        class="rounded-[4px] px-2 py-1 text-[7.5px] font-bold text-white"
                                        :style="{
                                            backgroundColor:
                                                themeColors.primary,
                                        }"
                                    >
                                        {{ previewCta }}
                                    </span>
                                </div>

                                <div class="mt-7 text-center">
                                    <div
                                        class="mx-auto max-w-[240px] text-[17px] leading-snug font-bold"
                                        :style="{
                                            fontFamily: `'${themeFonts.heading}', serif`,
                                        }"
                                    >
                                        {{
                                            pageList[0]?.title ||
                                            'Your website'
                                        }}
                                    </div>
                                    <div
                                        class="mx-auto mt-2 h-1.5 w-32 rounded-full opacity-25"
                                        :style="{
                                            backgroundColor: themeColors.text,
                                        }"
                                    ></div>
                                    <div
                                        class="mx-auto mt-1.5 h-1.5 w-24 rounded-full opacity-15"
                                        :style="{
                                            backgroundColor: themeColors.text,
                                        }"
                                    ></div>
                                    <span
                                        class="mt-4 inline-block rounded-[5px] px-3 py-1.5 text-[8px] font-bold text-white"
                                        :style="{
                                            backgroundColor:
                                                themeColors.primary,
                                        }"
                                    >
                                        {{
                                            hasPublishedSite
                                                ? 'Visit site'
                                                : 'Preview draft'
                                        }}
                                    </span>
                                </div>

                                <div class="mt-6 grid grid-cols-3 gap-2">
                                    <div
                                        v-for="stub in 3"
                                        :key="stub"
                                        class="h-9 rounded-[4px] opacity-[0.08]"
                                        :style="{
                                            backgroundColor: themeColors.text,
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </a>
                </section>

                <!-- Body: pages + right rail -->
                <div
                    class="mt-4 grid items-start gap-5 lg:grid-cols-[minmax(0,1.4fr)_minmax(280px,1fr)]"
                >
                    <!-- Pages -->
                    <section
                        class="overflow-hidden rounded-[10px] border border-editor-border bg-editor-panel"
                    >
                        <div
                            class="flex items-center justify-between gap-3 border-b border-editor-border px-5 py-4"
                        >
                            <div>
                                <h2 class="text-[13px] font-semibold">
                                    Pages
                                </h2>
                                <p
                                    class="mt-0.5 text-[11px] text-editor-text-muted"
                                >
                                    {{ summary.page_count }} page{{
                                        summary.page_count === 1 ? '' : 's'
                                    }}
                                    · edited
                                    {{
                                        relativeTime(summary.last_edited_at) ||
                                        'never'
                                    }}
                                </p>
                            </div>
                            <Link
                                :href="editor(tenant.subdomain)"
                                class="inline-flex h-8 items-center gap-1.5 rounded-[6px] border border-editor-border bg-editor-panel-muted px-3 text-[10.5px] font-bold transition hover:border-editor-border-strong"
                            >
                                Open builder
                                <ArrowRight :size="12" />
                            </Link>
                        </div>

                        <ul
                            v-if="visiblePages.length"
                            class="divide-y divide-editor-border"
                        >
                            <li
                                v-for="sitePage in visiblePages"
                                :key="sitePage.id"
                            >
                                <Link
                                    :href="`${editor.url(tenant.subdomain)}?page=${sitePage.slug}`"
                                    class="group flex items-center gap-3 px-5 py-3 transition hover:bg-editor-panel-muted"
                                >
                                    <span
                                        class="flex size-7 shrink-0 items-center justify-center rounded-[6px] border border-editor-border bg-editor-panel-muted text-editor-text-muted"
                                    >
                                        <Home
                                            v-if="sitePage.is_homepage"
                                            :size="12"
                                        />
                                        <FileText v-else :size="12" />
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <strong
                                            class="block truncate text-[12px] font-semibold"
                                        >
                                            {{ sitePage.title }}
                                        </strong>
                                        <code
                                            class="block truncate font-mono text-[9.5px] text-editor-text-muted"
                                        >
                                            /{{
                                                sitePage.slug === 'home'
                                                    ? ''
                                                    : sitePage.slug
                                            }}
                                        </code>
                                    </span>
                                    <span
                                        class="hidden text-[10px] text-editor-text-muted sm:block"
                                    >
                                        {{ relativeTime(sitePage.updated_at) }}
                                    </span>
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[8.5px] font-bold tracking-[0.05em] uppercase"
                                        :class="
                                            sitePage.is_published
                                                ? 'bg-emerald-100 text-emerald-800'
                                                : 'bg-editor-panel-muted text-editor-text-muted'
                                        "
                                    >
                                        {{
                                            sitePage.is_published
                                                ? 'Live'
                                                : 'Draft'
                                        }}
                                    </span>
                                    <ChevronRight
                                        :size="13"
                                        class="text-editor-border-strong transition group-hover:text-editor-text"
                                    />
                                </Link>
                            </li>
                        </ul>

                        <div v-else class="px-5 py-10 text-center">
                            <FileText
                                :size="20"
                                class="mx-auto text-editor-border-strong"
                            />
                            <p class="mt-2 text-[12px] font-semibold">
                                No pages yet
                            </p>
                            <p
                                class="mx-auto mt-1 max-w-xs text-[11px] leading-5 text-editor-text-muted"
                            >
                                {{
                                    can_apply_site_kit
                                        ? 'Apply a business kit to start with a full set of pages, or start from scratch.'
                                        : 'Open the builder to create your first page.'
                                }}
                            </p>
                            <Link
                                :href="primaryWorkspaceUrl"
                                class="mt-4 inline-flex h-9 items-center gap-1.5 rounded-[6px] bg-editor-text px-4 text-[11px] font-bold text-white transition hover:brightness-90"
                            >
                                <Sparkles
                                    v-if="can_apply_site_kit"
                                    :size="13"
                                />
                                {{
                                    can_apply_site_kit
                                        ? 'Browse kits'
                                        : 'Create a page'
                                }}
                            </Link>
                        </div>

                        <div
                            v-if="hiddenPageCount"
                            class="border-t border-editor-border px-5 py-2.5 text-[10.5px] text-editor-text-muted"
                        >
                            + {{ hiddenPageCount }} more in the builder
                        </div>
                    </section>

                    <!-- Right rail -->
                    <div class="grid gap-5">
                        <!-- Setup guide / health -->
                        <section
                            class="rounded-[10px] border border-editor-border bg-editor-panel p-5"
                        >
                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <h2 class="text-[13px] font-semibold">
                                    {{
                                        setupComplete
                                            ? 'Website health'
                                            : 'Setup guide'
                                    }}
                                </h2>
                                <span
                                    class="text-[10px] font-bold text-editor-text-muted"
                                >
                                    {{ completedSteps }}/{{
                                        setupSteps.length
                                    }}
                                </span>
                            </div>

                            <div
                                class="mt-3 h-1.5 overflow-hidden rounded-full bg-editor-panel-muted"
                            >
                                <div
                                    class="h-full rounded-full bg-emerald-500 transition-all duration-500"
                                    :style="{ width: `${setupProgress}%` }"
                                ></div>
                            </div>

                            <ul class="mt-4 grid gap-1">
                                <li
                                    v-for="step in setupSteps"
                                    :key="step.key"
                                >
                                    <Link
                                        :href="step.href"
                                        class="group flex items-start gap-2.5 rounded-[7px] px-2 py-2 transition hover:bg-editor-panel-muted"
                                    >
                                        <span
                                            class="mt-0.5 flex size-4.5 shrink-0 items-center justify-center rounded-full"
                                            :class="
                                                step.done
                                                    ? 'bg-emerald-500 text-white'
                                                    : 'border border-dashed border-editor-border-strong text-transparent'
                                            "
                                        >
                                            <Check
                                                v-if="step.done"
                                                :size="10"
                                            />
                                            <Circle v-else :size="10" />
                                        </span>
                                        <span class="min-w-0">
                                            <strong
                                                class="block text-[11.5px] font-semibold"
                                                :class="{
                                                    'text-editor-text-muted line-through decoration-editor-border-strong':
                                                        step.done,
                                                }"
                                            >
                                                {{ step.label }}
                                            </strong>
                                            <span
                                                v-if="!step.done"
                                                class="mt-0.5 block text-[10px] leading-4 text-editor-text-muted"
                                            >
                                                {{ step.description }}
                                            </span>
                                        </span>
                                    </Link>
                                </li>
                            </ul>
                        </section>

                        <!-- Theme -->
                        <section
                            class="rounded-[10px] border border-editor-border bg-editor-panel p-5"
                        >
                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <h2 class="text-[13px] font-semibold">
                                    Theme
                                </h2>
                                <Link
                                    :href="
                                        editor(tenant.subdomain, {
                                            query: { workspace: 'theme' },
                                        })
                                    "
                                    class="inline-flex items-center gap-1 text-[10.5px] font-bold text-editor-text-muted transition hover:text-editor-text"
                                >
                                    Customize
                                    <ArrowRight :size="11" />
                                </Link>
                            </div>

                            <div class="mt-3.5 flex items-center gap-1.5">
                                <span
                                    v-for="(color, name) in themeColors"
                                    :key="name"
                                    class="size-7 rounded-full border border-editor-border shadow-[inset_0_1px_2px_rgb(24_24_27/6%)]"
                                    :style="{ backgroundColor: color }"
                                    :title="`${name}: ${color}`"
                                ></span>
                                <span
                                    class="ml-2 min-w-0 truncate text-[10.5px] text-editor-text-muted"
                                >
                                    {{ themeFonts.heading }} ·
                                    {{ themeFonts.body }}
                                </span>
                            </div>
                        </section>

                        <!-- Shortcuts -->
                        <section
                            class="overflow-hidden rounded-[10px] border border-editor-border bg-editor-panel"
                        >
                            <h2
                                class="border-b border-editor-border px-5 py-3.5 text-[13px] font-semibold"
                            >
                                Quick actions
                            </h2>
                            <div class="divide-y divide-editor-border">
                                <Link
                                    :href="
                                        editor(tenant.subdomain, {
                                            query: {
                                                workspace: 'navigation',
                                            },
                                        })
                                    "
                                    class="group flex items-center gap-3 px-5 py-3 transition hover:bg-editor-panel-muted"
                                >
                                    <PanelTop
                                        :size="14"
                                        class="text-editor-text-muted"
                                    />
                                    <span class="flex-1 text-[11.5px] font-semibold"
                                        >Edit navigation</span
                                    >
                                    <ChevronRight
                                        :size="13"
                                        class="text-editor-border-strong transition group-hover:text-editor-text"
                                    />
                                </Link>
                                <Link
                                    :href="editor(tenant.subdomain)"
                                    class="group flex items-center gap-3 px-5 py-3 transition hover:bg-editor-panel-muted"
                                >
                                    <Images
                                        :size="14"
                                        class="text-editor-text-muted"
                                    />
                                    <span class="flex-1 text-[11.5px] font-semibold"
                                        >Manage media</span
                                    >
                                    <ChevronRight
                                        :size="13"
                                        class="text-editor-border-strong transition group-hover:text-editor-text"
                                    />
                                </Link>
                                <Link
                                    :href="designLibrary(tenant.subdomain)"
                                    class="group flex items-center gap-3 px-5 py-3 transition hover:bg-editor-panel-muted"
                                >
                                    <LayoutTemplate
                                        :size="14"
                                        class="text-editor-text-muted"
                                    />
                                    <span class="flex-1 text-[11.5px] font-semibold"
                                        >Browse kits</span
                                    >
                                    <ChevronRight
                                        :size="13"
                                        class="text-editor-border-strong transition group-hover:text-editor-text"
                                    />
                                </Link>
                            </div>
                        </section>
                    </div>
                </div>
            </template>

            <section
                v-else
                class="rounded-[10px] border border-red-300 bg-red-50 p-6 text-red-900"
            >
                <h2 class="text-sm font-semibold">Workspace unavailable</h2>
                <p class="mt-2 text-xs">
                    No tenant is attached to this account. Please register a
                    workspace or contact support.
                </p>
            </section>
        </main>
    </div>
</template>
