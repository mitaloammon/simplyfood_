<script setup lang="ts">
import axios from 'axios'
import { onMounted, ref } from 'vue'
import { apiErrorMessage } from '../../shared/api/errors'
import { money } from '../../shared/formatters'
import type { Category, Product } from '../../shared/types/api'
import {
  createCategory,
  createProduct,
  deleteCategory,
  deleteProduct,
  listCategories,
  listProducts,
  updateCategory,
  updateProduct,
  type CategoryPayload,
  type ProductPayload,
} from './catalog.service'

const categories = ref<Category[]>([])
const products = ref<Product[]>([])
const query = ref('')
const error = ref('')
const loading = ref(false)
const editingCategoryId = ref<string | null>(null)
const editingProductId = ref<string | null>(null)
const categoryForm = ref<CategoryPayload>({ name: '', sort_order: 0, active: true })
const productForm = ref<ProductPayload>(emptyProduct())

function emptyProduct(): ProductPayload {
  return {
    category_id: null,
    name: '',
    description: null,
    price: 0,
    cost_price: 0,
    sku: null,
    is_available: true,
    preparation_time_minutes: 0,
  }
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    const [categoryPage, productPage] = await Promise.all([listCategories(), listProducts(query.value)])
    categories.value = categoryPage.data
    products.value = productPage.data
  } catch (exception) {
    error.value = apiErrorMessage(exception)
  } finally {
    loading.value = false
  }
}

function editCategory(category: Category) {
  editingCategoryId.value = category.id
  categoryForm.value = { name: category.name, sort_order: category.sort_order, active: category.active }
}

async function saveCategory() {
  try {
    if (editingCategoryId.value) await updateCategory(editingCategoryId.value, categoryForm.value)
    else await createCategory(categoryForm.value)
    editingCategoryId.value = null
    categoryForm.value = { name: '', sort_order: 0, active: true }
    await load()
  } catch (exception) {
    error.value = apiErrorMessage(exception)
  }
}

function editProduct(product: Product) {
  editingProductId.value = product.id
  productForm.value = {
    category_id: product.category_id,
    name: product.name,
    description: product.description,
    price: Number(product.price),
    cost_price: Number(product.cost_price),
    sku: product.sku,
    is_available: product.is_available,
    preparation_time_minutes: product.preparation_time_minutes,
  }
}

async function saveProduct() {
  try {
    if (editingProductId.value) await updateProduct(editingProductId.value, productForm.value)
    else await createProduct(productForm.value)
    editingProductId.value = null
    productForm.value = emptyProduct()
    await load()
  } catch (exception) {
    error.value = apiErrorMessage(exception)
  }
}

async function removeCategory(category: Category) {
  if (!window.confirm('Remover a categoria ' + category.name + '?')) return
  error.value = ''
  try {
    await deleteCategory(category.id)
    await load()
  } catch (exception) {
    if (axios.isAxiosError(exception) && exception.response?.status === 409) {
      error.value = apiErrorMessage(exception)
      return
    }

    error.value = apiErrorMessage(exception)
  }
}

async function removeProduct(product: Product) {
  if (!window.confirm('Remover o produto ' + product.name + '?')) return
  try {
    await deleteProduct(product.id)
    await load()
  } catch (exception) {
    error.value = apiErrorMessage(exception)
  }
}

onMounted(load)
</script>

