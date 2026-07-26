<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\CreateExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Service\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExpenseController extends Controller
{
  public function __construct(
    private ExpenseService $expenseService
  ) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $expenses = $this->expenseService->findAll($paginate, $perPage, $page);

    return ExpenseResource::collection($expenses);
  }

  public function store(CreateExpenseRequest $request): JsonResponse
  {
    $expense = $this->expenseService->create($request->validated());
    return response()->json(new ExpenseResource($expense), Response::HTTP_CREATED);
  }

  public function show(Expense $expense): JsonResponse
  {
    $expenseWithRelations = $this->expenseService->findOne($expense);
    return response()->json(new ExpenseResource($expenseWithRelations));
  }

  public function update(UpdateExpenseRequest $request, Expense $expense): JsonResponse
  {
    $updatedExpense = $this->expenseService->update($expense, $request->validated());
    return response()->json(new ExpenseResource($updatedExpense));
  }

  public function destroy(Expense $expense): JsonResponse
  {
    $this->expenseService->delete($expense);
    return response()->json([
      'message' => 'Expense deleted successfully'
    ], Response::HTTP_OK);
  }
}
