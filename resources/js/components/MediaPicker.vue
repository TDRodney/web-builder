<script setup>
import { ref, onMounted } from 'vue';
import { useHttp } from '@inertiajs/vue3';

const emit = defineEmits(['select', 'close']);

const activeTab = ref('library');
const mediaItems = ref([]);
const isLoading = ref(false);
const isUploading = ref(false);
const uploadError = ref('');
const selectedId = ref(null);
const confirmDeleteId = ref(null);
const deleteHttp = useHttp({});

/**
 * Read the XSRF-TOKEN cookie that Laravel sets automatically.
 * Laravel expects this as the X-XSRF-TOKEN request header.
 */
const getCsrfToken = () => {
  const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
  return match ? decodeURIComponent(match[1]) : '';
};

const fetchMedia = async () => {
  isLoading.value = true;
  try {
    const res = await fetch('/editor/media', {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-XSRF-TOKEN': getCsrfToken(),
      },
      credentials: 'same-origin',
    });
    if (res.ok) {
      mediaItems.value = await res.json();
    }
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchMedia);

const selectItem = (item) => {
  selectedId.value = item.id;
  emit('select', item);
};

const confirmDelete = (id) => {
  confirmDeleteId.value = id;
};

const deleteItem = async (item) => {
  try {
    await deleteHttp.delete(`/editor/media/${item.id}`);
    mediaItems.value = mediaItems.value.filter(m => m.id !== item.id);
    if (selectedId.value === item.id) {
      selectedId.value = null;
    }
  } finally {
    confirmDeleteId.value = null;
  }
};

// Upload handling
const fileInput = ref(null);
const dropZoneActive = ref(false);

const handleDrop = (event) => {
  dropZoneActive.value = false;
  const files = Array.from(event.dataTransfer?.files || []);
  uploadFiles(files);
};

const handleFileInput = (event) => {
  const files = Array.from(event.target.files || []);
  uploadFiles(files);
};

const uploadFiles = async (files) => {
  if (!files.length) return;
  isUploading.value = true;
  uploadError.value = '';

  for (const file of files) {
    const formData = new FormData();
    formData.append('file', file);

    try {
      const res = await fetch('/editor/media', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-XSRF-TOKEN': getCsrfToken(),
        },
        credentials: 'same-origin',
        body: formData,
      });

      if (res.ok) {
        const newMedia = await res.json();
        mediaItems.value.unshift(newMedia);
      } else {
        const body = await res.json().catch(() => ({}));
        uploadError.value = body.message || `Upload failed (${res.status})`;
      }
    } catch (e) {
      uploadError.value = 'Network error during upload.';
    }
  }

  isUploading.value = false;
  activeTab.value = 'library';
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};
</script>


<template>
  <!-- Backdrop -->
  <div
    class="media-picker-backdrop"
    @click.self="emit('close')"
  >
    <!-- Modal -->
    <div class="media-picker-modal">
      <!-- Header -->
      <div class="modal-header">
        <h3 class="modal-title">Media Library</h3>
        <button class="modal-close-btn" @click="emit('close')">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Tabs -->
      <div class="modal-tabs">
        <button
          :class="['tab-btn', activeTab === 'library' ? 'tab-active' : '']"
          @click="activeTab = 'library'"
        >
          Library
          <span v-if="mediaItems.length" class="tab-count">{{ mediaItems.length }}</span>
        </button>
        <button
          :class="['tab-btn', activeTab === 'upload' ? 'tab-active' : '']"
          @click="activeTab = 'upload'"
        >
          Upload
        </button>
      </div>

      <!-- Library Tab -->
      <div v-if="activeTab === 'library'" class="tab-content">
        <div v-if="isLoading" class="status-message">Loading...</div>
        <div v-else-if="!mediaItems.length" class="status-message">
          No images yet. Switch to <button class="link-btn" @click="activeTab = 'upload'">Upload</button> to add some.
        </div>
        <div v-else class="media-grid">
          <div
            v-for="item in mediaItems"
            :key="item.id"
            :class="['media-card', selectedId === item.id ? 'media-card-selected' : '']"
            @click="selectItem(item)"
          >
            <img
              :src="item.thumb_url || item.url"
              :alt="item.filename"
              class="media-thumb"
              loading="lazy"
            />
            <div class="media-card-overlay">
              <span class="media-filename">{{ item.filename }}</span>
              <!-- Delete button — two-click confirm -->
              <button
                v-if="confirmDeleteId !== item.id"
                class="delete-btn"
                @click.stop="confirmDelete(item.id)"
                title="Delete"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
              <button
                v-else
                class="confirm-delete-btn"
                @click.stop="deleteItem(item)"
                title="Confirm delete"
              >
                Confirm delete
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Upload Tab -->
      <div v-else class="tab-content">
        <div
          class="drop-zone"
          :class="{ 'drop-zone-active': dropZoneActive }"
          @dragover.prevent="dropZoneActive = true"
          @dragleave="dropZoneActive = false"
          @drop.prevent="handleDrop"
          @click="fileInput?.click()"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="upload-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
          </svg>
          <p class="drop-label">Drag &amp; drop images, or click to browse</p>
          <p class="drop-subtext">JPEG, PNG, GIF, WebP, SVG — max 5 MB each</p>
          <input
            ref="fileInput"
            type="file"
            accept="image/*"
            multiple
            class="hidden-input"
            @change="handleFileInput"
          />
        </div>
        <div v-if="isUploading" class="upload-progress">Uploading...</div>
        <p v-if="uploadError" class="upload-error">{{ uploadError }}</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.media-picker-backdrop {
  position: fixed;
  inset: 0;
  z-index: 100;
  background: rgba(2, 6, 23, 0.75);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
}

