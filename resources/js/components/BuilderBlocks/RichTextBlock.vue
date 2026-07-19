<!-- eslint-disable vue/block-lang -->
<script setup>
/* eslint-disable vue/no-mutating-props */
import { Color, TextStyle } from '@tiptap/extension-text-style';
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { computed, inject, onBeforeUnmount, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    nodeId: { type: String, required: true },
    blockProps: { type: Object, default: () => ({}) },
});

const isEditable = inject('isEditable', false);
const colorMenuOpen = ref(false);
const colorMenuRoot = ref(null);

const themeColorOptions = [
    { label: 'Primary', value: 'var(--theme-primary)' },
    { label: 'Secondary', value: 'var(--theme-secondary)' },
    { label: 'Text', value: 'var(--theme-text)' },
];

const presetColors = [
    '#09090b',
    '#18181b',
    '#71717a',
    '#ffffff',
    '#fbbf24',
    '#ef4444',
    '#22c55e',
    '#3b82f6',
];

const editor = isEditable
    ? useEditor({
          content: props.blockProps.html || '<p>Start typing...</p>',
          extensions: [
              StarterKit,
              TextStyle,
              Color,
          ],
          onUpdate: ({ editor: instance }) => {
              props.blockProps.html = instance.getHTML();
          },
      })
    : null;

if (isEditable && editor) {
    watch(
        () => props.blockProps.html,
        (newVal) => {
            if (editor.value && editor.value.getHTML() !== newVal) {
                editor.value.commands.setContent(
                    newVal || '<p>Start typing...</p>',
                );
            }
        },
    );
}

const activeColor = computed(() => {
    if (!editor?.value) {
        return '';
    }

    return editor.value.getAttributes('textStyle').color || '';
});

const customHex = computed(() => {
    const value = activeColor.value;

    return /^#[0-9a-f]{6}$/i.test(value) ? value : '#18181b';
});

const setTextColor = (color) => {
    if (!editor?.value) {
        return;
    }

    editor.value.chain().focus().setColor(color).run();
    colorMenuOpen.value = false;
};

const clearTextColor = () => {
    if (!editor?.value) {
        return;
    }

    editor.value.chain().focus().unsetColor().run();
    colorMenuOpen.value = false;
};

const updateNativeColor = (event) => {
    setTextColor(event.target.value);
};

const onDocumentPointerDown = (event) => {
    if (!colorMenuOpen.value || !colorMenuRoot.value) {
        return;
    }

    if (!colorMenuRoot.value.contains(event.target)) {
        colorMenuOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('pointerdown', onDocumentPointerDown);
});

onUnmounted(() => {
    document.removeEventListener('pointerdown', onDocumentPointerDown);
});

onBeforeUnmount(() => {
    if (editor?.value) {
        editor.value.destroy();
    }
});
</script>

