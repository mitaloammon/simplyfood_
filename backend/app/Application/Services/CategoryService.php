<?php

namespace App\Application\Services;

use App\Domains\Product\Category;

class CategoryService extends BaseService
{
    protected string $modelClass = Category::class;
}
