# FLUXOGRAMA ESTRUTURA COMPLETA - SIMPLIFYFOOD FRONTEND (Vue 3 + TypeScript + Clean Frontend Architecture)
*Projeto Vue 3 + TypeScript + Vite + Clean Architecture + DDD + TDD*
*Alinhado ao Backend Laravel (Clean Architecture + Spec-Driven Development)*

## 1. Arquitetura Geral

```mermaid
graph TD
    A[Pages + Components (Presentation)] --> B[Composables + Pinia Stores (Application)]
    B --> C[Domain Entities + Value Objects]
    C --> D[Services / UseCases]
    D --> E[Shared API Client (Axios + Interceptors)]
    E --> F[Laravel Backend API]
    F --> G[Resources + DTOs]
    G --> E
    E --> D
    D --> C
    D --> H[TanStack Query Cache]
    B --> I[Layouts + Design System]
    A --> I

---

## 2. Estrutura Completa de Diretórios e Responsabilidades

frontend/
├── src/
│   ├── app/                          # Configuração global da aplicação
│   │   ├── router/                   # Vue Router + guards
│   │   ├── layouts/                  # AuthLayout, DashboardLayout, etc.
│   │   ├── plugins/                  # Pinia, Axios, etc.
│   │   └── bootstrap.ts
│   │
│   ├── modules/                      # Feature Modules (Bounded Contexts)
│   │   ├── auth/
│   │   ├── customers/
│   │   ├── orders/
│   │   ├── products/
│   │   ├── payments/
│   │   ├── dashboard/
│   │   └── ... 
│   │
│   ├── shared/                       # Camada compartilhada (reutilizável)
│   │   ├── api/                      # Axios client + interceptors
│   │   ├── components/               # Design System (BaseButton, BaseTable, etc.)
│   │   ├── composables/              # useAuth, usePermission, useTable, etc.
│   │   ├── domain/                   # Entities, Value Objects, Mappers
│   │   ├── stores/                   # Pinia Stores globais
│   │   ├── utils/
│   │   ├── types/
│   │   └── design-system/            # Tokens, themes, constants
│   │
│   ├── assets/
│   ├── types/                        # Tipos globais
│   └── tests/                        # Testes (Vitest + Cypress)
│
├── public/
├── vite.config.ts
├── tailwind.config.ts
├── cypress/
└── AGENTS.md                         # Este documento

## 3. Domain Layer (Entities)

export class CustomerEntity {
    constructor(
        public id: string,
        public name: string,
        public email: string,
        public whatsapp: string,
        public cpfCnpj: string,
        public address?: AddressValueObject
    ) {}

    isValidWhatsapp(): boolean {
        return /^55\d{10,11}$/.test(this.whatsapp);
    }

    getFullName(): string {
        return this.name;
    }
}

## 4. API Services (Infrastructure)

import { apiClient } from '@/shared/api/client';

export class CustomerApi {
    async create(data: CreateCustomerDto) {
        return apiClient.post('/customers', data);
    }

    async findByWhatsapp(whatsapp: string) {
        return apiClient.get(`/customers/by-whatsapp/${whatsapp}`);
    }
}

## 5. Application Layer (Services & Stores)

import { CustomerApi } from '@/modules/customers/api/CustomerApi';
import { CustomerEntity } from '@/shared/domain/entities/CustomerEntity';
import { CustomerMapper } from '@/shared/domain/mappers/CustomerMapper';

export class CreateCustomerService {
    constructor(private api: CustomerApi) {}

    async execute(dto: CreateCustomerDto): Promise<CustomerEntity> {
        // Regras de negócio
        const existing = await this.api.findByWhatsapp(dto.whatsapp);
        if (existing) {
            throw new Error('Customer already exists with this whatsapp.');
        }

        const response = await this.api.create(dto);
        return CustomerMapper.fromApi(response.data);
    }
}

## 6. Presentation Layer (Pages & Components)

CustomerForm.vue (Exemplo de componente)

<script setup lang="ts">
const props = defineProps<{
    modelValue: CustomerFormData;
}>();
const emit = defineEmits(['submit']);

const onSubmit = () => {
    emit('submit', props.modelValue);
};
</script>

<template>
    <BaseForm @submit="onSubmit">
        <!-- campos com VeeValidate + Zod -->
    </BaseForm>
</template>

CustomerPage.vue (Exemplo de página)

<script setup lang="ts">
const { createCustomer } = useCustomer();
</script>

## 7. Shared API Client

shared/api/client.ts

import axios from 'axios';
import { useAuthStore } from '@/shared/stores/auth';

export const apiClient = axios.create({
    baseURL: import.meta.env.VITE_API_URL,
    withCredentials: true,
});

// Interceptors (Auth, Refresh Token, Error Handling)
apiClient.interceptors.request.use(config => {
    const token = useAuthStore().token;
    if (token) config.headers.Authorization = `Bearer ${token}`;
    return config;
});

## 8. Camada TDD (Test-Driven Development)

Princípios do TDD no Frontend

Red → Green → Refactor
Testes como Especificação Executável
Isolamento com mocks (vi.mock, MSW)
Cobertura mínima de 85% em Domain e Application
Todo novo código deve ser precedido por testes

Estrutura Recomendada de Testes

src/tests/
├── unit/
│   ├── domain/
│   ├── services/
│   └── components/
├── integration/
├── e2e/ (Cypress)
└── fixtures/

Exemplo: CustomerServiceTest.ts

import { describe, it, expect, vi } from 'vitest';
import { CreateCustomerService } from '@/modules/customers/services/CreateCustomerService';

vi.mock('@/modules/customers/api/CustomerApi');

describe('CreateCustomerService', () => {
    it('creates a new customer', async () => {
        // Arrange, Act, Assert
    });

    it('throws error if customer already exists by whatsapp', async () => {
        // ...
    });
});

## 9. Variáveis Recomendadas no .env

```bash
VITE_API_URL=http://localhost:8000/api
VITE_APP_NAME="SimplyFood"
VITE_MARKETPLACE_URL="https://marketplace.simplyfood.com.br"
VITE_WHATSAPP_API_URL="https://api.twilio.com"
```

## 10. Comandos Úteis

```bash
# Desenvolvimento
npm run dev

