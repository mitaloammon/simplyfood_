import { computed, ref } from 'vue';
import { defineStore } from 'pinia';

export type ToastType = 'success' | 'error' | 'info';

export type ToastItem = {
  id: number;
  type: ToastType;
  title: string;
  message: string;
};

let toastId = 0;

export const useToastStore = defineStore('toast', () => {
  const toasts = ref<ToastItem[]>([]);

  const hasToasts = computed(() => toasts.value.length > 0);

  const push = (type: ToastType, title: string, message: string, timeoutMs = 4000) => {
    const id = ++toastId;
    toasts.value.push({ id, type, title, message });

    if (timeoutMs > 0) {
      window.setTimeout(() => dismiss(id), timeoutMs);
    }

    return id;
  };

  const success = (message: string, title = 'Sucesso') => push('success', title, message);
  const error = (message: string, title = 'Erro') => push('error', title, message);
  const info = (message: string, title = 'Aviso') => push('info', title, message);

  const dismiss = (id: number) => {
    toasts.value = toasts.value.filter((toast) => toast.id !== id);
  };

  const clear = () => {
    toasts.value = [];
  };

  return {
    toasts,
    hasToasts,
    push,
    success,
    error,
    info,
    dismiss,
    clear,
  };
});