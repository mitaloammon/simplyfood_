# FLUXOGRAMA ESTRUTURA COMPLETA - SIMPLIFYFOOD (Laravel + Clean Architecture + Spec-Driven Development + TDD)
*Projeto Laravel 12 + Vue 3 + Clean Architecture + DDD + TDD*
*Baseado em AGENTS.md*

## 1. Arquitetura Geral
```mermaid
graph TD
    A[Frontend (Vue 3)] --> B[Middleware: auth:sanctum + UserTokenValid.php]
    B --> C[FormRequest (Validação)]
    C --> D[Controller (finos)]
    D --> E[DTO]
    E --> F[Service / UseCase]
    F --> G[Repository]
    G --> H[Domain Entity (Model)]
    F --> I[External Service (Interface)]
    I --> J[Concrete Gateway (ex: MercadoPago)]
    F --> K[Event / Listener / Job]
    H --> L[(PostgreSQL)]
    F --> M[Resource (JSON)]
    M --> A
```
*Princípios respeitados:*
- Controllers *nunca* contêm regras de negócio
- Regras em *Services* ou *UseCases*
- Integrações externas via *Interfaces* (Strategy Pattern)
- **Testes obrigatórios via TDD**

---

## 2. Estrutura Completa de Diretórios e Responsabilidades

```bash
simplifyfood/
├── backend/ # Backend Laravel (Clean Architecture)
│   ├── app/ # Código principal da aplicação
│   │   ├── Application/ # Camada de Aplicação - Orquestração
│   │   │   ├── Services/ # Regras de negócio principais (Service Layer)
│   │   │   │   ├── BaseService.php → Classe base com métodos comuns
│   │   │   │   ├── CustomerService.php → Lógica de clientes (CRUD, ViaCEP, etc.)
│   │   │   │   ├── OrderService.php → Fluxo completo de pedidos
│   │   │   │   └── PaymentService.php → Integração com gateways de pagamento
│   │   │   ├── DTOs/ → Data Transfer Objects (imutáveis)
│   │   │   └── UseCases/ → Casos de uso específicos (ProcessPaymentUseCase.php)
│   │   │
│   │   ├── Domains/ # Camada de Domínio (DDD) - Entidades ricas
│   │   │   ├── Auth/ → User.php (RBAC + Sanctum)
│   │   │   ├── Customer/ → Customer.php + Address.php
│   │   │   ├── Order/ → Order.php + OrderItem.php
│   │   │   ├── Product/ → Product.php + Category.php
│   │   │   ├── Payment/ → PaymentTransaction.php
│   │   │   └── ... (Delivery, Ticket, WhatsApp, Notification, etc.)
│   │   │
│   │   ├── Http/ # Camada de Apresentação
│   │   │   ├── Controllers/ → Orquestração fina (sem regras de negócio)
│   │   │   │   ├── BaseController.php
│   │   │   │   ├── CustomerController.php
│   │   │   │   └── OrderController.php
│   │   │   ├── Requests/ → Form Requests (validações)
│   │   │   │   ├── StoreCustomerRequest.php
│   │   │   │   └── ProcessPaymentRequest.php
│   │   │   ├── Resources/ → API Resources (JSON padronizado)
│   │   │   └── Middleware/ → Auth + RBAC
│   │   │
│   │   ├── Infrastructure/ # Infraestrutura e Integrações
│   │   │   ├── Repositories/ → Repository Pattern (abstração de dados)
│   │   │   │   └── CustomerRepository.php
│   │   │   └── ExternalServices/ → Abstrações para serviços externos
│   │   │       └── Payment/
│   │   │           ├── PaymentGatewayInterface.php
│   │   │           └── MercadoPagoGateway.php
│   │   │
│   │   └── Shared/ → Código compartilhado
│   │       ├── Enums/ → Status de pedido, etc.
│   │       ├── Traits/ → Reutilizáveis
│   │       ├── Helpers/ → Funções utilitárias
│   │       └── Exceptions/ → Exceções personalizadas
│   │
│   ├── database/
│   │   └── migrations/ → 10+ migrations (roles, customers, orders, payments...)
│   │
│   ├── routes/
│   │   └── api.php → Rotas protegidas com middlewares
│   │
│   └── tests/ # ← Camada TDD (atualizada)
│       ├── Unit/
│       │   ├── Application/
│       │   │   ├── Services/
│       │   │   │   ├── CustomerServiceTest.php
│       │   │   │   ├── OrderServiceTest.php
│       │   │   │   └── PaymentServiceTest.php
│       │   │   └── UseCases/
│       │   │       └── ProcessPaymentUseCaseTest.php
│       │   ├── Domains/
│       │   │   ├── Customer/
│       │   │   │   ├── CustomerTest.php
│       │   │   │   └── AddressTest.php
│       │   │   └── Order/
│       │   │       └── OrderTest.php
│       │   └── Infrastructure/
│       │       └── Repositories/
│       │           └── CustomerRepositoryTest.php
│       ├── Feature/
│       │   ├── Customer/
│       │   │   └── CustomerApiTest.php
│       │   ├── Order/
│       │   │   └── OrderApiTest.php
│       │   └── Payment/
│       │       └── PaymentFlowTest.php
│       └── Architecture/
│           └── CleanArchitectureTest.php
│
├── infrastructure/ # Configurações de infraestrutura Docker
│   ├── docker/
│   │   └── Dockerfile → PHP 8.3 + PostgreSQL + Redis
│   └── nginx/
│       └── default.conf → Configuração para Laravel API + Vue SPA
│
├── docker-compose.yml # Orquestração completa (app, nginx, db, redis)
└── AGENTS.md # Guia de contexto e regras do projeto
```


