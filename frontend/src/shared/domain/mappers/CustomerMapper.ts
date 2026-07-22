import { CustomerEntity } from '../entities/CustomerEntity';
import { AddressValueObject } from '../value-objects/AddressValueObject';

export class CustomerMapper {
  static fromApi(raw: any): CustomerEntity {
    let addressVo: AddressValueObject | undefined;

    if (raw.addresses && Array.isArray(raw.addresses) && raw.addresses.length > 0) {
      const primary = raw.addresses[0];
      addressVo = new AddressValueObject(
        primary.cep || '',
        primary.address || '',
        primary.number || '',
        primary.complement || undefined,
        primary.neighborhood || undefined,
        primary.city || undefined,
        primary.state || undefined
      );
    } else if (raw.cep) {
      addressVo = new AddressValueObject(
        raw.cep || '',
        raw.address || '',
        raw.number || '',
        raw.complement || undefined,
        raw.neighborhood || undefined,
        raw.city || undefined,
        raw.state || undefined
      );
    }

    return new CustomerEntity(
      raw.id,
      raw.name,
      raw.email,
      raw.whatsapp || raw.phone || '',
      raw.cpf_cnpj || raw.cpfCnpj || '',
      addressVo
    );
  }

  static toApi(entity: CustomerEntity): any {
    return {
      id: entity.id,
      name: entity.name,
      email: entity.email,
      whatsapp: entity.whatsapp,
      cpf_cnpj: entity.cpfCnpj,
      cep: entity.address?.cep,
      address: entity.address?.address,
      number: entity.address?.number,
      complement: entity.address?.complement,
      neighborhood: entity.address?.neighborhood,
      city: entity.address?.city,
      state: entity.address?.state
    };
  }
}
