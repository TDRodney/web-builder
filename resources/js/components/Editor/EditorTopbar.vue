<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ExternalLink,
    Monitor,
    PanelLeft,
    Redo2,
    Smartphone,
    Tablet,
    Undo2,
} from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps({
    dashboardUrl: { type: String, required: true },
    liveUrl: { type: String, required: true },
    pageTitle: { type: String, default: 'Untitled page' },
    tenantName: { type: String, default: 'Workspace' },
    viewMode: { type: String, required: true },
    canUndo: { type: Boolean, default: false },
    canRedo: { type: Boolean, default: false },
    saveState: { type: String, default: 'saved' },
    commercePreview: { type: Object, default: () => ({}) },
});

const emit = defineEmits([
    'toggle-sidebar',
    'update:view-mode',
    'undo',
    'redo',
    'update:commerce-preview',
]);

const productPreviewOptions = computed(() =>
    (props.commercePreview?.options || []).filter(
        (option: Record<string, string>) => option.resource === 'product',
    ),
);

const viewModes = [
    { value: 'desktop', label: 'Desktop view', icon: Monitor },
    { value: 'tablet', label: 'Tablet view', icon: Tablet },
    { value: 'mobile', label: 'Mobile view', icon: Smartphone },
];
</script>

<template>
    <header class="editor-topbar">
        <div class="topbar-identity">
            <button
                type="button"
                class="sidebar-toggle"
                aria-label="Toggle editor sidebar"
                @click="emit('toggle-sidebar')"
            >
                <PanelLeft :size="16" />
            </button>

            <Link :href="dashboardUrl" class="back-link">
                <ArrowLeft :size="14" />
                <span>Back</span>
            </Link>

            <div class="builder-title">
                <span class="builder-kicker">Visual builder</span>
                <span class="builder-page"
                    >{{ tenantName }} / {{ pageTitle }}</span
                >
            </div>
        </div>

        <div class="preview-tools">
            <label v-if="productPreviewOptions.length" class="resource-preview">
                <span>Fixture product</span>
                <select
                    :value="commercePreview?.selected || ''"
                    @change="
                        emit(
                            'update:commerce-preview',
                            ($event.target as HTMLSelectElement).value,
                        )
                    "
                >
                    <option value="">Page binding</option>
                    <option
                        v-for="option in productPreviewOptions"
                        :key="option.source"
                        :value="option.source"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </label>
            <div class="viewport-switcher" aria-label="Preview size">
                <button
                    v-for="mode in viewModes"
                    :key="mode.value"
                    type="button"
                    class="viewport-button"
                    :class="{
                        'viewport-button-active': viewMode === mode.value,
                    }"
                    :aria-pressed="viewMode === mode.value"
                    @click="emit('update:view-mode', mode.value)"
                >
                    <component :is="mode.icon" :size="14" />
                    <span>{{ mode.label }}</span>
                </button>
            </div>
        </div>

        <div class="topbar-actions">
            <div
                class="save-state"
                :class="`save-state-${saveState}`"
                role="status"
                aria-live="polite"
            >
                <span class="save-state-dot"></span>
                <span>{{
                    saveState === 'saving'
                        ? 'Saving'
                        : saveState === 'error'
                          ? 'Save failed'
                          : 'Saved'
                }}</span>
            </div>

            <div class="history-actions">
                <button
                    type="button"
                    class="icon-action"
                    :disabled="!canUndo"
                    title="Undo"
                    aria-label="Undo"
                    @click="emit('undo')"
                >
                    <Undo2 :size="15" />
                </button>
                <button
                    type="button"
                    class="icon-action"
                    :disabled="!canRedo"
                    title="Redo"
                    aria-label="Redo"
                    @click="emit('redo')"
                >
                    <Redo2 :size="15" />
                </button>
            </div>

            <a
                :href="liveUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="live-link"
            >
                <span>Open live link</span>
                <ExternalLink :size="13" />
            </a>
        </div>
    </header>
</template>

<style scoped>
.editor-topbar {
    position: relative;
    z-index: 50;
    display: grid;
    grid-template-columns: minmax(260px, 1fr) auto minmax(260px, 1fr);
    align-items: center;
    min-height: 54px;
    padding: 0 12px 0 10px;
    color: #e5e7eb;
    background: #0c0c0d;
    border-bottom: 1px solid #252527;
}

.topbar-identity,
.topbar-actions,
.history-actions,
.viewport-switcher,
.preview-tools,
.back-link,
.live-link,
.save-state {
    display: flex;
    align-items: center;
}

.topbar-identity {
    min-width: 0;
    gap: 10px;
}

.sidebar-toggle {
    display: none;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    color: #a1a1aa;
    background: #171719;
    border: 1px solid #303033;
    border-radius: 5px;
    cursor: pointer;
}

