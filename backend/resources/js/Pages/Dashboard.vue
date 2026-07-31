<script setup>
import { computed } from 'vue';
import {
    BanknotesIcon,
    ClockIcon,
    ShoppingBagIcon,
    UsersIcon,
} from '@heroicons/vue/24/outline';

defineProps({
    user: {
        type: Object,
        required: true,
    },
    metrics: {
        type: Array,
        required: true,
    },
});

const iconMap = computed(() => ({
    users: UsersIcon,
    'shopping-bag': ShoppingBagIcon,
    banknotes: BanknotesIcon,
    clock: ClockIcon,
}));

const resolveIcon = (icon) => {
    return iconMap.value[icon] ?? UsersIcon;
};
</script>

<template>
    <main class="mx-auto min-h-screen max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <header class="mb-8 rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur">
            <h1 class="text-2xl font-semibold text-white sm:text-3xl">
                Bem-vindo de volta, <span class="text-orange-400">{{ user.name }}</span>
            </h1>
            <p class="mt-2 text-sm text-slate-300">
                Perfil atual: {{ user.role }}. Confira os indicadores consolidados de hoje.
            </p>
        </header>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article
                v-for="metric in metrics"
                :key="metric.key"
                class="rounded-2xl border border-white/10 bg-slate-900/70 p-5 shadow-lg shadow-black/20"
            >
                <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-orange-500/15 text-orange-300">
                    <component :is="resolveIcon(metric.icon)" class="h-6 w-6" />
                </div>
                <p class="text-sm text-slate-400">{{ metric.title }}</p>
                <p class="mt-2 text-2xl font-bold text-white">{{ metric.value }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ metric.description }}</p>
            </article>
        </section>
    </main>
</template>
