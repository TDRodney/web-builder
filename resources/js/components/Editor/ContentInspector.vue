<script setup>
import { inject } from 'vue';

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

const blockActions = inject('blockActions', null);
const forceSave = inject('forceSave', null);

const deleteSelectedBlock = () => {
    if (props.selectedBlock && blockActions) {
        blockActions.deleteBlockById(props.selectedBlock.id);
    }
};

// Repeater helpers
const addRepeaterItem = (fieldKey, subFields) => {
    if (!props.selectedBlock) return;
    if (
        !props.selectedBlock.props[fieldKey] ||
        !Array.isArray(props.selectedBlock.props[fieldKey])
    ) {
        props.selectedBlock.props[fieldKey] = [];
    }
    const newItem = {};
    subFields.forEach((sub) => {
        newItem[sub.key] =
            sub.type === 'select' ? sub.options?.[0]?.value || '' : '';
    });
    props.selectedBlock.props[fieldKey].push(newItem);
    if (forceSave) forceSave();
};

const deleteRepeaterItem = (fieldKey, index) => {
    if (!props.selectedBlock || !props.selectedBlock.props[fieldKey]) return;
    props.selectedBlock.props[fieldKey].splice(index, 1);
    if (forceSave) forceSave();
};

const moveRepeaterItem = (fieldKey, index, direction) => {
    if (!props.selectedBlock || !props.selectedBlock.props[fieldKey]) return;
    const list = props.selectedBlock.props[fieldKey];
    const targetIndex = index + direction;
    if (targetIndex < 0 || targetIndex >= list.length) return;
    const temp = list[index];
    list[index] = list[targetIndex];
    list[targetIndex] = temp;
    if (forceSave) forceSave();
};
</script>

<template>
    <div v-if="selectedBlock" class="inspector-form animate-fade-in space-y-4">
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

                <!-- Number input -->
                <input
                    v-else-if="field.type === 'number'"
                    type="number"
                    :min="field.min"
                    :max="field.max"
                    v-model.number="selectedBlock.props[field.key]"
                    class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none"
                />

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
    color: #8b9ab3;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.inspector-form :deep(input:not([type='color']):not([type='range'])),
.inspector-form :deep(select) {
    min-height: 36px;
    color: #f4f4f5;
    font-size: 11px;
    background: #19191b;
    border-color: #343438;
    border-radius: 5px;
}

.inspector-form :deep(input:not([type='color']):not([type='range']):focus),
.inspector-form :deep(select:focus) {
    border-color: #7185a5;
    box-shadow: 0 0 0 2px rgb(113 133 165 / 15%);
}

.inspector-form :deep(input[type='range']) {
    accent-color: #9db3d6;
}

.inspector-form :deep(input[type='color']) {
    border-color: #3f3f46;
    border-radius: 4px;
}

.repeater-card {
    background: #161618;
    border-color: #303033;
    border-radius: 5px;
}

.inspector-secondary-button,
.add-repeater-button {
    color: #c5cfdf;
    background: #1b1b1e;
    border-color: #38383c;
    border-radius: 5px;
}

.inspector-secondary-button:hover,
.add-repeater-button:hover {
    color: #ffffff;
    background: #29292c;
    border-color: #52525b;
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
    background: #151517;
    border: 1px dashed #343438;
    border-radius: 5px;
}

.inspector-empty-state span {
    color: #d4d4d8;
    font-size: 11px;
    font-weight: 650;
}

.inspector-empty-state p {
    margin: 5px 0 0;
    color: #71717a;
    font-size: 10px;
    line-height: 1.5;
}
</style>