*Responsabilidades por Camada (resumo):*
- *Domain*: Entidades com regras de negócio específicas
- *Application*: Services e UseCases concentram toda lógica
- *Http*: Entrada (Requests) e Saída (Resources + Controllers finos)
- *Infrastructure*: Persistência e integrações externas (abstraídas)
- *Tests*: TDD completo (Unit + Feature + Architecture)

---

## 3. Models (Domain)

**Customer.php**
```php
<?php
namespace App\Domains\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model {
    use SoftDeletes;
    protected $fillable = ['name', 'email', 'phone', 'whatsapp', 'cpf_cnpj'];
    public function addresses() { return $this->hasMany(Address::class); }
    public function orders() { return $this->hasMany(\App\Domains\Order\Order::class); }
}
```

*Address.php, Order.php, Product.php, PaymentTransaction.php seguem o mesmo padrão com relacionamentos e fillable.*

---

## 4. Repositories

**CustomerRepository.php**
```php
<?php
namespace App\Infrastructure\Repositories;
use App\Domains\Customer\Customer;

class CustomerRepository {
    public function __construct(protected Customer $model) {}
    public function create(array $data) { return $this->model->create($data); }
    public function findByWhatsapp(string $whatsapp) { return $this->model->where('whatsapp', $whatsapp)->first(); }
}
```

---

## 5. Controllers

**CustomerController.php**
```php
<?php
namespace App\Http\Controllers;
use App\Application\Services\CustomerService;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Resources\CustomerResource;

class CustomerController extends BaseController {
    public function __construct(protected CustomerService $service) {}
    public function store(StoreCustomerRequest $request) {
        $customer = $this->service->create($request->validated());
        return $this->successResponse(new CustomerResource($customer), 'Cliente criado!', 201);
    }
}
```

*BaseController.php e OrderController.php seguem o mesmo padrão.*

---

## 6. Payment Layer

**PaymentService.php**
```php
<?php
namespace App\Application\Services;
use App\Infrastructure\ExternalServices\Payment\PaymentGatewayInterface;

class PaymentService extends BaseService {
    public function __construct(protected PaymentGatewayInterface $gateway) {}
    public function process(array $data) {
        return $this->gateway->createCharge($data);
    }
}
```

---

## 6.1. Application Services

**CustomerService.php**
```php
<?php
namespace App\Application\Services;

use App\Domains\Customer\Customer;
use App\Infrastructure\Repositories\CustomerRepository;
use Illuminate\Support\Facades\Http;

class CustomerService extends BaseService
{
    protected string $modelClass = Customer::class;

    public function __construct(protected CustomerRepository $repository) {}

    public function create(array $data)
    {
        // Validação de duplicidade por WhatsApp
        if (!empty($data['whatsapp'])) {
            $existing = $this->repository->findByWhatsapp($data['whatsapp']);
            if ($existing) {
                throw new \Exception('Customer already exists with this whatsapp.');
            }
        }

        // Auto-preenchimento de endereço via ViaCEP
        if (!empty($data['cep'])) {
            $response = Http::get("https://viacep.com.br/ws/{$data['cep']}/json/");
            if ($response->successful()) {
                $viaCepData = $response->json();
                if (!isset($viaCepData['erro'])) {
                    $data['address'] = $viaCepData['logradouro'] ?? ($data['address'] ?? null);
                    $data['neighborhood'] = $viaCepData['bairro'] ?? ($data['neighborhood'] ?? null);
                    $data['city'] = $viaCepData['localidade'] ?? ($data['city'] ?? null);
                    $data['state'] = $viaCepData['uf'] ?? ($data['state'] ?? null);
                }
            }
        }

        return $this->repository->create($data);
    }
}
```