.media-picker-modal {
  background: #0f172a;
  border: 1px solid #1e293b;
  border-radius: 1rem;
  width: 100%;
  max-width: 720px;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
  overflow: hidden;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #1e293b;
  flex-shrink: 0;
}

.modal-title {
  font-size: 1rem;
  font-weight: 700;
  color: #f8fafc;
  margin: 0;
}

.modal-close-btn {
  background: transparent;
  border: 0;
  color: #64748b;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 0.375rem;
  transition: color 0.15s;
}
.modal-close-btn:hover {
  color: #f8fafc;
}

.icon {
  width: 1.25rem;
  height: 1.25rem;
  display: block;
}

.modal-tabs {
  display: flex;
  border-bottom: 1px solid #1e293b;
  padding: 0 1.5rem;
  flex-shrink: 0;
}

.tab-btn {
  background: transparent;
  border: 0;
  border-bottom: 2px solid transparent;
  color: #64748b;
  font-size: 0.8125rem;
  font-weight: 600;
  padding: 0.75rem 0;
  margin-right: 1.5rem;
  cursor: pointer;
  transition: color 0.15s, border-color 0.15s;
  display: flex;
  align-items: center;
  gap: 0.375rem;
}
.tab-btn:hover {
  color: #94a3b8;
}
.tab-active {
  color: var(--theme-primary, #6366f1);
  border-bottom-color: var(--theme-primary, #6366f1);
}

.tab-count {
  background: #1e293b;
  color: #94a3b8;
  font-size: 0.6875rem;
  padding: 0.125rem 0.4rem;
  border-radius: 9999px;
}

.tab-content {
  flex: 1;
  overflow-y: auto;
  padding: 1.25rem 1.5rem;
}

.status-message {
  text-align: center;
  color: #64748b;
  font-size: 0.875rem;
  padding: 3rem 0;
}

.link-btn {
  background: transparent;
  border: 0;
  color: var(--theme-primary, #6366f1);
  cursor: pointer;
  font-size: inherit;
  text-decoration: underline;
  padding: 0;
}

.media-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0.75rem;
}

.media-card {
  position: relative;
  aspect-ratio: 1;
  border-radius: 0.5rem;
  overflow: hidden;
  cursor: pointer;
  border: 2px solid transparent;
  transition: border-color 0.15s, transform 0.15s;
}
.media-card:hover {
  transform: scale(1.02);
}
.media-card-selected {
  border-color: var(--theme-primary, #6366f1);
  box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.3);
}

.media-thumb {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.media-card-overlay {
  position: absolute;
  inset: 0;
  background: rgba(2, 6, 23, 0);
  transition: background 0.15s;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 0.5rem;
}
.media-card:hover .media-card-overlay {
  background: rgba(2, 6, 23, 0.65);
}

.media-filename {
  font-size: 0.625rem;
  color: #f8fafc;
  font-weight: 500;
  word-break: break-all;
  opacity: 0;
  transition: opacity 0.15s;
}
.media-card:hover .media-filename {
  opacity: 1;
}

.delete-btn,
.confirm-delete-btn {
  background: rgba(239, 68, 68, 0.85);
  border: 0;
  border-radius: 0.25rem;
  color: #fff;
  cursor: pointer;
  font-size: 0.625rem;
  font-weight: 600;
  padding: 0.2rem 0.4rem;
  opacity: 0;
  transition: opacity 0.15s;
  align-self: flex-end;
  margin-top: 0.25rem;
}
.media-card:hover .delete-btn,
.media-card:hover .confirm-delete-btn {
  opacity: 1;
}

.icon-sm {
  width: 0.875rem;
  height: 0.875rem;
}

/* Upload Tab */
.drop-zone {
  border: 2px dashed #334155;
  border-radius: 0.75rem;
  padding: 3rem 2rem;
  text-align: center;
  cursor: pointer;
  transition: border-color 0.15s, background 0.15s;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
}
.drop-zone:hover,
.drop-zone-active {
  border-color: var(--theme-primary, #6366f1);
  background: rgba(99, 102, 241, 0.05);
}

.upload-icon {
  width: 3rem;
  height: 3rem;
  color: var(--theme-primary, #6366f1);
  opacity: 0.7;
}

.drop-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #cbd5e1;
  margin: 0;
}

.drop-subtext {
  font-size: 0.75rem;
  color: #64748b;
  margin: 0;
}

.hidden-input {
  display: none;
}

.upload-progress {
  margin-top: 1rem;
  text-align: center;
  font-size: 0.875rem;
  color: var(--theme-primary, #6366f1);
  font-weight: 600;
}

.upload-error {
  margin-top: 0.75rem;
  font-size: 0.8125rem;
  color: #f87171;
  text-align: center;
}
</style>
