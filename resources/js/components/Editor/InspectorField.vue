<!-- eslint-disable vue/block-lang -->
<script setup>
/* eslint-disable vue/no-mutating-props */
import { ChevronDown } from '@lucide/vue';
import { computed, inject, ref } from 'vue';
import FontSizeControl from '@/components/Editor/FontSizeControl.vue';
import ThemeColorControl from '@/components/Editor/ThemeColorControl.vue';

const props = defineProps({
    field: { type: Object, required: true },
    selectedBlock: { type: Object, required: true },
});

const emit = defineEmits(['open-media-picker']);

const forceSave = inject('forceSave', null);

const value = computed(() => props.selectedBlock.props[props.field.key]);

/* ------------------------------------------------------------------ */
/* Theme-color: collapsed swatch row that expands the full picker      */
/* ------------------------------------------------------------------ */

const colorOpen = ref(false);

const themeTokenLabels = {
    '--theme-primary': 'Primary',
    '--theme-secondary': 'Secondary',
    '--theme-bg': 'Background',
    '--theme-text': 'Text',
};

const describeColor = (raw) => {
    if (raw === 'transparent') {
        return 'None';
    }

    if (typeof raw === 'string' && raw.startsWith('--')) {
        return `Theme · ${themeTokenLabels[raw] ?? 'Token'}`;
    }

    return String(raw);
};

const colorSummary = computed(() => {
    const raw = value.value;

    if (typeof raw === 'string' && raw.length > 0) {
        return describeColor(raw);
    }

    if (props.field.clearable) {
        return 'Auto';
    }

    return describeColor(props.field.defaultValue ?? '--theme-primary');
});

const colorSwatch = computed(() => {
    const raw =
        typeof value.value === 'string' && value.value.length > 0
            ? value.value
            : (props.field.defaultValue ?? '--theme-primary');

    if (raw === 'transparent') {
        return { kind: 'transparent' };
    }

    if (String(raw).startsWith('--')) {
        return { kind: 'color', color: `var(${raw})` };
    }

    return { kind: 'color', color: String(raw) };
});

/* ------------------------------------------------------------------ */
/* Repeater helpers                                                    */
/* ------------------------------------------------------------------ */

const addRepeaterItem = () => {
    if (!Array.isArray(props.selectedBlock.props[props.field.key])) {
        props.selectedBlock.props[props.field.key] = [];
    }

    const newItem = {};
    (props.field.subFields || []).forEach((sub) => {
        newItem[sub.key] =
            sub.type === 'select' ? (sub.options?.[0]?.value ?? '') : '';
    });
    props.selectedBlock.props[props.field.key].push(newItem);
    expandedRepeaterIndex.value =
        props.selectedBlock.props[props.field.key].length - 1;

    if (forceSave) {
        forceSave();
    }
};

const deleteRepeaterItem = (index) => {
    props.selectedBlock.props[props.field.key]?.splice(index, 1);

    if (forceSave) {
        forceSave();
    }
};

const duplicateRepeaterItem = (index) => {
    const list = props.selectedBlock.props[props.field.key];

    if (!list?.[index]) {
        return;
    }

    list.splice(index + 1, 0, JSON.parse(JSON.stringify(list[index])));
    expandedRepeaterIndex.value = index + 1;

    if (forceSave) {
        forceSave();
    }
};

const moveRepeaterItem = (index, direction) => {
    const list = props.selectedBlock.props[props.field.key];
    const targetIndex = index + direction;

    if (!list || targetIndex < 0 || targetIndex >= list.length) {
        return;
    }

    const temp = list[index];
    list[index] = list[targetIndex];
    list[targetIndex] = temp;

    if (expandedRepeaterIndex.value === index) {
        expandedRepeaterIndex.value = targetIndex;
    }

    if (forceSave) {
        forceSave();
    }
};

/** Only one repeater item is expanded at a time; the rest collapse to a summary row. */
const expandedRepeaterIndex = ref(
    Array.isArray(props.selectedBlock.props[props.field.key]) &&
        props.selectedBlock.props[props.field.key].length <= 2
        ? 0
        : -1,
);

