import { describe, it, expect } from 'vitest';
import { CustomerEntity } from '@/shared/domain/entities/CustomerEntity';
import { AddressValueObject } from '@/shared/domain/value-objects/AddressValueObject';

describe('CustomerEntity & AddressValueObject Domain', () => {
  it('validates a correct Brazilian WhatsApp number', () => {
    const customer = new CustomerEntity(
      1,
      'João Silva',
      'joao@test.com',
      '5511999999999',
      '12345678901'
    );
    expect(customer.isValidWhatsapp()).toBe(true);
  });

  it('rejects an incorrect WhatsApp number pattern', () => {
    const customer = new CustomerEntity(
      1,
      'João Silva',
      'joao@test.com',
      '11999999999',
      '12345678901'
    );
    expect(customer.isValidWhatsapp()).toBe(false);
  });

  it('formats address information properly', () => {
    const address = new AddressValueObject(
      '01001000',
      'Praça da Sé',
      '100',
      'Apto 1',
      'Sé',
      'São Paulo',
      'SP'
    );
    expect(address.getFullAddress()).toBe('Praça da Sé, 100, Sé, São Paulo-SP CEP: 01001000');
  });
});
