import { api } from '../../shared/api/client'
import type { ApiEnvelope, Command, CommandStatus, DiningTable, Paginated, TableStatus } from '../../shared/types/api'

export async function listTables(): Promise<Paginated<DiningTable>> {
  const response = await api.get<ApiEnvelope<Paginated<DiningTable>>>('/tables', { params: { per_page: 100 } })
  return response.data.data
}

export async function createTable(number: number, capacity: number): Promise<DiningTable> {
  const response = await api.post<ApiEnvelope<DiningTable>>('/tables', { number, capacity })
  return response.data.data
}

export async function updateTableStatus(id: string, status: TableStatus): Promise<DiningTable> {
  const response = await api.patch<ApiEnvelope<DiningTable>>('/tables/' + id + '/status', { status })
  return response.data.data
}

export async function listCommands(): Promise<Paginated<Command>> {
  const response = await api.get<ApiEnvelope<Paginated<Command>>>('/commands', { params: { per_page: 100 } })
  return response.data.data
}

export async function openCommand(code: string, tableId: string): Promise<Command> {
  const response = await api.post<ApiEnvelope<Command>>('/commands', { code, table_id: tableId })
  return response.data.data
}

export async function updateCommandStatus(id: string, status: Exclude<CommandStatus, 'FREE'>): Promise<Command> {
  const response = await api.patch<ApiEnvelope<Command>>('/commands/' + id + '/status', { status })
  return response.data.data
}
