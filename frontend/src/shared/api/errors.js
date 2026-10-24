import axios from 'axios';
export function apiErrorMessage(error) {
    if (!axios.isAxiosError(error))
        return 'Não foi possível concluir a operação.';
    const response = error.response?.data;
    if (!response)
        return 'Não foi possível conectar à API.';
    const validation = response.errors ? Object.values(response.errors).flat()[0] : null;
    return validation || response.message || 'Não foi possível concluir a operação.';
}
