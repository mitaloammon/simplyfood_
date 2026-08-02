<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('id');
        $unitValues = array_keys(config('products.units', []));

        return [
            'category_id' => 'required|integer|exists:categories,id',
            'nome' => 'required|string|max:150',
            'descricao' => 'nullable|string|max:1000',
            'preco_venda' => 'required_without:preco|numeric|gt:0',
            'preco' => 'required_without:preco_venda|numeric|gt:0',
            'custo' => 'nullable|numeric|min:0',
            'unidade' => ['required', 'string', Rule::in($unitValues)],
            'codigo_barras' => [
                'nullable',
                'string',
                'max:64',
                Rule::unique('products', 'codigo_barras')->ignore($productId),
            ],
            'imagem' => 'nullable|string|max:2048',
            'imagem_file' => 'nullable|file|mimes:png,jpeg,jpg,webp|max:4096',
            'tempo_preparo' => 'nullable|integer|min:0',
            'ativo' => 'nullable|boolean',
            'controla_estoque' => 'nullable|boolean',
            'produzido_cozinha' => 'nullable|boolean',
            'delivery' => 'nullable|boolean',
            'balcao' => 'nullable|boolean',
            'mesa' => 'nullable|boolean',
            'retirada' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do produto e obrigatorio.',
            'category_id.required' => 'A categoria e obrigatoria.',
            'preco_venda.required_without' => 'O preco de venda e obrigatorio.',
            'preco.required_without' => 'O preco de venda e obrigatorio.',
            'preco_venda.gt' => 'O preco de venda deve ser maior que zero.',
            'preco.gt' => 'O preco de venda deve ser maior que zero.',
            'unidade.required' => 'A unidade e obrigatoria.',
            'codigo_barras.unique' => 'Codigo de barras ja cadastrado.',
        ];
    }
}
