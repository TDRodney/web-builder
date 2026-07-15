<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type Section = {
    id: string;
    type: string;
    settings: Record<string, any>;
    blocks: unknown[];
    disabled: boolean;
};
const props = defineProps<{
    template: Record<string, any>;
    sectionDefinitions: Record<string, any>;
    previewResource: string | null;
}>();
const sections = ref<Section[]>(
    structuredClone(props.template.draft_config?.sections ?? []),
);
const undoStack = ref<Section[][]>([]);
const redoStack = ref<Section[][]>([]);
const selectedId = ref<string | null>(sections.value[0]?.id ?? null);
const selected = computed(() =>
    sections.value.find((section) => section.id === selectedId.value),
);
const snapshot = () => structuredClone(sections.value);
const save = () =>
    router.put(
        `/commerce/templates/${props.template.id}`,
        { draft_config: { schemaVersion: 1, sections: sections.value } },
        { preserveState: true },
    );
const mutate = (action: () => void) => {
    undoStack.value.push(snapshot());
    redoStack.value = [];
    action();
    save();
};
const add = (type: string) => {
    if (!type) return;
    mutate(() =>
        sections.value.push({
            id: crypto.randomUUID(),
            type,
            settings: structuredClone(
                props.sectionDefinitions[type].defaultSettings,
            ),
            blocks: [],
            disabled: false,
        }),
    );
};
const duplicate = (index: number) =>
    mutate(() =>
        sections.value.splice(index + 1, 0, {
            ...structuredClone(sections.value[index]),
            id: crypto.randomUUID(),
        }),
    );
const remove = (index: number) => mutate(() => sections.value.splice(index, 1));
const move = (index: number, delta: number) =>
    mutate(() => {
        const target = index + delta;
        if (target >= 0 && target < sections.value.length)
            [sections.value[index], sections.value[target]] = [
                sections.value[target],
                sections.value[index],
            ];
    });
const undo = () => {
    const value = undoStack.value.pop();
    if (value) {
        redoStack.value.push(snapshot());
        sections.value = value;
        save();
    }
};
const redo = () => {
    const value = redoStack.value.pop();
    if (value) {
        undoStack.value.push(snapshot());
        sections.value = value;
        save();
    }
};
</script>

<template>
    <Head :title="`Edit ${template.label}`" />
    <main class="min-h-screen bg-slate-950 text-slate-100">
        <header
            class="flex items-center justify-between border-b border-white/10 px-6 py-4"
        >
            <div>
                <p class="text-xs tracking-widest text-slate-400 uppercase">
                    Commerce template
                </p>
                <h1 class="text-xl font-semibold">{{ template.label }}</h1>
            </div>
            <div class="flex gap-2">
                <button class="rounded border px-3 py-2" @click="undo">
                    Undo</button
                ><button class="rounded border px-3 py-2" @click="redo">
                    Redo</button
                ><button
                    class="rounded bg-white px-4 py-2 text-slate-950"
                    @click="
                        router.post(
                            `/commerce/templates/${template.id}/publish`,
                        )
                    "
                >
                    Publish
                </button>
            </div>
        </header>
        <div class="grid min-h-[calc(100vh-73px)] grid-cols-[320px_1fr_320px]">
            <aside class="border-r border-white/10 p-4">
                <h2 class="mb-3 font-medium">Sections</h2>
                <div
                    v-for="(section, index) in sections"
                    :key="section.id"
                    class="mb-2 rounded border border-white/10 p-3"
                    @click="selectedId = section.id"
                >
                    <div class="flex justify-between">
                        <span>{{
                            sectionDefinitions[section.type]?.label
                        }}</span
                        ><input
                            v-model="section.disabled"
                            type="checkbox"
                            title="Disabled"
                            @change="save"
                        />
                    </div>
                    <div class="mt-2 flex gap-2 text-xs">
                        <button @click.stop="move(index, -1)">Up</button
                        ><button @click.stop="move(index, 1)">Down</button
                        ><button @click.stop="duplicate(index)">
                            Duplicate</button
                        ><button @click.stop="remove(index)">Remove</button>
                    </div>
                </div>
                <select
                    class="mt-4 w-full bg-slate-900 p-2"
                    @change="add(($event.target as HTMLSelectElement).value)"
                >
                    <option value="">Add section…</option>
                    <option
                        v-for="(definition, key) in sectionDefinitions"
                        :key="key"
                        :value="key"
                    >
                        {{ definition.label }}
                    </option>
                </select>
            </aside>
            <section class="bg-stone-100 p-8 text-stone-900">
                <div class="mx-auto max-w-5xl rounded bg-white p-12 shadow">
                    <p
                        v-if="previewResource"
                        class="mb-6 text-xs text-stone-500"
                    >
                        Previewing {{ previewResource }}
                    </p>
                    <article
                        v-for="section in sections.filter(
                            (item) => !item.disabled,
                        )"
                        :key="section.id"
                        class="mb-6 border-b pb-6"
                    >
                        <p
                            class="text-xs tracking-widest text-stone-400 uppercase"
                        >
                            {{ sectionDefinitions[section.type]?.label }}
                        </p>
                        <h2 class="mt-2 text-3xl">
                            {{
                                section.settings.heading ||
                                section.settings.text ||
                                'Configure this section'
                            }}
                        </h2>
                        <p class="mt-2 text-stone-600">
                            {{ section.settings.body }}
                        </p>
                    </article>
                </div>
            </section>
            <aside class="border-l border-white/10 p-4">
                <h2 class="mb-3 font-medium">Settings</h2>
                <template v-if="selected"
                    ><label
                        v-for="(_, key) in selected.settings"
                        :key="key"
                        class="mb-3 block text-sm"
                        ><span class="mb-1 block capitalize">{{ key }}</span
                        ><input
                            v-model="selected.settings[key]"
                            class="w-full rounded bg-slate-900 p-2"
                            @change="save" /></label
                ></template>
                <p v-else class="text-sm text-slate-400">Select a section.</p>
            </aside>
        </div>
    </main>
</template>
