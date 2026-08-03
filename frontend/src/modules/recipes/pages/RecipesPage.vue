<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { RecipeApi, type IngredientDto, type RecipeDto } from '../api/RecipeApi';

const api = new RecipeApi();
const loading = ref(false);
const ingredients = ref<IngredientDto[]>([]);
const recipes = ref<RecipeDto[]>([]);

const loadData = async () => {
  loading.value = true;
  try {
    const [ingredientsResponse, recipesResponse] = await Promise.all([
      api.getIngredients(),
      api.getRecipes(),
    ]);

    ingredients.value = ingredientsResponse.data.data || [];
    recipes.value = recipesResponse.data.data || [];
  } finally {
    loading.value = false;
  }
};

onMounted(loadData);
</script>

<template>
  <section class="space-y-4">
    <header>
      <h1 class="text-2xl font-semibold text-slate-100">Fichas Tecnicas</h1>
      <p class="text-sm text-slate-300">Controle de ingredientes e baixa automatica por receita.</p>
    </header>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2" :aria-busy="loading">
      <article class="rounded-xl border border-slate-700 bg-slate-900 p-4">
        <p class="text-sm text-slate-300">Ingredientes cadastrados</p>
        <p class="text-lg font-semibold text-slate-100">{{ ingredients.length }}</p>
      </article>
      <article class="rounded-xl border border-slate-700 bg-slate-900 p-4">
        <p class="text-sm text-slate-300">Receitas cadastradas</p>
        <p class="text-lg font-semibold text-slate-100">{{ recipes.length }}</p>
      </article>
    </div>
  </section>
</template>
