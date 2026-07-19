<!-- eslint-disable vue/block-lang -->
<script setup>
/* eslint-disable vue/no-mutating-props */
import { computed, inject } from 'vue';
import InlineText from './InlineText.vue';

const props = defineProps({
    nodeId: { type: String, required: true },
    blockProps: { type: Object, default: () => ({}) },
});

const isEditable = inject('isEditable', false);

const items = computed(() =>
    Array.isArray(props.blockProps.items) ? props.blockProps.items : [],
);

const columns = computed(() => {
    const value = Number(props.blockProps.columns);

    return Number.isFinite(value) && value > 0 ? value : 2;
});

// Group items by category, preserving the order categories first appear.
const groups = computed(() => {
    const order = [];
    const map = new Map();

    items.value.forEach((item) => {
        const category = (item.category || 'Menu').trim() || 'Menu';

        if (!map.has(category)) {
            map.set(category, []);
            order.push(category);
        }

        map.get(category).push(item);
    });

    return order.map((category) => ({
        category,
        items: map.get(category),
    }));
});
</script>

<template>
    <div class="menu-block" :style="{ color: 'var(--theme-text)' }">
        <div v-if="blockProps.heading || isEditable" class="menu-heading-wrap">
            <InlineText
                tag="h2"
                class="menu-heading"
                :value="blockProps.heading"
                placeholder="Our Menu"
                aria-label="Menu heading"
                @update:value="blockProps.heading = $event"
            />
            <InlineText
                v-if="blockProps.subheading || isEditable"
                tag="p"
                class="menu-subheading"
                :value="blockProps.subheading"
                placeholder="Freshly prepared, served with care"
                aria-label="Menu subheading"
                multiline
                @update:value="blockProps.subheading = $event"
            />
        </div>

        <div
            v-if="groups.length"
            class="menu-grid"
            :style="{
                gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))`,
            }"
        >
            <div
                v-for="group in groups"
                :key="group.category"
                class="menu-category"
            >
                <h3 class="menu-category-title">{{ group.category }}</h3>
                <ul class="menu-items">
                    <li
                        v-for="(item, index) in group.items"
                        :key="index"
                        class="menu-item"
                    >
                        <div class="menu-item-head">
                            <InlineText
                                tag="span"
                                class="menu-item-name"
                                :value="item.name"
                                placeholder="Dish name"
                                aria-label="Dish name"
                                @update:value="item.name = $event"
                            />
                            <span class="menu-item-leader" aria-hidden="true" />
                            <InlineText
                                tag="span"
                                class="menu-item-price"
                                :value="item.price"
                                placeholder="$0"
                                aria-label="Price"
                                @update:value="item.price = $event"
                            />
                        </div>
                        <InlineText
                            v-if="item.description || isEditable"
                            tag="p"
                            class="menu-item-desc"
                            :value="item.description"
                            placeholder="Short description of the dish"
                            aria-label="Dish description"
                            multiline
                            @update:value="item.description = $event"
                        />
                    </li>
                </ul>
            </div>
        </div>

        <div v-else-if="isEditable" class="menu-placeholder">
            Add menu items in the sidebar inspector. Group dishes with the
            <strong>Category</strong> field (e.g. Starters, Mains, Desserts).
        </div>
    </div>
</template>

<style scoped>
.menu-block {
    width: 100%;
}

.menu-heading-wrap {
    text-align: center;
    margin-bottom: 2.5rem;
}

.menu-heading {
    font-family: var(--theme-font-heading, serif);
    font-size: clamp(1.75rem, 4vw, 2.75rem);
    font-weight: 800;
    line-height: 1.1;
    margin: 0;
}

.menu-subheading {
    font-family: var(--theme-font-body, sans-serif);
    font-size: 1rem;
    margin: 0.75rem auto 0;
    max-width: 34rem;
    color: color-mix(in srgb, var(--theme-text) 65%, transparent);
}

.menu-grid {
    display: grid;
    gap: 2.5rem 3rem;
}

@container (max-width: 640px) {
    .menu-grid {
        grid-template-columns: minmax(0, 1fr) !important;
    }
}

.menu-category-title {
    font-family: var(--theme-font-heading, serif);
    font-size: 1.25rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    margin: 0 0 1.25rem;
    padding-bottom: 0.5rem;
    color: var(--theme-primary, #6366f1);
    border-bottom: 2px solid
        color-mix(in srgb, var(--theme-primary) 30%, transparent);
}

.menu-items {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.menu-item-head {
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
}

.menu-item-name {
    font-family: var(--theme-font-heading, serif);
    font-size: 1.0625rem;
    font-weight: 600;
    white-space: nowrap;
}

.menu-item-leader {
    flex: 1 1 auto;
    height: 0;
    align-self: center;
    border-bottom: 1px dotted
        color-mix(in srgb, var(--theme-text) 30%, transparent);
    transform: translateY(-2px);
}

.menu-item-price {
    font-family: var(--theme-font-heading, serif);
    font-size: 1.0625rem;
    font-weight: 700;
    color: var(--theme-primary, #6366f1);
    white-space: nowrap;
}

.menu-item-desc {
    font-family: var(--theme-font-body, sans-serif);
    font-size: 0.875rem;
    line-height: 1.5;
    margin: 0.35rem 0 0;
    color: color-mix(in srgb, var(--theme-text) 60%, transparent);
}

.menu-placeholder {
    font-family: var(--theme-font-body, sans-serif);
    text-align: center;
    padding: 2rem;
    border: 2px dashed color-mix(in srgb, var(--theme-text) 15%, transparent);
    border-radius: var(--theme-border-radius, 8px);
    color: color-mix(in srgb, var(--theme-text) 65%, transparent);
}
</style>
