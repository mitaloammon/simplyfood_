<?php

namespace App\Application\Catalog;

use App\Exceptions\BusinessConflictException;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function paginate(User $user, int $perPage): LengthAwarePaginator
    {
        return Category::query()
            ->where('establishment_id', $user->establishment_id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function create(User $user, array $data): Category
    {
        return Category::query()->create([
            ...$data,
            'establishment_id' => $user->establishment_id,
        ]);
    }

    public function find(User $user, string $id): Category
    {
        return Category::query()
            ->where('establishment_id', $user->establishment_id)
            ->findOrFail($id);
    }

    public function update(User $user, string $id, array $data): Category
    {
        $category = $this->find($user, $id);
        $category->update($data);

        return $category->refresh();
    }

    public function delete(User $user, string $id): void
    {
        $category = $this->find($user, $id);

        $hasLinkedProducts = Product::query()
            ->where('establishment_id', $user->establishment_id)
            ->where('category_id', $category->id)
            ->exists();

        if ($hasLinkedProducts) {
            throw new BusinessConflictException(
                'Não é possível excluir a categoria enquanto houver produtos vinculados'
            );
        }

        $category->delete();
    }
}
