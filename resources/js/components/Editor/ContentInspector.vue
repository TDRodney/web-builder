<!-- eslint-disable vue/block-lang -->
<script setup>
/* eslint-disable vue/no-mutating-props */
import { inject, watch } from 'vue';
import FontSizeControl from '@/components/Editor/FontSizeControl.vue';
import ProductGridInspector from '@/components/Editor/ProductGridInspector.vue';
import SectionPatternInspector from '@/components/Editor/SectionPatternInspector.vue';
import ThemeColorControl from '@/components/Editor/ThemeColorControl.vue';

const props = defineProps({
    selectedBlock: {
        type: Object,
        default: null,
    },
    activeBlockDefinition: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['open-media-picker']);

const ensureDefaultProps = () => {
    if (!props.selectedBlock || !props.selectedBlock.props) {
        return;
    }

    const defaults = props.activeBlockDefinition?.defaultProps || {};
    const fields = props.activeBlockDefinition?.inspectorFields || [];

    fields.forEach((field) => {
        if (props.selectedBlock.props[field.key] === undefined) {
            let fallback = defaults[field.key];

            if (fallback === undefined) {
                if (field.type === 'select') {
                    fallback = field.options?.[0]?.value ?? '';
                } else if (field.type === 'toggle') {
                    fallback = false;
                } else if (field.type === 'range' || field.type === 'number') {
                    fallback = field.min ?? 0;
                } else {
                    fallback = '';
                }
            }

            props.selectedBlock.props[field.key] = fallback;
        }

        if (field.type === 'toggle') {
            const val = props.selectedBlock.props[field.key];

            if (typeof val === 'string') {
                props.selectedBlock.props[field.key] =
                    val === 'yes' || val === 'true';
            } else {
                props.selectedBlock.props[field.key] = Boolean(val);
            }
        }
    });
};

watch(
    () => [props.selectedBlock?.id, props.activeBlockDefinition?.type],
    () => {
        ensureDefaultProps();
    },
    { immediate: true },
);

const revealOptions = [
    { label: 'None', value: '' },
    { label: 'Fade up', value: 'fade-up' },
    { label: 'Fade in', value: 'fade-in' },
    { label: 'Scale in', value: 'scale-in' },
    { label: 'Slide from right', value: 'slide-left' },
    { label: 'Slide from left', value: 'slide-right' },
];

const revealDelayOptions = [
    { label: 'No delay', value: 0 },
    { label: '100 ms', value: 100 },
    { label: '200 ms', value: 200 },
    { label: '300 ms', value: 300 },
    { label: '450 ms', value: 450 },
];

const blockActions = inject('blockActions', null);
const forceSave = inject('forceSave', null);

const deleteSelectedBlock = () => {
    if (props.selectedBlock && blockActions) {
        blockActions.deleteBlockById(props.selectedBlock.id);
    }
};

// Repeater helpers
const addRepeaterItem = (fieldKey, subFields) => {
    if (!props.selectedBlock) {
        return;
    }

    if (
        !props.selectedBlock.props[fieldKey] ||
        !Array.isArray(props.selectedBlock.props[fieldKey])
    ) {
        props.selectedBlock.props[fieldKey] = [];
    }

    const newItem = {};
    subFields.forEach((sub) => {
        if (sub.type === 'select') {
            newItem[sub.key] = sub.options?.[0]?.value || '';
        } else if (sub.type === 'toggle') {
            newItem[sub.key] = false;
        } else {
            newItem[sub.key] = '';
        }
    });
    props.selectedBlock.props[fieldKey].push(newItem);

    if (forceSave) {
        forceSave();
    }
};

const deleteRepeaterItem = (fieldKey, index) => {
    if (!props.selectedBlock || !props.selectedBlock.props[fieldKey]) {
        return;
    }

    props.selectedBlock.props[fieldKey].splice(index, 1);

    if (forceSave) {
        forceSave();
    }
};

const moveRepeaterItem = (fieldKey, index, direction) => {
    if (!props.selectedBlock || !props.selectedBlock.props[fieldKey]) {
        return;
    }

    const list = props.selectedBlock.props[fieldKey];
    const targetIndex = index + direction;

    if (targetIndex < 0 || targetIndex >= list.length) {
        return;
    }

    const temp = list[index];
    list[index] = list[targetIndex];
    list[targetIndex] = temp;

    if (forceSave) {
        forceSave();
    }
};
</script>

<template>
    <div v-if="selectedBlock" class="inspector-form animate-fade-in space-y-4">
        <ProductGridInspector
            v-if="selectedBlock.type === 'ProductGridBlock'"
            :selected-block="selectedBlock"
        />

        <div
            v-else-if="selectedBlock.type === 'SectionBlock'"
            class="space-y-4"
        >
            <SectionPatternInspector :selected-block="selectedBlock" />

            <div v-if="activeBlockDefinition" class="space-y-4">
                <div
                    v-for="field in activeBlockDefinition.inspectorFields"
                    :key="field.key"
                    class="space-y-1"
                >
                    <label class="block text-xs font-semibold text-slate-400">
                        {{ field.label }}
                        <span
                            v-if="
                                field.type === 'range' &&
                                selectedBlock.props[field.key] !== undefined
                            "
                            class="text-slate-300"
                        >
                            : {{ selectedBlock.props[field.key] }}
                        </span>
                    </label>

                    <input
                        v-if="field.type === 'range'"
                        v-model.number="selectedBlock.props[field.key]"
                        type="range"
                        :min="field.min"
                        :max="field.max"
                        class="w-full accent-indigo-500"
                    />
                    <textarea
                        v-else-if="field.type === 'textarea'"
                        v-model="selectedBlock.props[field.key]"
                        rows="3"
                        :placeholder="field.placeholder"
                        class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
                    ></textarea>
                    <label
                        v-else-if="field.type === 'toggle'"
                        class="relative inline-flex cursor-pointer items-center py-1"
                    >
                        <input
                            type="checkbox"
                            v-model="selectedBlock.props[field.key]"
                            class="peer sr-only"
                        />
                        <div
                            class="peer h-5 w-9 rounded-full bg-slate-700 after:absolute after:top-[6px] after:left-[2px] after:h-4 after:w-4 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:bg-indigo-600 peer-checked:after:translate-x-4 peer-focus:outline-none"
                        ></div>
                        <span class="ml-2.5 text-xs font-medium text-slate-300">
                            {{ selectedBlock.props[field.key] ? 'On' : 'Off' }}
                        </span>
                    </label>
                    <input
                        v-else-if="field.type === 'number'"
                        v-model.number="selectedBlock.props[field.key]"
                        type="number"
                        :min="field.min"
                        :max="field.max"
                        class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white"
                    />
                    <select
                        v-else-if="field.type === 'select'"
                        v-model="selectedBlock.props[field.key]"
                        class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white"
                    >
                        <option
                            v-for="option in field.options"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                    <ThemeColorControl
                        v-else-if="field.type === 'theme-color'"
                        v-model="selectedBlock.props[field.key]"
                        :default-value="field.defaultValue"
                        :custom-default="field.customDefault"
                    />
                    <FontSizeControl
                        v-else-if="field.type === 'font-size'"
                        v-model="selectedBlock.props[field.key]"
                    />
                    <div
                        v-else-if="field.type === 'color'"
                        class="flex items-center gap-2"
                    >
                        <input
                            v-model="selectedBlock.props[field.key]"
                            type="color"
                            class="h-8 w-12 rounded border border-slate-700 bg-transparent p-0"
                        />
                        <code class="text-xs text-slate-300">{{
                            selectedBlock.props[field.key]
                        }}</code>
                    </div>
                    <div v-else-if="field.type === 'media'" class="space-y-2">
                        <img
                            v-if="selectedBlock.props[field.key]"
                            :src="String(selectedBlock.props[field.key])"
                            alt=""
                            class="h-24 w-full rounded object-cover"
                        />
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                type="button"
                                class="inspector-secondary-button rounded border px-2 py-2 text-xs"
                                @click="emit('open-media-picker', field.key)"
                            >
                                {{
                                    selectedBlock.props[field.key]
                                        ? 'Change image'
                                        : 'Choose image'
                                }}
                            </button>
                            <button
                                v-if="selectedBlock.props[field.key]"
                                type="button"
                                class="rounded border border-rose-500/20 px-2 py-2 text-xs text-rose-400"
                                @click="selectedBlock.props[field.key] = ''"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else-if="activeBlockDefinition" class="space-y-4">
            <div
                v-for="field in activeBlockDefinition.inspectorFields"
                :key="field.key"
                class="space-y-1"
            >
                <label class="block text-xs font-semibold text-slate-400">
                    {{ field.label }}
                    <span
                        v-if="
                            field.type === 'range' &&
                            selectedBlock.props[field.key] !== undefined
                        "
                        class="text-slate-300"
                    >
                        : {{ selectedBlock.props[field.key] }}px
                    </span>
                </label>

                <!-- Range slider -->
                <input
                    v-if="field.type === 'range'"
                    type="range"
                    :min="field.min ?? 10"
                    :max="field.max ?? 150"
                    v-model.number="selectedBlock.props[field.key]"
                    class="w-full accent-indigo-500"
                />

                <!-- Color picker -->
                <ThemeColorControl
                    v-else-if="field.type === 'theme-color'"
                    v-model="selectedBlock.props[field.key]"
                    :default-value="field.defaultValue"
                    :custom-default="field.customDefault"
                />

                <FontSizeControl
                    v-else-if="field.type === 'font-size'"
                    v-model="selectedBlock.props[field.key]"
                />

                <!-- Color picker -->
                <div
                    v-else-if="field.type === 'color'"
                    class="flex items-center gap-2"
                >
                    <input
                        type="color"
                        v-model="selectedBlock.props[field.key]"
                        class="h-8 w-12 cursor-pointer rounded border border-slate-700 bg-transparent p-0"
                    />
                    <span class="font-mono text-xs text-slate-300">{{
                        selectedBlock.props[field.key]
                    }}</span>
                </div>

                <!-- Textarea input -->
                <textarea
                    v-else-if="field.type === 'textarea'"
                    v-model="selectedBlock.props[field.key]"
                    rows="3"
                    :placeholder="field.placeholder"
                    class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
                ></textarea>

                <!-- Toggle switch input -->
                <label
                    v-else-if="field.type === 'toggle'"
                    class="relative inline-flex cursor-pointer items-center py-1"
                >
                    <input
                        type="checkbox"
                        v-model="selectedBlock.props[field.key]"
                        class="peer sr-only"
                    />
                    <div
                        class="peer h-5 w-9 rounded-full bg-slate-700 after:absolute after:top-[6px] after:left-[2px] after:h-4 after:w-4 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:bg-indigo-600 peer-checked:after:translate-x-4 peer-focus:outline-none"
                    ></div>
                    <span class="ml-2.5 text-xs font-medium text-slate-300">
                        {{ selectedBlock.props[field.key] ? 'On' : 'Off' }}
                    </span>
                </label>

                <!-- Number input -->
                <input
                    v-else-if="field.type === 'number'"
                    type="number"
                    :min="field.min"
                    :max="field.max"
                    v-model.number="selectedBlock.props[field.key]"
                    class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
                />

                <!-- Columns segmented control -->
                <div
                    v-else-if="field.type === 'columns'"
                    class="columns-control flex flex-wrap gap-1.5"
                    role="group"
                    :aria-label="field.label"
                >
                    <button
                        v-for="opt in field.options"
                        :key="opt.value"
                        type="button"
                        class="columns-option"
                        :class="{
                            'columns-option-active':
                                Number(selectedBlock.props[field.key]) ===
                                Number(opt.value),
                        }"
                        :aria-pressed="
                            Number(selectedBlock.props[field.key]) ===
                            Number(opt.value)
                        "
                        @click="
                            selectedBlock.props[field.key] = Number(opt.value)
                        "
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
                        :key="opt.value"
                        :value="opt.value"
                    >
                        {{ opt.label }}
                    </option>
                </select>

                <!-- Media picker -->
                <div v-else-if="field.type === 'media'" class="space-y-2">
                    <div
                        v-if="selectedBlock.props[field.key]"
                        class="relative overflow-hidden rounded"
                    >
                        <img
                            :src="selectedBlock.props[field.key]"
                            alt=""
                            class="h-24 w-full rounded object-cover"
                        />
                        <button
                            @click="selectedBlock.props[field.key] = ''"
                            class="absolute top-1 right-1 cursor-pointer rounded border-0 bg-slate-900/80 p-0.5 text-rose-400"
                            title="Remove image"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-3.5 w-3.5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                    <button
                        @click="emit('open-media-picker', field.key)"
                        class="inspector-secondary-button w-full cursor-pointer rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-xs font-semibold text-slate-300 transition-all hover:bg-slate-700"
                    >
                        {{
                            selectedBlock.props[field.key]
                                ? 'Change Image'
                                : 'Choose Image'
                        }}
                    </button>
                </div>

                <!-- Repeater Field -->
                <div v-else-if="field.type === 'repeater'" class="space-y-3">
                    <div
                        v-for="(item, index) in selectedBlock.props[
                            field.key
                        ] || []"
                        :key="index"
                        class="repeater-card group/item relative space-y-3 rounded-lg border border-slate-700/60 bg-slate-800/60 p-3"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-700/40 pb-2"
                        >
                            <span class="text-xs font-bold text-slate-300"
                                >Item #{{ index + 1 }}</span
                            >
                            <div class="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    @click="
                                        moveRepeaterItem(field.key, index, -1)
                                    "
                                    :disabled="index === 0"
                                    class="cursor-pointer border-0 bg-transparent text-slate-400 hover:text-white disabled:cursor-not-allowed disabled:opacity-30"
                                    title="Move Up"
                                >
                                    ↑
                                </button>
                                <button
                                    type="button"
                                    @click="
                                        moveRepeaterItem(field.key, index, 1)
                                    "
                                    :disabled="
                                        index ===
                                        selectedBlock.props[field.key]?.length -
                                            1
                                    "
                                    class="cursor-pointer border-0 bg-transparent text-slate-400 hover:text-white disabled:cursor-not-allowed disabled:opacity-30"
                                    title="Move Down"
                                >
                                    ↓
                                </button>
                                <button
                                    type="button"
                                    @click="
                                        deleteRepeaterItem(field.key, index)
                                    "
                                    class="ml-1 cursor-pointer border-0 bg-transparent text-rose-400 hover:text-rose-300"
                                    title="Delete Item"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-3.5 w-3.5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Sub fields -->
                        <div class="space-y-2">
                            <div
                                v-for="sub in field.subFields"
                                :key="sub.key"
                                class="space-y-1"
                            >
                                <label
                                    class="block text-[10px] font-semibold text-slate-400"
                                    >{{ sub.label }}</label
                                >

                                <!-- Sub field select -->
                                <select
                                    v-if="sub.type === 'select'"
                                    v-model="item[sub.key]"
                                    class="w-full cursor-pointer rounded border border-slate-700 bg-slate-900 px-2 py-1 text-xs text-white focus:border-indigo-500 focus:outline-none"
                                >
                                    <option
                                        v-for="opt in sub.options"
                                        :key="opt.value"
                                        :value="opt.value"
                                    >
                                        {{ opt.label }}
                                    </option>
                                </select>

                                <!-- Sub field text -->
                                <input
                                    v-else
                                    type="text"
                                    v-model="item[sub.key]"
                                    :placeholder="sub.placeholder"
                                    class="w-full rounded border border-slate-700 bg-slate-900 px-2 py-1 text-xs text-white focus:border-indigo-500 focus:outline-none"
                                />
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="addRepeaterItem(field.key, field.subFields)"
                        class="add-repeater-button flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-lg border border-dashed border-slate-600 bg-slate-800 px-3 py-2 text-xs font-semibold text-slate-300 transition-all hover:border-slate-500 hover:bg-slate-700"
                    >
                        + Add Item
                    </button>
                </div>

                <!-- Text fallback -->
                <input
                    v-else
                    type="text"
                    v-model="selectedBlock.props[field.key]"
                    :placeholder="field.placeholder"
                    class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
                />
            </div>
        </div>

        <div class="animation-section space-y-3">
            <span class="animation-section-title">Animation</span>
            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-400"
                    >Entrance on scroll</label
                >
                <select
                    v-model="selectedBlock.props.reveal"
                    class="w-full cursor-pointer rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
                >
                    <option
                        v-for="opt in revealOptions"
                        :key="opt.value"
                        :value="opt.value"
                    >
                        {{ opt.label }}
                    </option>
                </select>
            </div>
            <div v-if="selectedBlock.props.reveal" class="space-y-1">
                <label class="block text-xs font-semibold text-slate-400"
                    >Delay</label
                >
                <select
                    v-model.number="selectedBlock.props.revealDelay"
                    class="w-full cursor-pointer rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
                >
                    <option
                        v-for="opt in revealDelayOptions"
                        :key="opt.value"
                        :value="opt.value"
                    >
                        {{ opt.label }}
                    </option>
                </select>
            </div>
            <p class="animation-section-hint">
                Plays once on the published site as visitors scroll. The editor
                canvas stays static so blocks are always visible.
            </p>
        </div>

        <button
            @click="deleteSelectedBlock"
            class="delete-block-button mt-4 w-full cursor-pointer rounded-lg border-0 bg-rose-600 px-3 py-2 text-xs font-semibold text-white transition-all hover:bg-rose-500"
        >
            Delete Block
        </button>
    </div>
    <div v-else class="inspector-empty-state">
        <span>Nothing selected</span>
        <p>Click a block on the canvas to edit its content and appearance.</p>
    </div>
