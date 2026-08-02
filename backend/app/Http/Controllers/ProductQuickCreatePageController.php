<?php

namespace App\Http\Controllers;

use App\Application\Services\ProductService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductQuickCreatePageController extends Controller
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    public function __invoke(Request $request): Response
    {
        return Inertia::render('Products/QuickCreate', [
            'quickCreateOptions' => $this->productService->getQuickCreateOptions($request->user()),
        ]);
    }
}
