<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, provide } from 'vue';
import RenderPublicNode from '@/components/BuilderBlocks/RenderPublicNode.vue';
import SiteFooter from '@/components/SiteFooter.vue';
import SiteHeader from '@/components/SiteHeader.vue';
import { blockComponents } from '@/lib/blockRegistry';
import { commerceHydrationKey, emptyCommerceHydration } from '@/lib/commerce';
import { useTheme } from '@/lib/theme';

provide('blockRegistry', blockComponents);
provide('isEditable', false);

const props = defineProps({
    tenant: Object,
    page: Object,
    commerce_hydration: Object,
});

provide(
    commerceHydrationKey,
    computed(() => props.commerce_hydration || emptyCommerceHydration),
);

const { cssVars: themeVars, fontUrl } = useTheme(
    () => props.tenant?.theme_config,
);
</script>

<template>
    <Head>
        <link v-if="fontUrl" rel="stylesheet" :href="fontUrl" />
    </Head>
    <div
        class="flex min-h-screen flex-col font-sans"
        :style="[
            themeVars,
            {
                backgroundColor: themeVars['--theme-bg'],
                color: themeVars['--theme-text'],
            },
        ]"
    >
        <SiteHeader
            :navigation-config="props.tenant.navigation_config"
            :tenant-name="props.tenant.subdomain"
            :is-editable="false"
        />

        <main class="flex-grow">
            <RenderPublicNode
                v-for="block in page.published_config"
                :key="block.id"
                :node="block"
            />
        </main>

        <SiteFooter
            :navigation-config="props.tenant.navigation_config"
            :tenant-name="props.tenant.subdomain"
        />
    </div>
</template>
