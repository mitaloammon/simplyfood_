export class AddressValueObject {
  public readonly cep: string;
  public readonly address: string;
  public readonly number: string | number;
  public readonly complement?: string;
  public readonly neighborhood?: string;
  public readonly city?: string;
  public readonly state?: string;

  constructor(cep: string, address: string, number: string | number, complement?: string, neighborhood?: string, city?: string, state?: string) {
    this.cep = cep;
    this.address = address;
    this.number = number;
    this.complement = complement;
    this.neighborhood = neighborhood;
    this.city = city;
    this.state = state;
  }

  getFullAddress(): string {
    const parts = [this.address, this.number];
    if (this.neighborhood) parts.push(this.neighborhood);
    if (this.city && this.state) parts.push(`${this.city}-${this.state}`);
    return parts.join(', ') + ` CEP: ${this.cep}`;
  }
}
