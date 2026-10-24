import { api } from '../../shared/api/client';
export async function listTables() {
    const response = await api.get('/tables', { params: { per_page: 100 } });
    return response.data.data;
}
export async function createTable(number, capacity) {
    const response = await api.post('/tables', { number, capacity });
    return response.data.data;
}
export async function updateTableStatus(id, status) {
    const response = await api.patch('/tables/' + id + '/status', { status });
    return response.data.data;
}
export async function listCommands() {
    const response = await api.get('/commands', { params: { per_page: 100 } });
    return response.data.data;
}
export async function openCommand(code, tableId) {
    const response = await api.post('/commands', { code, table_id: tableId });
    return response.data.data;
}
export async function updateCommandStatus(id, status) {
    const response = await api.patch('/commands/' + id + '/status', { status });
    return response.data.data;
}
