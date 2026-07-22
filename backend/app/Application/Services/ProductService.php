<?php

namespace App\Application\Services;

use App\Domains\Product\Product;

class ProductService extends BaseService
{
    protected string $modelClass = Product::class;
}