<template>
  <section>
    <div class="mb-6">
      <h1 class="page-title">Produtos e categorias</h1>
      <p class="mt-1 text-sm text-slate-500">Catálogo disponível para ADMIN e MANAGER.</p>
    </div>
    <p v-if="error" class="mb-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ error }}</p>

    <div class="grid gap-6 xl:grid-cols-[340px_1fr]">
      <div class="space-y-6">
        <form class="card space-y-4" @submit.prevent="saveCategory">
          <div class="flex justify-between">
            <h2 class="font-bold">{{ editingCategoryId ? 'Editar categoria' : 'Nova categoria' }}</h2>
            <button v-if="editingCategoryId" type="button" class="text-sm underline" @click="editingCategoryId = null">Cancelar</button>
          </div>
          <label><span class="label">Nome</span><input v-model="categoryForm.name" class="field" required /></label>
          <label><span class="label">Ordem</span><input v-model.number="categoryForm.sort_order" class="field" type="number" min="0" /></label>
          <label class="flex items-center gap-2 text-sm"><input v-model="categoryForm.active" type="checkbox" /> Ativa</label>
          <button class="btn-primary w-full">Salvar categoria</button>
        </form>

        <div class="card">
          <h2 class="mb-3 font-bold">Categorias</h2>
          <div class="space-y-2">
            <div v-for="category in categories" :key="category.id" class="flex items-center gap-2 rounded-lg bg-slate-50 p-3">
              <div class="min-w-0 flex-1">
                <p class="truncate font-semibold">{{ category.name }}</p>
                <p class="text-xs text-[#44403c]">ordem {{ category.sort_order }} · {{ category.active ? 'ativa' : 'inativa' }}</p>
              </div>
              <button class="text-xs underline" @click="editCategory(category)">Editar</button>
              <button class="text-xs text-red-600 underline" @click="removeCategory(category)">Excluir</button>
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <form class="card grid gap-4 sm:grid-cols-2" @submit.prevent="saveProduct">
          <div class="flex justify-between sm:col-span-2">
            <h2 class="font-bold">{{ editingProductId ? 'Editar produto' : 'Novo produto' }}</h2>
            <button v-if="editingProductId" type="button" class="text-sm underline" @click="editingProductId = null; productForm = emptyProduct()">Cancelar</button>
          </div>
          <label><span class="label">Nome</span><input v-model="productForm.name" class="field" required /></label>
          <label><span class="label">SKU</span><input v-model="productForm.sku" class="field" /></label>
          <label><span class="label">Categoria</span><select v-model="productForm.category_id" class="field"><option :value="null">Sem categoria</option><option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option></select></label>
          <label><span class="label">Preço</span><input v-model.number="productForm.price" class="field" type="number" min="0" step="0.01" required /></label>
          <label><span class="label">Custo</span><input v-model.number="productForm.cost_price" class="field" type="number" min="0" step="0.01" /></label>
          <label><span class="label">Preparo (min)</span><input v-model.number="productForm.preparation_time_minutes" class="field" type="number" min="0" /></label>
          <label class="sm:col-span-2"><span class="label">Descrição</span><textarea v-model="productForm.description" class="field" rows="2" /></label>
          <label class="flex items-center gap-2 text-sm"><input v-model="productForm.is_available" type="checkbox" /> Disponível</label>
          <button class="btn-primary sm:justify-self-end">Salvar produto</button>
        </form>

        <form class="flex gap-2" @submit.prevent="load">
          <input v-model="query" class="field" placeholder="Buscar produto" />
          <button class="btn-secondary" :disabled="loading">Buscar</button>
        </form>

        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Produto</th><th>Preço</th><th>Disponível</th><th></th></tr></thead>
            <tbody>
              <tr v-for="product in products" :key="product.id">
                <td><p class="font-semibold">{{ product.name }}</p><p class="text-xs text-[#44403c]">{{ product.sku || 'sem SKU' }}</p></td>
                <td>{{ money(product.price) }}</td>
                <td><span class="badge">{{ product.is_available ? 'Sim' : 'Não' }}</span></td>
                <td class="whitespace-nowrap text-right"><button class="btn-secondary mr-2" @click="editProduct(product)">Editar</button><button class="btn-danger" @click="removeProduct(product)">Remover</button></td>
              </tr>
              <tr v-if="!products.length"><td colspan="4" class="text-center !py-10 text-[#44403c]">Nenhum produto encontrado.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</template>
