<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\CustomerService;
use App\Infrastructure\Repositories\CustomerRepository;
use App\Domains\Customer\Customer;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class CustomerServiceTest extends TestCase
{
    protected CustomerService $service;
    /** @var CustomerRepository&\Mockery\MockInterface */
    protected $repository;

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

        $this->repository->shouldReceive('findByWhatsapp')
            ->once()
            ->with('11999999999')
            ->andReturn(null);

        $this->repository->shouldReceive('create')
            ->once()
            ->with(Mockery::subset($data))
            ->andReturn(new Customer($data));

        // Act
        $customer = $this->service->post($data);

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

        $this->service->post(['whatsapp' => '11999999999']);
    }

    /** @test */
    public function it_creates_customer_even_when_viacep_is_unavailable()
    {
        $data = [
            'name' => 'Empresa Teste',
            'email' => 'empresa@test.com',
            'phone' => '7133331234',
            'whatsapp' => '71988887777',
            'cpf_cnpj' => '12345678000199',
            'cep' => '40050330',
            'address' => 'Endereco Manual',
            'neighborhood' => 'Centro',
            'city' => 'Salvador',
            'state' => 'BA',
        ];

        Http::fake(function () {
            throw new ConnectionException('SSL certificate problem');
        });

        $this->repository->shouldReceive('findByWhatsapp')
            ->once()
            ->with('71988887777')
            ->andReturn(null);

        $this->repository->shouldReceive('create')
            ->once()
            ->with(Mockery::subset($data))
            ->andReturn(new Customer($data));

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'ViaCEP lookup failed during customer creation.'
                    && ($context['cep'] ?? null) === '40050330'
                    && ($context['exception'] ?? null) === ConnectionException::class;
            });

        $customer = $this->service->post($data);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals('Empresa Teste', $customer->name);
    }
}
