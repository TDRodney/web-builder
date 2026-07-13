<script setup>
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import draggable from 'vuedraggable';

const props = defineProps({
  navigationConfig: {
    type: Object,
    required: true
  },
  pages: {
    type: Array,
    required: true
  },
  tenant: {
    type: Object,
    required: true
  }
});

const showNavigationSettings = ref(false);
const isSavingNav = ref(false);

const getCsrfToken = () => {
  return document.cookie
    .split('; ')
    .find((row) => row.startsWith('XSRF-TOKEN='))
    ?.split('=')[1]
    ? decodeURIComponent(
        document.cookie
          .split('; ')
          .find((row) => row.startsWith('XSRF-TOKEN='))
          ?.split('=')[1]
      )
    : '';
};

const addNavLink = () => {
  if (!props.navigationConfig.header.items) {
    props.navigationConfig.header.items = [];
  }
  props.navigationConfig.header.items.push({
    label: 'New Link',
    type: 'internal',
    slug: 'home',
    href: 'https://'
  });
};

const deleteNavLink = (index) => {
  props.navigationConfig.header.items.splice(index, 1);
};

const saveNavigation = async () => {
  isSavingNav.value = true;
  try {
    const res = await fetch('/editor/navigation', {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-XSRF-TOKEN': getCsrfToken(),
      },
      body: JSON.stringify({
        navigation_config: props.navigationConfig,
      }),
    });
    if (res.ok) {
      toast.success('Navigation settings saved successfully');
    } else {
      toast.error('Failed to save navigation settings');
    }
  } catch (e) {
    toast.error('Error saving navigation settings');
  } finally {
    isSavingNav.value = false;
  }
};
</script>

<template>
  <!-- Navigation Settings Panel -->
  <div class="border-t border-slate-800 pt-4 space-y-3">
    <button
      type="button"
      @click="showNavigationSettings = !showNavigationSettings"
      class="w-full flex items-center justify-between text-left text-xs font-bold text-slate-400 uppercase tracking-widest bg-transparent border-0 cursor-pointer p-0"
    >
      <span>Navigation Settings</span>
      <span class="text-slate-500">{{ showNavigationSettings ? '▼' : '►' }}</span>
    </button>

    <div v-if="showNavigationSettings" class="space-y-4 animate-fade-in bg-slate-900/40 p-3 rounded-lg border border-slate-800/80">
      <!-- Logo Toggle -->
      <label class="flex items-center gap-2 text-xs text-slate-300 font-semibold cursor-pointer">
        <input 
          type="checkbox" 
          v-model="navigationConfig.header.showLogo" 
          class="rounded border-slate-700 bg-slate-800 text-indigo-600 focus:ring-indigo-500 animate-none w-auto h-auto" 
        />
        Show Site Logo Text
      </label>

      <!-- Nav Links List -->
      <div class="space-y-2">
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Header Links</span>
        <div v-if="!navigationConfig.header.items || !navigationConfig.header.items.length" class="text-xs text-slate-500 italic">
          No navigation links added.
        </div>
        <draggable
          v-model="navigationConfig.header.items"
          item-key="label"
          handle=".nav-drag-handle"
          class="space-y-2"
        >
          <template #item="{ element, index }">
            <div class="bg-slate-850 border border-slate-700/50 rounded p-2 space-y-2 relative">
              <div class="flex items-center justify-between">
                <span class="nav-drag-handle text-slate-500 hover:text-slate-300 cursor-move text-xs">☰ Drag</span>
                <button
                  type="button"
                  @click="deleteNavLink(index)"
                  class="text-rose-450 hover:text-rose-300 bg-transparent border-0 cursor-pointer"
                  title="Delete Link"
                >
                  ✕
                </button>
              </div>

              <div class="space-y-1.5">
                <input
                  type="text"
                  v-model="element.label"
                  placeholder="Link Label"
                  class="w-full bg-slate-900 border border-slate-700 rounded px-2 py-1 text-white text-xs focus:outline-none focus:border-indigo-500"
                />
                <div class="grid grid-cols-2 gap-1.5">
                  <select
                    v-model="element.type"
                    class="w-full bg-slate-900 border border-slate-700 rounded px-1.5 py-1 text-white text-[10px] focus:outline-none focus:border-indigo-500 cursor-pointer"
                  >
                    <option value="internal">Internal</option>
                    <option value="external">External</option>
                  </select>
                  <select
                    v-if="element.type === 'internal'"
                    v-model="element.slug"
                    class="w-full bg-slate-900 border border-slate-700 rounded px-1.5 py-1 text-white text-[10px] focus:outline-none focus:border-indigo-500 cursor-pointer"
                  >
                    <option v-for="p in pages" :key="p.id" :value="p.slug">
                      {{ p.title }}
                    </option>
                  </select>
                  <input
                    v-else
                    type="text"
                    v-model="element.href"
                    placeholder="https://..."
                    class="w-full bg-slate-900 border border-slate-700 rounded px-1.5 py-1 text-white text-[10px] focus:outline-none focus:border-indigo-500"
                  />
                </div>
              </div>
            </div>
          </template>
        </draggable>

        <button
          type="button"
          @click="addNavLink"
          class="w-full bg-slate-800 hover:bg-slate-700/60 border border-slate-700 text-slate-300 text-xs font-semibold py-1.5 px-3 rounded transition-all cursor-pointer flex items-center justify-center gap-1.5 bg-transparent"
        >
          + Add Link
        </button>
      </div>

      <!-- CTA Button -->
      <div class="border-t border-slate-800/80 pt-3 space-y-2">
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">CTA Button</span>
        <label class="flex items-center gap-2 text-xs text-slate-300 font-semibold cursor-pointer">
          <input 
            type="checkbox" 
            v-model="navigationConfig.header.ctaButton.show" 
            class="rounded border-slate-700 bg-slate-800 text-indigo-600 focus:ring-indigo-500 animate-none w-auto h-auto" 
          />
          Show CTA Button
        </label>

        <div v-if="navigationConfig.header.ctaButton.show" class="space-y-2 pl-4 border-l-2 border-indigo-600/30">
          <input
            type="text"
            v-model="navigationConfig.header.ctaButton.label"
            placeholder="Button Label"
            class="w-full bg-slate-900 border border-slate-700 rounded px-2 py-1 text-white text-xs focus:outline-none focus:border-indigo-500"
          />
          <select
            v-model="navigationConfig.header.ctaButton.slug"
            class="w-full bg-slate-900 border border-slate-700 rounded px-2 py-1 text-white text-xs focus:outline-none focus:border-indigo-500 cursor-pointer"
          >
            <option v-for="p in pages" :key="p.id" :value="p.slug">
              {{ p.title }}
            </option>
          </select>
        </div>
      </div>

      <!-- FooterCopyright -->
      <div class="border-t border-slate-800/80 pt-3 space-y-2">
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Footer Copyright</span>
        <input
          type="text"
          v-model="navigationConfig.footer.copyright"
          placeholder="© 2026 Brand Name"
          class="w-full bg-slate-900 border border-slate-700 rounded px-2 py-1 text-white text-xs focus:outline-none focus:border-indigo-500"
        />
      </div>

      <button
        type="button"
        @click="saveNavigation"
        :disabled="isSavingNav"
        class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-xs font-semibold py-2 px-3 rounded-lg transition-all cursor-pointer border-0 mt-2 shadow-sm"
      >
        {{ isSavingNav ? 'Saving...' : 'Save Navigation' }}
      </button>
    </div>
  </div>
</template>
