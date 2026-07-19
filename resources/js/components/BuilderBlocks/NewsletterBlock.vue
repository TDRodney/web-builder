<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import InlineText from './InlineText.vue';

defineProps<{ nodeId?: string; blockProps: Record<string, any> }>();
</script>
<template>
    <section>
        <InlineText
            tag="p"
            :value="blockProps.eyebrow"
            placeholder="Section eyebrow"
            aria-label="Newsletter eyebrow"
            @update:value="blockProps.eyebrow = $event"
        />
        <InlineText
            tag="h2"
            :value="blockProps.heading"
            placeholder="Newsletter heading"
            aria-label="Newsletter heading"
            @update:value="blockProps.heading = $event"
        />
        <InlineText
            tag="div"
            class="body"
            :value="blockProps.body"
            placeholder="Newsletter description"
            aria-label="Newsletter description"
            multiline
            @update:value="blockProps.body = $event"
        />
        <form @submit.prevent>
            <input
                type="email"
                :placeholder="blockProps.placeholder || 'Email address'"
            /><button>
                <InlineText
                    :value="blockProps.buttonLabel"
                    placeholder="Button label"
                    aria-label="Newsletter button label"
                    @update:value="blockProps.buttonLabel = $event"
                />
            </button>
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
