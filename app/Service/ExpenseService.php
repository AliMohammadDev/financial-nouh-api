<?php

namespace App\Service;

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
      $expense = Expense::create($data);
      return $expense->load(['employee.user', 'expenseable']);
    });
  }
  public function update(int $id, array $data): Expense
  {
    $expense = Expense::findOrFail($id);
    DB::transaction(function () use ($expense, $data) {
      $expense->update($data);
    });

    return $expense->load(['employee.user', 'createdBy', 'expenseable']);
  }

  public function delete(int $id): bool
  {
    $expense = Expense::findOrFail($id);
    return DB::transaction(function () use ($expense) {
      return (bool) $expense->delete();
    });
  }
}
