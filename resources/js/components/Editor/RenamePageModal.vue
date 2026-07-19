<script setup>
import { useHttp, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps({
  show: Boolean,
  page: Object,
  currentPageSlug: String
});

const emit = defineEmits(['close']);

const renameForm = useHttp({
  title: '',
  slug: ''
});

const pageActionError = ref('');

watch(() => props.page, (newPage) => {
  if (newPage) {
    renameForm.title = newPage.title;
    renameForm.slug = newPage.slug;
    renameForm.clearErrors();
  }
}, { immediate: true });

const extractHttpError = (error) => {
  if (error?.response?.data?.message) {
    return error.response.data.message;
  }

  if (error?.message) {
    return error.message;
  }

  return 'An unknown error occurred';
};

const submitRenamePage = async () => {
  if (!props.page) {
return;
}

  pageActionError.value = '';
  const loadingToast = toast.loading('Saving settings...');

  try {
    const res = await renameForm.patch(`/editor/pages/${props.page.id}`);

    if (res && res.status === 'success') {
      toast.success('Page settings updated', { id: loadingToast });
      emit('close');

      if (props.page.slug === props.currentPageSlug) {
        router.visit(`/editor?page=${res.page.slug}`);
      } else {
        router.reload({ only: ['pages'] });
      }
    }
  } catch (err) {
    const message = extractHttpError(err);

    if (message !== null) {
      pageActionError.value = message;
      toast.error(`Failed to update page: ${message}`, { id: loadingToast });
    } else {
      toast.dismiss(loadingToast);
    }
  }
};
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl space-y-4">
      <div class="flex items-center justify-between border-b border-slate-800 pb-3">
        <h3 class="text-base font-bold text-white">Rename Page Settings</h3>
        <button @click="emit('close')" class="text-slate-400 hover:text-white bg-transparent border-0 cursor-pointer p-1">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="submitRenamePage" class="space-y-4">
        <div>
          <label class="text-xs font-semibold text-slate-400 block mb-1">Page Title</label>
          <input 
            type="text" 
            v-model="renameForm.title" 
            required 
            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500 text-slate-100"
          />
          <p v-if="renameForm.errors.title" class="text-rose-500 text-xs mt-1">{{ renameForm.errors.title }}</p>
        </div>

        <div>
          <label class="text-xs font-semibold text-slate-400 block mb-1">URL Slug</label>
          <input 
            type="text" 
            v-model="renameForm.slug" 
            required 
            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500 font-mono text-slate-100"
          />
          <p v-if="renameForm.errors.slug" class="text-rose-500 text-xs mt-1">{{ renameForm.errors.slug }}</p>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
          <button 
            type="button" 
            @click="emit('close')" 
            class="bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold py-2 px-4 rounded-lg transition-all cursor-pointer border border-slate-700"
          >
            Cancel
          </button>
          <button 
            type="submit" 
            :disabled="renameForm.processing"
            class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-xs font-semibold py-2 px-4 rounded-lg transition-all cursor-pointer border-0"
          >
            {{ renameForm.processing ? 'Saving...' : 'Save Settings' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
