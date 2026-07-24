<script setup lang="ts">
import { ref } from 'vue';
import { useAuthStore } from '@/shared/stores/auth';

const authStore = useAuthStore();

const metrics = ref([
  { title: 'Clientes Cadastrados', value: '1.248', change: '+12% este mês', icon: '✎', color: '#3182ce' },
  { title: 'Pedidos Hoje', value: '42', change: '+8% em relação a ontem', icon: '☑︎', color: '#38a169' },
  { title: 'Faturamento Diário', value: 'R$ 2.450,00', change: '+15% em relação a ontem', icon: '$', color: '#dd6b20' },
  { title: 'Tempo Médio Entrega', value: '28 min', change: '-3 min em relação a ontem', icon: '⏱︎', color: '#e53e3e' }
]);
</script>

<template>
  <div class="dashboard-container">
    <div class="dashboard-header">
      <h1 class="welcome-title">Bem-vindo de volta, <span>{{ authStore.user?.name || 'Operador' }}</span>!</h1>
      <p class="welcome-subtitle">Aqui está um resumo do SimplyFood para o dia de hoje.</p>
    </div>

    <div class="metrics-grid">
      <div v-for="(metric, idx) in metrics" :key="idx" class="metric-card">
        <div class="metric-icon-wrapper" :style="{ backgroundColor: metric.color + '15', color: metric.color }">
          {{ metric.icon }}
        </div>
        <div class="metric-content">
          <p class="metric-title">{{ metric.title }}</p>
          <p class="metric-value">{{ metric.value }}</p>
          <p class="metric-change">{{ metric.change }}</p>
        </div>
      </div>
    </div>

    <div class="quick-actions-section">
      <h2 class="section-title">Ações Rápidas</h2>
      <div class="actions-grid">
        <router-link to="/customers" class="action-card">
          <div class="action-icon">↘︎</div>
          <div class="action-details">
            <h3>Cadastrar Novo Cliente</h3>
            <p>Adicione um novo cliente e suas informações de entrega.</p>
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
  font-family: 'Outfit', sans-serif;
  font-size: 2.2rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 0.35rem 0;
}

.welcome-title span {
  background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.welcome-subtitle {
  font-family: 'Inter', sans-serif;
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

.metric-card:hover {
  transform: translateY(-4px);
  border-color: rgba(255, 255, 255, 0.15);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.metric-icon-wrapper {
  font-size: 1.5rem;
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.metric-content {
  font-family: 'Inter', sans-serif;
}

.metric-title {
  font-size: 0.85rem;
  font-weight: 500;
  color: #a0aec0;
  margin: 0 0 0.5rem 0;
}

.metric-value {
  font-family: 'Outfit', sans-serif;
  font-size: 1.6rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 0.35rem 0;
}

.metric-change {
  font-size: 0.75rem;
  color: #48bb78;
  margin: 0;
}

.quick-actions-section {
  font-family: 'Inter', sans-serif;
}

.section-title {
  font-family: 'Outfit', sans-serif;
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

.action-icon {
  font-size: 1.8rem;
}

.action-details h3 {
  font-family: 'Outfit', sans-serif;
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
