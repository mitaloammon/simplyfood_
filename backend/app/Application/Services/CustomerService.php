<?php

namespace App\Application\Services;

use App\Domains\Customer\Customer;

use App\Infrastructure\Repositories\CustomerRepository;
use Illuminate\Support\Facades\Http;

class CustomerService extends BaseService
{
    protected string $modelClass = Customer::class;

    public function __construct(protected CustomerRepository $repository)
    {
    }

    public function create(array $data)
    {
        if (!empty($data['whatsapp'])) {
            $existing = $this->repository->findByWhatsapp($data['whatsapp']);
            if ($existing) {
                throw new \Exception('Customer already exists with this whatsapp.');
            }
        }

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
