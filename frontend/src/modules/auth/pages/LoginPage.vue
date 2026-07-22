<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { AuthService } from '../services/AuthService';
import BaseForm from '@/shared/components/BaseForm.vue';
import BaseInput from '@/shared/components/BaseInput.vue';
import BaseButton from '@/shared/components/BaseButton.vue';

const router = useRouter();
const authService = new AuthService();

const email = ref('');
const password = ref('');
const error = ref('');
const loading = ref(false);

const emailError = ref('');
const passwordError = ref('');

const validate = () => {
  let valid = true;
  emailError.value = '';
  passwordError.value = '';

  if (!email.value) {
    emailError.value = 'O e-mail é obrigatório.';
    valid = false;
  } else if (!/\S+@\S+\.\S+/.test(email.value)) {
    emailError.value = 'Formato de e-mail inválido.';
    valid = false;
  }

  if (!password.value) {
    passwordError.value = 'A senha é obrigatória.';
    valid = false;
  } else if (password.value.length < 6) {
    passwordError.value = 'A senha deve ter pelo menos 6 caracteres.';
    valid = false;
  }

  return valid;
};

const handleLogin = async () => {
  if (!validate()) return;

  loading.value = true;
  error.value = '';

  try {
    await authService.login({
      email: email.value,
      password: password.value,
    });
    router.push('/dashboard');
  } catch (err: any) {
    error.value = err.message || 'Credenciais inválidas. Tente novamente.';
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="login-card">
    <div class="login-header">
      <h1 class="brand-title">Simply<span>Food</span></h1>
      <p class="brand-subtitle">Gestão Inteligente de Food Delivery</p>
    </div>

    <div v-if="error" class="alert alert-error">
      {{ error }}
    </div>

    <BaseForm @submit="handleLogin">
      <BaseInput
        v-model="email"
        label="E-mail"
        type="email"
        placeholder="digite seu e-mail"
        :error="emailError"
        :disabled="loading"
      />

      <BaseInput
        v-model="password"
        label="Senha"
        type="password"
        placeholder="digite sua senha"
        :error="passwordError"
        :disabled="loading"
      />

      <div class="form-actions">
        <BaseButton type="submit" :loading="loading" variant="primary" class="w-full">
          Entrar no Sistema
        </BaseButton>
      </div>
    </BaseForm>
  </div>
</template>

<style scoped>
.login-card {
  width: 100%;
  max-width: 440px;
  background: rgba(26, 32, 44, 0.65);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
  padding: 2.5rem;
  backdrop-filter: blur(20px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.brand-title {
  font-family: 'Outfit', sans-serif;
  font-size: 2.5rem;
  font-weight: 800;
  letter-spacing: -0.05em;
  color: #ffffff;
  margin-bottom: 0.25rem;
}

.brand-title span {
  background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.brand-subtitle {
  font-family: 'Inter', sans-serif;
  font-size: 0.95rem;
  color: #a0aec0;
}

.alert {
  padding: 0.75rem 1rem;
  border-radius: 12px;
  font-family: 'Inter', sans-serif;
  font-size: 0.9rem;
  margin-bottom: 1.5rem;
  font-weight: 500;
  text-align: center;
}

.alert-error {
  background: rgba(229, 62, 62, 0.15);
  border: 1px solid rgba(229, 62, 62, 0.25);
  color: #fc8181;
}

.form-actions {
  margin-top: 1rem;
}

.w-full {
  width: 100%;
}
</style>
