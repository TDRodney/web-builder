<script setup lang="ts">
defineProps<{ nodeId?: string; blockProps: Record<string, any> }>();
</script>
<template>
    <section>
        <div class="heading">
            <div>
                <p>{{ blockProps.eyebrow }}</p>
                <h2>{{ blockProps.heading }}</h2>
            </div>
        </div>
        <div class="grid">
            <a
                v-for="item in blockProps.collections || []"
                :key="item.title"
                :href="item.url || '#'"
                ><div class="media">
                    <img
                        v-if="item.imageSrc"
                        :src="item.imageSrc"
                        :alt="item.imageAlt || item.title"
                    />
                </div>
                <h3>{{ item.title }}</h3>
                <span>{{ item.subtitle }}</span></a
            >
        </div>
    </section>
</template>
<style scoped>
section {
    font-family: var(--theme-font-body);
}
.heading {
    margin-bottom: 2rem;
}
p {
    font-size: 0.72rem;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}
h2 {
    margin: 0.4rem 0;
    font: 600 clamp(2rem, 4vw, 3.4rem)/1.1 var(--theme-font-heading);
}
.grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.2rem;
}
a {
    color: var(--theme-text);
    text-decoration: none;
}
.media {
    aspect-ratio: 4/5;
    background: color-mix(in srgb, var(--theme-text) 8%, transparent);
    overflow: hidden;
}
.media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.35s;
}
.media:hover img {
    transform: scale(1.025);
}
h3 {
    margin: 0.9rem 0 0.2rem;
    font: 600 1.2rem var(--theme-font-heading);
}
span {
    font-size: 0.85rem;
    opacity: 0.65;
}
@container (max-width:640px) {
    .grid {
        grid-template-columns: 1fr 1fr;
    }
    .grid a:last-child {
        grid-column: 1/-1;
    }
}
</style>
