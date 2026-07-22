<script setup lang="ts">
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/shared/stores/auth';
import { AuthService } from '@/modules/auth/services/AuthService';

const router = useRouter();
const authStore = useAuthStore();
const authService = new AuthService();

const handleLogout = () => {
  authService.logout();
  router.push('/auth/login');
};
</script>

<template>
  <div class="dashboard-layout">
    <aside class="sidebar">
      <div class="sidebar-header">
        <h2 class="sidebar-logo">Simply<span>Food</span></h2>
      </div>

      <nav class="nav-menu">
        <router-link to="/dashboard" class="nav-item" active-class="active">
          <span class="nav-icon">📊</span>
          <span class="nav-label">Dashboard</span>
        </router-link>

        <router-link to="/customers" class="nav-item" active-class="active">
          <span class="nav-icon">👥</span>
          <span class="nav-label">Clientes</span>
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <div class="user-profile">
          <p class="user-name">{{ authStore.user?.name || 'Operador' }}</p>
          <p class="user-role">{{ authStore.user?.role || 'OPERATOR' }}</p>
        </div>
        <button class="btn-logout" @click="handleLogout">
          <span class="logout-icon">🚪</span>
          Sair
        </button>
      </div>
    </aside>

    <main class="main-content">
      <router-view />
    </main>
  </div>
</template>

<style scoped>
.dashboard-layout {
  display: flex;
  min-height: 100vh;
  background-color: #0f172a;
  color: #f8fafc;
}

.sidebar {
  width: 260px;
  background: rgba(15, 23, 42, 0.95);
  border-right: 1px solid rgba(255, 255, 255, 0.05);
  display: flex;
  flex-direction: column;
  padding: 2rem 1.5rem;
  box-sizing: border-box;
}

.sidebar-header {
  margin-bottom: 3rem;
}

.sidebar-logo {
  font-family: 'Outfit', sans-serif;
  font-size: 1.8rem;
  font-weight: 800;
  color: #ffffff;
  margin: 0;
}

.sidebar-logo span {
  background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.nav-menu {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  flex-grow: 1;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.85rem 1rem;
  border-radius: 12px;
  color: #94a3b8;
  text-decoration: none;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
  transition: all 0.3s ease;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.03);
  color: #ffffff;
}

.nav-item.active {
  background: rgba(255, 75, 43, 0.1);
  color: #ff4b2b;
  border: 1px solid rgba(255, 75, 43, 0.15);
}

.sidebar-footer {
  margin-top: auto;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.user-profile {
  font-family: 'Inter', sans-serif;
}

.user-name {
  font-weight: 600;
  font-size: 0.95rem;
  margin: 0;
  color: #ffffff;
}

.user-role {
  font-size: 0.8rem;
  color: #64748b;
  margin: 0.15rem 0 0 0;
}

.btn-logout {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: #ef4444;
  border-radius: 12px;
  cursor: pointer;
  font-family: 'Outfit', sans-serif;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-logout:hover {
  background: #ef4444;
  color: #ffffff;
}

.main-content {
  flex-grow: 1;
  padding: 2.5rem;
  overflow-y: auto;
  box-sizing: border-box;
}
</style>
