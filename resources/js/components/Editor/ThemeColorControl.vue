<script setup lang="ts">
import { Check } from '@lucide/vue';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue?: string | null;
        defaultValue?: string;
        customDefault?: string;
    }>(),
    {
        modelValue: null,
        defaultValue: '--theme-primary',
        customDefault: '#18181b',
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const themeColors = [
    { label: 'Primary', value: '--theme-primary' },
    { label: 'Secondary', value: '--theme-secondary' },
    { label: 'Background', value: '--theme-bg' },
    { label: 'Text', value: '--theme-text' },
];

const presetColors = [
    '#ffffff',
    '#f4f4f5',
    '#d4d4d8',
    '#a1a1aa',
    '#71717a',
    '#3f3f46',
    '#18181b',
    '#09090b',
    '#f87171',
    '#ef4444',
    '#dc2626',
    '#b91c1c',
    '#fb7185',
    '#f43f5e',
    '#e11d48',
    '#be123c',
    '#fb923c',
    '#f97316',
    '#ea580c',
    '#c2410c',
    '#fbbf24',
    '#f59e0b',
    '#d97706',
    '#b45309',
    '#a3e635',
    '#84cc16',
    '#65a30d',
    '#4d7c0f',
    '#4ade80',
    '#22c55e',
    '#16a34a',
    '#15803d',
    '#34d399',
    '#10b981',
    '#059669',
    '#047857',
    '#2dd4bf',
    '#14b8a6',
    '#0d9488',
    '#0f766e',
    '#22d3ee',
    '#06b6d4',
    '#0891b2',
    '#0e7490',
    '#60a5fa',
    '#3b82f6',
    '#2563eb',
    '#1d4ed8',
    '#818cf8',
    '#6366f1',
    '#4f46e5',
    '#4338ca',
    '#a78bfa',
    '#8b5cf6',
    '#7c3aed',
    '#6d28d9',
    '#c084fc',
    '#a855f7',
    '#9333ea',
    '#7e22ce',
    '#f472b6',
    '#ec4899',
    '#db2777',
    '#be185d',
];

const hexPattern = /^#[0-9a-f]{6}$/i;
const currentValue = computed(
    () => props.modelValue || props.defaultValue || props.customDefault,
);
const customColor = computed(() =>
    hexPattern.test(currentValue.value)
        ? currentValue.value
        : props.customDefault,
);

const chooseColor = (value: string): void => {
    emit('update:modelValue', value);
};

const updateNativeColor = (event: Event): void => {
    chooseColor((event.target as HTMLInputElement).value);
};

const commitHexColor = (event: Event): void => {
    const input = event.target as HTMLInputElement;
    const value = input.value.trim();

    if (hexPattern.test(value)) {
        chooseColor(value.toLowerCase());

        return;
    }

    input.value = customColor.value;
};
</script>

<template>
    <div class="grid gap-3">
        <div class="grid grid-cols-2 gap-1.5">
            <button
                v-for="color in themeColors"
                :key="color.value"
                type="button"
                class="relative min-h-8 rounded-[5px] border px-2 text-[9px] font-semibold transition"
                :class="
                    currentValue === color.value
                        ? 'border-editor-text bg-editor-text text-white'
                        : 'border-editor-border bg-editor-panel text-editor-text-muted hover:border-editor-border-strong hover:text-editor-text'
                "
                :aria-pressed="currentValue === color.value"
                @click="chooseColor(color.value)"
            >
                {{ color.label }}
            </button>
        </div>

        <div class="grid grid-cols-8 gap-1.5" aria-label="Preset colors">
            <button
                v-for="color in presetColors"
                :key="color"
                type="button"
                class="relative aspect-square min-w-0 rounded-[4px] border border-black/10 shadow-[inset_0_0_0_1px_rgb(255_255_255/0.14)] transition hover:scale-110 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-editor-text"
                :style="{ backgroundColor: color }"
                :aria-label="`Use ${color}`"
                :aria-pressed="currentValue.toLowerCase() === color"
                :title="color"
                @click="chooseColor(color)"
            >
                <span
                    v-if="currentValue.toLowerCase() === color"
                    class="absolute inset-0 flex items-center justify-center"
                >
                    <Check
                        :size="12"
                        :stroke-width="3"
                        :class="
                            ['#ffffff', '#f4f4f5', '#d4d4d8'].includes(color)
                                ? 'text-zinc-900'
                                : 'text-white'
                        "
                    />
                </span>
            </button>
        </div>

        <div class="flex items-center gap-2">
            <input
                type="color"
                :value="customColor"
                class="h-9 w-11 shrink-0 cursor-pointer rounded-[5px] border border-editor-border bg-transparent p-0.5"
                aria-label="Choose any color"
                @input="updateNativeColor"
            />
            <input
                type="text"
                :value="customColor"
                class="h-9 min-w-0 flex-1 rounded-[5px] border border-editor-border bg-editor-panel px-2.5 font-mono text-[10px] text-editor-text outline-none focus:border-editor-text"
                aria-label="Custom hex color"
                maxlength="7"
                placeholder="#18181b"
                @change="commitHexColor"
            />
        </div>
    </div>
</template>
