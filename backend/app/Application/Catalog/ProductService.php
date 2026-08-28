<?php

namespace App\Application\Catalog;

use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    public function paginate(User $user, array $filters, int $perPage): LengthAwarePaginator
    {
        return Product::query()
            ->where('establishment_id', $user->establishment_id)
            ->when(
                $filters['category_id'] ?? null,
                fn ($query, $categoryId) => $query->where('category_id', $categoryId)
            )
            ->when(
                array_key_exists('is_available', $filters),
                fn ($query) => $query->where('is_available', $filters['is_available'])
            )
            ->when($filters['q'] ?? null, function ($query, $term) {
                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function create(User $user, array $data): Product
    {
        return Product::query()->create([
            ...$data,
            'establishment_id' => $user->establishment_id,
        ]);
    }

    public function find(User $user, string $id): Product
    {
        return Product::query()
            ->where('establishment_id', $user->establishment_id)
            ->findOrFail($id);
    }

    public function update(User $user, string $id, array $data): Product
    {
        $product = $this->find($user, $id);
        $product->update($data);

        return $product->refresh();
    }

    public function delete(User $user, string $id): void
    {
        $this->find($user, $id)->delete();
    }
}
