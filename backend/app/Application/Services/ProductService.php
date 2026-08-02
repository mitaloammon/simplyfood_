<?php

namespace App\Application\Services;

use App\Domains\Auth\User\User;
use App\Domains\Product\Category;
use App\Domains\Product\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class ProductService extends BaseService
{
    protected string $modelClass = Product::class;

    public function get(array $filters = []): Collection
    {
        return Product::query()
            ->with('category')
            ->when(isset($filters['ativo']) && $filters['ativo'] !== '', function ($query) use ($filters) {
                $query->where('ativo', filter_var($filters['ativo'], FILTER_VALIDATE_BOOLEAN));
            })
            ->latest('id')
            ->get();
    }

    public function createFromPayload(array $validatedData, ?User $user): Product
    {
        $payload = $this->normalizePayload($validatedData, $user, true);
        return Product::query()->create($payload);
    }

    public function updateFromPayload(int|string $id, array $validatedData, ?User $user): Product
    {
        $product = Product::query()->findOrFail($id);
        $payload = $this->normalizePayload($validatedData, $user, false);
        $product->update($payload);

        return $product->fresh(['category']);
    }

    public function getQuickCreateOptions(?User $user): array
    {
        return [
            'categories' => Category::query()
                ->where('active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                ])
                ->values(),
            'units' => collect(config('products.units', []))
                ->map(fn (string $label, string $value) => [
                    'value' => $value,
                    'label' => $label,
                ])
                ->values(),
            'defaults' => [
                'ativo' => true,
                'controla_estoque' => false,
                'produzido_cozinha' => true,
                'delivery' => true,
                'balcao' => true,
                'mesa' => true,
                'retirada' => true,
                'unidade' => config('products.default_unit', 'UN'),
            ],
            'permissions' => [
                'can_create' => $user !== null,
                'can_update' => $user !== null,
            ],
            'validation_messages' => [
                'nome' => 'Nome do produto e obrigatorio.',
                'category_id' => 'Categoria e obrigatoria.',
                'preco_venda' => 'Preco de venda deve ser maior que zero.',
                'unidade' => 'Unidade e obrigatoria.',
                'codigo_barras' => 'Codigo de barras deve ser unico quando informado.',
            ],
        ];
    }

    private function normalizePayload(array $validatedData, ?User $user, bool $isCreate): array
    {
        $salePrice = isset($validatedData['preco_venda'])
            ? (float) $validatedData['preco_venda']
            : (float) ($validatedData['preco'] ?? 0);

        $payload = [
            'category_id' => (int) $validatedData['category_id'],
            'nome' => $validatedData['nome'],
            'descricao' => $validatedData['descricao'] ?? null,
            'preco' => $salePrice,
            'preco_venda' => $salePrice,
            'custo' => array_key_exists('custo', $validatedData) ? $validatedData['custo'] : null,
            'unidade' => $validatedData['unidade'],
            'codigo_barras' => $validatedData['codigo_barras'] ?? null,
            'tempo_preparo' => (int) ($validatedData['tempo_preparo'] ?? 0),
            'ativo' => (bool) ($validatedData['ativo'] ?? true),
            'controla_estoque' => (bool) ($validatedData['controla_estoque'] ?? false),
            'produzido_cozinha' => (bool) ($validatedData['produzido_cozinha'] ?? true),
            'delivery' => (bool) ($validatedData['delivery'] ?? true),
            'balcao' => (bool) ($validatedData['balcao'] ?? true),
            'mesa' => (bool) ($validatedData['mesa'] ?? true),
            'retirada' => (bool) ($validatedData['retirada'] ?? true),
        ];

        if (!empty($validatedData['imagem']) && is_string($validatedData['imagem'])) {
            $payload['imagem'] = $validatedData['imagem'];
        }

        if (!empty($validatedData['imagem_file']) && $validatedData['imagem_file'] instanceof UploadedFile) {
            $payload['imagem'] = $validatedData['imagem_file']->store('products', 'public');
        }

        if ($isCreate && $user) {
            $payload['created_by'] = $user->id;
        }

        if ($user) {
            $payload['updated_by'] = $user->id;
        }

        return $payload;
    }
}
