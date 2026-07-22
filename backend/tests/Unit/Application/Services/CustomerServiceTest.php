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

        $this->repository->shouldReceive('findByWhatsapp')
            ->once()
            ->with('11999999999')
            ->andReturn(null);

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
