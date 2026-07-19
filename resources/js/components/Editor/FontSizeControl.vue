<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue?: string | number | null;
    }>(),
    {
        modelValue: '16px',
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const presets = [
    { label: 'Small', value: '14px' },
    { label: 'Body', value: '16px' },
    { label: 'Lead', value: '20px' },
    { label: 'Heading', value: 'clamp(1.75rem, 4vw, 2.5rem)' },
    { label: 'Hero', value: 'clamp(2.5rem, 7vw, 5rem)' },
    { label: 'Display', value: 'clamp(3.25rem, 10vw, 7rem)' },
];

const currentValue = computed(() => String(props.modelValue || '16px'));

const toPixels = (value: string): number => {
    const lengths = [
        ...value.matchAll(/([0-9]*\.?[0-9]+)\s*(px|rem|em)/gi),
    ].map((match) => {
        const amount = Number.parseFloat(match[1]);

        return match[2].toLowerCase() === 'px' ? amount : amount * 16;
    });

    if (lengths.length > 0) {
        return Math.max(...lengths);
    }

    const fallback = Number.parseFloat(value);

    return Number.isFinite(fallback) ? fallback : 16;
};

const pixelValue = computed(() =>
    Math.min(144, Math.max(12, Math.round(toPixels(currentValue.value)))),
);

const chooseSize = (value: string): void => {
    emit('update:modelValue', value);
};

const updatePixelSize = (event: Event): void => {
    const value = Number.parseInt((event.target as HTMLInputElement).value, 10);

    if (Number.isFinite(value)) {
        chooseSize(`${Math.min(144, Math.max(12, value))}px`);
    }
};

const commitAdvancedSize = (event: Event): void => {
    const input = event.target as HTMLInputElement;
    const value = input.value.trim();

    if (value !== '') {
        chooseSize(value);

        return;
    }

    input.value = currentValue.value;
};
</script>

<template>
    <div class="grid gap-3">
        <div class="grid grid-cols-3 gap-1.5">
            <button
                v-for="preset in presets"
                :key="preset.value"
                type="button"
                class="min-h-9 rounded-[5px] border px-1.5 text-[9px] font-semibold transition"
                :class="
                    currentValue === preset.value
                        ? 'border-editor-text bg-editor-text text-white'
                        : 'border-editor-border bg-editor-panel text-editor-text-muted hover:border-editor-border-strong hover:text-editor-text'
                "
                :aria-pressed="currentValue === preset.value"
                @click="chooseSize(preset.value)"
            >
                {{ preset.label }}
            </button>
        </div>

        <div
            class="grid gap-2 rounded-[6px] border border-editor-border bg-editor-panel-muted p-2.5"
        >
            <div class="flex items-center justify-between gap-2">
                <span class="text-[9px] font-semibold text-editor-text-muted">
                    Drag left to make smaller
                </span>
                <output
                    class="font-mono text-[10px] font-semibold text-editor-text"
                >
                    {{ pixelValue }}px
                </output>
            </div>
            <input
                type="range"
                min="12"
                max="144"
                step="1"
                :value="pixelValue"
                class="w-full cursor-pointer accent-editor-text"
                aria-label="Font size"
                @input="updatePixelSize"
            />
            <div class="flex items-center gap-2">
                <input
                    type="number"
                    min="12"
                    max="144"
                    :value="pixelValue"
                    class="h-8 min-w-0 flex-1 rounded-[5px] border border-editor-border bg-editor-panel px-2 font-mono text-[10px] text-editor-text outline-none focus:border-editor-text"
                    aria-label="Font size in pixels"
                    @change="updatePixelSize"
                />
                <span class="text-[9px] font-semibold text-editor-text-muted"
                    >px</span
                >
            </div>
        </div>

        <details
            class="group rounded-[5px] border border-editor-border bg-editor-panel"
        >
            <summary
                class="cursor-pointer px-2.5 py-2 text-[9px] font-semibold text-editor-text-muted"
            >
                Advanced responsive size
            </summary>
            <div class="border-t border-editor-border p-2.5">
                <input
                    type="text"
                    :value="currentValue"
                    class="h-9 w-full rounded-[5px] border border-editor-border bg-editor-panel px-2.5 font-mono text-[10px] text-editor-text outline-none focus:border-editor-text"
                    aria-label="Advanced CSS font size"
                    placeholder="clamp(2rem, 5vw, 4rem)"
                    @change="commitAdvancedSize"
                />
            </div>
        </details>
    </div>
</template>