const toggleRepeaterItem = (index) => {
    expandedRepeaterIndex.value =
        expandedRepeaterIndex.value === index ? -1 : index;
};

const repeaterItemSummary = (item) => {
    const subFields = props.field.subFields || [];
    const textParts = subFields
        .filter((sub) => sub.type !== 'select' && sub.type !== 'media')
        .map((sub) => item[sub.key])
        .filter((part) => typeof part === 'string' && part.trim().length > 0);

    return textParts.slice(0, 2).join(' — ') || 'Untitled item';
};

/* ------------------------------------------------------------------ */
/* Segmented / toggle helpers                                          */
/* ------------------------------------------------------------------ */

const isOptionActive = (option) => {
    if (typeof option.value === 'number') {
        return Number(value.value) === option.value;
    }

    return value.value === option.value;
};

const selectOption = (option) => {
    props.selectedBlock.props[props.field.key] = option.value;
};

const toggleValue = () => {
    props.selectedBlock.props[props.field.key] = !props.selectedBlock.props[
        props.field.key
    ];
};
</script>

<template>
    <!-- Toggle renders its own single-row layout -->
    <div
        v-if="field.type === 'toggle'"
        class="flex min-h-9 items-center justify-between gap-3"
    >
        <label class="flex-1 cursor-pointer" @click="toggleValue">
            {{ field.label }}
        </label>
        <button
            type="button"
            role="switch"
            :aria-checked="Boolean(value)"
            :aria-label="field.label"
            class="toggle-track"
            :class="{ 'toggle-track-on': Boolean(value) }"
            @click="toggleValue"
        >
            <span class="toggle-thumb" />
        </button>
    </div>

    <div v-else class="space-y-1">
        <label class="block">
            {{ field.label }}
            <span
                v-if="field.type === 'range' && value !== undefined"
                class="normal-case"
            >
                : {{ value }}px
            </span>
        </label>

        <!-- Range slider + numeric input -->
        <div v-if="field.type === 'range'" class="flex items-center gap-2">
            <input
                v-model.number="selectedBlock.props[field.key]"
                type="range"
                :min="field.min ?? 0"
                :max="field.max ?? 150"
                class="min-w-0 flex-1 accent-indigo-500"
            />
            <input
                v-model.number="selectedBlock.props[field.key]"
                type="number"
                :min="field.min ?? 0"
                :max="field.max ?? 150"
                class="range-number w-16 shrink-0 rounded-lg border border-slate-700 bg-slate-800 px-2 py-1.5 text-sm text-white focus:border-indigo-500 focus:outline-none"
            />
        </div>

        <!-- Number input -->
        <input
            v-else-if="field.type === 'number'"
            v-model.number="selectedBlock.props[field.key]"
            type="number"
            :min="field.min"
            :max="field.max"
            class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
        />

        <!-- Segmented buttons (alignment, fonts, sizes, columns) -->
        <div
            v-else-if="field.type === 'segmented' || field.type === 'columns'"
            class="flex flex-wrap gap-1.5"
            role="group"
            :aria-label="field.label"
        >
            <button
                v-for="opt in field.options"
                :key="String(opt.value)"
                type="button"
                class="segment-option"
                :class="{ 'segment-option-active': isOptionActive(opt) }"
                :aria-pressed="isOptionActive(opt)"
                @click="selectOption(opt)"
            >
                {{ opt.label }}
            </button>
        </div>

        <!-- Select dropdown -->
        <select
            v-else-if="field.type === 'select'"
            v-model="selectedBlock.props[field.key]"
            class="w-full cursor-pointer rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
        >
            <option
                v-for="opt in field.options"
                :key="String(opt.value)"
                :value="opt.value"
            >
                {{ opt.label }}
            </option>
        </select>

        <!-- Theme color: collapsed swatch row, full picker on demand -->
        <div v-else-if="field.type === 'theme-color'" class="space-y-2">
            <button
                type="button"
                class="color-row"
                :aria-expanded="colorOpen"
                @click="colorOpen = !colorOpen"
            >
                <span
                    class="color-row-swatch"
                    :class="{
                        'transparent-swatch':
                            colorSwatch.kind === 'transparent',
                    }"
                    :style="
                        colorSwatch.kind === 'color'
                            ? { backgroundColor: colorSwatch.color }
                            : {}
                    "
                    aria-hidden="true"
                />
                <span class="color-row-value">{{ colorSummary }}</span>
                <ChevronDown
                    :size="13"
                    :stroke-width="2.25"
                    class="shrink-0 transition"
                    :class="{ 'rotate-180': colorOpen }"
                />
            </button>
            <div v-if="colorOpen" class="color-row-panel">
                <ThemeColorControl
                    v-model="selectedBlock.props[field.key]"
                    :default-value="field.defaultValue"
                    :custom-default="field.customDefault"
                    :compact="field.compact !== false"
                    :clearable="Boolean(field.clearable)"
                    :clear-label="field.clearLabel || 'Use preset'"
                    :allow-transparent="Boolean(field.allowTransparent)"
                />
            </div>
        </div>

        <FontSizeControl
            v-else-if="field.type === 'font-size'"
            v-model="selectedBlock.props[field.key]"
        />

        <!-- Legacy raw color input (kept for backwards compatibility) -->
        <div
            v-else-if="field.type === 'color'"
            class="flex items-center gap-2"
        >
            <input
                v-model="selectedBlock.props[field.key]"
                type="color"
                class="h-8 w-12 cursor-pointer rounded border border-slate-700 bg-transparent p-0"
            />
            <span class="font-mono text-xs text-slate-300">{{ value }}</span>
        </div>

        <!-- Media picker -->
        <div v-else-if="field.type === 'media'" class="space-y-2">
            <div v-if="value" class="relative overflow-hidden rounded">
                <img
                    :src="String(value)"
                    alt=""
                    class="h-24 w-full rounded object-cover"
                />
                <button
                    type="button"
                    class="absolute top-1 right-1 cursor-pointer rounded border-0 bg-slate-900/80 px-1.5 py-0.5 text-[10px] font-semibold text-rose-400"
                    title="Remove image"
                    @click="selectedBlock.props[field.key] = ''"
                >
                    Remove
                </button>
            </div>
            <button
                type="button"
                class="inspector-secondary-button w-full cursor-pointer rounded-lg border px-3 py-2 text-xs font-semibold transition-all"
                @click="emit('open-media-picker', field.key)"
            >
                {{ value ? 'Change Image' : 'Choose Image' }}
            </button>
        </div>

        <!-- Repeater -->
        <div v-else-if="field.type === 'repeater'" class="space-y-2">
            <div
                v-for="(item, index) in selectedBlock.props[field.key] || []"
                :key="index"
                class="repeater-card overflow-hidden rounded-lg border"
            >
                <div class="flex items-center gap-1 px-2 py-1.5">
                    <button
                        type="button"
                        class="flex min-w-0 flex-1 cursor-pointer items-center gap-1.5 border-0 bg-transparent text-left"
                        :aria-expanded="expandedRepeaterIndex === index"
                        @click="toggleRepeaterItem(index)"
                    >
                        <ChevronDown
                            :size="12"
                            :stroke-width="2.5"
                            class="shrink-0 text-slate-400 transition"
                            :class="{
                                '-rotate-90': expandedRepeaterIndex !== index,
                            }"
                        />
                        <span
                            class="truncate text-[11px] font-semibold text-slate-200 normal-case"
                        >
                            {{ repeaterItemSummary(item) }}
                        </span>
                    </button>
                    <button
                        type="button"
                        :disabled="index === 0"
                        class="repeater-icon-button"
                        title="Move up"
                        @click="moveRepeaterItem(index, -1)"
                    >
                        ↑
                    </button>
                    <button
                        type="button"
                        :disabled="
                            index ===
                            (selectedBlock.props[field.key]?.length ?? 0) - 1
                        "
                        class="repeater-icon-button"
                        title="Move down"
                        @click="moveRepeaterItem(index, 1)"
                    >
                        ↓
                    </button>
                    <button
                        type="button"
                        class="repeater-icon-button"
                        title="Duplicate item"
                        @click="duplicateRepeaterItem(index)"
                    >
                        ⧉
                    </button>
                    <button
                        type="button"
                        class="repeater-icon-button repeater-icon-danger"
                        title="Delete item"
                        @click="deleteRepeaterItem(index)"
                    >
                        ✕
                    </button>
                </div>

                <div
                    v-if="expandedRepeaterIndex === index"
                    class="space-y-2 border-t border-slate-700/40 p-2.5"
                >
                    <div
                        v-for="sub in field.subFields"
                        :key="sub.key"
                        class="space-y-1"
                    >
                        <label class="block text-[10px]">{{ sub.label }}</label>
                        <select
                            v-if="sub.type === 'select'"
                            v-model="item[sub.key]"
                            class="w-full cursor-pointer rounded border border-slate-700 bg-slate-900 px-2 py-1 text-xs text-white focus:border-indigo-500 focus:outline-none"
                        >
                            <option
                                v-for="opt in sub.options"
                                :key="String(opt.value)"
                                :value="opt.value"
                            >
                                {{ opt.label }}
                            </option>
                        </select>
                        <div v-else-if="sub.type === 'media'" class="space-y-1">
                            <img
                                v-if="item[sub.key]"
                                :src="String(item[sub.key])"
                                alt=""
                                class="h-16 w-full rounded object-cover"
                            />
                            <button
                                type="button"
                                class="inspector-secondary-button w-full cursor-pointer rounded border px-2 py-1.5 text-[10px] font-semibold"
                                @click="
                                    emit('open-media-picker', {
                                        fieldKey: field.key,
                                        index,
                                        subKey: sub.key,
                                    })
                                "
                            >
                                {{ item[sub.key] ? 'Change' : 'Choose' }} Image
                            </button>
                        </div>
                        <textarea
                            v-else-if="sub.type === 'textarea'"
                            v-model="item[sub.key]"
                            rows="2"
                            :placeholder="sub.placeholder"
                            class="w-full resize-y rounded border border-slate-700 bg-slate-900 px-2 py-1 text-xs text-white focus:border-indigo-500 focus:outline-none"
                        />
                        <input
                            v-else
                            v-model="item[sub.key]"
                            type="text"
                            :placeholder="sub.placeholder"
                            class="w-full rounded border border-slate-700 bg-slate-900 px-2 py-1 text-xs text-white focus:border-indigo-500 focus:outline-none"
                        />
                    </div>
                </div>
            </div>

            <button
                type="button"
                class="add-repeater-button flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-lg border border-dashed px-3 py-2 text-xs font-semibold transition-all"
                @click="addRepeaterItem"
            >
                + Add Item
            </button>
        </div>

        <!-- Textarea -->
        <textarea
            v-else-if="field.type === 'textarea'"
            v-model="selectedBlock.props[field.key]"
            rows="3"
            :placeholder="field.placeholder"
            class="w-full resize-y rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
        />

        <!-- Text fallback -->
        <input
            v-else
            v-model="selectedBlock.props[field.key]"
            type="text"
            :placeholder="field.placeholder"
            class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
        />
    </div>
