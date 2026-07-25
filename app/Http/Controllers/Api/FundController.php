<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fund\AttachCurrencyRequest;
use App\Http\Requests\Fund\CreateFundRequest;
use App\Http\Requests\Fund\UpdateFundRequest;
use App\Http\Resources\FundResource;
use App\Models\Fund;
use App\Service\FundService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FundController extends Controller
{
  public function __construct(
    private FundService $fundService
  ) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $funds = $this->fundService->findAll($paginate, $perPage, $page);

    return FundResource::collection($funds);
  }

  public function store(CreateFundRequest $request): JsonResponse
  {
    $fund = $this->fundService->create($request->validated());
    return response()->json(new FundResource($fund->load('user')), Response::HTTP_CREATED);
  }

  public function show(Fund $fund): JsonResponse
  {
    $fundWithRelations = $this->fundService->findOne($fund);
    return response()->json(new FundResource($fundWithRelations));
  }

  public function update(UpdateFundRequest $request, Fund $fund): JsonResponse
  {
    $updatedFund = $this->fundService->update($fund, $request->validated());
    return response()->json(new FundResource($updatedFund));
  }

  public function destroy(Fund $fund): JsonResponse
  {
    $this->fundService->delete($fund);
    return response()->json([
      'message' => 'Fund deleted successfully'
    ], Response::HTTP_OK);
  }

  public function attachCurrency(AttachCurrencyRequest $request, Fund $fund): JsonResponse
  {
    $updatedFund = $this->fundService->attachCurrency($fund, $request->validated());

    return response()->json([
      'message' => 'Currency attached to fund successfully',
      'data'    => new FundResource($updatedFund)
    ], Response::HTTP_OK);
  }
}
