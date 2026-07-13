<script setup>
import { inject, onBeforeUnmount, watch } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';

const props = defineProps({
  nodeId: { type: String, required: true },
  blockProps: { type: Object, default: () => ({}) },
});

const isEditable = inject('isEditable', false);

// Setup tiptap editor if editable
const editor = isEditable
  ? useEditor({
      content: props.blockProps.html || '<p>Start typing...</p>',
      extensions: [StarterKit],
      onUpdate: ({ editor }) => {
        props.blockProps.html = editor.getHTML();
      },
    })
  : null;

// Watch outer prop changes in editor to sync content (e.g. undo/redo)
if (isEditable && editor) {
  watch(
    () => props.blockProps.html,
    (newVal) => {
      if (editor.value && editor.value.getHTML() !== newVal) {
        editor.value.commands.setContent(newVal || '<p>Start typing...</p>');
      }
    }
  );
}

onBeforeUnmount(() => {
  if (editor?.value) {
    editor.value.destroy();
  }
});
</script>

<template>
  <div class="rich-text-block-wrapper" :style="{ color: 'var(--theme-text)' }">
    <div v-if="isEditable && editor" class="tiptap-editor-container">
      <!-- Toolbar -->
      <div class="tiptap-toolbar">
        <button
          type="button"
          @click="editor.chain().focus().toggleBold().run()"
          :class="{ 'btn-active': editor.isActive('bold') }"
          title="Bold"
        >
          B
        </button>
        <button
          type="button"
          @click="editor.chain().focus().toggleItalic().run()"
          :class="{ 'btn-active': editor.isActive('italic') }"
          title="Italic"
        >
          I
        </button>
        <button
          type="button"
          @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
          :class="{ 'btn-active': editor.isActive('heading', { level: 1 }) }"
          title="Heading 1"
        >
          H1
        </button>
        <button
          type="button"
          @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
          :class="{ 'btn-active': editor.isActive('heading', { level: 2 }) }"
          title="Heading 2"
        >
          H2
        </button>
        <button
          type="button"
          @click="editor.chain().focus().toggleBulletList().run()"
          :class="{ 'btn-active': editor.isActive('bulletList') }"
          title="Bullet List"
        >
          • List
        </button>
        <button
          type="button"
          @click="editor.chain().focus().toggleOrderedList().run()"
          :class="{ 'btn-active': editor.isActive('orderedList') }"
          title="Ordered List"
        >
          1. List
        </button>
      </div>

      <!-- Editor Content -->
      <EditorContent :editor="editor" class="tiptap-content-area" />
    </div>

    <!-- Live Storefront / Preview Mode -->
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
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.02);
  border-radius: 6px;
  overflow: hidden;
}

.tiptap-toolbar {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  background: rgba(255, 255, 255, 0.05);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  padding: 6px;
}

.tiptap-toolbar button {
  background: transparent;
  border: 0;
  color: #94a3b8;
  cursor: pointer;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 4px 8px;
  border-radius: 4px;
  transition: all 0.15s;
}

.tiptap-toolbar button:hover {
  background: rgba(255, 255, 255, 0.08);
  color: #f8fafc;
}

.tiptap-toolbar button.btn-active {
  background: var(--theme-primary, #6366f1);
  color: #ffffff;
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

/* Public prose styles */
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
