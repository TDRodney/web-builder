<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  navigationConfig: { type: Object, default: () => ({}) },
  pages: { type: Array, default: () => [] },
  tenantName: { type: String, default: 'My Workspace' },
  isEditable: { type: Boolean, default: false },
});

const headerItems = computed(() => {
  return props.navigationConfig?.header?.items || [];
});

const logoText = computed(() => {
  return props.tenantName;
});

const showLogo = computed(() => {
  return props.navigationConfig?.header?.showLogo !== false;
});

const ctaButton = computed(() => {
  return props.navigationConfig?.header?.ctaButton || {};
});

const getPageUrl = (slug) => {
  if (props.isEditable) {
    return 'javascript:void(0)';
  }
  return slug === 'home' ? '/' : `/${slug}`;
};
</script>

<template>
  <header
    class="site-header"
    :style="{
      backgroundColor: 'var(--theme-bg, #ffffff)',
      borderBottom: '1px solid rgba(0, 0, 0, 0.08)',
      color: 'var(--theme-text, #0f172a)',
    }"
  >
    <div class="header-container">
      <!-- Logo -->
      <div v-if="showLogo" class="site-logo">
        <component
          :is="isEditable ? 'span' : Link"
          :href="isEditable ? null : '/'"
          class="logo-link"
          :style="{ fontFamily: 'var(--theme-font-heading, sans-serif)', color: 'var(--theme-text)' }"
        >
          {{ logoText }}
        </component>
      </div>

      <!-- Nav Items -->
      <nav class="header-nav">
        <ul class="nav-list">
          <li v-for="(item, index) in headerItems" :key="index" class="nav-item">
            <template v-if="item.type === 'external'">
              <a
                :href="item.href || '#'"
                target="_blank"
                class="nav-link"
                :style="{ fontFamily: 'var(--theme-font-body, sans-serif)', color: 'var(--theme-text)' }"
              >
                {{ item.label }}
              </a>
            </template>
            <template v-else>
              <component
                :is="isEditable ? 'span' : Link"
                :href="isEditable ? null : getPageUrl(item.slug)"
                class="nav-link"
                :style="{ fontFamily: 'var(--theme-font-body, sans-serif)', color: 'var(--theme-text)' }"
              >
                {{ item.label }}
              </component>
            </template>
          </li>
        </ul>
      </nav>

      <!-- CTA Button -->
      <div v-if="ctaButton.show" class="header-cta">
        <component
          :is="isEditable ? 'span' : Link"
          :href="isEditable ? null : getPageUrl(ctaButton.slug)"
          class="cta-btn"
          :style="{
            borderRadius: 'var(--theme-border-radius, 6px)',
            backgroundColor: 'var(--theme-primary, #6366f1)',
            fontFamily: 'var(--theme-font-body, sans-serif)',
          }"
        >
          {{ ctaButton.label || 'Contact' }}
        </component>
      </div>
    </div>
  </header>
</template>

<style scoped>
.site-header {
  width: 100%;
  padding: 1rem 2rem;
  box-sizing: border-box;
  position: relative;
  z-index: 10;
}

.header-container {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
}

.site-logo {
  font-weight: 800;
  font-size: 1.25rem;
  letter-spacing: -0.02em;
}

.logo-link {
  text-decoration: none;
}

.header-nav {
  display: flex;
  align-items: center;
}

.nav-list {
  display: flex;
  list-style: none;
  padding: 0;
  margin: 0;
  gap: 2rem;
}

.nav-link {
  text-decoration: none;
  font-size: 0.875rem;
  font-weight: 600;
  opacity: 0.8;
  transition: opacity 0.15s, color 0.15s;
}

.nav-link:hover {
  opacity: 1;
  color: var(--theme-primary, #6366f1) !important;
}

.cta-btn {
  display: inline-block;
  text-decoration: none;
  color: #ffffff;
  padding: 0.5rem 1.25rem;
  font-size: 0.8125rem;
  font-weight: 700;
  transition: opacity 0.15s, filter 0.15s;
}

.cta-btn:hover {
  filter: brightness(1.1);
}

@media (max-width: 768px) {
  .header-nav {
    display: none;
  }
}
</style>
