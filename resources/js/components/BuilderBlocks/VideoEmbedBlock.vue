<script setup>
import { computed, inject } from 'vue';

const props = defineProps({
  nodeId: { type: String, required: true },
  blockProps: { type: Object, default: () => ({}) },
});

const isEditable = inject('isEditable', false);

const embedUrl = computed(() => {
  const url = props.blockProps.url || '';
  const provider = props.blockProps.provider || 'youtube';

  if (!url) return '';

  if (provider === 'raw') {
    return url;
  }

  if (provider === 'youtube') {
    // Match youtube video ID
    const ytReg = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i;
    const match = url.match(ytReg);
    return match ? `https://www.youtube.com/embed/${match[1]}` : url;
  }

  if (provider === 'vimeo') {
    // Match vimeo video ID
    const vimeoReg = /(?:vimeo\.com\/|player\.vimeo\.com\/video\/)([0-9]+)/i;
    const match = url.match(vimeoReg);
    return match ? `https://player.vimeo.com/video/${match[1]}` : url;
  }

  if (provider === 'loom') {
    // Match loom video ID
    const loomReg = /(?:loom\.com\/share\/|loom\.com\/embed\/)([a-zA-Z0-9]+)/i;
    const match = url.match(loomReg);
    return match ? `https://www.loom.com/embed/${match[1]}` : url;
  }

  return url;
});
</script>

<template>
  <div class="video-embed-block-wrapper">
    <div
      v-if="embedUrl"
      class="video-container"
      :style="{ aspectRatio: blockProps.aspectRatio || '16/9' }"
    >
      <iframe
        :src="embedUrl"
        frameborder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
        allowfullscreen
        class="video-iframe"
      ></iframe>
    </div>

    <div
      v-else-if="isEditable"
      class="video-placeholder"
      :style="{ aspectRatio: blockProps.aspectRatio || '16/9' }"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="placeholder-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
      </svg>
      <span class="placeholder-text">Enter video URL in the inspector panel</span>
    </div>
  </div>
</template>

<style scoped>
.video-embed-block-wrapper {
  width: 100%;
}

.video-container {
  position: relative;
  width: 100%;
  border-radius: var(--theme-border-radius, 8px);
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  background: #000;
}

.video-iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.video-placeholder {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  background: rgba(255, 255, 255, 0.04);
  border: 2px dashed rgba(255, 255, 255, 0.15);
  border-radius: var(--theme-border-radius, 8px);
}

.placeholder-icon {
  width: 3rem;
  height: 3rem;
  color: var(--theme-primary, #6366f1);
  opacity: 0.5;
}

.placeholder-text {
  font-size: 0.8125rem;
  font-family: var(--theme-font-body, sans-serif);
  color: var(--theme-text, #94a3b8);
  opacity: 0.6;
}
</style>
