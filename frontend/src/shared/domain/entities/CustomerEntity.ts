import { AddressValueObject } from '../value-objects/AddressValueObject';

export class CustomerEntity {
  public readonly id: string | number;
  public readonly name: string;
  public readonly email: string;
  public readonly whatsapp: string;
  public readonly cpfCnpj: string;
  public readonly address?: AddressValueObject;

  constructor(id: string | number, name: string, email: string, whatsapp: string, cpfCnpj: string, address?: AddressValueObject) {
    this.id = id;
    this.name = name;
    this.email = email;
    this.whatsapp = whatsapp;
    this.cpfCnpj = cpfCnpj;
    this.address = address;
  }

  isValidWhatsapp(): boolean {
    const cleanWhatsapp = this.whatsapp.replace(/\D/g, '');
    return /^55\d{10,11}$/.test(cleanWhatsapp);
  }

  getFullName(): string {
    return this.name;
  }
}
