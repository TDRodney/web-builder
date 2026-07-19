<script setup lang="ts">
import { useHttp } from '@inertiajs/vue3';
import { Check, Loader2, Palette, Radius, Save, Type } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

import { update as updateTheme } from '@/actions/App/Http/Controllers/TenantThemeController';
import { useTheme } from '@/lib/theme';

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

interface ThemePreviewPatch {
    colors?: Partial<ThemeColors>;
    typography?: Partial<ThemeTypography>;
    borderRadius?: string;
}

interface ThemeSaveResponse {
    status?: string;
    message?: string;
    theme_config?: ThemeConfig;
}

type StudioSection = 'colors' | 'typography' | 'corners';

const props = defineProps<{
    tenantName: string;
    themeConfig?: ThemeConfig | null;
}>();

const emit = defineEmits<{
    saved: [themeConfig: ThemeConfig];
}>();

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

const initial = props.themeConfig ?? defaultTheme;

const http = useHttp<ThemeConfig, ThemeSaveResponse>({
    colors: { ...initial.colors },
    typography: { ...initial.typography },
    borderRadius: initial.borderRadius,
});

const activeSection = ref<StudioSection>('colors');

const sections: Array<{ key: StudioSection; label: string }> = [
    { key: 'colors', label: 'Colors' },
    { key: 'typography', label: 'Typography' },
    { key: 'corners', label: 'Corners' },
];

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

const bodyFonts = [...headingFonts];

const radiusPresets = [
    { label: 'Sharp', value: '0px' },
    { label: 'Rounded', value: '4px' },
    { label: 'Soft', value: '8px' },
    { label: 'Extra', value: '16px' },
    { label: 'Pill', value: '9999px' },
];

const colorPalettes = [
    {
        group: 'Site kits',
        label: 'Restaurant Warmth',
        colors: {
            primary: '#8B3A2B',
            secondary: '#D6A85F',
            background: '#FFF9F2',
            text: '#241A16',
        },
    },
    {
        group: 'Site kits',
        label: 'Retail Editorial',
        colors: {
            primary: '#111827',
            secondary: '#C08457',
            background: '#FAFAF9',
            text: '#1C1917',
        },
    },
    {
        group: 'Site kits',
        label: 'Hotel Refined',
        colors: {
            primary: '#153B4A',
            secondary: '#C9A86A',
            background: '#F7F5F0',
            text: '#1E2930',
        },
    },
    {
        group: 'Starter',
        label: 'Clean Ink',
        colors: {
            primary: '#18181b',
            secondary: '#52525b',
            background: '#ffffff',
            text: '#09090b',
        },
    },
    {
        group: 'Starter',
        label: 'Coastal Blue',
        colors: {
            primary: '#0e7490',
            secondary: '#0369a1',
            background: '#f0f9ff',
            text: '#0c4a6e',
        },
    },
    {
        group: 'Starter',
        label: 'Forest Mint',
        colors: {
            primary: '#15803d',
            secondary: '#ca8a04',
            background: '#f0fdf4',
            text: '#14532d',
        },
    },
    {
        group: 'Starter',
        label: 'Night Studio',
        colors: {
            primary: '#38bdf8',
            secondary: '#34d399',
            background: '#0f172a',
            text: '#f8fafc',
        },
    },
    {
        group: 'Starter',
        label: 'Default Indigo',
        colors: {
            primary: '#4f46e5',
            secondary: '#0ea5e9',
            background: '#ffffff',
            text: '#0f172a',
        },
    },
];

const paletteGroups = computed(() => {
    const groups: Array<{
        name: string;
        palettes: typeof colorPalettes;
    }> = [];

    for (const palette of colorPalettes) {
        const existing = groups.find((group) => group.name === palette.group);

        if (existing) {
            existing.palettes.push(palette);
        } else {
            groups.push({ name: palette.group, palettes: [palette] });
        }
    }

    return groups;
});

const brandColorKeys: Array<keyof ThemeColors> = ['primary', 'secondary'];
const surfaceColorKeys: Array<keyof ThemeColors> = ['background', 'text'];

const workingTheme = computed<ThemeConfig>(() => ({
    colors: { ...http.colors },
    typography: { ...http.typography },
    borderRadius: http.borderRadius,
}));

