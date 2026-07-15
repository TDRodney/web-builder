<script setup>
import { ref } from 'vue';

const props = defineProps({
    blockDefinitions: {
        type: Array,
        required: true,
    },
    blockPresets: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['add-block', 'add-preset']);

const activeLibraryTab = ref('blocks');

const addBlock = (type) => {
    emit('add-block', type);
};

const addPreset = (preset) => {
    emit('add-preset', preset);
};
</script>

<template>
    <!-- Block Library -->
    <div class="block-library">
        <!-- Library Tabs -->
        <div class="mb-3 flex gap-3 border-b border-slate-800">
            <button
                type="button"
                :class="[
                    'cursor-pointer border-0 border-b-2 bg-transparent pb-2 text-xs font-bold transition-colors',
                    activeLibraryTab === 'blocks'
                        ? 'border-indigo-500 text-indigo-400'
                        : 'border-transparent text-slate-500 hover:text-slate-300',
                ]"
                @click="activeLibraryTab = 'blocks'"
            >
                Blocks
            </button>
            <button
                type="button"
                :class="[
                    'cursor-pointer border-0 border-b-2 bg-transparent pb-2 text-xs font-bold transition-colors',
                    activeLibraryTab === 'presets'
                        ? 'border-indigo-500 text-indigo-400'
                        : 'border-transparent text-slate-500 hover:text-slate-300',
                ]"
                @click="activeLibraryTab = 'presets'"
            >
                Presets
            </button>
        </div>

        <!-- Tab Content: Blocks -->
        <div
            v-if="activeLibraryTab === 'blocks'"
            class="grid grid-cols-2 gap-2"
        >
            <button
                v-for="def in blockDefinitions"
                :key="def.type"
                @click="addBlock(def.type)"
                :class="def.type === 'AtomicText' ? 'col-span-2' : ''"
                class="cursor-pointer rounded border border-indigo-500/20 bg-indigo-600/10 px-2.5 py-2 text-xs font-medium text-indigo-300 transition-colors hover:bg-indigo-600/20"
            >
                + {{ def.label }}
            </button>
            <p
                class="library-tip col-span-2 mt-2 text-[10px] text-slate-500 italic"
            >
                If a layout container is selected, the new block is nested
                inside it.
            </p>
        </div>

        <!-- Tab Content: Presets -->
        <div v-else-if="activeLibraryTab === 'presets'" class="space-y-2">
            <button
                v-for="preset in blockPresets"
                :key="preset.key"
                @click="addPreset(preset)"
                class="group w-full cursor-pointer rounded border border-slate-700/60 bg-slate-800/40 p-2.5 text-left transition-all hover:border-slate-600 hover:bg-slate-800/80"
            >
                <div
                    class="text-xs font-bold text-indigo-300 group-hover:text-indigo-200"
                >
                    {{ preset.label }}
                </div>
                <div
                    class="mt-0.5 text-[10px] leading-relaxed text-slate-500 group-hover:text-slate-400"
                >
                    {{ preset.description }}
                </div>
            </button>
        </div>
    </div>
</template>

<style scoped>
.block-library {
    margin: 0;
    padding: 0;
    border: 0;
}

.block-library > :first-child {
    margin-bottom: 12px;
    gap: 18px;
    border-color: #2c2c2f;
}

.block-library > :first-child button {
    color: #71717a;
    font-size: 9px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.block-library > :first-child button:hover {
    color: #d4d4d8;
}

.block-library > :first-child button[class*='text-indigo'] {
    color: #b7c6dd;
    border-bottom-color: #9db3d6;
}

.block-library > .grid button {
    min-height: 38px;
    color: #c5cfdf;
    font-size: 10px;
    background: #181a1e;
    border-color: #303846;
    border-radius: 5px;
}

.block-library > .grid button:hover {
    color: #ffffff;
    background: #222731;
    border-color: #52627a;
}

.block-library > .space-y-2 button {
    background: #171719;
    border-color: #303033;
    border-radius: 5px;
}

.block-library > .space-y-2 button:hover {
    background: #202023;
    border-color: #48484d;
}

.block-library > .space-y-2 button > div:first-child {
    color: #c5cfdf;
}

.library-tip {
    color: #63636b;
    font-style: normal;
    line-height: 1.45;
}
</style>