<template>
    <div class="rich-text-block-wrapper" :style="{ color: 'var(--theme-text)' }">
        <div v-if="isEditable && editor" class="tiptap-editor-container">
            <div class="tiptap-toolbar" role="toolbar" aria-label="Text formatting">
                <button
                    type="button"
                    :class="{ 'btn-active': editor.isActive('bold') }"
                    title="Bold"
                    @click="editor.chain().focus().toggleBold().run()"
                >
                    B
                </button>
                <button
                    type="button"
                    :class="{ 'btn-active': editor.isActive('italic') }"
                    title="Italic"
                    @click="editor.chain().focus().toggleItalic().run()"
                >
                    I
                </button>
                <button
                    type="button"
                    :class="{
                        'btn-active': editor.isActive('heading', { level: 1 }),
                    }"
                    title="Heading 1"
                    @click="
                        editor.chain().focus().toggleHeading({ level: 1 }).run()
                    "
                >
                    H1
                </button>
                <button
                    type="button"
                    :class="{
                        'btn-active': editor.isActive('heading', { level: 2 }),
                    }"
                    title="Heading 2"
                    @click="
                        editor.chain().focus().toggleHeading({ level: 2 }).run()
                    "
                >
                    H2
                </button>
                <button
                    type="button"
                    :class="{ 'btn-active': editor.isActive('bulletList') }"
                    title="Bullet List"
                    @click="editor.chain().focus().toggleBulletList().run()"
                >
                    • List
                </button>
                <button
                    type="button"
                    :class="{ 'btn-active': editor.isActive('orderedList') }"
                    title="Ordered List"
                    @click="editor.chain().focus().toggleOrderedList().run()"
                >
                    1. List
                </button>

                <div ref="colorMenuRoot" class="color-menu">
                    <button
                        type="button"
                        class="color-trigger"
                        :class="{ 'btn-active': Boolean(activeColor) }"
                        title="Text color"
                        aria-haspopup="true"
                        :aria-expanded="colorMenuOpen"
                        @click="colorMenuOpen = !colorMenuOpen"
                    >
                        <span class="color-trigger-label">A</span>
                        <span
                            class="color-swatch"
                            :style="{
                                background: activeColor || 'var(--theme-text)',
                            }"
                        />
                    </button>

                    <div
                        v-if="colorMenuOpen"
                        class="color-popover"
                        role="menu"
                        aria-label="Text color"
                    >
                        <div class="color-section">
                            <span class="color-section-label">Theme</span>
                            <div class="color-token-row">
                                <button
                                    v-for="option in themeColorOptions"
                                    :key="option.value"
                                    type="button"
                                    class="color-token"
                                    :class="{
                                        'color-token-active':
                                            activeColor === option.value,
                                    }"
                                    @click="setTextColor(option.value)"
                                >
                                    {{ option.label }}
                                </button>
                            </div>
                        </div>

                        <div class="color-section">
                            <span class="color-section-label">Presets</span>
                            <div class="color-swatch-grid">
                                <button
                                    v-for="color in presetColors"
                                    :key="color"
                                    type="button"
                                    class="color-swatch-btn"
                                    :class="{
                                        'color-swatch-active':
                                            activeColor.toLowerCase() ===
                                            color,
                                    }"
                                    :style="{ backgroundColor: color }"
                                    :title="color"
                                    :aria-label="`Use ${color}`"
                                    @click="setTextColor(color)"
                                />
                            </div>
                        </div>

                        <div class="color-section color-custom-row">
                            <input
                                type="color"
                                :value="customHex"
                                aria-label="Custom text color"
                                @input="updateNativeColor"
                            />
                            <button
                                type="button"
                                class="color-clear"
                                @click="clearTextColor"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <EditorContent :editor="editor" class="tiptap-content-area" />
        </div>

        <div
            v-else
            class="prose-content"
            v-html="blockProps.html || '<p>Start typing...</p>'"
        ></div>
    </div>
</template>

<style scoped>
.rich-text-block-wrapper {
    font-family: var(--theme-font-body, sans-serif);
    line-height: 1.6;
}

.tiptap-editor-container {
    border: 1px solid color-mix(in srgb, var(--theme-text) 12%, transparent);
    background: color-mix(in srgb, var(--theme-text) 2%, transparent);
    border-radius: var(--theme-border-radius, 6px);
    overflow: visible;
}

.tiptap-toolbar {
    position: relative;
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    align-items: center;
    background: color-mix(in srgb, var(--theme-text) 4%, transparent);
    border-bottom: 1px solid
        color-mix(in srgb, var(--theme-text) 12%, transparent);
    padding: 6px;
}

.tiptap-toolbar button {
    background: transparent;
    border: 0;
    color: color-mix(in srgb, var(--theme-text) 70%, transparent);
    cursor: pointer;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 4px;
    transition: all 0.15s;
}

.tiptap-toolbar button:hover {
    background: color-mix(in srgb, var(--theme-text) 8%, transparent);
    color: var(--theme-text);
}

