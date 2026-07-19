<script setup lang="ts">
import { PanelBottom, PanelTop } from '@lucide/vue';
import { ref, watch } from 'vue';

import DashboardNavbarStudio from '@/components/DashboardNavbarStudio.vue';
import FooterStudio from '@/components/Editor/FooterStudio.vue';
import type { NavigationConfig } from '@/types/navigation';

interface ThemeConfig {
    colors?: {
        primary?: string;
        secondary?: string;
        background?: string;
        text?: string;
    };
    typography?: {
        headingFont?: string;
        bodyFont?: string;
    };
    borderRadius?: string;
}

interface NavigationOption {
    label: string;
    description: string;
}

const props = defineProps<{
    tenantName: string;
    navigationConfig: NavigationConfig;
    themeConfig?: ThemeConfig | null;
    defaultVariant: string;
    defaultMenuMode: 'simple' | 'mega';
    variants: Record<
        string,
        { label: string; description: string; category: string }
    >;
    surfaceModes: Record<
        'design' | 'transparent' | 'theme' | 'custom',
        NavigationOption
    >;
    menuModes: Record<'simple' | 'mega', NavigationOption>;
    actionPositions: Record<'start' | 'end', NavigationOption>;
    actionVariants: Record<'primary' | 'outline' | 'text', string>;
    pages: Array<{ slug: string; title: string }>;
}>();

const emit = defineEmits<{
    saved: [navigationConfig: NavigationConfig];
}>();

const activeArea = ref<'header' | 'footer'>('header');
const workingNavigation = ref<NavigationConfig>(
    JSON.parse(JSON.stringify(props.navigationConfig)),
);

watch(
    () => props.navigationConfig,
    (navigationConfig) => {
        workingNavigation.value = JSON.parse(JSON.stringify(navigationConfig));
    },
    { deep: true },
);

const handleSaved = (navigationConfig: NavigationConfig): void => {
    workingNavigation.value = JSON.parse(JSON.stringify(navigationConfig));
    emit('saved', navigationConfig);
};
</script>

<template>
    <section class="flex h-full min-h-0 flex-col gap-2">
        <nav
            class="flex shrink-0 items-center gap-1 rounded-[6px] border border-editor-border bg-editor-panel p-1"
            aria-label="Navigation workspace areas"
        >
            <button
                type="button"
                class="inline-flex h-8 items-center gap-1.5 rounded-[4px] px-3 text-[10px] font-semibold transition"
                :class="
                    activeArea === 'header'
                        ? 'bg-editor-text text-white'
                        : 'text-editor-text-muted hover:bg-editor-panel-muted hover:text-editor-text'
                "
                @click="activeArea = 'header'"
            >
                <PanelTop :size="13" /> Header
            </button>
            <button
                type="button"
                class="inline-flex h-8 items-center gap-1.5 rounded-[4px] px-3 text-[10px] font-semibold transition"
                :class="
                    activeArea === 'footer'
                        ? 'bg-editor-text text-white'
                        : 'text-editor-text-muted hover:bg-editor-panel-muted hover:text-editor-text'
                "
                @click="activeArea = 'footer'"
            >
                <PanelBottom :size="13" /> Footer
            </button>
        </nav>

        <div class="min-h-0 flex-1">
            <DashboardNavbarStudio
                v-if="activeArea === 'header'"
                :tenant-name="tenantName"
                :navigation-config="workingNavigation"
                :theme-config="themeConfig"
                :default-variant="defaultVariant"
                :default-menu-mode="defaultMenuMode"
                :variants="variants"
                :surface-modes="surfaceModes"
                :menu-modes="menuModes"
                :action-positions="actionPositions"
                :action-variants="actionVariants"
                :pages="pages"
                @saved="handleSaved"
            />
            <FooterStudio
                v-else
                :tenant-name="tenantName"
                :navigation-config="workingNavigation"
                :theme-config="themeConfig"
                :pages="pages"
                @saved="handleSaved"
            />
        </div>
    </section>
</template>
