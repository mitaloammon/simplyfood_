import { api } from '../../shared/api/client';
export async function getCurrentShift() {
    const response = await api.get('/cash/current');
    return response.data.data;
}
export async function getCashHistory() {
    const response = await api.get('/cash/history', { params: { per_page: 100 } });
    return response.data.data;
}
export async function openCash(cash_register_id, opening_balance) {
    const response = await api.post('/cash/open', { cash_register_id, opening_balance });
    return response.data.data;
}
export async function addCashMovement(type, amount, description) {
    await api.post('/cash/movements', { type, amount, description: description || null });
}
export async function closeCash(closing_balance, notes) {
    const response = await api.post('/cash/close', {
        closing_balance,
        notes: notes || null,
    });
    return response.data.data;
}
