<script setup lang="ts">
import { computed } from 'vue';
import { useAuthStore } from '@/shared/stores/auth';
import { useDashboardMetrics } from '../composables/useDashboardMetrics';

const authStore = useAuthStore();
const { loading, errorMessage, metrics, user, loadMetrics } = useDashboardMetrics();

const welcomeName = computed(() => {
  const rawName = user.value?.name || authStore.user?.name || 'Operador';
  return rawName.charAt(0).toUpperCase() + rawName.slice(1);
});

const metricColorMap: Record<string, string> = {
  customers: '#3182ce',
  orders_today: '#38a169',
  revenue_today: '#dd6b20',
  delivery_avg: '#e53e3e',
};

const resolveMetricColor = (key: string): string => metricColorMap[key] || '#94a3b8';
</script>

<template>
  <div class="dashboard-container">
    <div class="dashboard-header">
      <h1 class="welcome-title">Bem-vindo de volta, <span>{{ welcomeName }}</span>!</h1>
      <p class="welcome-subtitle">Aqui está um resumo do SimplyFood para o dia de hoje.</p>
    </div>

    <div v-if="errorMessage" class="alert alert-error">
      <p>{{ errorMessage }}</p>
      <button class="retry-button" @click="loadMetrics">Tentar novamente</button>
    </div>

    <div class="metrics-grid" :aria-busy="loading">
      <div v-if="loading" v-for="idx in 4" :key="`skeleton-${idx}`" class="metric-card metric-card-skeleton">
        <div class="skeleton skeleton-dot"></div>
        <div class="metric-content">
          <div class="skeleton skeleton-line"></div>
          <div class="skeleton skeleton-value"></div>
        </div>
      </div>

      <div v-else v-for="metric in metrics" :key="metric.key" class="metric-card">
        <div class="metric-bullet" :style="{ backgroundColor: resolveMetricColor(metric.key) }"></div>
        <div class="metric-content">
          <p class="metric-title">{{ metric.title }}</p>
          <p class="metric-value">{{ metric.value }}</p>
          <p class="metric-description">{{ metric.description }}</p>
        </div>
      </div>
    </div>

    <div class="quick-actions-section">
      <h2 class="section-title">Ações Rápidas</h2>
      <div class="actions-grid">
        <router-link :to="{ path: '/customers', query: { action: 'create' } }" class="action-card">
          <div class="action-details">
            <h3>Novo Cliente</h3>
            <p>Inicia o fluxo de cadastro de um novo cliente.</p>
          </div>
        </router-link>

        <router-link :to="{ path: '/orders', query: { action: 'create' } }" class="action-card">
          <div class="action-details">
            <h3>Novo Pedido</h3>
            <p>Inicia o fluxo de criacao de um novo pedido.</p>
          </div>
        </router-link>
      </div>
    </div>
  </div>
</template>

<style scoped>
.dashboard-container {
  max-width: 1200px;
  margin: 0 auto;
  animation: fadeIn 0.5s ease-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.dashboard-header {
  margin-bottom: 2.5rem;
}

.welcome-title {
  font-family: inherit;
  font-size: 2.2rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 0.35rem 0;
}

.welcome-title span {
  background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.welcome-subtitle {
  font-family: inherit;
  font-size: 0.95rem;
  color: #a0aec0;
  margin: 0;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
  margin-bottom: 3rem;
}

@media (max-width: 1024px) {
  .metrics-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .metrics-grid {
    grid-template-columns: 1fr;
  }
}

.metric-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  padding: 1.5rem;
  display: flex;
  align-items: flex-start;
  gap: 1.25rem;
  backdrop-filter: blur(10px);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.metric-card-skeleton {
  pointer-events: none;
}

.metric-card:hover {
  transform: translateY(-4px);
  border-color: rgba(255, 255, 255, 0.15);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.metric-bullet {
  width: 12px;
  height: 12px;
  border-radius: 999px;
  margin-top: 0.3rem;
  flex-shrink: 0;
}

.skeleton {
  background: linear-gradient(90deg, rgba(148, 163, 184, 0.15) 25%, rgba(148, 163, 184, 0.35) 50%, rgba(148, 163, 184, 0.15) 75%);
  background-size: 200% 100%;
  animation: pulse 1.4s ease-in-out infinite;
}

@keyframes pulse {
  0% {
    background-position: 200% 0;
  }

  100% {
    background-position: -200% 0;
  }
}

.skeleton-dot {
  width: 12px;
  height: 12px;
  border-radius: 999px;
  margin-top: 0.3rem;
}

.skeleton-line {
  width: 145px;
  height: 14px;
  border-radius: 999px;
  margin-bottom: 0.55rem;
}

.skeleton-value {
  width: 100px;
  height: 24px;
  border-radius: 999px;
}

.alert {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.5rem;
  padding: 1rem 1.25rem;
  border-radius: 12px;
}

.alert-error {
  background: rgba(229, 62, 62, 0.15);
  border: 1px solid rgba(229, 62, 62, 0.25);
  color: #fc8181;
}

.retry-button {
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: rgba(15, 23, 42, 0.65);
  color: #f8fafc;
  border-radius: 10px;
  padding: 0.45rem 0.8rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.retry-button:hover {
  border-color: rgba(255, 255, 255, 0.24);
  background: rgba(30, 41, 59, 0.7);
}

.metric-content {
  font-family: inherit;
}

.metric-title {
  font-size: 0.85rem;
  font-weight: 500;
  color: #a0aec0;
  margin: 0 0 0.5rem 0;
}

.metric-value {
  font-family: inherit;
  font-size: 1.6rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 0.35rem 0;
}

.metric-description {
  font-size: 0.8rem;
  color: #94a3b8;
  margin: 0;
}

.quick-actions-section {
  font-family: inherit;
}

.section-title {
  font-family: inherit;
  font-size: 1.5rem;
  font-weight: 600;
  color: #ffffff;
  margin-bottom: 1.5rem;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

@media (max-width: 768px) {
  .actions-grid {
    grid-template-columns: 1fr;
  }
}

.action-card {
  background: rgba(255, 75, 43, 0.03);
  border: 1px solid rgba(255, 75, 43, 0.1);
  border-radius: 20px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  text-decoration: none;
  color: inherit;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.action-card:hover {
  transform: translateY(-4px);
  background: rgba(255, 75, 43, 0.08);
  border-color: #ff4b2b;
  box-shadow: 0 10px 20px rgba(255, 75, 43, 0.15);
}

.action-details h3 {
  font-family: inherit;
  font-size: 1.1rem;
  font-weight: 600;
  color: #ffffff;
  margin: 0 0 0.25rem 0;
}

.action-details p {
  font-size: 0.875rem;
  color: #a0aec0;
  margin: 0;
}
</style>
