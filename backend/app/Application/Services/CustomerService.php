<?php

namespace App\Application\Services;

use App\Domains\Customer\Customer;

use App\Infrastructure\Repositories\CustomerRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Throwable;

class CustomerService extends BaseService
{
    protected string $modelClass = Customer::class;

    public function __construct(protected CustomerRepository $repository)
    {
    }

    public function post(array $data): Customer
    {
        $userId = isset($data['user_id']) ? (int) $data['user_id'] : null;

        if (!empty($data['whatsapp'])) {
            $existing = $userId
                ? $this->repository->findByWhatsappForUser($data['whatsapp'], $userId)
                : $this->repository->findByWhatsapp($data['whatsapp']);

            if ($existing) {
                throw new \Exception('Customer already exists with this whatsapp.');
            }
        }

        if (!empty($data['cep'])) {
            $cep = preg_replace('/\D/', '', (string) $data['cep']);

            if (!empty($cep)) {
                try {
                    $response = Http::timeout(5)->get("https://viacep.com.br/ws/{$cep}/json/");

                    if ($response->successful()) {
                        $viaCepData = $response->json();

                        if (!isset($viaCepData['erro'])) {
                            $data['address'] = $viaCepData['logradouro'] ?? ($data['address'] ?? null);
                            $data['neighborhood'] = $viaCepData['bairro'] ?? ($data['neighborhood'] ?? null);
                            $data['city'] = $viaCepData['localidade'] ?? ($data['city'] ?? null);
                            $data['state'] = $viaCepData['uf'] ?? ($data['state'] ?? null);
                        }
                    }
                } catch (Throwable $exception) {
                    Log::warning('ViaCEP lookup failed during customer creation.', [
                        'cep' => $cep,
                        'exception' => $exception::class,
                        'message' => $exception->getMessage(),
                    ]);
                }
            }
        }

        return $this->repository->create($data);
    }

    public function getByUser(int $userId, array $filters = []): Collection
    {
        return $this->repository->getByUser($userId, $filters);
    }

    public function findByUserOrFail(int|string $id, int $userId): Customer
    {
        $customer = $this->repository->findByUser($id, $userId);

        if (!$customer) {
            throw (new ModelNotFoundException())->setModel(Customer::class, [(string) $id]);
        }

        return $customer;
    }

    public function updateByUser(int|string $id, int $userId, array $data): Customer
    {
        $customer = $this->findByUserOrFail($id, $userId);
        $customer->update($data);

        return $customer;
    }

    public function deleteByUser(int|string $id, int $userId): bool
    {
        $customer = $this->findByUserOrFail($id, $userId);
        return (bool) $customer->delete();
    }
}
