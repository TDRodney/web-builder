<script setup lang="ts">
defineProps<{ nodeId?: string; blockProps: Record<string, any> }>();
</script>
<template>
    <div class="announcement">{{ blockProps.text }}</div>
</template>
<style scoped>
.announcement {
    padding: 0.7rem 1rem;
    text-align: center;
    font-family: var(--theme-font-body);
    font-size: 0.82rem;
    letter-spacing: 0.08em;
    background: var(--theme-primary, #111827);
    color: var(--theme-bg, #fff);
}
</style>
