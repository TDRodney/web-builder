<script setup lang="ts">
import { Head, Link, useHttp, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookOpen,
    Check,
    CircleUserRound,
    ExternalLink,
    FolderGit2,
    LayoutDashboard,
    LayoutTemplate,
    LogOut,
    Monitor,
    Palette,
    Radius,
    Save,
    Settings,
    SlidersHorizontal,
    Type,
} from '@lucide/vue';
import { computed } from 'vue';
import { Toaster, toast } from 'vue-sonner';

import { index as designLibrary } from '@/routes/tenant/designs';

defineOptions({ layout: [] });

interface ThemeColors {
    primary: string;
    secondary: string;
    background: string;
    text: string;
}

interface ThemeTypography {
    headingFont: string;
    bodyFont: string;
}

interface ThemeConfig {
    colors: ThemeColors;
    typography: ThemeTypography;
    borderRadius: string;
}

interface HttpError {
    response?: {
        data?: {
            error?: string;
            message?: string;
        };
    };
}

interface ThemeSaveResponse {
    status?: string;
    message?: string;
}

const props = defineProps<{
    tenant?: {
        id: number;
        subdomain: string;
    } | null;
    theme_config?: ThemeConfig | null;
    can_apply_site_kit?: boolean;
    central_navigation: {
        account_settings_url: string;
        logout_url: string;
        csrf_token: string;
    };
}>();

const page = usePage();
const user = computed(() => page.props.auth.user);

const defaultTheme: ThemeConfig = {
    colors: {
        primary: '#4f46e5',
        secondary: '#0ea5e9',
        background: '#ffffff',
        text: '#0f172a',
    },
    typography: {
        headingFont: 'Instrument Sans',
        bodyFont: 'Instrument Sans',
    },
    borderRadius: '8px',
};

const initial = props.theme_config ?? defaultTheme;

const http = useHttp<ThemeConfig, ThemeSaveResponse>({
    colors: { ...initial.colors },
    typography: { ...initial.typography },
    borderRadius: initial.borderRadius,
});

const headingFonts = [
    'Instrument Sans',
    'Inter',
    'Outfit',
    'Poppins',
    'Roboto',
    'Plus Jakarta Sans',
    'Lora',
    'Playfair Display',
    'Merriweather',
    'EB Garamond',
];

const bodyFonts = [
    'Instrument Sans',
    'Inter',
    'Outfit',
    'Poppins',
    'Roboto',
    'Plus Jakarta Sans',
    'Lora',
    'Playfair Display',
    'Merriweather',
    'EB Garamond',
];

const radiusPresets = [
    { label: 'Sharp', value: '0px' },
    { label: 'Rounded', value: '4px' },
    { label: 'Soft', value: '8px' },
    { label: 'Extra', value: '16px' },
    { label: 'Pill', value: '9999px' },
];

const colorPalettes = [
    {
        label: 'Modern Indigo',
        colors: {
            primary: '#4f46e5',
            secondary: '#0ea5e9',
            background: '#ffffff',
            text: '#0f172a',
        },
    },
    {
        label: 'Editorial Warm',
        colors: {
            primary: '#1e293b',
            secondary: '#b45309',
            background: '#fcfbf7',
            text: '#0f172a',
        },
    },
    {
        label: 'Minimal Dark',
        colors: {
            primary: '#3b82f6',
            secondary: '#10b981',
            background: '#0f172a',
            text: '#f8fafc',
        },
    },
    {
        label: 'Forest Mint',
        colors: {
            primary: '#15803d',
            secondary: '#eab308',
            background: '#f0fdf4',
            text: '#14532d',
        },
    },
];

function applyPalette(palette: (typeof colorPalettes)[number]): void {
    Object.assign(http.colors, palette.colors);
}

function updateThemeColor(key: keyof ThemeColors, event: Event): void {
    http.colors[key] = (event.target as HTMLInputElement).value;
}

function updateHeadingFont(event: Event): void {
    http.typography.headingFont = (event.target as HTMLSelectElement).value;
}

function updateBodyFont(event: Event): void {
    http.typography.bodyFont = (event.target as HTMLSelectElement).value;
}

async function saveTheme(): Promise<void> {
    try {
        const response = await http.patch('/theme');

        if (response?.status === 'success') {
            toast.success('Theme settings saved!');

            return;
        }

        toast.error(response?.message || 'Failed to save theme settings');
    } catch (error: unknown) {
        const requestError = error as HttpError;
        const message =
            requestError.response?.data?.message ||
            requestError.response?.data?.error ||
            'Failed to save theme settings';

        toast.error(message);
    }
}

