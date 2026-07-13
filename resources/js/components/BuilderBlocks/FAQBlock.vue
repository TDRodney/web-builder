<script setup>
import { ref, inject } from 'vue';

const props = defineProps({
  nodeId: { type: String, required: true },
  blockProps: { type: Object, default: () => ({}) },
});

const isEditable = inject('isEditable', false);

// Keep track of which accordion items are open
const openItems = ref({});

const toggleItem = (index) => {
  openItems.value[index] = !openItems.value[index];
};
</script>

<template>
  <div class="faq-block-wrapper">
    <div v-if="blockProps.items && blockProps.items.length" class="faq-list">
      <div
        v-for="(item, index) in blockProps.items"
        :key="index"
        class="faq-item"
        :class="{ 'faq-item-open': openItems[index] }"
      >
        <button
          type="button"
          class="faq-question-btn"
          @click="toggleItem(index)"
        >
          <span class="faq-question-text">{{ item.question || 'Empty Question' }}</span>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="faq-chevron"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            :style="{ transform: openItems[index] ? 'rotate(180deg)' : 'rotate(0deg)' }"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <div
          class="faq-answer-container"
          :style="{ maxHeight: openItems[index] ? '500px' : '0px' }"
        >
          <div class="faq-answer-text">
            {{ item.answer || 'Empty Answer text.' }}
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="isEditable" class="faq-placeholder">
      Add FAQ accordion items in the sidebar inspector.
    </div>
  </div>
</template>

<style scoped>
.faq-block-wrapper {
  width: 100%;
}

.faq-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.faq-item {
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  padding-bottom: 0.5rem;
  transition: all 0.2s ease;
}

.faq-question-btn {
  width: 100%;
  background: transparent;
  border: 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.875rem 0;
  cursor: pointer;
  text-align: left;
  color: var(--theme-text, #0f172a);
}

.faq-question-text {
  font-family: var(--theme-font-heading, sans-serif);
  font-size: 1.05rem;
  font-weight: 600;
  transition: color 0.15s ease;
}

.faq-question-btn:hover .faq-question-text {
  color: var(--theme-primary, #6366f1);
}

.faq-chevron {
  width: 1.25rem;
  height: 1.25rem;
  color: var(--theme-primary, #6366f1);
  transition: transform 0.25s ease;
}

.faq-answer-container {
  overflow: hidden;
  transition: max-height 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.faq-answer-text {
  font-family: var(--theme-font-body, sans-serif);
  font-size: 0.9375rem;
  line-height: 1.6;
  color: var(--theme-text, #334155);
  opacity: 0.85;
  padding: 0.25rem 0 1rem 0;
}

.faq-placeholder {
  font-family: var(--theme-font-body, sans-serif);
  text-align: center;
  padding: 2rem;
  border: 2px dashed rgba(255, 255, 255, 0.15);
  border-radius: 6px;
  color: var(--theme-text);
  opacity: 0.6;
}
</style>