</template>

<style scoped>
.inspector-form :deep(label) {
    color: var(--editor-text-muted);
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.inspector-form :deep(input:not([type='color']):not([type='range'])),
.inspector-form :deep(select) {
    min-height: 36px;
    color: var(--editor-text);
    font-size: 11px;
    background: var(--editor-panel);
    border-color: var(--editor-border-strong);
    border-radius: 5px;
}

.inspector-form :deep(input:not([type='color']):not([type='range']):focus),
.inspector-form :deep(select:focus) {
    border-color: var(--editor-accent);
    box-shadow: 0 0 0 3px rgb(79 70 229 / 10%);
}

.inspector-form :deep(input[type='range']) {
    accent-color: var(--editor-accent);
}

.inspector-form :deep(input[type='color']) {
    border-color: var(--editor-border-strong);
    border-radius: 4px;
}

.repeater-card {
    background: var(--editor-panel-muted);
    border-color: var(--editor-border);
    border-radius: 5px;
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

.columns-option {
    flex: 1 1 0;
    min-width: 2.25rem;
    min-height: 36px;
    color: var(--editor-text-muted);
    background: var(--editor-panel);
    border: 1px solid var(--editor-border);
    border-radius: 5px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition:
        color 140ms ease,
        background-color 140ms ease,
        border-color 140ms ease;
}

.columns-option:hover {
    color: var(--editor-text);
    border-color: var(--editor-border-strong);
}

.columns-option-active {
    color: #fff;
    background: var(--editor-text);
    border-color: var(--editor-text);
}

.animation-section {
    padding: 12px;
    background: var(--editor-panel-muted);
    border: 1px solid var(--editor-border);
    border-radius: 5px;
}

.animation-section-title {
    display: block;
    color: var(--editor-text);
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.animation-section-hint {
    margin: 0;
    color: var(--editor-text-muted);
    font-size: 9px;
    line-height: 1.5;
}

.delete-block-button {
    color: #fca5a5;
    background: rgb(127 29 29 / 22%);
    border: 1px solid rgb(248 113 113 / 22%);
    border-radius: 5px;
}

.delete-block-button:hover {
    color: #fee2e2;
    background: rgb(153 27 27 / 34%);
}

.inspector-empty-state {
    padding: 26px 18px;
    text-align: center;
    background: var(--editor-panel-muted);
    border: 1px dashed var(--editor-border-strong);
    border-radius: 5px;
}

.inspector-empty-state span {
    color: var(--editor-text);
    font-size: 11px;
    font-weight: 650;
}

.inspector-empty-state p {
    margin: 5px 0 0;
    color: var(--editor-text-muted);
    font-size: 10px;
    line-height: 1.5;
}
</style>
