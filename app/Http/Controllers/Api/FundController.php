<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fund\CreateFundRequest;
use App\Http\Requests\Fund\UpdateFundRequest;
use App\Http\Resources\FundResource;
use App\Service\FundService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class FundController extends Controller
{
  public function __construct(
    private FundService $fundService
  ) {}

  public function index(): JsonResponse
  {
    $funds = $this->fundService->findAll();
    return response()->json(FundResource::collection($funds));
  }

  public function store(CreateFundRequest $request): JsonResponse
  {
    $fund = $this->fundService->create($request->validated());
    return response()->json(new FundResource($fund->load('user')), Response::HTTP_CREATED);
  }

  public function show(int $id): JsonResponse
  {
    $fund = $this->fundService->findOne($id);
    return response()->json(new FundResource($fund));
  }

  public function update(UpdateFundRequest $request, int $id): JsonResponse
  {
    $fund = $this->fundService->update($id, $request->validated());
    return response()->json(new FundResource($fund));
  }

  public function destroy(int $id): JsonResponse
  {
    $this->fundService->delete($id);
    return response()->json([
      'message' => 'Fund deleted successfully'
    ], Response::HTTP_OK);
  }
}
