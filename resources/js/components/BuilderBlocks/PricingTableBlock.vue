<script setup>
import { inject, computed } from 'vue';

const props = defineProps({
  nodeId: { type: String, required: true },
  blockProps: { type: Object, default: () => ({}) },
});

const isEditable = inject('isEditable', false);

const plansList = computed(() => {
  return props.blockProps.plans || [];
});

const getFeatures = (featuresString) => {
  if (!featuresString) return [];
  return featuresString.split(',').map((f) => f.trim()).filter(Boolean);
};
</script>

<template>
  <div class="pricing-block-wrapper" :style="{ color: 'var(--theme-text, #0f172a)' }">
    <div v-if="plansList.length" class="plans-grid">
      <div
        v-for="(plan, index) in plansList"
        :key="index"
        class="plan-card"
        :class="{ 'plan-card-popular': plan.isPopular === 'yes' }"
        :style="{
          borderRadius: 'var(--theme-border-radius, 8px)',
          borderColor: plan.isPopular === 'yes' ? 'var(--theme-primary, #6366f1)' : 'rgba(255,255,255,0.06)'
        }"
      >
        <span v-if="plan.isPopular === 'yes'" class="popular-badge">POPULAR</span>

        <h3 class="plan-title">{{ plan.title || 'Plan Name' }}</h3>
        
        <div class="plan-price-wrapper">
          <span class="plan-price" :style="{ color: plan.isPopular === 'yes' ? 'var(--theme-primary, #6366f1)' : 'var(--theme-text)' }">
            {{ plan.price || '$0' }}
          </span>
          <span class="plan-period">{{ plan.period || '/mo' }}</span>
        </div>

        <ul class="plan-features">
          <li v-for="(feat, fIdx) in getFeatures(plan.features)" :key="fIdx">
            <svg xmlns="http://www.w3.org/2000/svg" class="check-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            {{ feat }}
          </li>
        </ul>

        <a
          v-if="plan.ctaText"
          :href="isEditable ? 'javascript:void(0)' : (plan.ctaUrl || '#')"
          class="plan-cta-btn"
          :class="plan.isPopular === 'yes' ? 'cta-popular' : 'cta-standard'"
          :style="{
            borderRadius: 'var(--theme-border-radius, 6px)',
            backgroundColor: plan.isPopular === 'yes' ? 'var(--theme-primary, #6366f1)' : 'transparent',
            borderColor: 'var(--theme-primary, #6366f1)'
          }"
        >
          {{ plan.ctaText }}
        </a>
      </div>
    </div>

    <div v-else-if="isEditable" class="pricing-placeholder">
      Add pricing plans in the sidebar inspector.
    </div>
  </div>
</template>

<style scoped>
.pricing-block-wrapper {
  width: 100%;
}

.plans-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.5rem;
}

.plan-card {
  position: relative;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.06);
  padding: 2.25rem 1.75rem;
  display: flex;
  flex-direction: column;
  box-shadow: 0 10px 30px -15px rgba(0, 0, 0, 0.2);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.plan-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.3);
}

.plan-card-popular {
  border-width: 2px;
  background: rgba(255, 255, 255, 0.03);
}

.popular-badge {
  position: absolute;
  top: 1rem;
  right: 1rem;
  font-size: 0.625rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  padding: 0.25rem 0.6rem;
  border-radius: 9999px;
  background: var(--theme-primary, #6366f1);
  color: #ffffff;
}

.plan-title {
  font-family: var(--theme-font-heading, sans-serif);
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0 0 1rem 0;
}

.plan-price-wrapper {
  display: flex;
  align-items: baseline;
  gap: 0.25rem;
  margin-bottom: 1.5rem;
}

.plan-price {
  font-family: var(--theme-font-heading, sans-serif);
  font-size: 2.25rem;
  font-weight: 800;
}

.plan-period {
  font-family: var(--theme-font-body, sans-serif);
  font-size: 0.875rem;
  opacity: 0.6;
}

.plan-features {
  list-style: none;
  padding: 0;
  margin: 0 0 2rem 0;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  flex-grow: 1;
}

.plan-features li {
  font-family: var(--theme-font-body, sans-serif);
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  opacity: 0.85;
}

.check-icon {
  width: 1rem;
  height: 1rem;
  color: var(--theme-primary, #6366f1);
  flex-shrink: 0;
}

.plan-cta-btn {
  display: block;
  text-align: center;
  text-decoration: none;
  font-family: var(--theme-font-body, sans-serif);
  font-size: 0.875rem;
  font-weight: 700;
  padding: 0.75rem 1rem;
  transition: all 0.15s ease;
  border: 1px solid transparent;
}

.cta-popular {
  color: #ffffff;
}

.cta-popular:hover {
  filter: brightness(1.1);
}

.cta-standard {
  color: var(--theme-primary, #6366f1);
}

.cta-standard:hover {
  background: rgba(99, 102, 241, 0.08);
}

.pricing-placeholder {
  font-family: var(--theme-font-body, sans-serif);
  text-align: center;
  padding: 2rem;
  border: 2px dashed rgba(255, 255, 255, 0.15);
  border-radius: 6px;
  color: var(--theme-text);
  opacity: 0.6;
}
</style>