.back-link {
    height: 30px;
    padding: 0 10px;
    gap: 6px;
    color: #cbd5e1;
    font-size: 11px;
    font-weight: 650;
    letter-spacing: 0.04em;
    text-decoration: none;
    text-transform: uppercase;
    border: 1px solid #6b7280;
    border-radius: 6px;
    transition:
        border-color 150ms ease,
        color 150ms ease,
        background 150ms ease;
}

.back-link:hover,
.back-link:focus-visible {
    color: #ffffff;
    background: #1c1c1f;
    border-color: #d1d5db;
}

.builder-title {
    display: flex;
    min-width: 0;
    flex-direction: column;
    padding-left: 8px;
    border-left: 1px solid #2b2b2e;
}

.builder-kicker {
    color: #7c8eae;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.15em;
    text-transform: uppercase;
}

.builder-page {
    max-width: 240px;
    overflow: hidden;
    color: #9ca3af;
    font-size: 10px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.viewport-switcher {
    padding: 3px;
    gap: 2px;
    background: #171719;
    border: 1px solid #2d2d30;
    border-radius: 6px;
}
.preview-tools {
    justify-self: center;
    gap: 8px;
}
.resource-preview {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #7c8eae;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}
.resource-preview select {
    max-width: 150px;
    height: 30px;
    padding: 0 8px;
    color: #d1d5db;
    background: #171719;
    border: 1px solid #303033;
    border-radius: 5px;
    font-size: 10px;
}

.viewport-button {
    display: inline-flex;
    align-items: center;
    height: 28px;
    padding: 0 12px;
    gap: 7px;
    color: #72809a;
    font-size: 11px;
    font-weight: 600;
    background: transparent;
    border: 0;
    border-radius: 4px;
    cursor: pointer;
    transition:
        color 150ms ease,
        background 150ms ease;
}

.viewport-button:hover {
    color: #d1d5db;
}

.viewport-button-active {
    color: #ffffff;
    background: #343437;
    box-shadow: inset 0 0 0 1px #3f3f43;
}

.topbar-actions {
    justify-self: end;
    gap: 10px;
}

.save-state {
    gap: 6px;
    color: #9ca3af;
    font-size: 10px;
    font-weight: 650;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.save-state-dot {
    width: 7px;
    height: 7px;
    background: #22c55e;
    border-radius: 9999px;
    box-shadow: 0 0 8px rgb(34 197 94 / 45%);
}

.save-state-saving .save-state-dot {
    background: #f59e0b;
    box-shadow: 0 0 8px rgb(245 158 11 / 45%);
    animation: pulse 1.2s ease-in-out infinite;
}

.save-state-error {
    color: #fca5a5;
}

.save-state-error .save-state-dot {
    background: #ef4444;
    box-shadow: 0 0 8px rgb(239 68 68 / 45%);
}

.history-actions {
    gap: 3px;
}

.icon-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    color: #cbd5e1;
    background: #171719;
    border: 1px solid #303033;
    border-radius: 5px;
    cursor: pointer;
}

.icon-action:hover:not(:disabled) {
    color: #ffffff;
    background: #29292c;
}

.icon-action:disabled {
    color: #52525b;
    cursor: not-allowed;
    opacity: 0.7;
}

.live-link {
    height: 30px;
    padding: 0 11px;
    gap: 6px;
    color: #ffffff;
    font-size: 11px;
    font-weight: 650;
    text-decoration: none;
    background: #262629;
    border: 1px solid #303033;
    border-radius: 5px;
    transition:
        background 150ms ease,
        border-color 150ms ease;
}

.live-link:hover,
.live-link:focus-visible {
    background: #343438;
    border-color: #52525b;
}

button:focus-visible,
a:focus-visible {
    outline: 2px solid #93c5fd;
    outline-offset: 2px;
}

@keyframes pulse {
    50% {
        opacity: 0.45;
    }
}

@media (prefers-reduced-motion: reduce) {
    .save-state-saving .save-state-dot {
        animation: none;
    }
}

@media (max-width: 1180px) {
    .editor-topbar {
        grid-template-columns: minmax(220px, 1fr) auto minmax(220px, 1fr);
    }

    .builder-page,
    .save-state {
        display: none;
    }

    .viewport-button span {
        display: none;
    }

    .viewport-button {
        width: 32px;
        padding: 0;
        justify-content: center;
    }
}

@media (max-width: 820px) {
    .editor-topbar {
        grid-template-columns: 1fr auto;
    }

    .sidebar-toggle {
        display: inline-flex;
    }

    .builder-title,
    .history-actions {
        display: none;
    }

    .viewport-switcher {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }

    .resource-preview {
        display: none;
    }

    .live-link span {
        display: none;
    }

    .live-link {
        width: 32px;
        padding: 0;
        justify-content: center;
    }
}

@media (max-width: 520px) {
    .back-link span {
        display: none;
    }
    .back-link {
        width: 32px;
        padding: 0;
        justify-content: center;
    }
}
</style>
