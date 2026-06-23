<?php

namespace App\Service;

use App\Models\EmployeePayment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EmployeePaymentService
{
  public function findAll(): Collection
  {
    return EmployeePayment::with('employee.user')->get();
  }

  public function findOne(int $id): EmployeePayment
  {
    return EmployeePayment::with('employee.user')->findOrFail($id);
  }

  public function create(array $data): EmployeePayment
  {
    return DB::transaction(function () use ($data) {
      return EmployeePayment::create($data);
    });
  }

  public function update(int $id, array $data): EmployeePayment
  {
    $payment = EmployeePayment::findOrFail($id);

    DB::transaction(function () use ($payment, $data) {
      $payment->update($data);
    });

    return $payment->load('employee.user');
  }

  public function delete(int $id): bool
  {
    $payment = EmployeePayment::findOrFail($id);

    return DB::transaction(function () use ($payment) {
      return (bool) $payment->delete();
    });
  }
}