# Build
npm run build

# Testes
npm run test           # Vitest
npm run test:coverage
npm run cypress:open   # Cypress

# Lint
npm run lint
npm run format
```

Recomendações de Pacotes:

vitest, @vue/test-utils, cypress
zod, vee-validate, @tanstack/vue-query
eslint, prettier, husky

Este documento é a fonte de verdade para o frontend.
Todo código novo deve respeitar rigorosamente esta arquitetura.

---

## 11. Classes e Estrutura Real do Projeto (Scaffold de Arquitetura)

Abaixo estão listados os arquivos implementados na fundação da arquitetura limpa:

### Camada Shared (Compartilhada)
- **API Client**: [client.ts](file:///c:/Users/MITALO/Desktop/simplyfood/frontend/src/shared/api/client.ts) - Configuração do Axios com interceptor para injeção dinâmica do token Bearer.
- **Store Pinia**: [auth.ts](file:///c:/Users/MITALO/Desktop/simplyfood/frontend/src/shared/stores/auth.ts) - Gerenciamento de estado e persistência das credenciais do usuário.
- **Entidades de Domínio**:
  - [CustomerEntity.ts](file:///c:/Users/MITALO/Desktop/simplyfood/frontend/src/shared/domain/entities/CustomerEntity.ts) - Entidade rica representando o cliente.
  - [AddressValueObject.ts](file:///c:/Users/MITALO/Desktop/simplyfood/frontend/src/shared/domain/value-objects/AddressValueObject.ts) - Objeto de valor encapsulando lógica de endereço.
- **Domain Mapper**: [CustomerMapper.ts](file:///c:/Users/MITALO/Desktop/simplyfood/frontend/src/shared/domain/mappers/CustomerMapper.ts) - Conversão de DTOs e respostas cruas da API em entidades ricas.
- **Componentes do Design System (Shared Components)**:
  - [BaseButton.vue](file:///c:/Users/MITALO/Desktop/simplyfood/frontend/src/shared/components/BaseButton.vue) - Botão com estados de carregamento e variantes visuais.
  - [BaseInput.vue](file:///c:/Users/MITALO/Desktop/simplyfood/frontend/src/shared/components/BaseInput.vue) - Input com controle de foco premium e exibição de erro.
  - [BaseForm.vue](file:///c:/Users/MITALO/Desktop/simplyfood/frontend/src/shared/components/BaseForm.vue) - Wrapper estrutural de formulários.

### Módulos de Feature (Bounded Contexts)
#### Módulo de Autenticação (`modules/auth/`)
- [AuthApi.ts](file:///c:/Users/MITALO/Desktop/simplyfood/frontend/src/modules/auth/api/AuthApi.ts) - Comunicação HTTP com rotas de Login e Registro do backend Laravel.
- [AuthService.ts](file:///c:/Users/MITALO/Desktop/simplyfood/frontend/src/modules/auth/services/AuthService.ts) - Serviço de aplicação para orquestrar fluxos de autenticação.
- [LoginPage.vue](file:///c:/Users/MITALO/Desktop/simplyfood/frontend/src/modules/auth/pages/LoginPage.vue) - Página de Login premium com validações e glassmorphism.

#### Módulo de Clientes (`modules/customers/`)
- [CustomerApi.ts](file:///c:/Users/MITALO/Desktop/simplyfood/frontend/src/modules/customers/api/CustomerApi.ts) - Integração HTTP com endpoints `/customers`.
- [CreateCustomerService.ts](file:///c:/Users/MITALO/Desktop/simplyfood/frontend/src/modules/customers/services/CreateCustomerService.ts) - UseCase contendo a regra de negócio para verificação de duplicidade.
- [customerSchema.ts](file:///c:/Users/MITALO/Desktop/simplyfood/frontend/src/modules/customers/validators/customerSchema.ts) - Validação rigorosa dos campos usando Zod.
- [CustomerForm.vue](file:///c:/Users/MITALO/Desktop/simplyfood/frontend/src/modules/customers/components/CustomerForm.vue) - Componente de formulário com auto-preenchimento via ViaCEP.
- [CustomerPage.vue](file:///c:/Users/MITALO/Desktop/simplyfood/frontend/src/modules/customers/pages/CustomerPage.vue) - Página de cadastro de clientes.

#### Módulo de Dashboard (`modules/dashboard/`)
- [DashboardPage.vue](file:///c:/Users/MITALO/Desktop/simplyfood/frontend/src/modules/dashboard/pages/DashboardPage.vue) - Painel principal com métricas diárias e ações rápidas.

### Infraestrutura da Aplicação Vue
- **Vue Router**: [index.ts](file:///c:/Users/MITALO/Desktop/simplyfood/frontend/src/app/router/index.ts) - Roteamento com guards baseados no estado de autenticação.
- **Layouts Globais**:
  - [AuthLayout.vue](file:///c:/Users/MITALO/Desktop/simplyfood/frontend/src/app/layouts/AuthLayout.vue) - Container centralizado com fundo animado premium.
  - [DashboardLayout.vue](file:///c:/Users/MITALO/Desktop/simplyfood/frontend/src/app/layouts/DashboardLayout.vue) - Grid de dashboard com barra de navegação lateral.

### Cobertura de Testes Unitários e Integração (Vitest)
- [CustomerEntity.spec.ts](file:///c:/Users/MITALO/Desktop/simplyfood/frontend/src/tests/unit/domain/CustomerEntity.spec.ts) - Testes unitários para validação de domínio de cliente e endereço.
- [CreateCustomerService.spec.ts](file:///c:/Users/MITALO/Desktop/simplyfood/frontend/src/tests/unit/services/CreateCustomerService.spec.ts) - Teste de regra de negócio, mock do cliente HTTP e exceções.

---

## Changelog
- **v1.0** - Criação inicial alinhada ao backend Laravel.
- **v1.1** - Implementação completa da fundação da arquitetura limpa frontend (Shared layer, Feature modules de Auth e Customers, layouts integrados, roteamento dinâmico com Navigation Guards, formulários reativos validados por Zod, integração ViaCEP, e suíte de testes Vitest automatizada).

