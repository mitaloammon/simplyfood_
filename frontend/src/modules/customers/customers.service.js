import { api } from '../../shared/api/client';
export async function listCustomers(q = '') {
    const response = await api.get('/customers', {
        params: { q: q || undefined, per_page: 100 },
    });
    return response.data.data;
}
export async function createCustomer(payload) {
    const response = await api.post('/customers', payload);
    return response.data.data;
}
export async function updateCustomer(id, payload) {
    const response = await api.patch('/customers/' + id, payload);
    return response.data.data;
}
export async function deleteCustomer(id) {
    await api.delete('/customers/' + id);
}
