<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, Phone, ShoppingBag } from '@lucide/vue';

const props = defineProps({
    navigationConfig: { type: Object, default: () => ({}) },
    pages: { type: Array, default: () => [] },
    tenantName: { type: String, default: 'My Workspace' },
    isEditable: { type: Boolean, default: false },
    showCart: { type: Boolean, default: false },
    cartCount: { type: Number, default: 0 },
});

const emit = defineEmits(['open-cart', 'navigate-page']);

const page = usePage();

const headerItems = computed(() => props.navigationConfig?.header?.items || []);
const logoText = computed(() => props.tenantName);
const showLogo = computed(
    () => props.navigationConfig?.header?.showLogo !== false,
);
const ctaButton = computed(
    () => props.navigationConfig?.header?.ctaButton || {},
);

const getPageUrl = (slug) => {
    if (props.isEditable) {
        return undefined;
    }

    return slug === 'home' ? '/' : `/${slug}`;
};

const navigateInEditor = (slug) => {
    if (props.isEditable) {
        emit('navigate-page', slug);
    }
};

const normalizedPath = computed(() => {
    const path = page.url.split('?')[0].replace(/\/$/, '');

    return path || '/';
});

const isItemActive = (item) => {
    if (item.type === 'external') {
        return false;
    }

    if (props.isEditable) {
        return page.props.page?.slug === item.slug;
    }

    return (
        normalizedPath.value === (item.slug === 'home' ? '/' : `/${item.slug}`)
    );
};
</script>

<template>
    <header class="site-header">
        <div class="header-shell">
            <div v-if="showLogo" class="site-logo">
                <component
                    :is="isEditable ? 'button' : Link"
                    :type="isEditable ? 'button' : undefined"
                    :href="isEditable ? undefined : '/'"
                    class="logo-link"
                    @click="navigateInEditor('home')"
                >
                    {{ logoText }}
                </component>
            </div>

            <nav class="header-nav" aria-label="Primary navigation">
                <ul class="nav-list">
                    <li
                        v-for="(item, index) in headerItems"
                        :key="`${item.label}-${index}`"
                        class="nav-item"
                    >
                        <a
                            v-if="item.type === 'external'"
                            :href="item.href || '#'"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="nav-link"
                        >
                            {{ item.label }}
                        </a>

                        <component
                            :is="isEditable ? 'button' : Link"
                            :type="isEditable ? 'button' : undefined"
                            v-else
                            :href="getPageUrl(item.slug)"
                            :aria-current="
                                isItemActive(item) ? 'page' : undefined
                            "
                            :class="[
                                'nav-link',
                                { 'nav-link-active': isItemActive(item) },
                            ]"
                            @click="navigateInEditor(item.slug)"
                        >
                            {{ item.label }}
                            <span
                                v-if="isItemActive(item)"
                                class="active-dot"
                                aria-hidden="true"
                            ></span>
                        </component>
                    </li>
                </ul>
            </nav>

            <div v-if="ctaButton.show || showCart" class="header-actions">
                <button
                    v-if="showCart"
                    type="button"
                    class="action-button contact-action cart-action"
                    :aria-label="`Open shopping bag with ${cartCount} items`"
                    title="Shopping bag"
                    @click="emit('open-cart')"
                >
                    <ShoppingBag
                        :size="25"
                        :stroke-width="1.8"
                        aria-hidden="true"
                    />
                    <span v-if="cartCount" class="cart-count">{{
                        cartCount
                    }}</span>
                </button>
                <component
                    v-if="ctaButton.show"
                    :is="isEditable ? 'button' : Link"
                    :type="isEditable ? 'button' : undefined"
                    :href="getPageUrl(ctaButton.slug)"
                    class="action-button contact-action"
                    :aria-label="ctaButton.label || 'Contact us'"
                    :title="ctaButton.label || 'Contact us'"
                    @click="navigateInEditor(ctaButton.slug)"
                >
                    <Phone :size="30" :stroke-width="1.8" aria-hidden="true" />
                </component>

                <component
                    v-if="ctaButton.show"
                    :is="isEditable ? 'button' : Link"
                    :type="isEditable ? 'button' : undefined"
                    :href="getPageUrl(ctaButton.slug)"
                    class="action-button primary-action"
                    :aria-label="ctaButton.label || 'Continue'"
                    :title="ctaButton.label || 'Continue'"
                    @click="navigateInEditor(ctaButton.slug)"
                >
                    <ArrowRight
                        :size="34"
                        :stroke-width="1.8"
                        aria-hidden="true"
                    />
                </component>
            </div>
        </div>
    </header>
</template>

