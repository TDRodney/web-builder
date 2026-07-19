<script setup lang="ts">
import { ArrowRight } from '@lucide/vue';
import { computed } from 'vue';

import type {
    FooterLink,
    NavigationConfig,
    NavigationFooterConfig,
} from '@/types/navigation';

const props = withDefaults(
    defineProps<{
        navigationConfig?: NavigationConfig;
        tenantName?: string;
        isEditable?: boolean;
    }>(),
    {
        navigationConfig: () => ({ header: {}, footer: {} }),
        tenantName: 'My Workspace',
        isEditable: false,
    },
);

const footer = computed<NavigationFooterConfig>(
    () => props.navigationConfig?.footer || {},
);
const variant = computed(() => footer.value.variant || 'minimal');
const moduleOrder = computed(
    () =>
        footer.value.moduleOrder || [
            'brand',
            'links',
            'newsletter',
            'social',
            'copyright',
        ],
);
const copyrightText = computed(
    () =>
        footer.value.copyright ||
        `© ${new Date().getFullYear()} ${props.tenantName}. All rights reserved.`,
);

const surfaceStyle = computed<Record<string, string>>(() => {
    const mode = footer.value.backgroundMode || 'theme';

    if (mode === 'custom') {
        return {
            '--footer-background': footer.value.backgroundColor || '#18181b',
            '--footer-text': footer.value.textColor || '#ffffff',
            '--footer-content-width': `${footer.value.contentWidth || 1200}px`,
        };
    }

    if (mode === 'contrast') {
        return {
            '--footer-background': 'var(--theme-text, #18181b)',
            '--footer-text': 'var(--theme-bg, #ffffff)',
            '--footer-content-width': `${footer.value.contentWidth || 1200}px`,
        };
    }

    return {
        '--footer-background': 'var(--theme-bg, #ffffff)',
        '--footer-text': 'var(--theme-text, #18181b)',
        '--footer-content-width': `${footer.value.contentWidth || 1200}px`,
    };
});

const getLinkUrl = (link: FooterLink): string => {
    if (link.type === 'external') {
        return link.href || '#';
    }

    return !link.slug || link.slug === 'home' ? '/' : `/${link.slug}`;
};

const keepPreviewInPlace = (event: MouseEvent): void => {
    if (props.isEditable) {
        event.preventDefault();
    }
};
</script>

<template>
    <footer
        :class="['site-footer', `site-footer-${variant}`]"
        :style="surfaceStyle"
    >
        <div class="footer-shell">
            <template v-for="module in moduleOrder" :key="module">
                <section
                    v-if="module === 'brand' && footer.brand?.show !== false"
                    class="footer-module footer-brand"
                >
                    <img
                        v-if="footer.brand?.imageUrl"
                        :src="footer.brand.imageUrl"
                        :alt="footer.brand.alt || `${tenantName} logo`"
                        class="footer-logo"
                    />
                    <strong v-else>{{
                        footer.brand?.title || tenantName
                    }}</strong>
                    <p v-if="footer.brand?.description">
                        {{ footer.brand.description }}
                    </p>
                </section>

                <section
                    v-else-if="
                        module === 'links' &&
                        footer.showLinks !== false &&
                        footer.linkGroups?.length
                    "
                    class="footer-module footer-links"
                >
                    <div
                        v-for="group in footer.linkGroups"
                        :key="group.id"
                        class="footer-link-group"
                    >
                        <strong>{{ group.label }}</strong>
                        <nav :aria-label="`${group.label} footer links`">
                            <a
                                v-for="link in group.links"
                                :key="link.id || link.label"
                                :href="getLinkUrl(link)"
                                :target="
                                    link.type === 'external'
                                        ? '_blank'
                                        : undefined
                                "
                                :rel="
                                    link.type === 'external'
                                        ? 'noopener noreferrer'
                                        : undefined
                                "
                                @click="keepPreviewInPlace"
                            >
                                {{ link.label }}
                            </a>
                        </nav>
                    </div>
                </section>

                <section
                    v-else-if="
                        module === 'newsletter' && footer.newsletter?.show
                    "
                    class="footer-module footer-newsletter"
                >
                    <span
                        v-if="footer.newsletter.eyebrow"
                        class="footer-kicker"
                    >
                        {{ footer.newsletter.eyebrow }}
                    </span>
                    <strong>{{
                        footer.newsletter.heading || 'Stay in the loop'
                    }}</strong>
                    <p v-if="footer.newsletter.body">
                        {{ footer.newsletter.body }}
                    </p>
                    <a
                        :href="footer.newsletter.buttonUrl || '/contact'"
                        class="footer-action"
                        @click="keepPreviewInPlace"
                    >
                        {{ footer.newsletter.buttonLabel || 'Get updates' }}
                        <ArrowRight :size="15" />
                    </a>
                </section>

                <section
                    v-else-if="
                        module === 'social' &&
                        footer.showSocial !== false &&
                        footer.socialLinks?.length
                    "
                    class="footer-module footer-social"
                    aria-label="Social links"
                >
                    <a
                        v-for="social in footer.socialLinks"
                        :key="social.id"
                        :href="social.href || '#'"
                        target="_blank"
                        rel="noopener noreferrer"
                        @click="keepPreviewInPlace"
                    >
                        {{ social.label }}
                    </a>
                </section>

                <section
                    v-else-if="
                        module === 'copyright' && footer.showCopyright !== false
                    "
                    class="footer-module footer-copyright"
                >
                    <p>{{ copyrightText }}</p>
                </section>
            </template>
        </div>
    </footer>