const tenantEditorUrl = computed(() => {
    if (!props.tenant) {
        return '';
    }

    const protocol = window.location.protocol;
    const hostParts = window.location.host.split('.');
    const baseHost =
        hostParts.length > 2
            ? hostParts.slice(-2).join('.')
            : window.location.host;

    return `${protocol}//${props.tenant.subdomain}.${baseHost}/editor`;
});

const tenantPublicUrl = computed(() => {
    if (!props.tenant) {
        return '';
    }

    const protocol = window.location.protocol;
    const hostParts = window.location.host.split('.');
    const baseHost =
        hostParts.length > 2
            ? hostParts.slice(-2).join('.')
            : window.location.host;

    return `${protocol}//${props.tenant.subdomain}.${baseHost}/`;
});

const workspaceHost = computed(() => {
    if (!tenantPublicUrl.value) {
        return 'No workspace assigned';
    }

    return new URL(tenantPublicUrl.value).host;
});
</script>

<template>
    <Head title="Dashboard" />
    <Toaster rich-colors position="bottom-right" />

    <div
        class="grid min-h-screen grid-rows-[54px_minmax(0,1fr)] overflow-hidden bg-[#0c0c0d] font-sans text-zinc-100"
    >
        <header
            class="relative z-50 flex min-w-0 items-center justify-between gap-4 border-b border-[#252527] bg-[#0c0c0d] px-3 sm:px-4"
        >
            <div class="flex min-w-0 items-center gap-3">
                <div
                    class="flex size-8 shrink-0 items-center justify-center rounded-[5px] border border-[#303033] bg-[#171719] text-[#a9bad5]"
                >
                    <LayoutDashboard :size="16" />
                </div>
                <div class="min-w-0 border-l border-[#2b2b2e] pl-3">
                    <span
                        class="block text-[10px] font-bold tracking-[0.15em] text-[#7c8eae] uppercase"
                    >
                        Editor Dashboard
                    </span>
                    <span class="block truncate text-[10px] text-[#9ca3af]">
                        {{ props.tenant?.subdomain || 'Account dashboard' }}
                    </span>
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <a
                    v-if="tenant"
                    :href="tenantPublicUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex h-[30px] items-center justify-center gap-1.5 rounded-[5px] border border-[#303033] bg-[#262629] px-2.5 text-[11px] font-semibold text-white transition hover:border-zinc-600 hover:bg-[#343438] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-300"
                >
                    <span class="hidden sm:inline">Open live link</span>
                    <ExternalLink :size="13" />
                </a>

                <form :action="central_navigation.logout_url" method="post">
                    <input
                        type="hidden"
                        name="_token"
                        :value="central_navigation.csrf_token"
                    />
                    <button
                        type="submit"
                        class="inline-flex size-[30px] items-center justify-center rounded-[5px] border border-[#303033] bg-[#171719] text-zinc-400 transition hover:bg-[#29292c] hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-300"
                        title="Log out"
                        aria-label="Log out"
                    >
                        <LogOut :size="14" />
                    </button>
                </form>
            </div>
        </header>

        <div
            class="grid min-h-0 grid-cols-1 overflow-y-auto min-[880px]:grid-cols-[280px_minmax(0,1fr)] min-[880px]:overflow-hidden"
        >
            <aside
                class="flex min-h-0 flex-col border-b border-[#29292c] bg-[#101011] min-[880px]:border-r min-[880px]:border-b-0"
            >
                <div
                    class="flex min-h-[66px] items-center justify-between border-b border-[#252527] px-4 py-3"
                >
                    <div>
                        <span
                            class="block text-[9px] font-bold tracking-[0.14em] text-[#7c8eae] uppercase"
                        >
                            Workspace
                        </span>
                        <h1
                            class="mt-1 text-[13px] font-semibold text-zinc-100"
                        >
                            Dashboard
                        </h1>
                    </div>
                </div>

                <nav
                    class="grid grid-cols-3 border-b border-[#252527] min-[880px]:grid-cols-1 min-[880px]:border-b-0 min-[880px]:p-3"
                    aria-label="Dashboard sections"
                >
                    <a
                        href="#overview"
                        class="flex h-11 items-center gap-2 border-r border-[#202022] bg-[#171719] px-4 text-[10px] font-bold tracking-[0.05em] text-white uppercase min-[880px]:rounded-[5px] min-[880px]:border min-[880px]:border-[#3f4652]"
                    >
                        <Monitor :size="14" />
                        Overview
                    </a>
                    <a
                        href="#theme-settings"
                        class="flex h-11 items-center gap-2 px-4 text-[10px] font-bold tracking-[0.05em] text-zinc-500 uppercase transition hover:bg-[#151517] hover:text-zinc-200 min-[880px]:mt-1 min-[880px]:rounded-[5px]"
                    >
                        <Palette :size="14" />
                        Site theme
                    </a>
                    <Link
                        v-if="tenant"
                        :href="designLibrary(tenant.subdomain)"
                        class="flex h-11 items-center gap-2 px-4 text-[10px] font-bold tracking-[0.05em] text-zinc-500 uppercase transition hover:bg-[#151517] hover:text-zinc-200 min-[880px]:mt-1 min-[880px]:rounded-[5px]"
                    >
                        <LayoutTemplate :size="14" />
                        Site kits
                    </Link>
                </nav>

                <div
                    class="hidden min-h-0 flex-1 flex-col px-4 py-3 min-[880px]:flex"
                >
                    <div
                        class="rounded-[5px] border border-[#2b2b2e] bg-[#171719] p-3"
                    >
                        <span
                            class="text-[9px] font-bold tracking-[0.12em] text-[#7c8eae] uppercase"
                        >
                            Signed in as
                        </span>
                        <div class="mt-3 flex items-center gap-2.5">
                            <span
                                class="flex size-8 shrink-0 items-center justify-center rounded-full border border-[#3f3f46] bg-[#202023] text-zinc-400"
                            >
                                <CircleUserRound :size="16" />
                            </span>
                            <div class="min-w-0">
                                <strong
                                    class="block truncate text-[11px] font-semibold text-zinc-200"
                                >
                                    {{ user?.name }}
                                </strong>
                                <small
                                    class="block truncate text-[9px] text-zinc-500"
                                >
                                    {{ user?.email }}
                                </small>
                            </div>
                        </div>
                        <a
                            :href="central_navigation.account_settings_url"
                            class="mt-3 inline-flex h-8 w-full items-center justify-center gap-1.5 rounded-[4px] border border-[#303033] bg-[#202023] text-[10px] font-semibold text-zinc-300 transition hover:bg-[#29292c] hover:text-white"
                        >
                            <Settings :size="12" />
                            Account settings
                        </a>
                    </div>

                    <div class="mt-auto border-t border-[#29292c] pt-3">
                        <a
                            href="https://laravel.com/docs/starter-kits#vue"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-2 px-1 py-1.5 text-[9px] font-semibold text-zinc-500 transition hover:text-zinc-300"
                        >
                            <BookOpen :size="12" />
                            Documentation
                        </a>
                        <a
                            href="https://github.com/laravel/vue-starter-kit"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-2 px-1 py-1.5 text-[9px] font-semibold text-zinc-500 transition hover:text-zinc-300"
                        >
                            <FolderGit2 :size="12" />
                            Repository
                        </a>
                    </div>
                </div>
            </aside>

            <main class="dashboard-workspace min-h-0 min-w-0 overflow-y-auto">
                <div
                    class="mx-auto flex w-full max-w-[1180px] flex-col gap-5 p-4 sm:p-6 lg:p-8"
                >
                    <section
                        id="overview"
                        class="overflow-hidden rounded-[7px] border border-[#303033] bg-[#101011] shadow-[0_18px_60px_rgb(0_0_0/38%)]"
                    >
                        <div
                            class="flex flex-col gap-4 border-b border-[#29292c] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                        >
                            <div>
                                <span
                                    class="text-[9px] font-bold tracking-[0.14em] text-[#7c8eae] uppercase"
                                >
                                    Workspace overview
                                </span>
                                <h2
                                    class="mt-1 text-xl font-semibold tracking-tight text-zinc-100"
                                >
                                    Welcome back, {{ user?.name }}
                                </h2>
                                <p class="mt-1 text-xs text-zinc-500">
                                    Manage your storefront and visual identity
                                    from one place.
                                </p>
                            </div>

                            <a
                                v-if="tenant"
                                :href="tenantEditorUrl"
                                class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-[5px] border border-white bg-zinc-100 px-4 text-[11px] font-bold text-[#101011] transition hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-300"
                            >
                                Open visual builder
                                <ArrowRight :size="14" />
                            </a>
                        </div>

                        <div
                            v-if="tenant"
                            class="grid gap-px bg-[#29292c] md:grid-cols-[1.25fr_0.75fr]"
                        >
                            <div class="bg-[#151517] p-5 sm:p-6">
                                <div
                                    class="flex items-start justify-between gap-4"
                                >
                                    <div>
                                        <span
                                            class="text-[9px] font-bold tracking-[0.12em] text-zinc-500 uppercase"
                                        >
                                            Live workspace
                                        </span>
                                        <div
                                            class="mt-2 flex items-center gap-2"
                                        >
                                            <span
                                                class="size-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgb(34_197_94/45%)]"
                                            ></span>
                                            <strong
                                                class="text-sm font-semibold text-zinc-100"
                                            >
                                                Published site available
                                            </strong>
                                        </div>
                                    </div>
                                    <Monitor
                                        :size="18"
                                        class="text-[#7c8eae]"
                                    />
                                </div>

                                <code
                                    class="mt-5 block w-fit rounded-[4px] border border-[#303033] bg-[#0c0c0d] px-2.5 py-1.5 text-[11px] text-[#a9bad5]"
                                >
                                    {{ workspaceHost }}
                                </code>
                                <p
                                    class="mt-3 max-w-xl text-xs leading-5 text-zinc-500"
                                >
                                    Build pages in the editor, preview
                                    responsive layouts, and publish changes to
                                    this dedicated storefront.
                                </p>
                            </div>

                            <div
                                class="flex flex-col justify-between gap-5 bg-[#121214] p-5 sm:p-6"
                            >
                                <div>
                                    <span
                                        class="text-[9px] font-bold tracking-[0.12em] text-zinc-500 uppercase"
                                    >
                                        Quick actions
                                    </span>
                                    <p
                                        class="mt-2 text-xs leading-5 text-zinc-400"
                                    >
                                        Preview professional site kits or
                                        inspect the published experience.
                                    </p>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <Link
                                        v-if="tenant"
                                        :href="designLibrary(tenant.subdomain)"
                                        class="inline-flex h-9 items-center justify-center gap-1.5 rounded-[5px] border border-[#3f4652] bg-[#1b2028] text-[10px] font-semibold text-[#c4d2e8] transition hover:bg-[#252d38] hover:text-white"
                                    >
                                        <LayoutTemplate :size="12" />
                                        {{
                                            props.can_apply_site_kit
                                                ? 'Choose kit'
                                                : 'Browse kits'
                                        }}
                                    </Link>
                                    <a
                                        :href="tenantPublicUrl"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex h-9 items-center justify-center gap-1.5 rounded-[5px] border border-[#303033] bg-[#202023] text-[10px] font-semibold text-zinc-300 transition hover:bg-[#29292c] hover:text-white"
                                    >
                                        <ExternalLink :size="12" />
                                        View live
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div v-else class="bg-[#151517] p-6">
                            <div
                                class="rounded-[5px] border border-red-400/20 bg-red-950/20 p-5"
                            >
                                <span
                                    class="text-[9px] font-bold tracking-[0.12em] text-red-300 uppercase"
                                >
                                    Workspace unavailable
                                </span>
                                <h3
                                    class="mt-2 text-sm font-semibold text-zinc-100"
                                >
                                    No tenant is attached to this account
                                </h3>
                                <p class="mt-2 text-xs leading-5 text-zinc-400">
                                    Please contact support or register a
                                    workspace before opening the visual builder.
                                </p>
                            </div>
                        </div>
                    </section>

                    <section
                        v-if="tenant"
                        id="theme-settings"
                        class="overflow-hidden rounded-[7px] border border-[#303033] bg-[#101011] shadow-[0_18px_60px_rgb(0_0_0/38%)]"
                    >
                        <div
                            class="flex flex-col gap-3 border-b border-[#29292c] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                        >
                            <div>
                                <span
                                    class="text-[9px] font-bold tracking-[0.14em] text-[#7c8eae] uppercase"
                                >
                                    Shared site settings
                                </span>
                                <h2
                                    class="mt-1 text-base font-semibold text-zinc-100"
                                >
                                    Theme controls
                                </h2>
                                <p class="mt-1 text-xs text-zinc-500">
                                    Changes apply across the editor canvas and
                                    published pages.
                                </p>
                            </div>
                            <div
                                class="flex size-9 items-center justify-center rounded-[5px] border border-[#303033] bg-[#18181a] text-[#7c8eae]"
                            >
                                <Palette :size="16" />
                            </div>
                        </div>

                        <div class="grid gap-px bg-[#29292c] lg:grid-cols-2">
                            <div class="space-y-6 bg-[#151517] p-5 sm:p-6">
                                <fieldset>
                                    <legend
                                        class="flex items-center gap-2 text-[9px] font-bold tracking-[0.12em] text-[#7c8eae] uppercase"
                                    >
                                        <Palette :size="13" />
                                        Color palettes
                                    </legend>
                                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                        <button
                                            v-for="palette in colorPalettes"
                                            :key="palette.label"
                                            type="button"
                                            class="group rounded-[5px] border border-[#303033] bg-[#1a1a1c] p-3 text-left transition hover:border-[#5b6d88] hover:bg-[#1d222a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-300"
                                            @click="applyPalette(palette)"
                                        >
                                            <span
                                                class="flex items-center justify-between gap-2"
                                            >
                                                <span class="flex gap-1">
                                                    <span
                                                        v-for="(
                                                            color, key
                                                        ) in palette.colors"
                                                        :key="key"
                                                        class="size-4 rounded-full border border-white/10"
                                                        :style="{
                                                            backgroundColor:
                                                                color,
                                                        }"
                                                    ></span>
                                                </span>
                                                <Check
                                                    :size="12"
                                                    class="text-zinc-600 opacity-0 transition group-hover:opacity-100"
                                                />
                                            </span>
                                            <span
                                                class="mt-2 block text-[10px] font-semibold text-zinc-300"
                                            >
                                                {{ palette.label }}
                                            </span>
                                        </button>
                                    </div>
                                </fieldset>

                                <fieldset>
                                    <legend
                                        class="flex items-center gap-2 text-[9px] font-bold tracking-[0.12em] text-[#7c8eae] uppercase"
                                    >
                                        <SlidersHorizontal :size="13" />
                                        Custom colors
                                    </legend>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        <label
                                            v-for="(value, key) in http.colors"
                                            :key="key"
                                            class="block"
                                        >
                                            <span
                                                class="mb-1.5 block text-[9px] font-semibold text-zinc-500 capitalize"
                                            >
                                                {{ key }}
                                            </span>
                                            <span
                                                class="flex items-center gap-2"
                                            >
                                                <input
                                                    type="color"
                                                    :value="value"
                                                    class="size-9 shrink-0 cursor-pointer rounded-[4px] border border-[#3f3f46] bg-transparent p-0.5"
                                                    @input="
                                                        updateThemeColor(
                                                            key,
                                                            $event,
                                                        )
                                                    "
                                                />
                                                <input
                                                    type="text"
                                                    :value="value"
                                                    class="h-9 min-w-0 flex-1 rounded-[4px] border border-[#303033] bg-[#0f0f10] px-2.5 font-mono text-[10px] text-zinc-300 transition outline-none focus:border-[#64748b] focus:ring-2 focus:ring-blue-300/20"
                                                    @input="
                                                        updateThemeColor(
                                                            key,
                                                            $event,
                                                        )
                                                    "
                                                />
                                            </span>
                                        </label>
                                    </div>
                                </fieldset>
                            </div>

                            <div class="space-y-6 bg-[#121214] p-5 sm:p-6">
                                <fieldset>
                                    <legend
                                        class="flex items-center gap-2 text-[9px] font-bold tracking-[0.12em] text-[#7c8eae] uppercase"
                                    >
                                        <Type :size="13" />
                                        Typography
                                    </legend>
                                    <div
                                        class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2"
                                    >
                                        <label class="block">
                                            <span
                                                class="mb-1.5 block text-[9px] font-semibold text-zinc-500"
                                            >
                                                Heading font
                                            </span>
                                            <select
                                                :value="
                                                    http.typography.headingFont
                                                "
                                                class="h-9 w-full rounded-[4px] border border-[#303033] bg-[#18181a] px-2.5 text-[10px] text-zinc-300 transition outline-none focus:border-[#64748b] focus:ring-2 focus:ring-blue-300/20"
                                                @change="updateHeadingFont"
                                            >
                                                <option
                                                    v-for="font in headingFonts"
                                                    :key="font"
                                                    :value="font"
                                                >
                                                    {{ font }}
                                                </option>
                                            </select>
                                        </label>
                                        <label class="block">
                                            <span
                                                class="mb-1.5 block text-[9px] font-semibold text-zinc-500"
                                            >
                                                Body font
                                            </span>
                                            <select
                                                :value="
                                                    http.typography.bodyFont
                                                "
                                                class="h-9 w-full rounded-[4px] border border-[#303033] bg-[#18181a] px-2.5 text-[10px] text-zinc-300 transition outline-none focus:border-[#64748b] focus:ring-2 focus:ring-blue-300/20"
                                                @change="updateBodyFont"
                                            >
                                                <option
                                                    v-for="font in bodyFonts"
                                                    :key="font"
                                                    :value="font"
                                                >
                                                    {{ font }}
                                                </option>
                                            </select>
                                        </label>
                                    </div>
                                </fieldset>

                                <fieldset>
                                    <legend
                                        class="flex items-center gap-2 text-[9px] font-bold tracking-[0.12em] text-[#7c8eae] uppercase"
                                    >
                                        <Radius :size="13" />
                                        Corner roundness
                                    </legend>
                                    <div
                                        class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-5 lg:grid-cols-3 xl:grid-cols-5"
                                    >
                                        <button
                                            v-for="radius in radiusPresets"
                                            :key="radius.value"
                                            type="button"
                                            class="flex h-9 items-center justify-center rounded-[4px] border text-[9px] font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-300"
                                            :class="
                                                http.borderRadius ===
                                                radius.value
                                                    ? 'border-[#5b6d88] bg-[#1d222a] text-[#b9c8df]'
                                                    : 'border-[#303033] bg-[#18181a] text-zinc-500 hover:border-zinc-600 hover:text-zinc-300'
                                            "
                                            @click="
                                                http.borderRadius = radius.value
                                            "
                                        >
                                            {{ radius.label }}
                                        </button>
                                    </div>
                                </fieldset>

                                <div
                                    class="rounded-[5px] border border-[#2b2b2e] bg-[#171719] p-4"
                                >
                                    <span
                                        class="text-[9px] font-bold tracking-[0.12em] text-zinc-500 uppercase"
                                    >
                                        Current theme preview
                                    </span>
                                    <div
                                        class="mt-3 overflow-hidden border"
                                        :style="{
                                            color: http.colors.text,
                                            backgroundColor:
                                                http.colors.background,
                                            borderColor: http.colors.primary,
                                            borderRadius: http.borderRadius,
                                            fontFamily:
                                                http.typography.bodyFont,
                                        }"
                                    >
                                        <div class="p-4">
                                            <strong
                                                class="block text-sm"
                                                :style="{
                                                    fontFamily:
                                                        http.typography
                                                            .headingFont,
                                                }"
                                            >
                                                Storefront heading
                                            </strong>
                                            <span
                                                class="mt-1 block text-[10px] opacity-70"
                                            >
                                                A quick view of your global
                                                design choices.
                                            </span>
                                            <span
                                                class="mt-3 inline-flex px-3 py-1.5 text-[9px] font-semibold text-white"
                                                :style="{
                                                    backgroundColor:
                                                        http.colors.primary,
                                                    borderRadius:
                                                        http.borderRadius,
                                                }"
                                            >
                                                Primary action
                                            </span>
                                        </div>
                                        <div
                                            class="h-1"
                                            :style="{
                                                backgroundColor:
                                                    http.colors.secondary,
                                            }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <footer
                            class="flex flex-col gap-3 border-t border-[#29292c] bg-[#0d0d0e] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                        >
                            <p class="text-[10px] leading-4 text-zinc-500">
                                Save before returning to the editor to load the
                                latest theme settings.
                            </p>
                            <button
                                type="button"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-[5px] border border-white bg-zinc-100 px-5 text-[11px] font-bold text-[#101011] shadow-[0_8px_24px_rgb(0_0_0/28%)] transition hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-300 disabled:cursor-not-allowed disabled:opacity-45"
                                :disabled="http.processing"
                                @click="saveTheme"
                            >
                                <Save :size="14" />
                                {{
                                    http.processing
                                        ? 'Saving...'
                                        : 'Save theme settings'
                                }}
                            </button>
                        </footer>
                    </section>
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.dashboard-workspace {
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
    scroll-behavior: smooth;
}

@media (prefers-reduced-motion: reduce) {
    .dashboard-workspace {
        scroll-behavior: auto;
    }
}
</style>
