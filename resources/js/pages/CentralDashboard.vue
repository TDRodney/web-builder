<script setup lang="ts">
import { Head, Link, useHttp, usePage } from '@inertiajs/vue3';
import { Toaster, toast } from 'vue-sonner';
import { computed, reactive, ref } from 'vue';

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

const props = defineProps<{
    tenant?: {
        id: number;
        subdomain: string;
    } | null;
    theme_config?: ThemeConfig | null;
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

const themeColors = reactive({ ...initial.colors });
const themeTypography = reactive({ ...initial.typography });
const themeBorderRadius = ref(initial.borderRadius);

const http = useHttp({});

const isSaving = ref(false);

const headingFonts = [
    'Instrument Sans', 'Inter', 'Outfit', 'Poppins', 'Roboto', 'Plus Jakarta Sans',
    'Lora', 'Playfair Display', 'Merriweather', 'EB Garamond',
];

const bodyFonts = [
    'Instrument Sans', 'Inter', 'Outfit', 'Poppins', 'Roboto', 'Plus Jakarta Sans',
    'Lora', 'Playfair Display', 'Merriweather', 'EB Garamond',
];

const radiusPresets = [
    { label: 'Sharp', value: '0px' },
    { label: 'Rounded', value: '4px' },
    { label: 'Soft', value: '8px' },
    { label: 'Extra', value: '16px' },
    { label: 'Pill', value: '9999px' },
];

const colorPalettes = [
    { label: 'Modern Indigo', colors: { primary: '#4f46e5', secondary: '#0ea5e9', background: '#ffffff', text: '#0f172a' } },
    { label: 'Editorial Warm', colors: { primary: '#1e293b', secondary: '#b45309', background: '#fcfbf7', text: '#0f172a' } },
    { label: 'Minimal Dark', colors: { primary: '#3b82f6', secondary: '#10b981', background: '#0f172a', text: '#f8fafc' } },
    { label: 'Forest Mint', colors: { primary: '#15803d', secondary: '#eab308', background: '#f0fdf4', text: '#14532d' } },
];

function applyPalette(palette: typeof colorPalettes[number]) {
    Object.assign(themeColors, palette.colors);
}

async function saveTheme() {
    isSaving.value = true;
    try {
        http.colors = { ...themeColors };
        http.typography = { ...themeTypography };
        http.borderRadius = themeBorderRadius.value;
        const res = await http.patch('/theme');
        if (res?.status === 'success') {
            toast.success('Theme settings saved!');
        } else {
            toast.error(res?.message || 'Failed to save theme settings');
        }
    } catch (err) {
        const message = err?.response?.data?.message || err?.response?.data?.error || 'Failed to save theme settings';
        toast.error(message);
    } finally {
        isSaving.value = false;
    }
}

const tenantEditorUrl = computed(() => {
    if (!props.tenant) {
        return '';
    }

    const protocol = window.location.protocol;
    const hostParts = window.location.host.split('.');
    const baseHost = hostParts.length > 2 ? hostParts.slice(-2).join('.') : window.location.host;

    return `${protocol}//${props.tenant.subdomain}.${baseHost}/editor`;
});

const tenantPublicUrl = computed(() => {
    if (!props.tenant) {
        return '';
    }

    const protocol = window.location.protocol;
    const hostParts = window.location.host.split('.');
    const baseHost = hostParts.length > 2 ? hostParts.slice(-2).join('.') : window.location.host;

    return `${protocol}//${props.tenant.subdomain}.${baseHost}/`;
});
</script>

<template>
    <Head title="Central Dashboard - Nexura" />
    <Toaster richColors position="bottom-right" />

    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 text-slate-100 flex items-start justify-center p-6 relative overflow-hidden font-sans">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl animate-pulse"></div>

        <div class="max-w-3xl w-full space-y-6 z-10 relative py-8">
            <!-- Header -->
            <div class="bg-slate-900/60 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 md:p-12 shadow-2xl">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 border-b border-slate-850 pb-8 mb-8">
                    <div>
                        <span class="text-indigo-400 text-xs font-semibold uppercase tracking-widest bg-indigo-500/10 px-3 py-1 rounded-full border border-indigo-500/20">Central Plane</span>
                        <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mt-2 bg-gradient-to-r from-white via-slate-200 to-indigo-300 bg-clip-text text-transparent">
                            Nexura Engine
                        </h1>
                        <p class="text-slate-400 text-sm mt-1">Hello, {{ user?.name }} &mdash; manage your account settings and tenant workspaces.</p>
                    </div>

                    <div class="flex gap-3">
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 border border-slate-700 hover:border-slate-600 focus:outline-none"
                        >
                            Sign Out
                        </Link>
                    </div>
                </div>

                <!-- Workspace Status Cards -->
                <div class="grid gap-6">
                    <div v-if="tenant" class="bg-gradient-to-r from-indigo-950/40 to-slate-900/40 border border-indigo-500/20 rounded-2xl p-6 md:p-8 hover:border-indigo-500/40 transition-all duration-300">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                                    <span class="flex h-2.5 w-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                                    Workspace Active
                                </h2>
                                <p class="text-slate-400 text-xs mt-1 uppercase tracking-wider font-semibold">Subdomain Identifier</p>
                                <code class="text-indigo-300 font-mono text-sm bg-slate-950/80 px-2 py-1 rounded mt-1.5 inline-block border border-slate-850">
                                    {{ tenant.subdomain }}.domain.localhost
                                </code>
                            </div>
                        </div>

                        <p class="text-slate-300 text-sm mt-6 mb-8 leading-relaxed">
                            Your multi-tenant canvas editor is ready. You can modify pages and preview the published layouts dynamically on your dedicated sandbox subdomain.
                        </p>

                        <div class="flex flex-wrap gap-4">
                            <a
                                :href="tenantEditorUrl"
                                class="flex-1 text-center bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 hover:shadow-lg hover:shadow-indigo-500/25 focus:outline-none"
                            >
                                Open Canvas Editor
                            </a>
                            <a
                                :href="tenantPublicUrl"
                                target="_blank"
                                class="flex-1 text-center bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold py-3 px-6 rounded-xl transition-all duration-200 border border-slate-700 hover:border-slate-600 focus:outline-none flex items-center justify-center gap-1.5"
                            >
                                View Live Site
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <div v-else class="bg-red-950/20 border border-red-500/20 rounded-2xl p-6 md:p-8">
                        <h2 class="text-xl font-bold text-red-400">Workspace Missing</h2>
                        <p class="text-slate-300 text-sm mt-2">
                            We could not detect an active tenant workspace associated with your user account. Please contact support or re-register.
                        </p>
                    </div>
                </div>

                <!-- Footer Details -->
                <div class="mt-8 text-center text-xs text-slate-500">
                    Single-Database multi-tenancy enabled. Cookies scoped to <span class="text-indigo-400">.domain.localhost</span>.
                </div>
            </div>

            <!-- Theme Settings Panel -->
            <div v-if="tenant" class="bg-slate-900/60 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 md:p-10 shadow-2xl">
                <div class="border-b border-slate-800 pb-6 mb-8">
                    <h2 class="text-2xl font-bold text-white">Theme Settings</h2>
                    <p class="text-slate-400 text-sm mt-1">Customize the look and feel of your site.</p>
                </div>

                <!-- Color Palette Presets -->
                <div class="mb-8">
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-3">Color Palettes</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <button
                            v-for="palette in colorPalettes"
                            :key="palette.label"
                            type="button"
                            @click="applyPalette(palette)"
                            class="bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 hover:border-indigo-500/50 rounded-xl p-3 text-left transition-all duration-200 focus:outline-none"
                        >
                            <div class="flex gap-1 mb-2">
                                <span class="w-4 h-4 rounded-full" :style="{ backgroundColor: palette.colors.primary }"></span>
                                <span class="w-4 h-4 rounded-full" :style="{ backgroundColor: palette.colors.secondary }"></span>
                                <span class="w-4 h-4 rounded-full" :style="{ backgroundColor: palette.colors.background }"></span>
                                <span class="w-4 h-4 rounded-full" :style="{ backgroundColor: palette.colors.text }"></span>
                            </div>
                            <span class="text-xs text-slate-300 font-medium">{{ palette.label }}</span>
                        </button>
                    </div>
                </div>

                <!-- Custom Colors -->
                <div class="mb-8">
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-3">Custom Colors</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div v-for="(value, key) in themeColors" :key="key">
                            <label class="block text-xs text-slate-400 mb-1.5 capitalize">{{ key }}</label>
                            <div class="flex items-center gap-2">
                                <input
                                    type="color"
                                    :value="value"
                                    @input="themeColors[key] = ($event.target as HTMLInputElement).value"
                                    class="w-10 h-10 rounded-lg cursor-pointer border border-slate-700 bg-transparent p-0.5"
                                />
                                <input
                                    type="text"
                                    :value="value"
                                    @input="themeColors[key] = ($event.target as HTMLInputElement).value"
                                    class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-2 py-1.5 text-xs text-slate-200 font-mono focus:outline-none focus:border-indigo-500"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Typography -->
                <div class="mb-8">
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-3">Typography</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-slate-400 mb-1.5">Heading Font</label>
                            <select
                                :value="themeTypography.headingFont"
                                @change="themeTypography.headingFont = ($event.target as HTMLSelectElement).value"
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500"
                            >
                                <option v-for="font in headingFonts" :key="font" :value="font">{{ font }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1.5">Body Font</label>
                            <select
                                :value="themeTypography.bodyFont"
                                @change="themeTypography.bodyFont = ($event.target as HTMLSelectElement).value"
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500"
                            >
                                <option v-for="font in bodyFonts" :key="font" :value="font">{{ font }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Border Radius -->
                <div class="mb-8">
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider mb-3">Corner Roundness</h3>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="r in radiusPresets"
                            :key="r.value"
                            type="button"
                            @click="themeBorderRadius = r.value"
                            :class="[
                                'px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 border focus:outline-none',
                                themeBorderRadius === r.value
                                    ? 'bg-indigo-600 text-white border-indigo-500 shadow-lg shadow-indigo-500/25'
                                    : 'bg-slate-800 text-slate-300 border-slate-700 hover:border-slate-600'
                            ]"
                        >
                            {{ r.label }}
                        </button>
                    </div>
                </div>

                <!-- Save Button -->
                <button
                    type="button"
                    @click="saveTheme"
                    :disabled="isSaving"
                    class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 hover:shadow-lg hover:shadow-indigo-500/25 focus:outline-none"
                >
                    {{ isSaving ? 'Saving...' : 'Save Theme Settings' }}
                </button>
            </div>
        </div>
    </div>
</template>