---

## 7. Infrastructure

**infrastructure/docker/Dockerfile**
```dockerfile
FROM php:8.3-fpm-alpine
RUN apk add --no-cache git curl libpng-dev ... postgresql-dev
RUN docker-php-ext-install pdo pdo_pgsql ...
RUN pecl install redis && docker-php-ext-enable redis
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/backend
EXPOSE 9000
CMD ["php-fpm"]
```

**infrastructure/nginx/default.conf**
```nginx
server {
    listen 80;
    root /var/www/backend/public;
    location /api { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        include fastcgi_params;
    }
    location / {
        root /var/www/frontend/dist;
        try_files $uri $uri/ /index.html;
    }
}
```

**docker-compose.yml**
```yaml
version: '3.8'
services:
  app:
    build: ./infrastructure/docker
    volumes: ["./backend:/var/www/backend"]
    depends_on: [db, redis]
  nginx:
    image: nginx:alpine
    ports: ["8000:80"]
    volumes:
      - ./infrastructure/nginx/default.conf:/etc/nginx/conf.d/default.conf
      - ./backend/public:/var/www/backend/public
    depends_on: [app]
  db:
    image: postgres:16
    environment: { POSTGRES_DB: simplifyfood, POSTGRES_USER: postgres, POSTGRES_PASSWORD: password }
  redis:
    image: redis:alpine
```

---

## 8. Rotas da API, Middlewares e Autenticação

### 8.1. Padrão de entrada da API
O ponto de entrada principal do backend é o arquivo de rotas da API, onde a aplicação organiza endpoints públicos e protegidos de forma explícita. A estrutura segue um padrão simples e previsível:

- Rotas públicas: saúde da aplicação, login e cadastro
- Rotas protegidas: clientes, produtos e pedidos
- Middleware de token: valida presença/consistência do token
- Middleware de autorização: restringe acesso por papel/role

```php
// backend/routes/api.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// 1) Rota pública de health check
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// 2) Grupo público de autenticação
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// 3) Grupo protegido com múltiplos middlewares
Route::middleware(['token.valid', 'auth.system:ADMIN,MANAGER,OPERATOR'])->group(function () {
    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'get']);
        Route::get('/{id}', [CustomerController::class, 'show']);
        Route::put('/{id}', [CustomerController::class, 'update']);
        Route::delete('/{id}', [CustomerController::class, 'deleted']);
    });

    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'get']);
        Route::get('/active', [ProductController::class, 'getActive']);
        Route::post('/', [ProductController::class, 'post']);
    });

    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'get']);
        Route::get('/{id}', [OrderController::class, 'show']);
        Route::post('/', [OrderController::class, 'post']);
        Route::patch('/{id}/status', [OrderController::class, 'changeStatus']);
    });
});
```

### 8.2. Middlewares aplicados e responsabilidade de cada um

- `token.valid`
  - Responsável por validar o token informado via Bearer Token ou por autenticação baseada em Sanctum.
  - Se o token não existir, estiver ausente ou for inválido, a resposta padrão é `401 Unauthorized`.
  - O fluxo é implementado em `app/Http/Middleware/UserTokenValid.php`.

- `auth.system:ADMIN,MANAGER,OPERATOR`
  - Responsável por restringir o acesso às rotas protegidas conforme o papel do usuário.
  - Quando o usuário não possui permissão para o papel exigido, o comportamento esperado é `403 Forbidden`.
  - Esse middleware atua como camada de autorização após a validação do token.

### 8.3. Prefixos utilizados por grupo

- Grupo público de autenticação: prefixo `auth`
  - `/api/auth/login`
  - `/api/auth/register`

- Grupo protegido de clientes: prefixo `customers`
  - `/api/customers`
  - `/api/customers/{id}`

- Grupo protegido de produtos: prefixo `products`
  - `/api/products`
  - `/api/products/active`

- Grupo protegido de pedidos: prefixo `orders`
  - `/api/orders`
  - `/api/orders/{id}`

- Rota pública de saúde: `/api/health`

### 8.4. Códigos de erro possíveis nas autenticações e retornos

- `401 Unauthorized`
  - Utilizado quando o token não é enviado, é inválido ou não passa pela validação de `token.valid`.
  - Exemplo de retorno:
    ```json
    {
      "message": "Unauthorized: Token is missing or invalid.",
      "status": "error"
    }
    ```

