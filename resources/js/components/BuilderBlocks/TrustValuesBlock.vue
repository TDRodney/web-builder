<script setup lang="ts">
import InlineText from './InlineText.vue';

defineProps<{ nodeId?: string; blockProps: Record<string, any> }>();
</script>
<template>
    <section>
        <div v-for="(item, index) in blockProps.items || []" :key="index">
            <InlineText
                tag="h3"
                :value="item.title"
                placeholder="Value title"
                aria-label="Value title"
                @update:value="item.title = $event"
            />
            <InlineText
                tag="p"
                :value="item.body"
                placeholder="Value description"
                aria-label="Value description"
                multiline
                @update:value="item.body = $event"
            />
        </div>
    </section>
</template>
<style scoped>
section {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
    border-block: 1px solid
        color-mix(in srgb, var(--theme-text) 15%, transparent);
    padding: 2rem 0;
    font-family: var(--theme-font-body);
}
div {
    text-align: center;
}
h3 {
    font: 600 1rem var(--theme-font-heading);
}
p {
    margin-top: 0.4rem;
    font-size: 0.82rem;
    line-height: 1.5;
    opacity: 0.65;
}
@container (max-width:600px) {
    section {
        grid-template-columns: 1fr;
    }
}
</style>
