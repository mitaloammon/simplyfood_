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
    protected CustomerService $customerService;

    public function __construct(CustomerService $service)
    {
        parent::__construct($service);
        $this->customerService = $service;
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $payload = $request->validated();

        if ($request->user()) {
            $payload['user_id'] = $request->user()->id;
        }

        $customer = $this->customerService->post($payload);

        return response()->json([
            'status' => 'success',
            'data' => new CustomerResource($customer),
            'message' => 'Cliente criado!'
        ], Response::HTTP_CREATED);
    }

    public function get(Request $request): JsonResponse
    {
        $user = $request->user();
        $customers = $user
            ? $this->customerService->getByUser($user->id, $request->all())
            : $this->customerService->get($request->all());

        $customers->load(['addresses']); // eager load addresses
        
        return response()->json([
            'status' => 'success',
            'data' => CustomerResource::collection($customers)
        ], Response::HTTP_OK);
    }

    public function show(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        $customer = $user
            ? $this->customerService->findByUserOrFail($id, $user->id)
            : $this->customerService->find($id);

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

        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
        }

        $customer = $this->customerService->post($validated);
        
        return response()->json([
            'status' => 'success',
            'data' => new CustomerResource($customer)
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        $storeRequest = StoreCustomerRequest::createFrom($request);
        $validated = validator($storeRequest->all(), $storeRequest->rules())->validate();

        $customer = $request->user()
            ? $this->customerService->updateByUser($id, $request->user()->id, $validated)
            : $this->customerService->update($id, $validated);
        
        return response()->json([
            'status' => 'success',
            'data' => new CustomerResource($customer)
        ], Response::HTTP_OK);
    }

    public function deleted(int|string $id): JsonResponse
    {
        $user = request()->user();
        $success = $user
            ? $this->customerService->deleteByUser($id, $user->id)
            : $this->customerService->deleted($id);

        return response()->json([
            'status' => 'success',
            'success' => $success,
            'message' => $success ? 'Cliente removido com sucesso.' : 'Nao foi possivel remover o cliente.',
        ], Response::HTTP_OK);
    }
}
