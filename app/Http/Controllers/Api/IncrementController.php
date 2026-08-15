<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Increment\CreateIncrementRequest;
use App\Http\Requests\Increment\UpdateIncrementRequest;
use App\Http\Resources\IncrementResource;
use App\Models\Increment;
use App\Service\IncrementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IncrementController extends Controller
{
  public function __construct(
    private IncrementService $incrementService
  ) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $increments = $this->incrementService->findAll($paginate, $perPage, $page);

    return IncrementResource::collection($increments);
  }

  public function store(CreateIncrementRequest $request): JsonResponse
  {
    $increment = $this->incrementService->create($request->validated());
    return response()->json(new IncrementResource($increment), Response::HTTP_CREATED);
  }

  public function show(Increment $increment): JsonResponse
  {
    $incrementData = $this->incrementService->findOne($increment);
    return response()->json(new IncrementResource($incrementData));
  }

  public function update(UpdateIncrementRequest $request, Increment $increment): JsonResponse
  {
    $updatedIncrement = $this->incrementService->update($increment, $request->validated());
    return response()->json(new IncrementResource($updatedIncrement));
  }

  public function destroy(Increment $increment): JsonResponse
  {
    $this->incrementService->delete($increment);
    return response()->json([
      'message' => 'تم حذف الزيادة بنجاح'
    ], Response::HTTP_OK);
  }
}
