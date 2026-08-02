<script setup lang="ts">
import { useToastStore } from '@/shared/stores/toast';

const toastStore = useToastStore();

const toneClasses = {
  success: 'border-emerald-500/30 bg-emerald-950/80 text-emerald-100',
  error: 'border-rose-500/30 bg-rose-950/80 text-rose-100',
  info: 'border-sky-500/30 bg-sky-950/80 text-sky-100',
} as const;
</script>

<template>
  <div class="pointer-events-none fixed right-4 top-4 z-[120] flex w-[min(420px,calc(100vw-2rem))] flex-col gap-3">
    <transition-group name="toast-slide">
      <article
        v-for="toast in toastStore.toasts"
        :key="toast.id"
        class="pointer-events-auto rounded-xl border px-4 py-3 shadow-2xl backdrop-blur"
        :class="toneClasses[toast.type]"
      >
        <div class="flex items-start justify-between gap-3">
          <div>
            <p class="text-sm font-semibold">{{ toast.title }}</p>
            <p class="mt-1 text-sm leading-5 opacity-90">{{ toast.message }}</p>
          </div>

          <button type="button" class="text-sm opacity-80 transition hover:opacity-100" @click="toastStore.dismiss(toast.id)">
            Fechar
          </button>
        </div>
      </article>
    </transition-group>
  </div>
</template>

<style scoped>
.toast-slide-enter-active,
.toast-slide-leave-active {
  transition: all 0.22s ease;
}

.toast-slide-enter-from,
.toast-slide-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>