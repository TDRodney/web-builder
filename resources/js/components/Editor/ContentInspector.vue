<!-- eslint-disable vue/block-lang -->
<script setup>
/* eslint-disable vue/no-mutating-props */
import { ChevronDown } from '@lucide/vue';
import { computed, inject, ref, watch } from 'vue';
import InspectorField from '@/components/Editor/InspectorField.vue';
import ProductGridInspector from '@/components/Editor/ProductGridInspector.vue';
import SectionPatternInspector from '@/components/Editor/SectionPatternInspector.vue';

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

const deleteSelectedBlock = () => {
    if (props.selectedBlock && blockActions) {
        blockActions.deleteBlockById(props.selectedBlock.id);
    }
};

const groupedFields = computed(() => {
    const fields = props.activeBlockDefinition?.inspectorFields ?? [];
    const content = fields.filter(
        (field) => (field.group ?? 'content') === 'content',
    );
    const style = fields.filter((field) => field.group === 'style');

    return { content, style };
});

const sectionMeta = {
    content: { title: 'Content' },
    style: { title: 'Style & Layout' },
    effects: { title: 'Effects' },
};

const openSections = ref({ content: true, style: false, effects: false });

watch(
    () => props.selectedBlock?.id,
    () => {
        const hasContent = groupedFields.value.content.length > 0;
        openSections.value = {
            content: hasContent,
            style: !hasContent,
            effects: Boolean(props.selectedBlock?.props?.reveal),
        };
    },
    { immediate: true },
);

const toggleSection = (key) => {
    openSections.value[key] = !openSections.value[key];
};

const visibleSections = computed(() =>
    ['content', 'style'].filter((key) => groupedFields.value[key].length > 0),
);
</script>

<template>
    <div v-if="selectedBlock" class="inspector-form animate-fade-in space-y-3">
        <ProductGridInspector
            v-if="selectedBlock.type === 'ProductGridBlock'"
            :selected-block="selectedBlock"
        />

        <template v-else>
            <SectionPatternInspector
                v-if="selectedBlock.type === 'SectionBlock'"
                :selected-block="selectedBlock"
            />

            <section
                v-for="sectionKey in visibleSections"
                :key="sectionKey"
                class="inspector-section"
            >
                <button
                    type="button"
                    class="inspector-section-header"
                    :aria-expanded="openSections[sectionKey]"
                    @click="toggleSection(sectionKey)"
                >
                    <span>{{ sectionMeta[sectionKey].title }}</span>
                    <span class="inspector-section-meta">
                        {{ groupedFields[sectionKey].length }}
                        <ChevronDown
                            :size="13"
                            :stroke-width="2.5"
                            class="transition"
                            :class="{ 'rotate-180': openSections[sectionKey] }"
                        />
                    </span>
                </button>
                <div
                    v-if="openSections[sectionKey]"
                    class="inspector-section-body space-y-3.5"
                >
                    <InspectorField
                        v-for="field in groupedFields[sectionKey]"
                        :key="field.key"
                        :field="field"
                        :selected-block="selectedBlock"
                        @open-media-picker="emit('open-media-picker', $event)"
                    />
                </div>
            </section>
        </template>

        <section class="inspector-section">
            <button
                type="button"
                class="inspector-section-header"
                :aria-expanded="openSections.effects"
                @click="toggleSection('effects')"
            >
                <span>{{ sectionMeta.effects.title }}</span>
                <span class="inspector-section-meta">
                    <span
                        v-if="selectedBlock.props.reveal"
                        class="inspector-section-badge"
                        >On</span
                    >
                    <ChevronDown
                        :size="13"
                        :stroke-width="2.5"
                        class="transition"
                        :class="{ 'rotate-180': openSections.effects }"
                    />
                </span>
            </button>
            <div
                v-if="openSections.effects"
                class="inspector-section-body space-y-3"
            >
                <div class="space-y-1">
                    <label class="inspector-label">Entrance on scroll</label>
                    <select
                        v-model="selectedBlock.props.reveal"
                        class="inspector-select"
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
                    <label class="inspector-label">Delay</label>
                    <select
                        v-model.number="selectedBlock.props.revealDelay"
                        class="inspector-select"
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
                    Plays once on the published site as visitors scroll. The
                    editor canvas stays static so blocks are always visible.
                </p>
            </div>
        </section>

        <button
            class="delete-block-button mt-2 w-full cursor-pointer rounded-lg border-0 px-3 py-2 text-xs font-semibold transition-all"
            @click="deleteSelectedBlock"
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
.inspector-section {
    overflow: hidden;
    background: var(--editor-panel-muted);
    border: 1px solid var(--editor-border);
    border-radius: 6px;
}

.inspector-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 9px 11px;
    color: var(--editor-text);
    background: transparent;
    border: 0;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    cursor: pointer;
}

.inspector-section-header:hover {
    color: var(--editor-accent);
}

.inspector-section-meta {
    display: inline-flex;
    gap: 5px;
    align-items: center;
    color: var(--editor-text-muted);
    font-size: 9px;
}

.inspector-section-badge {
    padding: 1px 6px;
    color: var(--editor-accent);
    background: var(--editor-accent-soft);
    border-radius: 999px;
    font-size: 8px;
    font-weight: 700;
}

.inspector-section-body {
    padding: 4px 11px 13px;
}

.inspector-label {
    display: block;
    color: var(--editor-text-muted);
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.inspector-select {
    width: 100%;
    min-height: 36px;
    padding: 8px 12px;
    color: var(--editor-text);
    font-size: 11px;
    background: var(--editor-panel);
    border: 1px solid var(--editor-border-strong);
    border-radius: 5px;
    cursor: pointer;
}

.inspector-select:focus {
    border-color: var(--editor-accent);
    outline: none;
    box-shadow: 0 0 0 3px rgb(79 70 229 / 10%);
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
