<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transation\CreateTransactionRequest;
use App\Http\Requests\Transation\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Service\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
  public function __construct(
    private TransactionService $transactionService
  ) {}

  public function index(Request $request)
  {
    $paginate = $request->boolean('paginate', false);
    $perPage  = $request->input('per_page', 10);
    $page     = $request->input('page', 1);

    $transactions = $this->transactionService->findAll($paginate, $perPage, $page);

    return TransactionResource::collection($transactions);
  }

  public function store(CreateTransactionRequest $request)
  {
    $transaction = $this->transactionService->create($request->validated());

    return new TransactionResource($transaction);
  }

  public function show(Transaction $transaction)
  {
    $transaction = $this->transactionService->findOne($transaction);

    return new TransactionResource($transaction);
  }

  public function update(UpdateTransactionRequest $request, Transaction $transaction)
  {
    $transaction = $this->transactionService->update($transaction, $request->validated());

    return new TransactionResource($transaction);
  }

  public function destroy(Transaction $transaction)
  {
    $this->transactionService->delete($transaction);

    return response()->json([
      'message' => 'تم حذف المعاملة بنجاح',
    ], 200);
  }
}