</template>

<style scoped>
.site-footer {
    box-sizing: border-box;
    width: 100%;
    color: var(--footer-text);
    background: var(--footer-background);
    border-top: 1px solid color-mix(in srgb, currentColor 12%, transparent);
    font-family: var(--theme-font-body, sans-serif);
}

.footer-shell {
    display: grid;
    width: min(100%, var(--footer-content-width));
    padding: clamp(3rem, 7vw, 6.5rem) clamp(1.25rem, 4vw, 3rem) 2rem;
    margin-inline: auto;
    gap: clamp(2rem, 5vw, 5rem);
}

.site-footer-columns .footer-shell,
.site-footer-newsletter .footer-shell,
.site-footer-editorial .footer-shell {
    grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.8fr);
    align-items: start;
}

.site-footer-editorial .footer-shell {
    grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
}

.footer-module {
    min-width: 0;
}

.footer-brand strong,
.footer-newsletter > strong {
    display: block;
    max-width: 14ch;
    font-family: var(--theme-font-heading, sans-serif);
    font-size: clamp(1.6rem, 3.5vw, 3.25rem);
    line-height: 1;
    letter-spacing: -0.035em;
}

.footer-brand p,
.footer-newsletter p {
    max-width: 42ch;
    margin: 1rem 0 0;
    font-size: 0.88rem;
    line-height: 1.7;
    opacity: 0.68;
}

.footer-logo {
    display: block;
    width: min(180px, 100%);
    max-height: 70px;
    object-fit: contain;
    object-position: left center;
}

.footer-links {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 2rem;
}

.footer-link-group > strong,
.footer-kicker {
    display: block;
    font-size: 0.68rem;
    font-weight: 750;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.footer-link-group nav {
    display: grid;
    margin-top: 1rem;
    gap: 0.72rem;
}

.footer-link-group a,
.footer-social a {
    color: inherit;
    font-size: 0.82rem;
    text-decoration: none;
    opacity: 0.68;
    transition: opacity 150ms ease;
}

.footer-link-group a:hover,
.footer-social a:hover {
    opacity: 1;
}

.footer-newsletter > strong {
    margin-top: 0.8rem;
}

.footer-action {
    display: inline-flex;
    align-items: center;
    padding: 0.75rem 1rem;
    margin-top: 1.25rem;
    gap: 0.5rem;
    color: var(--theme-bg, #ffffff);
    background: var(--theme-primary, #18181b);
    border-radius: var(--theme-border-radius, 8px);
    font-size: 0.78rem;
    font-weight: 700;
    text-decoration: none;
}

.footer-social {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.footer-copyright {
    padding-top: 1.5rem;
    border-top: 1px solid color-mix(in srgb, currentColor 12%, transparent);
}

.footer-copyright p {
    margin: 0;
    font-size: 0.72rem;
    opacity: 0.55;
}

.site-footer-minimal .footer-shell {
    grid-template-columns: 1fr;
    padding-block: 2rem;
    gap: 1.25rem;
    text-align: center;
}

.site-footer-minimal .footer-brand strong,
.site-footer-minimal .footer-brand p {
    margin-inline: auto;
}

.site-footer-minimal .footer-social {
    justify-content: center;
}

@container (max-width: 720px) {
    .site-footer-columns .footer-shell,
    .site-footer-newsletter .footer-shell,
    .site-footer-editorial .footer-shell {
        grid-template-columns: 1fr;
    }

    .footer-links {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 720px) {
    .site-footer-columns .footer-shell,
    .site-footer-newsletter .footer-shell,
    .site-footer-editorial .footer-shell {
        grid-template-columns: 1fr;
    }

    .footer-links {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (prefers-reduced-motion: reduce) {
    .footer-link-group a,
    .footer-social a {
        transition: none;
    }
}
</style>
