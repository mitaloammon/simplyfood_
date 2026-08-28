<?php

namespace App\Http\Controllers\Api;

use App\Application\Customers\CustomerService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\ListCustomersRequest;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customers)
    {
    }

    public function index(ListCustomersRequest $request): JsonResponse
    {
        $customers = $this->customers->paginate(
            $request->user(),
            $request->validated('q'),
            $request->integer('per_page', 20)
        );

        $customers->through(
            fn ($customer) => CustomerResource::make($customer)->resolve($request)
        );

        return response()->json([
            'status' => 'success',
            'data' => $customers,
            'message' => 'Clientes listados com sucesso',
        ]);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customers->create($request->user(), $request->validated());

        return response()->json([
            'status' => 'success',
            'data' => CustomerResource::make($customer)->resolve($request),
            'message' => 'Cliente criado com sucesso',
        ], 201);
    }

    public function show(Request $request, string $customer): JsonResponse
    {
        $customer = $this->customers->find($request->user(), $customer);

        return response()->json([
            'status' => 'success',
            'data' => CustomerResource::make($customer)->resolve($request),
            'message' => 'Cliente encontrado com sucesso',
        ]);
    }

    public function update(UpdateCustomerRequest $request, string $customer): JsonResponse
    {
        $customer = $this->customers->update(
            $request->user(),
            $customer,
            $request->validated()
        );

        return response()->json([
            'status' => 'success',
            'data' => CustomerResource::make($customer)->resolve($request),
            'message' => 'Cliente atualizado com sucesso',
        ]);
    }

    public function destroy(Request $request, string $customer): JsonResponse
    {
        $this->customers->delete($request->user(), $customer);

        return response()->json([
            'status' => 'success',
            'data' => null,
            'message' => 'Cliente removido com sucesso',
        ]);
    }
}
