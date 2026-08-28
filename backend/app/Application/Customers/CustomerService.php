<?php

namespace App\Application\Customers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function paginate(User $user, ?string $term, int $perPage): LengthAwarePaginator
    {
        return Customer::query()
            ->where('establishment_id', $user->establishment_id)
            ->when($term, function ($query, $term) {
                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('document', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function create(User $user, array $data): Customer
    {
        return Customer::query()->create([
            ...$data,
            'establishment_id' => $user->establishment_id,
        ]);
    }

    public function find(User $user, string $id): Customer
    {
        return Customer::query()
            ->where('establishment_id', $user->establishment_id)
            ->findOrFail($id);
    }

    public function update(User $user, string $id, array $data): Customer
    {
        $customer = $this->find($user, $id);
        $customer->update($data);

        return $customer->refresh();
    }

    public function delete(User $user, string $id): void
    {
        $this->find($user, $id)->delete();
    }
}
