<!-- eslint-disable vue/block-lang -->
<script setup>
import { computed, inject, ref } from 'vue';

const props = defineProps({
  nodeId: { type: String, required: true },
  blockProps: { type: Object, default: () => ({}) },
});

const isEditable = inject('isEditable', false);
const activated = ref(false);

const videoId = computed(() => {
  const url = props.blockProps.url || '';
  const provider = props.blockProps.provider || 'youtube';

  if (!url) {
    return '';
  }

  if (provider === 'youtube') {
    const ytReg = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i;
    const match = url.match(ytReg);

    return match ? match[1] : '';
  }

  if (provider === 'vimeo') {
    const vimeoReg = /(?:vimeo\.com\/|player\.vimeo\.com\/video\/)([0-9]+)/i;
    const match = url.match(vimeoReg);

    return match ? match[1] : '';
  }

  if (provider === 'loom') {
    const loomReg = /(?:loom\.com\/share\/|loom\.com\/embed\/)([a-zA-Z0-9]+)/i;
    const match = url.match(loomReg);

    return match ? match[1] : '';
  }

  return '';
});

const embedUrl = computed(() => {
  const url = props.blockProps.url || '';
  const provider = props.blockProps.provider || 'youtube';

  if (!url) {
    return '';
  }

  if (provider === 'raw') {
    return url;
  }

  const autoplay = activated.value ? '?autoplay=1' : '';

  if (provider === 'youtube' && videoId.value) {
    return `https://www.youtube.com/embed/${videoId.value}${autoplay}`;
  }

  if (provider === 'vimeo' && videoId.value) {
    return `https://player.vimeo.com/video/${videoId.value}${autoplay}`;
  }

  if (provider === 'loom' && videoId.value) {
    return `https://www.loom.com/embed/${videoId.value}`;
  }

  return url;
});

// A poster is shown before the iframe loads. YouTube thumbnails are available
// statically; other providers can use a custom poster image if supplied.
const posterSrc = computed(() => {
  if (props.blockProps.posterUrl) {
    return props.blockProps.posterUrl;
  }

  const provider = props.blockProps.provider || 'youtube';

  if (provider === 'youtube' && videoId.value) {
    return `https://img.youtube.com/vi/${videoId.value}/hqdefault.jpg`;
  }

  return '';
});

const hasVideo = computed(() => Boolean(props.blockProps.url));

// On the public site, clicking the poster swaps in the iframe (lite embed).
// In the editor we never load the iframe so the canvas stays lightweight.
const activate = () => {
  if (!isEditable) {
    activated.value = true;
  }
};
</script>

<template>
  <div class="video-embed-block-wrapper">
    <!-- Loaded player (public, after click, or raw provider) -->
    <div
      v-if="embedUrl && (activated || blockProps.provider === 'raw')"
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

    <!-- Thumbnail poster with play button -->
    <button
      v-else-if="hasVideo"
      type="button"
      class="video-poster"
      :class="{ 'video-poster--image': posterSrc }"
      :style="{
        aspectRatio: blockProps.aspectRatio || '16/9',
        backgroundImage: posterSrc ? `url(${posterSrc})` : undefined,
      }"
      :aria-label="isEditable ? 'Video preview' : 'Play video'"
      @click="activate"
    >
      <span class="play-badge" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="currentColor" class="play-icon">
          <path d="M8 5v14l11-7z" />
        </svg>
      </span>
    </button>

    <!-- Empty state (editor only) -->
    <div
      v-else-if="isEditable"
      class="video-placeholder"
      :style="{ aspectRatio: blockProps.aspectRatio || '16/9' }"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="placeholder-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
      </svg>
      <span class="placeholder-text">Enter a video URL in the inspector panel</span>
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

.video-poster {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 0;
  border: 0;
  border-radius: var(--theme-border-radius, 8px);
  overflow: hidden;
  cursor: pointer;
  background-color: color-mix(in srgb, var(--theme-text, #0f172a) 8%, transparent);
  background-position: center;
  background-size: cover;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.video-poster:not(.video-poster--image) {
  background-image:
    radial-gradient(
      120% 90% at 20% 0%,
      color-mix(in srgb, var(--theme-primary, #6366f1) 22%, transparent) 0%,
      transparent 60%
    ),
    radial-gradient(
      110% 100% at 85% 100%,
      color-mix(in srgb, var(--theme-secondary, #a78bfa) 26%, transparent) 0%,
      transparent 55%
    ),
    color-mix(in srgb, var(--theme-text, #0f172a) 6%, transparent);
}

.video-poster::after {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.12);
  transition: background 0.2s ease;
}

.video-poster:hover::after {
  background: rgba(0, 0, 0, 0.22);
}

.play-badge {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 4.5rem;
  height: 4.5rem;
  border-radius: 9999px;
  color: #fff;
  background: color-mix(in srgb, var(--theme-primary, #6366f1) 92%, black);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  transition: transform 0.2s ease;
}

.video-poster:hover .play-badge {
  transform: scale(1.08);
}

.play-icon {
  width: 1.9rem;
  height: 1.9rem;
  margin-left: 0.2rem;
}

.video-placeholder {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  background: color-mix(in srgb, var(--theme-text, #0f172a) 5%, transparent);
  border: 2px dashed color-mix(in srgb, var(--theme-text, #0f172a) 15%, transparent);
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
