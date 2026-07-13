<script setup>
import { inject } from 'vue';

const props = defineProps({
  selectedBlock: {
    type: Object,
    default: null
  },
  activeBlockDefinition: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['open-media-picker']);

const blockActions = inject('blockActions', null);
const forceSave = inject('forceSave', null);

const deleteSelectedBlock = () => {
  if (props.selectedBlock && blockActions) {
    blockActions.deleteBlockById(props.selectedBlock.id);
  }
};

// Repeater helpers
const addRepeaterItem = (fieldKey, subFields) => {
  if (!props.selectedBlock) return;
  if (!props.selectedBlock.props[fieldKey] || !Array.isArray(props.selectedBlock.props[fieldKey])) {
    props.selectedBlock.props[fieldKey] = [];
  }
  const newItem = {};
  subFields.forEach((sub) => {
    newItem[sub.key] = sub.type === 'select' ? (sub.options?.[0]?.value || '') : '';
  });
  props.selectedBlock.props[fieldKey].push(newItem);
  if (forceSave) forceSave();
};

const deleteRepeaterItem = (fieldKey, index) => {
  if (!props.selectedBlock || !props.selectedBlock.props[fieldKey]) return;
  props.selectedBlock.props[fieldKey].splice(index, 1);
  if (forceSave) forceSave();
};

const moveRepeaterItem = (fieldKey, index, direction) => {
  if (!props.selectedBlock || !props.selectedBlock.props[fieldKey]) return;
  const list = props.selectedBlock.props[fieldKey];
  const targetIndex = index + direction;
  if (targetIndex < 0 || targetIndex >= list.length) return;
  const temp = list[index];
  list[index] = list[targetIndex];
  list[targetIndex] = temp;
  if (forceSave) forceSave();
};
</script>

<template>
  <div v-if="selectedBlock" class="space-y-4 animate-fade-in">
    <div v-if="activeBlockDefinition" class="space-y-4">
      <div v-for="field in activeBlockDefinition.inspectorFields" :key="field.key" class="space-y-1">
        <label class="text-xs font-semibold text-slate-400 block">
          {{ field.label }}
          <span v-if="field.type === 'range' && selectedBlock.props[field.key] !== undefined" class="text-slate-300">
            : {{ selectedBlock.props[field.key] }}px
          </span>
        </label>

        <!-- Range slider -->
        <input
          v-if="field.type === 'range'"
          type="range"
          :min="field.min ?? 10"
          :max="field.max ?? 150"
          v-model.number="selectedBlock.props[field.key]"
          class="w-full accent-indigo-500"
        />

        <!-- Color picker -->
        <div v-else-if="field.type === 'color'" class="flex items-center gap-2">
          <input
            type="color"
            v-model="selectedBlock.props[field.key]"
            class="h-8 w-12 border border-slate-700 bg-transparent cursor-pointer rounded p-0"
          />
          <span class="text-xs font-mono text-slate-300">{{ selectedBlock.props[field.key] }}</span>
        </div>

        <!-- Number input -->
        <input
          v-else-if="field.type === 'number'"
          type="number"
          :min="field.min"
          :max="field.max"
          v-model.number="selectedBlock.props[field.key]"
          class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500"
        />

        <!-- Select dropdown -->
        <select
          v-else-if="field.type === 'select'"
          v-model="selectedBlock.props[field.key]"
          class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500 cursor-pointer"
        >
          <option v-for="opt in field.options" :key="opt.value" :value="opt.value">
            {{ opt.label }}
          </option>
        </select>

        <!-- Media picker -->
        <div v-else-if="field.type === 'media'" class="space-y-2">
          <div v-if="selectedBlock.props[field.key]" class="relative rounded overflow-hidden">
            <img
              :src="selectedBlock.props[field.key]"
              alt=""
              class="w-full h-24 object-cover rounded"
            />
            <button
              @click="selectedBlock.props[field.key] = ''"
              class="absolute top-1 right-1 bg-slate-900/80 text-rose-400 border-0 rounded p-0.5 cursor-pointer"
              title="Remove image"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <button
            @click="emit('open-media-picker', field.key)"
            class="w-full bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 text-xs font-semibold py-2 px-3 rounded-lg transition-all cursor-pointer"
          >
            {{ selectedBlock.props[field.key] ? 'Change Image' : 'Choose Image' }}
          </button>
        </div>

        <!-- Repeater Field -->
        <div v-else-if="field.type === 'repeater'" class="space-y-3">
          <div 
            v-for="(item, index) in (selectedBlock.props[field.key] || [])" 
            :key="index" 
            class="bg-slate-800/60 border border-slate-700/60 rounded-lg p-3 space-y-3 relative group/item"
          >
            <div class="flex items-center justify-between border-b border-slate-700/40 pb-2">
              <span class="text-xs font-bold text-slate-300">Item #{{ index + 1 }}</span>
              <div class="flex items-center gap-1.5">
                <button
                  type="button"
                  @click="moveRepeaterItem(field.key, index, -1)"
                  :disabled="index === 0"
                  class="text-slate-400 hover:text-white bg-transparent border-0 cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
                  title="Move Up"
                >
                  ↑
                </button>
                <button
                  type="button"
                  @click="moveRepeaterItem(field.key, index, 1)"
                  :disabled="index === (selectedBlock.props[field.key]?.length - 1)"
                  class="text-slate-400 hover:text-white bg-transparent border-0 cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
                  title="Move Down"
                >
                  ↓
                </button>
                <button
                  type="button"
                  @click="deleteRepeaterItem(field.key, index)"
                  class="text-rose-400 hover:text-rose-300 bg-transparent border-0 cursor-pointer ml-1"
                  title="Delete Item"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Sub fields -->
            <div class="space-y-2">
              <div v-for="sub in field.subFields" :key="sub.key" class="space-y-1">
                <label class="text-[10px] font-semibold text-slate-400 block">{{ sub.label }}</label>
                
                <!-- Sub field select -->
                <select
                  v-if="sub.type === 'select'"
                  v-model="item[sub.key]"
                  class="w-full bg-slate-900 border border-slate-700 rounded px-2 py-1 text-white text-xs focus:outline-none focus:border-indigo-500 cursor-pointer"
                >
                  <option v-for="opt in sub.options" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                  </option>
                </select>

                <!-- Sub field text -->
                <input
                  v-else
                  type="text"
                  v-model="item[sub.key]"
                  :placeholder="sub.placeholder"
                  class="w-full bg-slate-900 border border-slate-700 rounded px-2 py-1 text-white text-xs focus:outline-none focus:border-indigo-500"
                />
              </div>
            </div>
          </div>

          <button
            type="button"
            @click="addRepeaterItem(field.key, field.subFields)"
            class="w-full bg-slate-800 hover:bg-slate-700 border border-dashed border-slate-600 hover:border-slate-500 text-slate-300 text-xs font-semibold py-2 px-3 rounded-lg transition-all cursor-pointer flex items-center justify-center gap-1.5"
          >
            + Add Item
          </button>
        </div>

        <!-- Text fallback -->
        <input
          v-else
          type="text"
          v-model="selectedBlock.props[field.key]"
          :placeholder="field.placeholder"
          class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-indigo-500"
        />
      </div>
    </div>

    <button 
      @click="deleteSelectedBlock" 
      class="w-full bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold py-2 px-3 rounded-lg transition-all cursor-pointer border-0 mt-4"
    >
      Delete Block
    </button>
  </div>
  <p v-else class="text-sm text-slate-500 mt-4 italic">Click a block on the canvas to inspect it.</p>
</template>
