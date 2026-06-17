<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Supplier\CreateSupplierRequest;
use App\Http\Requests\User\Supplier\UpdateSupplierRequest;
use App\Http\Resources\User\SupplierResource;
use App\Service\User\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SupplierController extends Controller
{
  public function __construct(
    private SupplierService $supplierService
  ) {}

  public function index(): JsonResponse
  {
    $suppliers = $this->supplierService->findAll();
    return response()->json(SupplierResource::collection($suppliers));
  }

  public function store(CreateSupplierRequest $request): JsonResponse
  {
    $supplier = $this->supplierService->create($request->validated());
    return response()->json(new SupplierResource($supplier), Response::HTTP_CREATED);
  }

  public function show(int $id): JsonResponse
  {
    $supplier = $this->supplierService->findOne($id);
    return response()->json(new SupplierResource($supplier));
  }

  public function update(UpdateSupplierRequest $request, int $id): JsonResponse
  {
    $supplier = $this->supplierService->update($id, $request->validated());
    return response()->json(new SupplierResource($supplier));
  }

  public function destroy(int $id): JsonResponse
  {
    $this->supplierService->delete($id);
    return response()->json([
      'message' => 'Supplier deleted successfully'
    ], Response::HTTP_OK);
  }
}
