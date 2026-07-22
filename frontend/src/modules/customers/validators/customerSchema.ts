import { z } from 'zod';

export const customerSchema = z.object({
  name: z.string().min(3, 'O nome deve ter no mínimo 3 caracteres'),
  email: z.string().email('E-mail em formato inválido'),
  whatsapp: z.string().regex(/^55\d{10,11}$/, 'WhatsApp inválido. Deve seguir o padrão 55 + DDD + Número (ex: 5511999999999)'),
  cpfCnpj: z.string().min(11, 'CPF/CNPJ deve ter entre 11 e 14 dígitos').max(14, 'CPF/CNPJ deve ter entre 11 e 14 dígitos'),
  cep: z.string().regex(/^\d{8}$/, 'CEP deve conter exatamente 8 dígitos numéricos').optional().or(z.literal('')),
  address: z.string().min(3, 'Endereço deve conter pelo menos 3 caracteres').optional().or(z.literal('')),
  number: z.string().min(1, 'Número é obrigatório').optional().or(z.literal('')),
  complement: z.string().optional(),
  neighborhood: z.string().optional(),
  city: z.string().optional(),
  state: z.string().max(2, 'Estado deve conter 2 letras').optional().or(z.literal('')),
});

export type CustomerFormInput = z.infer<typeof customerSchema>;