- `401 Unauthorized` nas rotas de autenticação
  - O controller de login retorna `401` quando as credenciais são inválidas ou a autenticação falha.

- `400 Bad Request`
  - Utilizado no endpoint de registro quando os dados enviados não passam na validação ou no fluxo de criação.

- `403 Forbidden`
  - Esperado quando o usuário está autenticado, mas não possui um dos papéis permitidos (`ADMIN`, `MANAGER` ou `OPERATOR`) para acessar uma rota protegida.

- `422 Unprocessable Entity`
  - Utilizado em operações específicas, como alteração de status de pedido, quando o payload recebido é semanticamente inválido.

### 8.5. Regras de negócio para as rotas protegidas

- As rotas protegidas devem sempre ser tratadas como operações sensíveis e exigirem autenticação explícita.
- A validação de token ocorre antes de qualquer execução de controller.
- A autorização por role ocorre depois da validação de identidade.
- Controllers continuam finos; a lógica de negócio deve permanecer em Services/UseCases.

---

## 9. Camada TDD (Test-Driven Development)

### Princípios do TDD no SimplifyFood

1. **Red → Green → Refactor**
2. Testes como Especificação Executável
3. Isolamento total com Mocks
4. Cobertura mínima de 80% em Application e Domain
5. Todo novo código deve ser precedido por testes

## 10. Variáveis Recomendadas no .env (Exemplo) 

# ==================== APP CONFIG ====================
APP_NAME=SimplifyFood
APP_ENV=local                    # Opções: local | homologacao | production | testing
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE=America/Sao_Paulo

# ==================== DATABASE ====================
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=simplifyfood
DB_USERNAME=postgres
DB_PASSWORD=password

# ==================== CACHE & QUEUE ====================
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# ==================== REDIS ====================
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# ==================== EXTERNAL SERVICES ====================
VIACEP_BASE_URL=https://viacep.com.br/ws
MERCADOPAGO_PUBLIC_KEY=TEST-...
MERCADOPAGO_ACCESS_TOKEN=TEST-...

# ==================== LOGGING ====================
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# ==================== MAIL ====================
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="no-reply@simplifyfood.com.br"
MAIL_FROM_NAME="${APP_NAME}"

# ==================== SANCTUM / AUTH ====================
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:8000

# ==================== TESTING ====================
TEST_DB_DATABASE=simplifyfood_test
TEST_DB_USERNAME=postgres
TEST_DB_PASSWORD=password


### Estrutura Recomendada de Testes

```bash
tests/
├── Unit/
│   ├── Application/
│   │   ├── Services/
│   │   │   ├── CustomerServiceTest.php
│   │   │   ├── OrderServiceTest.php
│   │   │   └── PaymentServiceTest.php
│   │   └── UseCases/
│   │       └── ProcessPaymentUseCaseTest.php
│   ├── Domains/
│   │   ├── Customer/
│   │   │   ├── CustomerTest.php
│   │   │   └── AddressTest.php
│   │   └── Order/
│   │       └── OrderTest.php
│   └── Infrastructure/
│       └── Repositories/
│           └── CustomerRepositoryTest.php
├── Feature/
│   ├── Customer/
│   │   └── CustomerApiTest.php
│   ├── Order/
│   │   └── OrderApiTest.php
│   └── Payment/
│       └── PaymentFlowTest.php
└── Architecture/
    └── CleanArchitectureTest.php
```

### Exemplo Completo: CustomerServiceTest.php

```php
<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\CustomerService;
use App\Infrastructure\Repositories\CustomerRepository;
use App\Domains\Customer\Customer;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class CustomerServiceTest extends TestCase
{
    protected CustomerService $service;
    protected CustomerRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(CustomerRepository::class);
        $this->service = new CustomerService($this->repository);
    }

    /** @test */
    public function it_creates_a_new_customer_and_fetches_address_via_viacep()
    {
        // Arrange
        $data = [
            'name' => 'João Silva',
            'email' => 'joao@test.com',
            'phone' => '11999999999',
            'whatsapp' => '11999999999',
            'cpf_cnpj' => '12345678901',
            'cep' => '01001000'
        ];

        $mockedAddress = ['logradouro' => 'Praça da Sé', 'bairro' => 'Sé', 'localidade' => 'São Paulo'];

        Http::fake([
            'viacep.com.br/*' => Http::response($mockedAddress, 200)
        ]);

        $this->repository->shouldReceive('create')
            ->once()
            ->with(Mockery::subset($data))
            ->andReturn(new Customer($data));

        // Act
        $customer = $this->service->create($data);

        // Assert
        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals('João Silva', $customer->name);
    }

    /** @test */
    public function it_throws_exception_when_customer_already_exists_by_whatsapp()
    {
        $this->expectException(\Exception::class);

        $this->repository->shouldReceive('findByWhatsapp')
            ->once()
            ->andReturn(new Customer(['whatsapp' => '11999999999']));

        $this->service->create(['whatsapp' => '11999999999']);
    }
}
```

