<script setup lang="ts">
import { useHttp } from '@inertiajs/vue3';
import {
    ArrowDown,
    ArrowUp,
    Check,
    Image,
    Loader2,
    Plus,
    Save,
    Trash2,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';

import { update as updateNavigation } from '@/actions/App/Http/Controllers/TenantNavigationController';
import MediaPicker from '@/components/MediaPicker.vue';
import SiteFooter from '@/components/SiteFooter.vue';
import { useTheme } from '@/lib/theme';
import type {
    NavigationConfig,
    NavigationFooterConfig,
} from '@/types/navigation';

type FooterModule = 'brand' | 'links' | 'newsletter' | 'social' | 'copyright';

interface NavigationSaveResponse {
    status?: string;
    message?: string;
    navigation_config?: NavigationConfig;
}

interface ThemeConfig {
    colors?: {
        primary?: string;
        background?: string;
        text?: string;
    };
    typography?: {
        headingFont?: string;
        bodyFont?: string;
    };
    borderRadius?: string;
}

const props = defineProps<{
    tenantName: string;
    navigationConfig: NavigationConfig;
    themeConfig?: ThemeConfig | null;
    pages: Array<{ slug: string; title: string }>;
}>();

const emit = defineEmits<{
    saved: [navigationConfig: NavigationConfig];
}>();

const initialNavigation = JSON.parse(
    JSON.stringify(props.navigationConfig),
) as NavigationConfig;

const defaultModuleOrder: FooterModule[] = [
    'brand',
    'links',
    'newsletter',
    'social',
    'copyright',
];

const footer = initialNavigation.footer as NavigationFooterConfig;
footer.variant ??= 'columns';
footer.moduleOrder ??= [...defaultModuleOrder];
footer.backgroundMode ??= 'contrast';
footer.backgroundColor ??= '#18181b';
footer.textColor ??= '#ffffff';
footer.contentWidth ??= 1200;
footer.showLinks ??= true;
footer.showSocial ??= true;
footer.showCopyright ??= true;
footer.brand ??= {
    show: true,
    title: props.tenantName,
    description:
        'Add a short statement about the business and what makes it distinct.',
    imageUrl: '',
    alt: `${props.tenantName} logo`,
};
footer.linkGroups ??= [
    {
        id: 'footer-pages',
        label: 'Explore',
        links: props.pages.slice(0, 5).map((page) => ({
            id: `footer-${page.slug}`,
            label: page.title,
            type: 'internal' as const,
            slug: page.slug,
        })),
    },
];
footer.newsletter ??= {
    show: false,
    eyebrow: 'Stay connected',
    heading: 'Hear what is happening next',
    body: 'Use this callout to direct visitors to your contact or newsletter service.',
    buttonLabel: 'Get updates',
    buttonUrl: '/contact',
};
footer.socialLinks ??= [];

const navigationHttp = useHttp<
    { navigation_config: NavigationConfig },
    NavigationSaveResponse
>({ navigation_config: initialNavigation });

const workingFooter = computed(() => navigationHttp.navigation_config.footer);
const { cssVars } = useTheme(() => props.themeConfig);
const showMediaPicker = ref(false);

const variants = [
    {
        key: 'minimal' as const,
        label: 'Minimal',
        description: 'Brand, social links, and copyright in a quiet finish.',
        order: ['brand', 'social', 'copyright'] as FooterModule[],
    },
    {
        key: 'columns' as const,
        label: 'Link columns',
        description: 'Brand statement beside organized navigation groups.',
        order: ['brand', 'links', 'social', 'copyright'] as FooterModule[],
    },
    {
        key: 'newsletter' as const,
        label: 'Callout footer',
        description: 'Add a prominent contact or newsletter-service callout.',
        order: [
            'brand',
            'links',
            'newsletter',
            'social',
            'copyright',
        ] as FooterModule[],
    },
    {
        key: 'editorial' as const,
        label: 'Editorial',
        description:
            'Large brand and callout typography with supporting links.',
        order: [
            'newsletter',
            'brand',
            'links',
            'social',
            'copyright',
        ] as FooterModule[],
    },
];

const moduleLabels: Record<FooterModule, string> = {
    brand: 'Brand',
    links: 'Link groups',
    newsletter: 'Callout',
    social: 'Social links',
    copyright: 'Copyright',
};

const footerModuleOrder = computed<FooterModule[]>(
    () =>
        (workingFooter.value.moduleOrder as FooterModule[] | undefined) ??
        defaultModuleOrder,
);

const applyVariant = (variant: (typeof variants)[number]): void => {
    workingFooter.value.variant = variant.key;
    workingFooter.value.moduleOrder = [...variant.order];
    workingFooter.value.showLinks = variant.order.includes('links');
    workingFooter.value.newsletter ??= {};
    workingFooter.value.newsletter.show = variant.order.includes('newsletter');
    workingFooter.value.showSocial = variant.order.includes('social');
    workingFooter.value.showCopyright = true;
};

const isModuleEnabled = (module: FooterModule): boolean => {
    if (module === 'brand') {
        return workingFooter.value.brand?.show !== false;
    }

    if (module === 'links') {
        return workingFooter.value.showLinks !== false;
    }

    if (module === 'newsletter') {
        return workingFooter.value.newsletter?.show === true;
    }

    if (module === 'social') {
        return workingFooter.value.showSocial !== false;
    }

    return workingFooter.value.showCopyright !== false;
};

const setModuleEnabled = (module: FooterModule, enabled: boolean): void => {
    if (module === 'brand' && workingFooter.value.brand) {
        workingFooter.value.brand.show = enabled;
    } else if (module === 'links') {
        workingFooter.value.showLinks = enabled;
    } else if (module === 'newsletter' && workingFooter.value.newsletter) {
        workingFooter.value.newsletter.show = enabled;
    } else if (module === 'social') {
        workingFooter.value.showSocial = enabled;
    } else if (module === 'copyright') {
        workingFooter.value.showCopyright = enabled;
    }
};

const moveModule = (index: number, direction: -1 | 1): void => {
    workingFooter.value.moduleOrder ??= [...defaultModuleOrder];
    const order = workingFooter.value.moduleOrder as FooterModule[];
    const target = index + direction;

    if (target < 0 || target >= order.length) {
        return;
    }

    [order[index], order[target]] = [order[target], order[index]];
};

const addLinkGroup = (): void => {
    workingFooter.value.linkGroups ??= [];
    workingFooter.value.linkGroups.push({
        id: `footer-group-${Date.now()}`,
        label: 'New group',
        links: [],
    });
};

const addGroupLink = (groupIndex: number): void => {
    workingFooter.value.linkGroups?.[groupIndex]?.links.push({
        id: `footer-link-${Date.now()}`,
        label: 'New link',
        type: 'internal',
        slug: props.pages[0]?.slug || 'home',
    });
};

const addSocialLink = (): void => {
    workingFooter.value.socialLinks ??= [];
    workingFooter.value.socialLinks.push({
        id: `footer-social-${Date.now()}`,
        label: 'Instagram',
        href: 'https://instagram.com',
    });
};

const onMediaSelected = (item: { url: string }): void => {
    if (workingFooter.value.brand) {
        workingFooter.value.brand.imageUrl = item.url;
    }

    showMediaPicker.value = false;
};

const saveFooter = async (): Promise<void> => {
    try {
        const response = await navigationHttp.patch(
            updateNavigation.url(props.tenantName),
        );

        if (response?.status === 'success' && response.navigation_config) {
            emit('saved', response.navigation_config);
            toast.success('Footer design saved');

            return;
        }

        toast.error(response?.message || 'Unable to save the footer design');
    } catch {
        toast.error('Unable to save the footer design');
    }
};
</script>

<template>
    <section
        class="flex h-full min-h-0 flex-col overflow-hidden rounded-[7px] border border-editor-border bg-editor-panel"
    >
        <header
            class="shrink-0 border-b border-editor-border px-4 py-3 sm:px-5"
        >
            <span
                class="text-[9px] font-bold tracking-[0.14em] text-editor-text-muted uppercase"
            >
                Global footer
            </span>
            <h2 class="mt-1 text-base font-semibold text-editor-text">
                Footer structure
            </h2>
            <p class="mt-1 text-xs text-editor-text-muted">
                Choose a composition, reorder its modules, and keep it shared
                across every page.
            </p>
        </header>

        <div
            class="grid min-h-0 flex-1 gap-px overflow-hidden bg-editor-border lg:grid-cols-[320px_minmax(0,1fr)]"
        >
            <aside class="min-h-0 overflow-y-auto bg-editor-panel p-4">
                <section>
                    <span
                        class="text-[9px] font-bold tracking-[0.1em] text-editor-text-muted uppercase"
                        >Layout</span
                    >
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <button
                            v-for="variant in variants"
                            :key="variant.key"
                            type="button"
                            class="relative min-h-24 rounded-[5px] border p-2.5 text-left transition"
                            :class="
                                workingFooter.variant === variant.key
                                    ? 'border-editor-accent bg-editor-accent-soft text-editor-text'
                                    : 'border-editor-border bg-editor-panel-muted text-editor-text-muted hover:border-editor-border-strong hover:text-editor-text'
                            "
                            @click="applyVariant(variant)"
                        >
                            <Check
                                v-if="workingFooter.variant === variant.key"
                                :size="12"
                                class="absolute top-2 right-2 text-editor-accent"
                            />
                            <strong class="block text-[10px]">{{
                                variant.label
                            }}</strong>
                            <small class="mt-1 block text-[8px] leading-4">{{
                                variant.description
                            }}</small>
                        </button>
                    </div>
                </section>

                <section class="mt-5 border-t border-editor-border pt-4">
                    <span
                        class="text-[9px] font-bold tracking-[0.1em] text-editor-text-muted uppercase"
                        >Module order</span
                    >
                    <div class="mt-2 grid gap-1.5">
                        <div
                            v-for="(module, index) in footerModuleOrder"
                            :key="module"
                            class="flex min-h-10 items-center gap-2 rounded-[5px] border border-editor-border bg-editor-panel-muted px-2"
                        >
                            <span
                                class="min-w-0 flex-1 text-[10px] font-semibold text-editor-text"
                                >{{ moduleLabels[module] }}</span
                            >
                            <button
                                type="button"
                                class="text-editor-text-muted disabled:opacity-30"
                                :disabled="index === 0"
                                @click="moveModule(index, -1)"
                            >
                                <ArrowUp :size="12" />
                            </button>
                            <button
                                type="button"
                                class="text-editor-text-muted disabled:opacity-30"
                                :disabled="
                                    index === footerModuleOrder.length - 1
                                "
                                @click="moveModule(index, 1)"
                            >
                                <ArrowDown :size="12" />
                            </button>
                            <label
                                class="relative inline-flex cursor-pointer items-center"
                            >
                                <input
                                    type="checkbox"
                                    class="peer sr-only"
                                    :checked="isModuleEnabled(module)"
                                    @change="
                                        setModuleEnabled(
                                            module,
                                            ($event.target as HTMLInputElement)
                                                .checked,
                                        )
                                    "
                                />
                                <span
                                    class="h-4 w-7 rounded-full bg-editor-border-strong peer-checked:bg-editor-accent"
                                ></span>
                                <span
                                    class="absolute left-0.5 size-3 rounded-full bg-white transition-transform peer-checked:translate-x-3"
                                ></span>
                            </label>
                        </div>
                    </div>
                </section>

                <section
                    v-if="workingFooter.brand"
                    class="mt-5 border-t border-editor-border pt-4"
                >
                    <span
                        class="text-[9px] font-bold tracking-[0.1em] text-editor-text-muted uppercase"
                        >Brand</span
                    >
                    <div class="mt-2 grid gap-2">
                        <input
                            v-model="workingFooter.brand.title"
                            class="studio-input"
                            placeholder="Brand name"
                        />
                        <textarea
                            v-model="workingFooter.brand.description"
                            class="studio-input min-h-20 py-2"
                            placeholder="Short brand statement"
                        ></textarea>
                        <button
                            type="button"
                            class="studio-action"
                            @click="showMediaPicker = true"
                        >
                            <Image :size="13" />
                            {{
                                workingFooter.brand.imageUrl
                                    ? 'Change logo'
                                    : 'Choose logo'
                            }}
                        </button>
                    </div>
                </section>

                <section
                    v-if="workingFooter.newsletter"
                    class="mt-5 border-t border-editor-border pt-4"
                >
                    <span
                        class="text-[9px] font-bold tracking-[0.1em] text-editor-text-muted uppercase"
                        >Callout</span
                    >
                    <div class="mt-2 grid gap-2">
                        <input
                            v-model="workingFooter.newsletter.eyebrow"
                            class="studio-input"
                            placeholder="Eyebrow"
                        />
                        <input
                            v-model="workingFooter.newsletter.heading"
                            class="studio-input"
                            placeholder="Heading"
                        />
                        <textarea
                            v-model="workingFooter.newsletter.body"
                            class="studio-input min-h-20 py-2"
                            placeholder="Description"
                        ></textarea>
                        <input
                            v-model="workingFooter.newsletter.buttonLabel"
                            class="studio-input"
                            placeholder="Button label"
                        />
                        <input
                            v-model="workingFooter.newsletter.buttonUrl"
                            class="studio-input"
                            placeholder="Button URL"
                        />
                    </div>
                </section>

                <section class="mt-5 border-t border-editor-border pt-4">
                    <div class="flex items-center justify-between gap-2">
                        <span
                            class="text-[9px] font-bold tracking-[0.1em] text-editor-text-muted uppercase"
                            >Link groups</span
                        >
                        <button
                            type="button"
                            class="studio-mini-action"
                            @click="addLinkGroup"
                        >
                            <Plus :size="11" /> Group
                        </button>
                    </div>
                    <div class="mt-2 grid gap-2">
                        <div
                            v-for="(
                                group, groupIndex
                            ) in workingFooter.linkGroups"
                            :key="group.id"
                            class="rounded-[5px] border border-editor-border bg-editor-panel-muted p-2"
                        >
                            <div class="flex gap-1.5">
                                <input
                                    v-model="group.label"
                                    class="studio-input min-w-0 flex-1"
                                    placeholder="Group label"
                                />
                                <button
                                    type="button"
                                    class="text-rose-400"
                                    @click="
                                        workingFooter.linkGroups?.splice(
                                            groupIndex,
                                            1,
                                        )
                                    "
                                >
                                    <Trash2 :size="13" />
                                </button>
                            </div>
                            <div class="mt-2 grid gap-1.5">
                                <div
                                    v-for="(link, linkIndex) in group.links"
                                    :key="link.id"
                                    class="grid grid-cols-[1fr_1fr_auto] gap-1"
                                >
                                    <input
                                        v-model="link.label"
                                        class="studio-input"
                                        placeholder="Label"
                                    />
                                    <select
                                        v-model="link.slug"
                                        class="studio-input"
                                    >
                                        <option
                                            v-for="page in pages"
                                            :key="page.slug"
                                            :value="page.slug"
                                        >
                                            {{ page.title }}
                                        </option>
                                    </select>
                                    <button
                                        type="button"
                                        class="text-rose-400"
                                        @click="
                                            group.links.splice(linkIndex, 1)
                                        "
                                    >
                                        <Trash2 :size="12" />
                                    </button>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="studio-mini-action mt-2"
                                @click="addGroupLink(groupIndex)"
                            >
                                <Plus :size="11" /> Link
                            </button>
                        </div>
                    </div>
                </section>

                <section class="mt-5 border-t border-editor-border pt-4">
                    <div class="flex items-center justify-between gap-2">
                        <span
                            class="text-[9px] font-bold tracking-[0.1em] text-editor-text-muted uppercase"
                            >Social links</span
                        >
                        <button
                            type="button"
                            class="studio-mini-action"
                            @click="addSocialLink"
                        >
                            <Plus :size="11" /> Link
                        </button>
                    </div>
                    <div class="mt-2 grid gap-1.5">
                        <div
                            v-for="(
                                social, socialIndex
                            ) in workingFooter.socialLinks"
                            :key="social.id"
                            class="grid grid-cols-[1fr_1fr_auto] gap-1"
                        >
                            <input
                                v-model="social.label"
                                class="studio-input"
                                placeholder="Label"
                            />
                            <input
                                v-model="social.href"
                                class="studio-input"
                                placeholder="https://"
                            />
                            <button
                                type="button"
                                class="text-rose-400"
                                @click="
                                    workingFooter.socialLinks?.splice(
                                        socialIndex,
                                        1,
                                    )
                                "
                            >
                                <Trash2 :size="12" />
                            </button>
                        </div>
                    </div>
                </section>

                <section class="mt-5 border-t border-editor-border pt-4">
                    <span
                        class="text-[9px] font-bold tracking-[0.1em] text-editor-text-muted uppercase"
                        >Appearance</span
                    >
                    <div class="mt-2 grid gap-2">
                        <select
                            v-model="workingFooter.backgroundMode"
                            class="studio-input"
                        >
                            <option value="theme">Site theme</option>
                            <option value="contrast">Theme contrast</option>
                            <option value="custom">Custom colors</option>
                        </select>
                        <div
                            v-if="workingFooter.backgroundMode === 'custom'"
                            class="grid grid-cols-2 gap-2"
                        >
                            <label class="text-[8px] text-editor-text-muted"
                                >Background<input
                                    v-model="workingFooter.backgroundColor"
                                    type="color"
                                    class="mt-1 h-8 w-full"
                            /></label>
                            <label class="text-[8px] text-editor-text-muted"
                                >Text<input
                                    v-model="workingFooter.textColor"
                                    type="color"
                                    class="mt-1 h-8 w-full"
                            /></label>
                        </div>
                        <input
                            v-model="workingFooter.copyright"
                            class="studio-input"
                            placeholder="Copyright text"
                        />
                    </div>
                </section>
            </aside>

            <main class="min-h-0 overflow-auto bg-editor-bg p-4 sm:p-6">
                <div
                    class="canvas-runtime mx-auto min-h-[620px] overflow-hidden bg-[var(--theme-bg)] text-[var(--theme-text)] shadow-[var(--editor-shadow)]"
                    :style="cssVars"
                >
                    <div class="min-h-[280px] p-8 opacity-45">
                        <div
                            class="h-3 w-24 rounded bg-[var(--theme-primary)]"
                        ></div>
                        <div
                            class="mt-4 h-8 w-2/3 rounded bg-[var(--theme-text)]"
                        ></div>
                    </div>
                    <SiteFooter
                        :navigation-config="navigationHttp.navigation_config"
                        :tenant-name="tenantName"
                        :is-editable="true"
                    />
                </div>
            </main>
        </div>

        <footer
            class="flex shrink-0 items-center justify-between gap-3 border-t border-editor-border bg-editor-panel px-4 py-3 sm:px-5"
        >
            <p class="text-[10px] text-editor-text-muted">
                Saving updates the footer on every page.
            </p>
            <button
                type="button"
                class="inline-flex h-10 items-center justify-center gap-2 rounded-[5px] bg-editor-text px-5 text-[11px] font-bold text-white disabled:opacity-45"
                :disabled="navigationHttp.processing"
                @click="saveFooter"
            >
                <Loader2
                    v-if="navigationHttp.processing"
                    :size="14"
                    class="animate-spin"
                />
                <Save v-else :size="14" />
                {{ navigationHttp.processing ? 'Saving…' : 'Save footer' }}
            </button>
        </footer>

        <MediaPicker
            v-if="showMediaPicker"
            @select="onMediaSelected"
            @close="showMediaPicker = false"
        />
    </section>
</template>

<style scoped>
.studio-input {
    min-height: 34px;
    width: 100%;
    border: 1px solid var(--editor-border);
    border-radius: 4px;
    background: var(--editor-panel);
    padding-inline: 8px;
    color: var(--editor-text);
    font-size: 10px;
    outline: none;
}

.studio-input:focus {
    border-color: var(--editor-accent);
}

.studio-action,
.studio-mini-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 34px;
    gap: 5px;
    border: 1px solid var(--editor-border);
    border-radius: 4px;
    color: var(--editor-text);
    background: var(--editor-panel-muted);
    font-size: 9px;
    font-weight: 700;
}

.studio-mini-action {
    min-height: 26px;
    padding-inline: 7px;
}
</style>
