import { api } from '../../shared/api/client';
export async function listOrders(status = '') {
    const response = await api.get('/orders', {
        params: { status: status || undefined, per_page: 100 },
    });
    return response.data.data;
}
export async function createOrder(payload) {
    const response = await api.post('/orders', payload);
    return response.data.data;
}
export async function addOrderItem(orderId, payload) {
    const response = await api.post('/orders/' + orderId + '/items', payload);
    return response.data.data;
}
export async function updateOrderStatus(orderId, status) {
    const response = await api.patch('/orders/' + orderId + '/status', { status });
    return response.data.data;
}
export async function addPayment(orderId, payment_method, amount) {
    await api.post('/orders/' + orderId + '/payments', { payment_method, amount });
}
