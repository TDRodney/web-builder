<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, provide } from 'vue';
import RenderPublicNode from '@/components/BuilderBlocks/RenderPublicNode.vue';
import CommerceCartDrawer from '@/components/CommerceCartDrawer.vue';
import SiteFooter from '@/components/SiteFooter.vue';
import SiteHeader from '@/components/SiteHeader.vue';
import { blockComponents } from '@/lib/blockRegistry';
import { commerceHydrationKey, emptyCommerceHydration } from '@/lib/commerce';
import { commerceCartKey, createCommerceCart } from '@/lib/commerceCart';
import { useTheme } from '@/lib/theme';

provide('blockRegistry', blockComponents);
provide('isEditable', false);

const props = defineProps({
    tenant: Object,
    page: Object,
    commerce_hydration: Object,
    commerce_cart: Object,
    commerce_enabled: Boolean,
});

provide(
    commerceHydrationKey,
    computed(() => props.commerce_hydration || emptyCommerceHydration),
);

const commerceCart = createCommerceCart(props.commerce_cart || null);
provide(commerceCartKey, commerceCart);

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
            :show-cart="props.commerce_enabled"
            :cart-count="commerceCart.itemCount.value"
            @open-cart="commerceCart.open"
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
        <CommerceCartDrawer
            v-if="props.commerce_enabled"
            :theme-vars="themeVars"
        />
    </div>
</template>
