import { api } from '../../shared/api/client';
export async function listCategories() {
    const response = await api.get('/categories', { params: { per_page: 100 } });
    return response.data.data;
}
export async function createCategory(payload) {
    const response = await api.post('/categories', payload);
    return response.data.data;
}
export async function updateCategory(id, payload) {
    const response = await api.patch('/categories/' + id, payload);
    return response.data.data;
}
export async function deleteCategory(id) {
    await api.delete('/categories/' + id);
}
export async function listProducts(q = '') {
    const response = await api.get('/products', {
        params: { q: q || undefined, per_page: 100 },
    });
    return response.data.data;
}
export async function createProduct(payload) {
    const response = await api.post('/products', payload);
    return response.data.data;
}
export async function updateProduct(id, payload) {
    const response = await api.patch('/products/' + id, payload);
    return response.data.data;
}
export async function deleteProduct(id) {
    await api.delete('/products/' + id);
}
