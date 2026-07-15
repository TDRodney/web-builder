<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import SiteFooter from '@/components/SiteFooter.vue';
import SiteHeader from '@/components/SiteHeader.vue';
import { useTheme } from '@/lib/theme';

const props = defineProps<{
    tenant: Record<string, any>;
    template: Record<string, any>;
    resource: Record<string, any>;
    type: string;
}>();
const { cssVars, fontUrl } = useTheme(() => props.tenant.theme_config);
const data = computed(() => props.resource.data ?? {});
const products = computed(() => data.value.products ?? []);
</script>

<template>
    <Head :title="data.title || 'Store'" />
    <link v-if="fontUrl" rel="stylesheet" :href="fontUrl" />
    <div class="canvas-runtime min-h-screen" :style="cssVars">
        <div
            v-if="resource.stale"
            class="bg-amber-100 px-4 py-2 text-center text-sm text-amber-950"
        >
            Catalog information may be out of date. Purchasing is temporarily
            unavailable.
        </div>
        <SiteHeader
            :navigation-config="tenant.navigation_config"
            :tenant-name="tenant.subdomain"
        />
        <main>
            <template
                v-for="section in template.sections?.filter(
                    (item: any) => !item.disabled,
                )"
                :key="section.id"
            >
                <section
                    v-if="section.type === 'announcement'"
                    class="bg-[var(--theme-primary)] px-4 py-2 text-center text-sm text-white"
                >
                    {{ section.settings.text }}
                </section>
                <section
                    v-else-if="section.type === 'image-hero'"
                    class="mx-auto grid max-w-7xl gap-8 px-6 py-20 md:grid-cols-2"
                >
                    <div class="self-center">
                        <h1 class="text-5xl font-semibold">
                            {{ section.settings.heading }}
                        </h1>
                        <p class="mt-5 text-lg opacity-70">
                            {{ section.settings.body }}
                        </p>
                        <Link
                            :href="
                                section.settings.linkUrl || '/collections/all'
                            "
                            class="mt-8 inline-block bg-[var(--theme-primary)] px-6 py-3 text-white"
                            >{{ section.settings.linkLabel }}</Link
                        >
                    </div>
                    <img
                        v-if="section.settings.image"
                        :src="section.settings.image"
                        alt=""
                        class="h-[520px] w-full object-cover"
                    />
                </section>
                <section
                    v-else-if="
                        section.type === 'image-with-text' ||
                        section.type === 'rich-text'
                    "
                    class="mx-auto max-w-4xl px-6 py-16 text-center"
                >
                    <h2 class="text-4xl font-semibold">
                        {{ section.settings.heading }}
                    </h2>
                    <p class="mt-5 text-lg opacity-70">
                        {{ section.settings.body }}
                    </p>
                </section>
                <section
                    v-else-if="section.type === 'main-product'"
                    class="mx-auto grid max-w-7xl gap-10 px-6 py-12 md:grid-cols-2"
                >
                    <img
                        :src="data.images?.[0]"
                        :alt="data.title"
                        class="aspect-square w-full bg-stone-100 object-cover"
                    />
                    <div>
                        <h1 class="text-4xl font-semibold">{{ data.title }}</h1>
                        <p class="mt-4 text-xl">
                            {{ data.variants?.[0]?.price?.formatted }}
                        </p>
                        <p class="mt-6 opacity-70">{{ data.description }}</p>
                        <button
                            class="mt-8 bg-[var(--theme-primary)] px-6 py-3 text-white disabled:opacity-40"
                            :disabled="
                                !resource.purchasingEnabled ||
                                !data.variants?.[0]?.available
                            "
                        >
                            Add to cart
                        </button>
                    </div>
                </section>
                <section
                    v-else-if="
                        [
                            'product-grid',
                            'featured-collection',
                            'product-recommendations',
                        ].includes(section.type)
                    "
                    class="mx-auto max-w-7xl px-6 py-16"
                >
                    <h2 class="mb-8 text-3xl font-semibold">
                        {{ section.settings.heading || data.title }}
                    </h2>
                    <div class="grid grid-cols-2 gap-5 md:grid-cols-4">
                        <Link
                            v-for="product in products"
                            :key="product.id"
                            :href="`/products/${product.handle}`"
                            ><img
                                :src="product.images?.[0]"
                                :alt="product.title"
                                class="aspect-[4/5] w-full bg-stone-100 object-cover"
                            />
                            <h3 class="mt-3 font-medium">
                                {{ product.title }}
                            </h3>
                            <p class="text-sm opacity-70">
                                {{ product.variants?.[0]?.price?.formatted }}
                            </p></Link
                        >
                    </div>
                </section>
                <section
                    v-else
                    class="mx-auto max-w-4xl px-6 py-14 text-center"
                >
                    <h2 class="text-3xl font-semibold">
                        {{ section.settings.heading }}
                    </h2>
                    <p class="mt-3 opacity-70">{{ section.settings.body }}</p>
                </section>
            </template>
            <section
                v-if="type === 'collection' && !template.sections?.length"
                class="mx-auto max-w-7xl px-6 py-16"
            >
                <h1 class="text-4xl font-semibold">{{ data.title }}</h1>
            </section>
            <section
                v-if="type === 'cart'"
                class="mx-auto max-w-4xl px-6 py-20"
            >
                <h1 class="text-4xl font-semibold">Your cart</h1>
                <p class="mt-4 opacity-70">Your cart is currently empty.</p>
            </section>
        </main>
        <SiteFooter
            :navigation-config="tenant.navigation_config"
            :tenant-name="tenant.subdomain"
        />
    </div>
</template>