.tiptap-toolbar button.btn-active {
    background: var(--theme-primary, #6366f1);
    color: #ffffff;
}

.color-menu {
    position: relative;
    margin-left: 2px;
}

.color-trigger {
    display: inline-flex !important;
    align-items: center;
    gap: 4px;
    min-height: 28px;
}

.color-trigger-label {
    font-weight: 800;
    text-decoration: underline;
    text-decoration-thickness: 2px;
    text-underline-offset: 2px;
}

.color-swatch {
    width: 12px;
    height: 12px;
    border-radius: 9999px;
    border: 1px solid color-mix(in srgb, var(--theme-text) 20%, transparent);
}

.color-popover {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    z-index: 20;
    width: 220px;
    padding: 10px;
    background: #ffffff;
    border: 1px solid #e4e4e7;
    border-radius: 8px;
    box-shadow: 0 12px 28px rgba(24, 24, 27, 0.16);
}

.color-section + .color-section {
    margin-top: 10px;
}

.color-section-label {
    display: block;
    margin-bottom: 6px;
    color: #71717a;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.color-token-row {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 4px;
}

.color-token {
    min-height: 28px !important;
    padding: 0 4px !important;
    color: #3f3f46 !important;
    background: #f4f4f5 !important;
    border: 1px solid #e4e4e7 !important;
    border-radius: 4px !important;
    font-size: 9px !important;
}

.color-token:hover {
    border-color: #a1a1aa !important;
    color: #18181b !important;
}

.color-token-active {
    color: #fff !important;
    background: #18181b !important;
    border-color: #18181b !important;
}

.color-swatch-grid {
    display: grid;
    grid-template-columns: repeat(8, minmax(0, 1fr));
    gap: 4px;
}

.color-swatch-btn {
    aspect-ratio: 1;
    width: 100%;
    min-width: 0;
    padding: 0 !important;
    border: 1px solid rgba(0, 0, 0, 0.12) !important;
    border-radius: 4px !important;
}

.color-swatch-active {
    outline: 2px solid #18181b;
    outline-offset: 1px;
}

.color-custom-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

.color-custom-row input[type='color'] {
    width: 36px;
    height: 32px;
    padding: 2px;
    border: 1px solid #e4e4e7;
    border-radius: 4px;
    background: transparent;
    cursor: pointer;
}

.color-clear {
    flex: 1;
    min-height: 32px !important;
    color: #3f3f46 !important;
    background: #f4f4f5 !important;
    border: 1px solid #e4e4e7 !important;
}

.tiptap-content-area :deep(.tiptap) {
    outline: none;
    min-height: 100px;
    padding: 12px;
    color: var(--theme-text, #0f172a);
}

.tiptap-content-area :deep(.tiptap) p {
    margin: 0 0 1rem 0;
}

.tiptap-content-area :deep(.tiptap) h1,
.tiptap-content-area :deep(.tiptap) h2 {
    font-family: var(--theme-font-heading, sans-serif);
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
    font-weight: 700;
}

.tiptap-content-area :deep(.tiptap) h1 {
    font-size: 1.75rem;
}

.tiptap-content-area :deep(.tiptap) h2 {
    font-size: 1.35rem;
}

.tiptap-content-area :deep(.tiptap) ul {
    list-style-type: disc;
    margin-left: 1.5rem;
    margin-bottom: 1rem;
}

.tiptap-content-area :deep(.tiptap) ol {
    list-style-type: decimal;
    margin-left: 1.5rem;
    margin-bottom: 1rem;
}

.prose-content :deep(p) {
    margin: 0 0 1rem 0;
}

.prose-content :deep(h1),
.prose-content :deep(h2) {
    font-family: var(--theme-font-heading, sans-serif);
    margin-top: 1.75rem;
    margin-bottom: 0.75rem;
    font-weight: 700;
    color: var(--theme-text);
}

.prose-content :deep(h1) {
    font-size: 2rem;
}

.prose-content :deep(h2) {
    font-size: 1.5rem;
}

.prose-content :deep(ul) {
    list-style-type: disc;
    margin-left: 1.5rem;
    margin-bottom: 1rem;
}

.prose-content :deep(ol) {
    list-style-type: decimal;
    margin-left: 1.5rem;
    margin-bottom: 1rem;
}
</style>
