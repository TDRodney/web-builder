<script setup lang="ts">
import { Check, LayoutTemplate } from '@lucide/vue';
import { inject, ref } from 'vue';
import { toast } from 'vue-sonner';

import { changeSectionPattern, sectionPatterns } from '@/lib/sectionPatterns';

type BlockNode = {
    id: string;
    type: string;
    props: Record<string, unknown>;
    children?: BlockNode[];
};

const props = defineProps<{
    selectedBlock: BlockNode;
}>();

const forceSave = inject<(() => Promise<void>) | null>('forceSave', null);
const isChanging = ref(false);

const selectPattern = async (key: string): Promise<void> => {
    if (props.selectedBlock.props.patternKey === key || isChanging.value) {
        return;
    }

    isChanging.value = true;

    try {
        const changed = changeSectionPattern(props.selectedBlock, key);

        if (!changed) {
            toast.error('That section layout is unavailable.');

            return;
        }

        await forceSave?.();
        toast.success('Section layout changed — your content was preserved.');
    } finally {
        isChanging.value = false;
    }
};
</script>

<template>
    <section class="pattern-picker">
        <div class="pattern-heading">
            <span>
                <LayoutTemplate :size="13" />
                Change layout
            </span>
            <p>
                Switch the structure while keeping matching text, actions, and
                media.
            </p>
        </div>

        <div class="pattern-grid">
            <button
                v-for="pattern in sectionPatterns"
                :key="pattern.key"
                type="button"
                class="pattern-card"
                :class="{
                    'pattern-card-active':
                        selectedBlock.props.patternKey === pattern.key,
                }"
                :disabled="isChanging"
                @click="selectPattern(pattern.key)"
            >
                <span
                    class="pattern-thumbnail"
                    :class="`pattern-thumbnail-${pattern.key}`"
                    aria-hidden="true"
                >
                    <i></i><i></i><i></i>
                </span>
                <span class="pattern-copy">
                    <strong>{{ pattern.label }}</strong>
                    <small>{{ pattern.description }}</small>
                </span>
                <Check
                    v-if="selectedBlock.props.patternKey === pattern.key"
                    :size="13"
                    class="pattern-check"
                />
            </button>
        </div>
    </section>
</template>

<style scoped>
.pattern-picker {
    display: grid;
    padding: 12px;
    gap: 10px;
    background: var(--editor-panel-muted);
    border: 1px solid var(--editor-border);
    border-radius: 5px;
}

.pattern-heading > span {
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--editor-text);
    font-size: 10px;
    font-weight: 700;
}

.pattern-heading p {
    margin: 5px 0 0;
    color: var(--editor-text-muted);
    font-size: 9px;
    line-height: 1.5;
}

.pattern-grid {
    display: grid;
    gap: 7px;
}

.pattern-card {
    position: relative;
    display: grid;
    grid-template-columns: 66px minmax(0, 1fr);
    align-items: center;
    min-height: 66px;
    padding: 7px;
    gap: 9px;
    color: var(--editor-text-muted);
    text-align: left;
    background: var(--editor-panel);
    border: 1px solid var(--editor-border);
    border-radius: 5px;
    cursor: pointer;
    transition:
        border-color 120ms ease,
        background-color 120ms ease;
}

.pattern-card:hover,
.pattern-card-active {
    color: var(--editor-text);
    background: var(--editor-accent-soft);
    border-color: color-mix(in srgb, var(--editor-accent) 42%, white);
}

.pattern-thumbnail {
    display: grid;
    min-height: 48px;
    padding: 6px;
    gap: 4px;
    background: #ffffff;
    border: 1px solid #dedee3;
    border-radius: 3px;
}

.pattern-thumbnail i {
    display: block;
    height: 4px;
    background: #b7bcc6;
    border-radius: 999px;
}

.pattern-thumbnail i:nth-child(1) {
    width: 34%;
    background: #8f6fdd;
}

.pattern-thumbnail i:nth-child(2) {
    width: 84%;
    height: 8px;
    background: #3f4652;
}

.pattern-thumbnail i:nth-child(3) {
    width: 62%;
}

.pattern-thumbnail-hero-centered {
    align-content: center;
    justify-items: center;
}

.pattern-thumbnail-hero-split-right,
.pattern-thumbnail-hero-split-left {
    grid-template-columns: 1fr 0.85fr;
    align-content: center;
}

.pattern-thumbnail-hero-split-left i:last-child,
.pattern-thumbnail-hero-split-right i:last-child {
    width: 100%;
    height: 32px;
    grid-row: 1 / 4;
    background: #ddd6cf;
    border-radius: 2px;
}

.pattern-thumbnail-hero-split-left i:last-child {
    grid-column: 1;
}

.pattern-thumbnail-hero-split-left i:not(:last-child) {
    grid-column: 2;
}

.pattern-thumbnail-hero-editorial i:nth-child(2) {
    width: 100%;
    height: 18px;
}

.pattern-thumbnail-hero-minimal {
    align-content: center;
}

.pattern-copy {
    min-width: 0;
}

.pattern-copy strong,
.pattern-copy small {
    display: block;
}

.pattern-copy strong {
    color: inherit;
    font-size: 10px;
    font-weight: 700;
}

.pattern-copy small {
    margin-top: 3px;
    font-size: 8px;
    line-height: 1.45;
}

.pattern-check {
    position: absolute;
    top: 7px;
    right: 7px;
    color: var(--editor-accent);
}

@media (prefers-reduced-motion: reduce) {
    .pattern-card {
        transition: none;
    }
}
</style>
