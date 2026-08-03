import { apiClient } from '@/shared/api/client';

export interface IngredientDto {
  id: number;
  name: string;
  unit: string;
  current_stock: number;
  min_stock: number;
  active: boolean;
}

export interface RecipeDto {
  id: number;
  product_id: number;
  name: string;
  yield_quantity: number;
  active: boolean;
}

export class RecipeApi {
  async getIngredients() {
    return apiClient.get<{ status: string; data: IngredientDto[] }>('/ingredients');
  }

  async createIngredient(payload: Partial<IngredientDto> & { name: string; unit: string }) {
    return apiClient.post<{ status: string; data: IngredientDto }>('/ingredients', payload);
  }

  async getRecipes() {
    return apiClient.get<{ status: string; data: RecipeDto[] }>('/recipes');
  }

  async createRecipe(payload: { product_id: number; name: string; yield_quantity?: number; active?: boolean }) {
    return apiClient.post<{ status: string; data: RecipeDto }>('/recipes', payload);
  }

  async addItem(recipeId: number, payload: { ingredient_id: number; quantity: number }) {
    return apiClient.post(`/recipes/${recipeId}/items`, payload);
  }

  async consume(recipeId: number, multiplier = 1) {
    return apiClient.post(`/recipes/${recipeId}/consume`, { multiplier });
  }
}
