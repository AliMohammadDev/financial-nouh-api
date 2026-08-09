<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MoneyExchange\CreateMoneyExchangeRequest;
use App\Http\Requests\MoneyExchange\UpdateMoneyExchangeRequest;
use App\Http\Resources\MoneyExchangeResource;
use App\Models\MoneyExchange;
use App\Service\MoneyExchangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MoneyExchangeController extends Controller
{
  public function __construct(
    private MoneyExchangeService $moneyExchangeService
  ) {}

  public function index(Request $request): JsonResponse
  {
    $paginate = filter_var($request->query('paginate', false), FILTER_VALIDATE_BOOLEAN);
    $perPage  = (int) $request->query('per_page', 10);
    $page     = (int) $request->query('page', 1);

    $exchanges = $this->moneyExchangeService->findAll(
      paginate: $paginate,
      perPage: $perPage,
      page: $page
    );

    if ($paginate) {
      return MoneyExchangeResource::collection($exchanges)->response();
    }

    return response()->json([
      'data' => MoneyExchangeResource::collection($exchanges),
    ], Response::HTTP_OK);
  }

  public function store(CreateMoneyExchangeRequest $request): JsonResponse
  {
    $exchange = $this->moneyExchangeService->create($request->validated());

    return response()->json([
      'message' => 'تم إنشاء العملية بنجاح',
      'data'    => new MoneyExchangeResource($exchange),
    ], Response::HTTP_CREATED);
  }

  public function show(MoneyExchange $moneyExchange): JsonResponse
  {
    $exchange = $this->moneyExchangeService->findOne($moneyExchange);

    return response()->json([
      'data' => new MoneyExchangeResource($exchange),
    ], Response::HTTP_OK);
  }

  public function update(UpdateMoneyExchangeRequest $request, MoneyExchange $moneyExchange): JsonResponse
  {
    $exchange = $this->moneyExchangeService->update($moneyExchange, $request->validated());

    return response()->json([
      'message' => 'تم تحديث العملية بنجاح',
      'data'    => new MoneyExchangeResource($exchange),
    ], Response::HTTP_OK);
  }

  public function destroy(MoneyExchange $moneyExchange): JsonResponse
  {
    $this->moneyExchangeService->delete($moneyExchange);

    return response()->json([
      'message' => 'تم حذف العملية بنجاح',
    ], Response::HTTP_OK);
  }
}
