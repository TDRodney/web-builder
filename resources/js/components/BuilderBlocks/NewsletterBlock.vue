<script setup lang="ts">
defineProps<{ nodeId?: string; blockProps: Record<string, any> }>();
</script>
<template>
    <section>
        <p>{{ blockProps.eyebrow }}</p>
        <h2>{{ blockProps.heading }}</h2>
        <div class="body">{{ blockProps.body }}</div>
        <form @submit.prevent>
            <input
                type="email"
                :placeholder="blockProps.placeholder || 'Email address'"
            /><button>{{ blockProps.buttonLabel || 'Subscribe' }}</button>
        </form>
    </section>
</template>
<style scoped>
section {
    padding: clamp(2.5rem, 7vw, 6rem);
    text-align: center;
    background: color-mix(in srgb, var(--theme-secondary) 14%, var(--theme-bg));
    font-family: var(--theme-font-body);
}
p {
    font-size: 0.72rem;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}
h2 {
    margin: 0.6rem 0;
    font: 600 clamp(2rem, 4vw, 3.4rem) var(--theme-font-heading);
}
.body {
    opacity: 0.7;
}
form {
    display: flex;
    max-width: 34rem;
    margin: 2rem auto 0;
    border-bottom: 1px solid var(--theme-text);
}
input {
    flex: 1;
    min-width: 0;
    padding: 1rem 0.25rem;
    background: transparent;
    outline: 0;
}
button {
    padding: 1rem;
    background: transparent;
    font-weight: 600;
}
</style>