const previewTheme = ref<ThemeConfig | null>(null);
const previewLabel = ref<string | null>(null);
const effectiveTheme = computed(() => previewTheme.value ?? workingTheme.value);

const { cssVars } = useTheme(() => effectiveTheme.value);

const selectedPaletteLabel = computed(() => {
    const match = colorPalettes.find(
        (palette) =>
            palette.colors.primary === http.colors.primary &&
            palette.colors.secondary === http.colors.secondary &&
            palette.colors.background === http.colors.background &&
            palette.colors.text === http.colors.text,
    );

    return match?.label ?? null;
});

const accessibilityWarnings = computed<string[]>(() => {
    const warnings: string[] = [];
    const colors = effectiveTheme.value.colors;

    if (colorContrast(colors.background, colors.text) < 4.5) {
        warnings.push(
            'Body text contrast against the background is below 4.5:1.',
        );
    }

    if (colorContrast(colors.primary, '#ffffff') < 4.5) {
        warnings.push(
            'Primary color may not support white button text at 4.5:1.',
        );
    }

    return warnings;
});

function previewThemeChoice(patch: ThemePreviewPatch, label: string): void {
    previewTheme.value = {
        colors: {
            ...workingTheme.value.colors,
            ...patch.colors,
        },
        typography: {
            ...workingTheme.value.typography,
            ...patch.typography,
        },
        borderRadius: patch.borderRadius ?? workingTheme.value.borderRadius,
    };
    previewLabel.value = label;
}

function clearThemePreview(): void {
    previewTheme.value = null;
    previewLabel.value = null;
}

function applyPalette(palette: (typeof colorPalettes)[number]): void {
    clearThemePreview();
    Object.assign(http.colors, palette.colors);
}

function applyHeadingFont(font: string): void {
    clearThemePreview();
    http.typography.headingFont = font;
}

function applyBodyFont(font: string): void {
    clearThemePreview();
    http.typography.bodyFont = font;
}

function applyBorderRadius(borderRadius: string): void {
    clearThemePreview();
    http.borderRadius = borderRadius;
}

function updateThemeColor(key: keyof ThemeColors, event: Event): void {
    http.colors[key] = (event.target as HTMLInputElement).value;
}

function colorContrast(background: string, foreground: string): number {
    const luminance = (hex: string): number => {
        const channels = hex
            .replace('#', '')
            .match(/.{2}/g)
            ?.map((channel) => Number.parseInt(channel, 16) / 255);

        if (!channels || channels.some(Number.isNaN)) {
            return 21;
        }

        const [red, green, blue] = channels.map((channel) =>
            channel <= 0.03928
                ? channel / 12.92
                : ((channel + 0.055) / 1.055) ** 2.4,
        );

        return 0.2126 * red + 0.7152 * green + 0.0722 * blue;
    };

    const lighter = Math.max(luminance(background), luminance(foreground));
    const darker = Math.min(luminance(background), luminance(foreground));

    return (lighter + 0.05) / (darker + 0.05);
}

async function saveTheme(): Promise<void> {
    clearThemePreview();

    try {
        const response = await http.patch(updateTheme.url(props.tenantName));

        if (response?.status === 'success' && response.theme_config) {
            emit('saved', response.theme_config);
            toast.success('Theme settings saved');

            return;
        }

        toast.error(response?.message || 'Failed to save theme settings');
    } catch {
        toast.error('Failed to save theme settings');
    }
}

watch(activeSection, clearThemePreview);
</script>

