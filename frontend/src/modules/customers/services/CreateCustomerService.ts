import { CustomerApi } from '../api/CustomerApi';
import type { CreateCustomerDto } from '../api/CustomerApi';
import { CustomerEntity } from '@/shared/domain/entities/CustomerEntity';
import { CustomerMapper } from '@/shared/domain/mappers/CustomerMapper';

export class CreateCustomerService {
  private readonly api: CustomerApi;

  constructor(api: CustomerApi) {
    this.api = api;
  }

  async execute(dto: CreateCustomerDto): Promise<CustomerEntity> {
    // 1. Business rule: Check if a customer with this WhatsApp already exists
    const existing = await this.api.findByWhatsapp(dto.whatsapp);
    if (existing && existing.data && existing.data.data && Array.isArray(existing.data.data) && existing.data.data.length > 0) {
      throw new Error('Customer already exists with this whatsapp.');
    }

    // 2. Submit customer details to API
    const response = await this.api.create(dto);
    const rawCustomer = response.data.data || response.data;
    
    // 3. Map infrastructure response into rich domain entity
    return CustomerMapper.fromApi(rawCustomer);
  }
}
