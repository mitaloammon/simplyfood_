import { api } from '../../shared/api/client';
export async function loginRequest(email, password) {
    const response = await api.post('/auth/login', { email, password });
    return response.data.data;
}
export async function logoutRequest() {
    await api.post('/auth/logout');
}