### Exemplo Feature Test: CustomerApiTest.php

```php
<?php

namespace Tests\Feature\Customer;

use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    /** @test */
    public function it_can_create_a_customer_via_api()
    {
        $payload = [
            'name' => 'Maria Oliveira',
            'email' => 'maria@test.com',
            'whatsapp' => '11988888888',
            'cpf_cnpj' => '98765432100'
        ];

        $response = $this->postJson('/api/customers', $payload);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'data' => ['id', 'name', 'email', 'whatsapp'],
                     'message'
                 ]);

        $this->assertDatabaseHas('customers', ['email' => 'maria@test.com']);
    }
}
```

---

### Comandos Úteis

```bash
# Testes Unitários
php artisan test --filter CustomerServiceTest

# Testes de Feature
php artisan test --testsuite=Feature

# Cobertura de Código
php artisan test --coverage

# Watch mode (com Pest)
./vendor/bin/pest --watch
```

**Recomendações de Pacotes:**
- `pestphp/pest` + `pestphp/pest-plugin-arch`
- `mockery/mockery`
- `phpstan/phpstan` (opcional para análise estática)

---

## 10. Segregação e Configuração de Ambientes

O projeto suporta a segregação completa entre os ambientes: `local`, `homologacao`, `production` e `testing`.

### Diretrizes de Segurança e Configurações Customizadas

Para garantir a segregação e robustez entre os ambientes no nível do framework, foram aplicadas as seguintes lógicas customizadas:

- **Timezone e Segurança em [config/app.php](file:///c:/Users/MITALO/Desktop/simplyfood/backend/config/app.php)**:
  - O timezone da aplicação lê dinamicamente a variável `APP_TIMEZONE` do `.env` (com fallback para `'America/Sao_Paulo'`).
  - A opção `debug` é forçada a `false` em ambiente de produção (`APP_ENV === 'production'`), independente do que estiver definido em `APP_DEBUG`, evitando exibição indesejada de dados sensíveis.

- **Banco de Dados em [config/database.php](file:///c:/Users/MITALO/Desktop/simplyfood/backend/config/database.php)**:
  - A conexão `mysql` detecta se o ambiente atual é `testing`. Caso afirmativo, ela substitui dinamicamente o banco, o usuário e a senha pelas variáveis `TEST_DB_DATABASE`, `TEST_DB_USERNAME` e `TEST_DB_PASSWORD`.

- **Mapeamento de Testes em [phpunit.xml](file:///c:/Users/MITALO/Desktop/simplyfood/backend/phpunit.xml)**:
  - A suíte de testes padrão usa SQLite em memória (`:memory:`) por questões de performance.
  - Para rodar testes no PostgreSQL real (como em pipelines de CI ou homologação), os desenvolvedores podem criar um arquivo `.env.testing` baseado em `.env.testing.example`, cujas variáveis sobrescreverão as do `phpunit.xml`.

---

### Comandos de Configuração por Ambiente

#### Ambiente Local
```bash
# 1. Copiar as variáveis de ambiente
cp .env.example .env

# 2. Instalar dependências
composer install

# 3. Gerar a chave da aplicação
php artisan key:generate

# 4. Rodar as migrações locais
php artisan migrate
```

#### Ambiente de Homologação / Produção
```bash
# 1. Copiar variáveis de ambiente do respectivo ambiente
cp .env.example .env # E preencher com as credenciais reais

# 2. Instalar dependências otimizadas (sem dev)
composer install --no-dev --optimize-autoloader

# 3. Otimizar carregamento de arquivos do Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Rodar as migrações de produção com segurança (force)
php artisan migrate --force
```

#### Ambiente de Testes
```bash
# Execução padrão (SQLite em memória)
php artisan test

# Opcional: Execução no PostgreSQL real de testes
# 1. Copiar variáveis de ambiente de teste
cp .env.testing.example .env.testing # E ajustar credenciais se necessário
# 2. Rodar testes
php artisan test
```

---