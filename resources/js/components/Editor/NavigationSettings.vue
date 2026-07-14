<script setup>
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import draggable from 'vuedraggable';

const props = defineProps({
    navigationConfig: {
        type: Object,
        required: true,
    },
    pages: {
        type: Array,
        required: true,
    },
    tenant: {
        type: Object,
        required: true,
    },
});

const showNavigationSettings = ref(true);
const isSavingNav = ref(false);
const navigationItemKeys = new WeakMap();
let navigationItemKeySequence = 0;

const getNavigationItemKey = (item) => {
    if (!navigationItemKeys.has(item)) {
        navigationItemKeySequence += 1;
        navigationItemKeys.set(
            item,
            `navigation-item-${navigationItemKeySequence}`,
        );
    }

    return navigationItemKeys.get(item);
};

const getCsrfToken = () => {
    return document.cookie
        .split('; ')
        .find((row) => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1]
        ? decodeURIComponent(
              document.cookie
                  .split('; ')
                  .find((row) => row.startsWith('XSRF-TOKEN='))
                  ?.split('=')[1],
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
        href: 'https://',
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
                Accept: 'application/json',
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
    <div class="navigation-settings space-y-3">
        <button
            type="button"
            @click="showNavigationSettings = !showNavigationSettings"
            class="flex w-full cursor-pointer items-center justify-between border-0 bg-transparent p-0 text-left text-xs font-bold tracking-widest text-slate-400 uppercase"
        >
            <span>Navigation Settings</span>
            <span class="text-slate-500">{{
                showNavigationSettings ? '−' : '+'
            }}</span>
        </button>

        <div
            v-if="showNavigationSettings"
            class="animate-fade-in space-y-4 rounded-lg border border-slate-800/80 bg-slate-900/40 p-3"
        >
            <!-- Logo Toggle -->
            <label
                class="flex cursor-pointer items-center gap-2 text-xs font-semibold text-slate-300"
            >
                <input
                    type="checkbox"
                    v-model="navigationConfig.header.showLogo"
                    class="h-auto w-auto animate-none rounded border-slate-700 bg-slate-800 text-indigo-600 focus:ring-indigo-500"
                />
                Show Site Logo Text
            </label>

            <!-- Nav Links List -->
            <div class="space-y-2">
                <span
                    class="block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                    >Header Links</span
                >
                <div
                    v-if="
                        !navigationConfig.header.items ||
                        !navigationConfig.header.items.length
                    "
                    class="text-xs text-slate-500 italic"
                >
                    No navigation links added.
                </div>
                <draggable
                    v-model="navigationConfig.header.items"
                    :item-key="getNavigationItemKey"
                    handle=".nav-drag-handle"
                    class="space-y-2"
                >
                    <template #item="{ element, index }">
                        <div
                            class="bg-slate-850 relative space-y-2 rounded border border-slate-700/50 p-2"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="nav-drag-handle cursor-move text-xs text-slate-500 hover:text-slate-300"
                                    >Drag to reorder</span
                                >
                                <button
                                    type="button"
                                    @click="deleteNavLink(index)"
                                    class="text-rose-450 cursor-pointer border-0 bg-transparent hover:text-rose-300"
                                    title="Delete Link"
                                >
                                    ×
                                </button>
                            </div>

                            <div class="space-y-1.5">
                                <input
                                    type="text"
                                    v-model="element.label"
                                    placeholder="Link Label"
                                    class="w-full rounded border border-slate-700 bg-slate-900 px-2 py-1 text-xs text-white focus:border-indigo-500 focus:outline-none"
                                />
                                <div class="grid grid-cols-2 gap-1.5">
                                    <select
                                        v-model="element.type"
                                        class="w-full cursor-pointer rounded border border-slate-700 bg-slate-900 px-1.5 py-1 text-[10px] text-white focus:border-indigo-500 focus:outline-none"
                                    >
                                        <option value="internal">
                                            Internal
                                        </option>
                                        <option value="external">
                                            External
                                        </option>
                                    </select>
                                    <select
                                        v-if="element.type === 'internal'"
                                        v-model="element.slug"
                                        class="w-full cursor-pointer rounded border border-slate-700 bg-slate-900 px-1.5 py-1 text-[10px] text-white focus:border-indigo-500 focus:outline-none"
                                    >
                                        <option
                                            v-for="p in pages"
                                            :key="p.id"
                                            :value="p.slug"
                                        >
                                            {{ p.title }}
                                        </option>
                                    </select>
                                    <input
                                        v-else
                                        type="text"
                                        v-model="element.href"
                                        placeholder="https://..."
                                        class="w-full rounded border border-slate-700 bg-slate-900 px-1.5 py-1 text-[10px] text-white focus:border-indigo-500 focus:outline-none"
                                    />
                                </div>
                            </div>
                        </div>
                    </template>
                </draggable>

                <button
                    type="button"
                    @click="addNavLink"
                    class="flex w-full cursor-pointer items-center justify-center gap-1.5 rounded border border-slate-700 bg-slate-800 bg-transparent px-3 py-1.5 text-xs font-semibold text-slate-300 transition-all hover:bg-slate-700/60"
                >
                    + Add Link
                </button>
            </div>

            <!-- CTA Button -->
            <div class="space-y-2 border-t border-slate-800/80 pt-3">
                <span
                    class="block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                    >CTA Button</span
                >
                <label
                    class="flex cursor-pointer items-center gap-2 text-xs font-semibold text-slate-300"
                >
                    <input
                        type="checkbox"
                        v-model="navigationConfig.header.ctaButton.show"
                        class="h-auto w-auto animate-none rounded border-slate-700 bg-slate-800 text-indigo-600 focus:ring-indigo-500"
                    />
                    Show CTA Button
                </label>

                <div
                    v-if="navigationConfig.header.ctaButton.show"
                    class="space-y-2 border-l-2 border-indigo-600/30 pl-4"
                >
                    <input
                        type="text"
                        v-model="navigationConfig.header.ctaButton.label"
                        placeholder="Button Label"
                        class="w-full rounded border border-slate-700 bg-slate-900 px-2 py-1 text-xs text-white focus:border-indigo-500 focus:outline-none"
                    />
                    <select
                        v-model="navigationConfig.header.ctaButton.slug"
                        class="w-full cursor-pointer rounded border border-slate-700 bg-slate-900 px-2 py-1 text-xs text-white focus:border-indigo-500 focus:outline-none"
                    >
                        <option v-for="p in pages" :key="p.id" :value="p.slug">
                            {{ p.title }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- FooterCopyright -->
            <div class="space-y-2 border-t border-slate-800/80 pt-3">
                <span
                    class="block text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                    >Footer Copyright</span
                >
                <input
                    type="text"
                    v-model="navigationConfig.footer.copyright"
                    placeholder="© 2026 Brand Name"
                    class="w-full rounded border border-slate-700 bg-slate-900 px-2 py-1 text-xs text-white focus:border-indigo-500 focus:outline-none"
                />
            </div>

            <button
                type="button"
                @click="saveNavigation"
                :disabled="isSavingNav"
                class="mt-2 w-full cursor-pointer rounded-lg border-0 bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition-all hover:bg-indigo-500 disabled:opacity-50"
            >
                {{ isSavingNav ? 'Saving...' : 'Save Navigation' }}
            </button>
        </div>
    </div>
</template>

<style scoped>
.navigation-settings {
    padding: 0;
    border: 0;
}

.navigation-settings > button:first-child {
    min-height: 34px;
    padding: 0 9px;
    color: #9aa9c0;
    font-size: 9px;
    background: #171719;
    border: 1px solid #303033;
    border-radius: 5px;
}

.navigation-settings > div {
    margin-top: 9px;
    padding: 11px;
    background: #141416;
    border-color: #2e2e31;
    border-radius: 5px;
}

.navigation-settings :deep(label),
.navigation-settings :deep(span) {
    color: #9ca3af;
}

.navigation-settings :deep(input[type='text']),
.navigation-settings :deep(select) {
    min-height: 32px;
    color: #f4f4f5;
    background: #19191b;
    border-color: #343438;
    border-radius: 4px;
}

.navigation-settings :deep(input:focus),
.navigation-settings :deep(select:focus) {
    border-color: #7185a5;
}

.navigation-settings :deep(input[type='checkbox']) {
    accent-color: #9db3d6;
}

.navigation-settings :deep(.nav-drag-handle) {
    color: #71717a;
    font-size: 9px;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.navigation-settings :deep(button) {
    border-radius: 4px;
}

.navigation-settings > div > button:last-child {
    color: #111113;
    background: #e4e4e7;
    border: 1px solid #ffffff;
    border-radius: 5px;
}

.navigation-settings > div > button:last-child:hover {
    background: #ffffff;
}
</style>
