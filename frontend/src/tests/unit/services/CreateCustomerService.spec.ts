import { describe, it, expect, vi, beforeEach } from 'vitest';
import { CreateCustomerService } from '@/modules/customers/services/CreateCustomerService';
import { CustomerApi } from '@/modules/customers/api/CustomerApi';
import { CustomerEntity } from '@/shared/domain/entities/CustomerEntity';

describe('CreateCustomerService', () => {
  let apiMock: any;
  let service: CreateCustomerService;

  beforeEach(() => {
    apiMock = {
      create: vi.fn(),
      findByWhatsapp: vi.fn(),
    };
    service = new CreateCustomerService(apiMock as unknown as CustomerApi);
  });

  it('creates a customer successfully when whatsapp is not registered', async () => {
    const dto = {
      name: 'João Silva',
      email: 'joao@test.com',
      whatsapp: '5511999999999',
      cpf_cnpj: '12345678901',
      cep: '01001000',
      address: 'Praça da Sé',
      number: '100',
    };

    apiMock.findByWhatsapp.mockResolvedValue({
      data: {
        status: 'success',
        data: []
      }
    });

    apiMock.create.mockResolvedValue({
      data: {
        status: 'success',
        data: {
          id: 42,
          name: 'João Silva',
          email: 'joao@test.com',
          whatsapp: '5511999999999',
          cpf_cnpj: '12345678901',
          cep: '01001000',
          address: 'Praça da Sé',
          number: '100',
        }
      }
    });

    const customer = await service.execute(dto);

    expect(customer).toBeInstanceOf(CustomerEntity);
    expect(customer.id).toBe(42);
    expect(customer.name).toBe('João Silva');
    expect(apiMock.findByWhatsapp).toHaveBeenCalledWith('5511999999999');
    expect(apiMock.create).toHaveBeenCalledWith(dto);
  });

  it('throws an error if a customer already exists with this whatsapp', async () => {
    const dto = {
      name: 'Duplicate User',
      email: 'duplicate@test.com',
      whatsapp: '5511999999999',
      cpf_cnpj: '12345678901',
    };

    apiMock.findByWhatsapp.mockResolvedValue({
      data: {
        status: 'success',
        data: [
          { id: 1, name: 'Existing User', whatsapp: '5511999999999' }
        ]
      }
    });

    await expect(service.execute(dto)).rejects.toThrowError(
      'Customer already exists with this whatsapp.'
    );
    expect(apiMock.create).not.toHaveBeenCalled();
  });
});
