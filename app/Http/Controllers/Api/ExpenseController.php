<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\CreateExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Service\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ExpenseController extends Controller
{
  public function __construct(
    private ExpenseService $expenseService
  ) {}

  public function index(): JsonResponse
  {
    $expenses = $this->expenseService->findAll();
    return response()->json(ExpenseResource::collection($expenses));
  }

  public function store(CreateExpenseRequest $request): JsonResponse
  {
    $expense = $this->expenseService->create($request->validated());
    return response()->json(new ExpenseResource($expense), Response::HTTP_CREATED);
  }
  public function update(UpdateExpenseRequest $request, int $id): JsonResponse
  {
    $payment = $this->expenseService->update($id, $request->validated());
    return response()->json(new ExpenseResource($payment));
  }

  public function show(int $id): JsonResponse
  {
    $expense = $this->expenseService->findOne($id);
    return response()->json(new ExpenseResource($expense));
  }

  public function destroy(int $id): JsonResponse
  {
    $this->expenseService->delete($id);
    return response()->json([
      'message' => 'Expense deleted successfully'
    ], Response::HTTP_OK);
  }
}
