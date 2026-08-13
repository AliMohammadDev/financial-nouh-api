<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Supplier\CreateSupplierRequest;
use App\Http\Requests\User\Supplier\UpdateSupplierRequest;
use App\Http\Resources\User\SupplierResource;
use App\Models\Supplier;
use App\Service\User\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SupplierController extends Controller
{
  public function __construct(
    private SupplierService $supplierService
  ) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $suppliers = $this->supplierService->findAll($paginate, $perPage, $page);

    return SupplierResource::collection($suppliers);
  }

  public function store(CreateSupplierRequest $request): JsonResponse
  {
    $supplier = $this->supplierService->create(
      $request->validated(),
      $request->file('images')
    );
    return response()->json(new SupplierResource($supplier), Response::HTTP_CREATED);
  }

  public function show(Supplier $supplier): JsonResponse
  {
    $supplierWithRelations = $this->supplierService->findOne($supplier);
    return response()->json(new SupplierResource($supplierWithRelations));
  }

  public function update(UpdateSupplierRequest $request, Supplier $supplier): JsonResponse
  {
    $deletedMediaIds = $request->input('deleted_media_ids', []);

    $updatedSupplier = $this->supplierService->update(
      $supplier,
      $request->validated(),
      $request->file('images'),
      $deletedMediaIds
    );

    return response()->json(new SupplierResource($updatedSupplier));
  }
  public function destroy(Supplier $supplier): JsonResponse
  {
    $this->supplierService->delete($supplier);
    return response()->json([
      'message' => 'Supplier deleted successfully'
    ], Response::HTTP_OK);
  }
}