</template>

<style scoped>
label {
    color: var(--editor-text-muted);
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

input:not([type='color']):not([type='range']),
textarea,
select {
    min-height: 36px;
    color: var(--editor-text);
    font-size: 11px;
    background: var(--editor-panel);
    border-color: var(--editor-border-strong);
    border-radius: 5px;
}

input:not([type='color']):not([type='range']):focus,
select:focus {
    border-color: var(--editor-accent);
    box-shadow: 0 0 0 3px rgb(79 70 229 / 10%);
}

input[type='range'] {
    accent-color: var(--editor-accent);
}

input[type='color'] {
    border-color: var(--editor-border-strong);
    border-radius: 4px;
}

.range-number {
    min-height: 32px;
    padding-block: 4px;
}

.toggle-track {
    position: relative;
    width: 36px;
    height: 20px;
    padding: 0;
    background: var(--editor-border-strong);
    border: 1px solid var(--editor-border-strong);
    border-radius: 999px;
    cursor: pointer;
    transition: background-color 140ms ease;
}

.toggle-track-on {
    background: var(--editor-accent);
    border-color: var(--editor-accent);
}

.toggle-thumb {
    position: absolute;
    top: 1px;
    left: 1px;
    width: 16px;
    height: 16px;
    background: #fff;
    border-radius: 999px;
    transition: transform 140ms ease;
}

.toggle-track-on .toggle-thumb {
    transform: translateX(16px);
}

.segment-option {
    flex: 1 1 0;
    min-width: 2.25rem;
    min-height: 34px;
    padding-inline: 6px;
    color: var(--editor-text-muted);
    background: var(--editor-panel);
    border: 1px solid var(--editor-border);
    border-radius: 5px;
    font-size: 10px;
    font-weight: 700;
    cursor: pointer;
    transition:
        color 140ms ease,
        background-color 140ms ease,
        border-color 140ms ease;
}

.segment-option:hover {
    color: var(--editor-text);
    border-color: var(--editor-border-strong);
}

.segment-option-active {
    color: #fff;
    background: var(--editor-text);
    border-color: var(--editor-text);
}

.color-row {
    display: flex;
    gap: 8px;
    align-items: center;
    width: 100%;
    min-height: 36px;
    padding: 4px 8px;
    color: var(--editor-text-muted);
    background: var(--editor-panel);
    border: 1px solid var(--editor-border-strong);
    border-radius: 5px;
    cursor: pointer;
    transition: border-color 140ms ease;
}

.color-row:hover {
    color: var(--editor-text);
    border-color: var(--editor-accent);
}

.color-row-swatch {
    flex-shrink: 0;
    width: 22px;
    height: 22px;
    border: 1px solid var(--editor-border);
    border-radius: 4px;
    box-shadow: inset 0 0 0 1px rgb(255 255 255 / 12%);
}

.color-row-value {
    flex: 1;
    min-width: 0;
    overflow: hidden;
    font-size: 11px;
    font-weight: 600;
    text-align: left;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.color-row-panel {
    padding: 10px;
    background: var(--editor-panel-muted);
    border: 1px solid var(--editor-border);
    border-radius: 5px;
}

.transparent-swatch {
    background-color: #ffffff;
    background-image:
        linear-gradient(45deg, #d4d4d8 25%, transparent 25%),
        linear-gradient(-45deg, #d4d4d8 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, #d4d4d8 75%),
        linear-gradient(-45deg, transparent 75%, #d4d4d8 75%);
    background-size: 8px 8px;
    background-position:
        0 0,
        0 4px,
        4px -4px,
        -4px 0;
}

.repeater-card {
    background: var(--editor-panel-muted);
    border-color: var(--editor-border);
    border-radius: 5px;
}

.repeater-icon-button {
    flex-shrink: 0;
    min-width: 20px;
    padding: 2px;
    color: var(--editor-text-muted);
    background: transparent;
    border: 0;
    font-size: 11px;
    cursor: pointer;
}

.repeater-icon-button:hover:not(:disabled) {
    color: var(--editor-text);
}

.repeater-icon-button:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.repeater-icon-danger:hover:not(:disabled) {
    color: #fb7185;
}

.inspector-secondary-button,
.add-repeater-button {
    color: var(--editor-text);
    background: var(--editor-panel);
    border-color: var(--editor-border);
    border-radius: 5px;
}

.inspector-secondary-button:hover,
.add-repeater-button:hover {
    color: var(--editor-accent);
    background: var(--editor-accent-soft);
    border-color: color-mix(in srgb, var(--editor-accent) 30%, white);
}
</style>