<style scoped>
.site-header {
    position: relative;
    z-index: 10;
    box-sizing: border-box;
    width: 100%;
    padding: 1.25rem 2rem;
    background: var(--theme-bg, #ffffff);
    font-family: var(--theme-font-body, sans-serif);
}

.header-shell {
    display: flex;
    align-items: center;
    width: min(100%, 1180px);
    min-height: 5.25rem;
    margin: 0 auto;
    padding: 0.65rem 0.7rem 0.65rem 1.75rem;
    gap: clamp(1.5rem, 4vw, 4rem);
    box-sizing: border-box;
    color: var(--theme-bg, #ffffff);
    background: var(--theme-text, #0f172a);
    border: 1px solid color-mix(in srgb, var(--theme-bg) 22%, transparent);
    border-radius: calc(var(--theme-border-radius, 8px) * 2.5);
    box-shadow: 0 18px 50px
        color-mix(in srgb, var(--theme-text) 22%, transparent);
}

.site-logo {
    flex: 0 0 auto;
    max-width: 14rem;
    overflow: hidden;
    font-family: var(--theme-font-heading, sans-serif);
    font-size: 1.05rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.logo-link {
    padding: 0;
    color: inherit;
    background: transparent;
    border: 0;
    font: inherit;
    text-decoration: none;
    cursor: pointer;
}

.header-nav {
    min-width: 0;
    margin-left: auto;
}

.nav-list {
    display: flex;
    align-items: center;
    padding: 0;
    margin: 0;
    gap: clamp(1.25rem, 2.6vw, 2.75rem);
    list-style: none;
}

.nav-item {
    display: flex;
    align-items: center;
}

.nav-link {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 1.1rem 0 1.35rem;
    color: color-mix(in srgb, var(--theme-bg) 58%, transparent);
    font-size: clamp(0.95rem, 1.6vw, 1.2rem);
    font-weight: 500;
    line-height: 1;
    text-decoration: none;
    white-space: nowrap;
    cursor: pointer;
    border: 0;
    background: transparent;
    font-family: inherit;
    transition:
        color 180ms ease,
        transform 180ms ease;
}

.nav-link:hover,
.nav-link-active {
    color: var(--theme-bg, #ffffff);
    transform: translateY(-1px);
}

.active-dot {
    position: absolute;
    bottom: 0.35rem;
    left: 50%;
    width: 0.45rem;
    height: 0.45rem;
    background: var(--theme-primary, #4f46e5);
    border-radius: 9999px;
    transform: translateX(-50%);
    box-shadow: 0 0 0 3px
        color-mix(in srgb, var(--theme-primary) 15%, transparent);
}

.header-actions {
    display: flex;
    flex: 0 0 auto;
    align-items: center;
    gap: 0.7rem;
}

.action-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 4rem;
    height: 4rem;
    box-sizing: border-box;
    color: var(--theme-bg, #ffffff);
    border-radius: calc(var(--theme-border-radius, 8px) * 1.5);
    cursor: pointer;
    text-decoration: none;
    transition:
        filter 180ms ease,
        transform 180ms ease;
}
.cart-action {
    position: relative;
}
.cart-count {
    position: absolute;
    top: -0.25rem;
    right: -0.25rem;
    display: grid;
    min-width: 1.25rem;
    height: 1.25rem;
    padding: 0 0.2rem;
    place-items: center;
    color: var(--theme-bg, #fff);
    background: var(--theme-primary, #4f46e5);
    border-radius: 9999px;
    font-size: 0.65rem;
}

.action-button:hover {
    filter: brightness(0.9);
    transform: translateY(-2px);
}

.contact-action {
    background: color-mix(in srgb, var(--theme-bg) 14%, transparent);
    border: 1px solid color-mix(in srgb, var(--theme-bg) 7%, transparent);
}

.primary-action {
    background: var(--theme-primary, #4f46e5);
    border: 1px solid color-mix(in srgb, var(--theme-bg) 16%, transparent);
}

@media (max-width: 900px) {
    .site-header {
        padding-inline: 1rem;
    }

    .header-shell {
        gap: 1.5rem;
    }

    .site-logo {
        max-width: 9rem;
    }

    .nav-list {
        gap: 1.25rem;
    }
}

@media (max-width: 720px) {
    .site-header {
        padding: 0.75rem;
    }

    .header-shell {
        min-height: 4.5rem;
        padding: 0.5rem 0.55rem 0.5rem 1.2rem;
        border-radius: calc(var(--theme-border-radius, 8px) * 2);
    }

    .header-nav {
        display: none;
    }

    .header-actions {
        margin-left: auto;
    }

    .action-button {
        width: 3.4rem;
        height: 3.4rem;
    }
}

@media (max-width: 420px) {
    .contact-action {
        display: none;
    }
}
</style>
