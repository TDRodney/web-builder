<!-- eslint-disable vue/block-lang -->
<script setup>
import { GripVertical } from '@lucide/vue';
import { computed, inject, ref } from 'vue';
import draggable from 'vuedraggable';
import PresetThumbnail from '@/components/Editor/PresetThumbnail.vue';
import { groupBlockPresets } from '@/lib/blockPresets';

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
const canvasDrag = inject('canvasDrag', null);

const presetGroups = computed(() => groupBlockPresets(props.blockPresets));

const layoutColumn = (suffix) => ({
    id: `layoutcolumn-${Date.now()}-${suffix}-${Math.random().toString(36).slice(2, 6)}`,
    type: 'LayoutColumn',
    props: {
        padding: 20,
        backgroundColor: 'transparent',
        span: 'auto',
        width: 'auto',
        height: 'auto',
        gap: '0px',
    },
    children: [],
});

const cloneBlockDefinition = (definition) => ({
    id: `${definition.type.toLowerCase()}-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
    type: definition.type,
    props: JSON.parse(JSON.stringify(definition.defaultProps || {})),
    children:
        definition.type === 'LayoutGrid'
            ? [layoutColumn(1), layoutColumn(2), layoutColumn(3)]
            : [],
});

const handleDragStart = (event) => {
    canvasDrag?.start(event?.item?.dataset?.type || null, 'library');
};

const handleDragEnd = () => {
    canvasDrag?.end();
};

const addBlock = (type) => {
    emit('add-block', type);
};

const addPreset = (preset) => {
    emit('add-preset', preset);
};
</script>

<template>
    <div class="block-library">
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

        <Transition name="panel-switch" mode="out-in">
            <div v-if="activeLibraryTab === 'blocks'" key="blocks">
                <draggable
                    :list="blockDefinitions"
                    item-key="type"
                    :sort="false"
                    :clone="cloneBlockDefinition"
                    :group="{ name: 'canvas-tree', pull: 'clone', put: false }"
                    handle=".library-drag-handle"
                    class="grid grid-cols-2 gap-2"
                    @start="handleDragStart"
                    @end="handleDragEnd"
                >
                    <template #item="{ element: def }">
                        <div
                            class="library-block-item relative"
                            :class="
                                def.type === 'AtomicText' ? 'col-span-2' : ''
                            "
                            :data-type="def.type"
                        >
                            <button
                                type="button"
                                class="hover-lift h-full w-full cursor-pointer rounded border border-indigo-500/20 bg-indigo-600/10 px-2.5 py-2 pr-7 text-left text-xs font-medium text-indigo-300 transition-colors hover:bg-indigo-600/20"
                                @click="addBlock(def.type)"
                            >
                                + {{ def.label }}
                            </button>
                            <span
                                class="library-drag-handle absolute top-1/2 right-2 flex -translate-y-1/2 cursor-grab items-center justify-center text-editor-text-muted hover:text-editor-text active:cursor-grabbing"
                                title="Drag into the page"
                                @click.stop
                            >
                                <GripVertical :size="13" />
                            </span>
                        </div>
                    </template>
                </draggable>
                <p
                    class="library-tip col-span-2 mt-2 text-[10px] text-slate-500 italic"
                >
                    Click to add, or drag a block directly into the page.
                </p>
            </div>

            <div
                v-else-if="activeLibraryTab === 'presets'"
                key="presets"
                class="preset-library"
            >
                <p class="preset-intro">
                    Pick a layout by shape. One click inserts it — heroes stay
                    swappable with Change layout.
                </p>

                <section
                    v-for="group in presetGroups"
                    :key="group.key"
                    class="preset-group"
                >
                    <h3 class="preset-group-label">{{ group.label }}</h3>
                    <div class="preset-grid">
                        <button
                            v-for="preset in group.presets"
                            :key="preset.key"
                            type="button"
                            class="preset-card"
                            :title="preset.description"
                            @click="addPreset(preset)"
                        >
                            <PresetThumbnail :preview="preset.preview" />
                            <span class="preset-card-copy">
                                <strong class="preset-card-title">{{
                                    preset.label
                                }}</strong>
                                <span class="preset-card-desc">{{
                                    preset.description
                                }}</span>
                            </span>
                        </button>
                    </div>
                </section>
            </div>
        </Transition>
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
    border-color: var(--editor-border);
}

.block-library > :first-child button {
    color: var(--editor-text-muted);
    font-size: 9px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.block-library > :first-child button:hover {
    color: var(--editor-text);
}

.block-library > :first-child button[class*='text-indigo'] {
    color: var(--editor-accent);
    border-bottom-color: var(--editor-accent);
}

.block-library > .grid button {
    min-height: 38px;
    color: var(--editor-text);
    font-size: 10px;
    background: var(--editor-panel-muted);
    border-color: var(--editor-border);
    border-radius: 5px;
}

.block-library > .grid button:hover {
    color: var(--editor-accent);
    background: var(--editor-accent-soft);
    border-color: color-mix(in srgb, var(--editor-accent) 30%, white);
}

.library-tip {
    color: var(--editor-text-muted);
    font-style: normal;
    line-height: 1.45;
}

.preset-library {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.preset-intro {
    margin: 0;
    color: var(--editor-text-muted);
    font-size: 10px;
    line-height: 1.45;
}

.preset-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.preset-group-label {
    margin: 0;
    color: var(--editor-text-muted);
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.preset-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
}

.preset-card {
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 100%;
    padding: 8px;
    text-align: left;
    cursor: pointer;
    background: var(--editor-panel);
    border: 1px solid var(--editor-border);
    border-radius: 7px;
    transition:
        border-color 140ms ease,
        background-color 140ms ease,
        box-shadow 140ms ease,
        transform 140ms ease;
}

.preset-card:hover {
    background: var(--editor-panel-muted);
    border-color: var(--editor-border-strong);
    box-shadow: 0 8px 18px rgba(24, 24, 27, 0.06);
    transform: translateY(-1px);
}

.preset-card:hover :deep(.preset-thumb) {
    border-color: color-mix(in srgb, var(--theme-primary, #4f46e5) 35%, transparent);
    box-shadow: 0 0 0 1px
        color-mix(in srgb, var(--theme-primary, #4f46e5) 18%, transparent);
}

.preset-card:focus-visible {
    outline: 2px solid var(--editor-accent);
    outline-offset: 2px;
}

.preset-card-copy {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 0 2px 2px;
}

.preset-card-title {
    color: var(--editor-text);
    font-size: 11px;
    font-weight: 650;
    line-height: 1.3;
}

.preset-card-desc {
    color: var(--editor-text-muted);
    font-size: 10px;
    line-height: 1.4;
}
</style>
