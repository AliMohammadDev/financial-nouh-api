<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Currency\CreateCurrencyRequest;
use App\Http\Requests\Currency\UpdateCurrencyRequest;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use App\Service\CurrencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CurrencyController extends Controller
{
  public function __construct(
    private CurrencyService $currencyService
  ) {}

  public function index(Request $request): JsonResponse
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $currencies = $this->currencyService->findAll($paginate, $perPage, $page);

    return response()->json(CurrencyResource::collection($currencies));
  }

  public function store(CreateCurrencyRequest $request): JsonResponse
  {
    $currency = $this->currencyService->create($request->validated());
    return response()->json(new CurrencyResource($currency), Response::HTTP_CREATED);
  }

  public function show(Currency $currency): JsonResponse
  {
    $currencyWithRelations = $this->currencyService->findOne($currency);
    return response()->json(new CurrencyResource($currencyWithRelations));
  }

  public function update(UpdateCurrencyRequest $request, Currency $currency): JsonResponse
  {
    $updatedCurrency = $this->currencyService->update($currency, $request->validated());
    return response()->json(new CurrencyResource($updatedCurrency));
  }

  public function destroy(Currency $currency): JsonResponse
  {
    $this->currencyService->delete($currency);
    return response()->json([
      'message' => 'Currency deleted successfully'
    ], Response::HTTP_OK);
  }
}