<template>
    <section
        class="theme-studio-shell flex h-full min-h-0 flex-col overflow-hidden rounded-[7px] border border-editor-border bg-editor-panel"
    >
        <div
            class="flex shrink-0 flex-col gap-3 border-b border-editor-border px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5"
        >
            <div>
                <span
                    class="text-[9px] font-bold tracking-[0.14em] text-editor-text-muted uppercase"
                >
                    Shared site settings
                </span>
                <h2 class="mt-1 text-base font-semibold text-editor-text">
                    Global theme
                </h2>
                <p class="mt-1 text-xs text-editor-text-muted">
                    Colors, type, and corners update the live preview before you
                    save.
                </p>
            </div>
            <button
                type="button"
                class="inline-flex h-9 items-center justify-center gap-2 rounded-[5px] border border-editor-text bg-editor-text px-4 text-[11px] font-bold text-white transition hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-45"
                :disabled="http.processing"
                @click="saveTheme"
            >
                <Loader2
                    v-if="http.processing"
                    :size="14"
                    class="animate-spin"
                />
                <Save v-else :size="14" />
                {{ http.processing ? 'Saving...' : 'Save theme' }}
            </button>
        </div>

        <div
            class="grid min-h-0 flex-1 grid-cols-1 gap-px overflow-hidden bg-editor-border lg:grid-cols-[340px_minmax(0,1fr)]"
        >
            <aside
                class="order-1 min-h-0 min-w-0 overflow-y-auto bg-editor-panel p-4 lg:order-none"
                aria-label="Theme controls"
                @keydown.esc.capture="clearThemePreview"
            >
                <nav class="grid gap-0.5" aria-label="Theme sections">
                    <button
                        v-for="section in sections"
                        :key="section.key"
                        type="button"
                        class="flex min-h-9 w-full items-center gap-2 rounded-[5px] border px-2.5 text-left text-[10px] font-semibold transition"
                        :class="
                            activeSection === section.key
                                ? 'border-editor-border-strong bg-editor-panel-muted text-editor-text'
                                : 'border-transparent text-editor-text-muted hover:border-editor-border hover:bg-editor-panel-muted hover:text-editor-text'
                        "
                        @click="activeSection = section.key"
                    >
                        <Palette v-if="section.key === 'colors'" :size="13" />
                        <Type
                            v-else-if="section.key === 'typography'"
                            :size="13"
                        />
                        <Radius v-else :size="13" />
                        {{ section.label }}
                    </button>
                </nav>

                <div v-if="activeSection === 'colors'" class="mt-5 grid gap-5">
                    <fieldset>
                        <legend class="studio-legend">One-click palettes</legend>
                        <p class="studio-help">
                            Apply a full color set instantly — kit themes match
                            the onboarding designs, starters are ready-to-tweak
                            bases. Hover to preview, click to apply.
                        </p>
                        <div
                            v-for="group in paletteGroups"
                            :key="group.name"
                            class="mt-3 grid gap-2"
                        >
                            <span
                                class="text-[9px] font-bold tracking-[0.1em] text-editor-text-muted uppercase"
                            >
                                {{ group.name }}
                            </span>
                            <div
                                class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1"
                            >
                                <button
                                    v-for="palette in group.palettes"
                                    :key="palette.label"
                                    type="button"
                                    class="rounded-[6px] border p-3 text-left transition"
                                    :class="
                                        selectedPaletteLabel === palette.label
                                            ? 'border-editor-border-strong bg-editor-accent-soft'
                                            : 'border-editor-border bg-editor-panel-muted hover:border-editor-border-strong'
                                    "
                                    :aria-pressed="
                                        selectedPaletteLabel === palette.label
                                    "
                                    @pointerenter="
                                        previewThemeChoice(
                                            { colors: palette.colors },
                                            palette.label,
                                        )
                                    "
                                    @pointerleave="clearThemePreview"
                                    @focus="
                                        previewThemeChoice(
                                            { colors: palette.colors },
                                            palette.label,
                                        )
                                    "
                                    @blur="clearThemePreview"
                                    @click="applyPalette(palette)"
                                >
                                    <span
                                        class="flex items-center justify-between gap-2"
                                    >
                                        <span class="flex gap-1.5">
                                            <span
                                                v-for="(
                                                    color, key
                                                ) in palette.colors"
                                                :key="key"
                                                class="size-5 rounded-full border border-editor-border"
                                                :style="{
                                                    backgroundColor: color,
                                                }"
                                                :title="String(key)"
                                            ></span>
                                        </span>
                                        <span
                                            v-if="
                                                selectedPaletteLabel ===
                                                palette.label
                                            "
                                            class="flex size-5 items-center justify-center rounded-full bg-editor-text text-white"
                                        >
                                            <Check
                                                :size="11"
                                                :stroke-width="3"
                                            />
                                        </span>
                                    </span>
                                    <strong
                                        class="mt-2 block text-[11px] font-semibold text-editor-text"
                                    >
                                        {{ palette.label }}
                                    </strong>
                                </button>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="studio-legend">Brand colors</legend>
                        <div class="mt-3 grid gap-3">
                            <label
                                v-for="key in brandColorKeys"
                                :key="key"
                                class="block"
                            >
                                <span
                                    class="mb-1.5 block text-[9px] font-semibold tracking-[0.08em] text-editor-text-muted uppercase"
                                >
                                    {{ key }}
                                </span>
                                <span class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        :value="http.colors[key]"
                                        class="size-9 shrink-0 cursor-pointer rounded-[4px] border border-editor-border bg-transparent p-0.5"
                                        @input="updateThemeColor(key, $event)"
                                    />
                                    <input
                                        type="text"
                                        :value="http.colors[key]"
                                        class="h-9 min-w-0 flex-1 rounded-[4px] border border-editor-border bg-editor-panel px-2.5 font-mono text-[10px] text-editor-text outline-none focus:border-editor-border-strong"
                                        @input="updateThemeColor(key, $event)"
                                    />
                                </span>
                            </label>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="studio-legend">Surface colors</legend>
                        <div class="mt-3 grid gap-3">
                            <label
                                v-for="key in surfaceColorKeys"
                                :key="key"
                                class="block"
                            >
                                <span
                                    class="mb-1.5 block text-[9px] font-semibold tracking-[0.08em] text-editor-text-muted uppercase"
                                >
                                    {{ key }}
                                </span>
                                <span class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        :value="http.colors[key]"
                                        class="size-9 shrink-0 cursor-pointer rounded-[4px] border border-editor-border bg-transparent p-0.5"
                                        @input="updateThemeColor(key, $event)"
                                    />
                                    <input
                                        type="text"
                                        :value="http.colors[key]"
                                        class="h-9 min-w-0 flex-1 rounded-[4px] border border-editor-border bg-editor-panel px-2.5 font-mono text-[10px] text-editor-text outline-none focus:border-editor-border-strong"
                                        @input="updateThemeColor(key, $event)"
                                    />
                                </span>
                            </label>
                        </div>
                    </fieldset>
                </div>

                <div
                    v-else-if="activeSection === 'typography'"
                    class="mt-5 grid gap-4"
                >
                    <fieldset>
                        <legend class="studio-legend">Heading font</legend>
                        <div class="mt-3 grid gap-1.5">
                            <button
                                v-for="font in headingFonts"
                                :key="`heading-${font}`"
                                type="button"
                                class="rounded-[5px] border px-3 py-2.5 text-left transition"
                                :class="
                                    http.typography.headingFont === font
                                        ? 'border-editor-border-strong bg-editor-panel-muted text-editor-text'
                                        : 'border-editor-border bg-editor-panel text-editor-text-muted hover:border-editor-border-strong hover:text-editor-text'
                                "
                                :style="{ fontFamily: font }"
                                @pointerenter="
                                    previewThemeChoice(
                                        { typography: { headingFont: font } },
                                        `${font} headings`,
                                    )
                                "
                                @pointerleave="clearThemePreview"
                                @focus="
                                    previewThemeChoice(
                                        { typography: { headingFont: font } },
                                        `${font} headings`,
                                    )
                                "
                                @blur="clearThemePreview"
                                @click="applyHeadingFont(font)"
                            >
                                <span class="block text-sm font-semibold">
                                    {{ font }}
                                </span>
                                <span
                                    class="mt-0.5 block text-[10px] opacity-70"
                                >
                                    The quick brown fox
                                </span>
                            </button>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="studio-legend">Body font</legend>
                        <div class="mt-3 grid gap-1.5">
                            <button
                                v-for="font in bodyFonts"
                                :key="`body-${font}`"
                                type="button"
                                class="rounded-[5px] border px-3 py-2.5 text-left transition"
                                :class="
                                    http.typography.bodyFont === font
                                        ? 'border-editor-border-strong bg-editor-panel-muted text-editor-text'
                                        : 'border-editor-border bg-editor-panel text-editor-text-muted hover:border-editor-border-strong hover:text-editor-text'
                                "
                                :style="{ fontFamily: font }"
                                @pointerenter="
                                    previewThemeChoice(
                                        { typography: { bodyFont: font } },
                                        `${font} body`,
                                    )
                                "
                                @pointerleave="clearThemePreview"
                                @focus="
                                    previewThemeChoice(
                                        { typography: { bodyFont: font } },
                                        `${font} body`,
                                    )
                                "
                                @blur="clearThemePreview"
                                @click="applyBodyFont(font)"
                            >
                                <span class="block text-[12px] font-medium">
                                    {{ font }}
                                </span>
                                <span
                                    class="mt-0.5 block text-[10px] opacity-70"
                                >
                                    Readable body copy for pages and UI.
                                </span>
                            </button>
                        </div>
                    </fieldset>
                </div>

                <div v-else class="mt-5">
                    <legend class="studio-legend">Corner roundness</legend>
                    <p class="studio-help">
                        Applied to buttons, cards, and other chrome.
                    </p>
                    <div class="mt-3 grid gap-2">
                        <button
                            v-for="radius in radiusPresets"
                            :key="radius.value"
                            type="button"
                            class="flex min-h-12 items-center justify-between gap-3 rounded-[5px] border px-3 text-left transition"
                            :class="
                                http.borderRadius === radius.value
                                    ? 'border-editor-border-strong bg-editor-panel-muted text-editor-text'
                                    : 'border-editor-border bg-editor-panel text-editor-text-muted hover:border-editor-border-strong hover:text-editor-text'
                            "
                            @pointerenter="
                                previewThemeChoice(
                                    { borderRadius: radius.value },
                                    `${radius.label} corners`,
                                )
                            "
                            @pointerleave="clearThemePreview"
                            @focus="
                                previewThemeChoice(
                                    { borderRadius: radius.value },
                                    `${radius.label} corners`,
                                )
                            "
                            @blur="clearThemePreview"
                            @click="applyBorderRadius(radius.value)"
                        >
                            <span class="text-[11px] font-semibold">
                                {{ radius.label }}
                            </span>
                            <span
                                class="size-8 border-2 border-editor-text/40 bg-editor-panel-muted"
                                :style="{ borderRadius: radius.value }"
                            ></span>
                        </button>
                    </div>
                </div>
            </aside>

            <div
                class="order-2 flex min-h-0 min-w-0 flex-col overflow-y-auto bg-editor-panel p-4 sm:p-5 lg:order-none"
            >
                <div
                    class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <span class="flex flex-wrap items-center gap-2">
                            <span
                                class="text-[9px] font-bold tracking-[0.12em] text-editor-text-muted uppercase"
                            >
                                Live preview
                            </span>
                            <span
                                v-if="previewLabel"
                                class="rounded-full border border-editor-border bg-editor-accent-soft px-2 py-0.5 text-[9px] font-semibold text-editor-text"
                            >
                                Previewing {{ previewLabel }}
                            </span>
                        </span>
                        <p class="mt-1 text-[10px] text-editor-text-muted">
                            Hover or focus a choice to try it. Click to keep it
                            before saving.
                        </p>
                    </div>
                </div>

                <div
                    v-if="accessibilityWarnings.length"
                    class="mb-3 rounded-[5px] border border-amber-200 bg-amber-50 px-3 py-2"
                    role="status"
                >
                    <strong
                        class="text-[9px] font-bold tracking-[0.08em] text-amber-900 uppercase"
                    >
                        Accessibility checks
                    </strong>
                    <ul class="mt-1 grid gap-1 pl-4 text-[10px] text-amber-800">
                        <li
                            v-for="warning in accessibilityWarnings"
                            :key="warning"
                            class="list-disc"
                        >
                            {{ warning }}
                        </li>
                    </ul>
                </div>

                <div
                    class="theme-preview-stage min-h-[320px] flex-1 overflow-auto rounded-[7px] border border-editor-border bg-editor-bg p-3 sm:p-5"
                >
                    <div
                        class="mx-auto max-w-[720px] overflow-hidden shadow-[0_20px_70px_rgb(0_0_0/35%)]"
                        :style="[
                            cssVars,
                            {
                                backgroundColor: 'var(--theme-bg)',
                                color: 'var(--theme-text)',
                                borderRadius: 'var(--theme-border-radius)',
                                fontFamily: 'var(--theme-font-body)',
                            },
                        ]"
                    >
                        <div
                            class="flex items-center justify-between gap-3 border-b px-5 py-4"
                            :style="{
                                borderColor:
                                    'color-mix(in srgb, var(--theme-text) 12%, transparent)',
                            }"
                        >
                            <strong
                                class="text-sm"
                                :style="{
                                    fontFamily: 'var(--theme-font-heading)',
                                }"
                            >
                                {{ tenantName }}
                            </strong>
                            <span
                                class="inline-flex px-3 py-1.5 text-[10px] font-semibold text-white"
                                :style="{
                                    backgroundColor: 'var(--theme-primary)',
                                    borderRadius: 'var(--theme-border-radius)',
                                }"
                            >
                                Primary action
                            </span>
                        </div>

                        <div class="grid gap-4 px-5 py-8 sm:px-8">
                            <span
                                class="text-[10px] font-bold tracking-[0.14em] uppercase opacity-55"
                            >
                                Theme preview
                            </span>
                            <h3
                                class="max-w-md text-2xl leading-tight font-semibold sm:text-3xl"
                                :style="{
                                    fontFamily: 'var(--theme-font-heading)',
                                }"
                            >
                                Storefront heading that shows your type and
                                color.
                            </h3>
                            <p class="max-w-lg text-sm leading-6 opacity-75">
                                Body copy uses the selected body font against
                                your background and text colors. Adjust tokens
                                on the left to refine this composition.
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    class="inline-flex px-3.5 py-2 text-[11px] font-semibold text-white"
                                    :style="{
                                        backgroundColor: 'var(--theme-primary)',
                                        borderRadius:
                                            'var(--theme-border-radius)',
                                    }"
                                >
                                    Primary
                                </span>
                                <span
                                    class="inline-flex border px-3.5 py-2 text-[11px] font-semibold"
                                    :style="{
                                        color: 'var(--theme-secondary)',
                                        borderColor: 'var(--theme-secondary)',
                                        borderRadius:
                                            'var(--theme-border-radius)',
                                    }"
                                >
                                    Secondary
                                </span>
                            </div>
                            <div
                                class="mt-2 border p-4"
                                :style="{
                                    borderColor:
                                        'color-mix(in srgb, var(--theme-text) 14%, transparent)',
                                    borderRadius: 'var(--theme-border-radius)',
                                    backgroundColor:
                                        'color-mix(in srgb, var(--theme-text) 4%, var(--theme-bg))',
                                }"
                            >
                                <strong
                                    class="block text-sm"
                                    :style="{
                                        fontFamily: 'var(--theme-font-heading)',
                                    }"
                                >
                                    Sample card
                                </strong>
                                <p class="mt-1 text-[11px] opacity-70">
                                    Cards and inputs inherit corner roundness
                                    from the theme.
                                </p>
                                <input
                                    type="text"
                                    readonly
                                    value="Sample input"
                                    class="mt-3 h-9 w-full border bg-transparent px-3 text-[11px] outline-none"
                                    :style="{
                                        borderColor:
                                            'color-mix(in srgb, var(--theme-text) 18%, transparent)',
                                        borderRadius:
                                            'var(--theme-border-radius)',
                                        color: 'var(--theme-text)',
                                    }"
                                />
                            </div>
                        </div>

                        <div
                            class="h-1.5"
                            :style="{
                                backgroundColor: 'var(--theme-secondary)',
                            }"
                        ></div>
                    </div>
                </div>
            </div>
        </div>

        <footer
            class="flex shrink-0 flex-col gap-2 border-t border-editor-border bg-editor-panel px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5"
        >
            <p class="text-[10px] leading-4 text-editor-text-muted">
                Saving updates the editor canvas and published pages that use
                theme tokens.
            </p>
            <button
                type="button"
                class="inline-flex h-9 items-center justify-center gap-2 rounded-[5px] border border-editor-text bg-editor-text px-4 text-[11px] font-bold text-white transition hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-45"
                :disabled="http.processing"
                @click="saveTheme"
            >
                <Save :size="14" />
                Save theme
            </button>
        </footer>
    </section>
</template>

<style>
.theme-studio-shell {
    box-shadow: var(--editor-shadow) !important;
}

.theme-studio-shell fieldset {
    min-width: 0;
    margin: 0;
    padding: 0;
    border: 0;
}

.theme-studio-shell legend,
.studio-legend {
    display: block;
    color: var(--editor-text-muted);
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.studio-help {
    margin-top: 0.25rem;
    color: var(--editor-text-muted);
    font-size: 10px;
    line-height: 1rem;
}
</style>
