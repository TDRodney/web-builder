<script setup>
import { computed } from 'vue';

const props = defineProps({
  navigationConfig: { type: Object, default: () => ({}) },
  tenantName: { type: String, default: 'My Workspace' },
});

const copyrightText = computed(() => {
  return props.navigationConfig?.footer?.copyright || `© ${new Date().getFullYear()} ${props.tenantName}. All rights reserved.`;
});
</script>

<template>
  <footer
    class="site-footer"
    :style="{
      backgroundColor: 'var(--theme-bg, #ffffff)',
      borderTop: '1px solid rgba(0, 0, 0, 0.08)',
      color: 'var(--theme-text, #0f172a)',
    }"
  >
    <div class="footer-container">
      <p class="copyright-p" :style="{ fontFamily: 'var(--theme-font-body, sans-serif)' }">
        {{ copyrightText }}
      </p>
    </div>
  </footer>
</template>

<style scoped>
.site-footer {
  width: 100%;
  padding: 2rem;
  box-sizing: border-box;
  margin-top: auto;
}

.footer-container {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: center;
  align-items: center;
}

.copyright-p {
  font-size: 0.8125rem;
  opacity: 0.6;
  margin: 0;
  text-align: center;
}
</style>
