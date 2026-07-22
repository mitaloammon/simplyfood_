<?php

namespace App\Http\Controllers;

use App\Application\Services\CustomerService;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Resources\CustomerResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CustomerController extends BaseController
{
    public function __construct(CustomerService $service)
    {
        parent::__construct($service);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->service->create($request->validated());

        return response()->json([
            'data' => new CustomerResource($customer),
            'message' => 'Cliente criado!'
        ], Response::HTTP_CREATED);
    }

    public function get(Request $request): JsonResponse
    {
        $customers = $this->service->get($request->all());
        $customers->load(['addresses']); // eager load addresses
        
        return response()->json([
            'status' => 'success',
            'data' => CustomerResource::collection($customers)
        ], Response::HTTP_OK);
    }

    public function show(int|string $id): JsonResponse
    {
        $customer = $this->service->find($id);
        $customer->load(['addresses']);
        
        return response()->json([
            'status' => 'success',
            'data' => new CustomerResource($customer)
        ], Response::HTTP_OK);
    }

    public function post(Request $request): JsonResponse
    {
        $storeRequest = StoreCustomerRequest::createFrom($request);
        $validated = validator($storeRequest->all(), $storeRequest->rules())->validate();

        $customer = $this->service->post($validated);
        
        return response()->json([
            'status' => 'success',
            'data' => new CustomerResource($customer)
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        $storeRequest = StoreCustomerRequest::createFrom($request);
        $validated = validator($storeRequest->all(), $storeRequest->rules())->validate();

        $customer = $this->service->update($id, $validated);
        
        return response()->json([
            'status' => 'success',
            'data' => new CustomerResource($customer)
        ], Response::HTTP_OK);
    }
}
