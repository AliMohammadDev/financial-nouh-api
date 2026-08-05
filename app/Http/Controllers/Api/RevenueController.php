<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Revenue\CreateRevenueRequest;
use App\Http\Requests\Revenue\UpdateRevenueRequest;
use App\Http\Resources\RevenueResource;
use App\Models\Revenue;
use App\Service\RevenueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RevenueController extends Controller
{
  public function __construct(
    private RevenueService $revenueService
  ) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $revenues = $this->revenueService->findAll($paginate, $perPage, $page);

    return RevenueResource::collection($revenues);
  }

  public function store(CreateRevenueRequest $request): JsonResponse
  {
    $revenue = $this->revenueService->create($request->validated());
    return response()->json(new RevenueResource($revenue), Response::HTTP_CREATED);
  }

  public function show(Revenue $revenue): JsonResponse
  {
    $revenueWithRelations = $this->revenueService->findOne($revenue);
    return response()->json(new RevenueResource($revenueWithRelations));
  }

  public function update(UpdateRevenueRequest $request, Revenue $revenue): JsonResponse
  {
    $updatedRevenue = $this->revenueService->update($revenue, $request->validated());
    return response()->json(new RevenueResource($updatedRevenue));
  }

  public function destroy(Revenue $revenue): JsonResponse
  {
    $this->revenueService->delete($revenue);
    return response()->json([
      'message' => 'تم حذف الإيراد بنجاح'
    ], Response::HTTP_OK);
  }
}
