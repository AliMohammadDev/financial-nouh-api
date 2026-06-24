<?php

namespace App\Service;

use App\Enums\Currency;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
  public function findAll(): Collection
  {
    return Expense::with(['employee.user', 'createdBy', 'expenseable'])->get();
  }

  public function findOne(int $id): Expense
  {
    return Expense::with(['employee.user', 'createdBy', 'expenseable'])->findOrFail($id);
  }

  public function create(array $data): Expense
  {
    return DB::transaction(function () use ($data) {
      $box = $data['expenseable_type']::findOrFail($data['expenseable_id']);

      if ($data['currency'] === Currency::USD->value) {
        $box->decrement('balance_usd', $data['amount']);
      } else {
        $box->decrement('balance_syp', $data['amount']);
      }

      $expense = Expense::create($data);

      return $expense->load(['employee.user', 'createdBy', 'expenseable']);
    });
  }

  public function update(int $id, array $data): Expense
  {
    $expense = Expense::findOrFail($id);

    return DB::transaction(function () use ($expense, $data) {
      $oldBox = $expense->expenseable_type::findOrFail($expense->expenseable_id);

      if ($expense->currency === Currency::USD) {
        $oldBox->increment('balance_usd', $expense->amount);
      } else {
        $oldBox->increment('balance_syp', $expense->amount);
      }

      $targetType  = $data['expenseable_type'] ?? $expense->expenseable_type;
      $targetId    = $data['expenseable_id']   ?? $expense->expenseable_id;
      $newAmount   = $data['amount']            ?? $expense->amount;

      $newCurrency = isset($data['currency']) ? $data['currency'] : $expense->currency->value;

      $newBox = $targetType::findOrFail($targetId);

      if ($newCurrency === Currency::USD->value) {
        $newBox->decrement('balance_usd', $newAmount);
      } else {
        $newBox->decrement('balance_syp', $newAmount);
      }

      $expense->update($data);

      return $expense->load(['employee.user', 'createdBy', 'expenseable']);
    });
  }

  public function delete(int $id): bool
  {
    $expense = Expense::findOrFail($id);

    return DB::transaction(function () use ($expense) {
      $box = $expense->expenseable_type::findOrFail($expense->expenseable_id);

      if ($expense->currency === Currency::USD) {
        $box->increment('balance_usd', $expense->amount);
      } else {
        $box->increment('balance_syp', $expense->amount);
      }

      return (bool) $expense->delete();
    });
  }
}
