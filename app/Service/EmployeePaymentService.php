<?php

namespace App\Service;

use App\Models\EmployeePayment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmployeePaymentService
{
  public function findAll(
    bool $paginate = false,
    int $perPage = 10,
    int $page = 1,
    array $columns = ["*"]
  ): LengthAwarePaginator|Collection {

    $filters = [
      AllowedFilter::callback('search', function ($query, $value) {
        $query->whereHas('employee.user', function ($q) use ($value) {
          $q->where('name', 'like', "%{$value}%")
            ->orWhere('email', 'like', "%{$value}%");
        });
      }),
    ];

    $query = QueryBuilder::for(EmployeePayment::class)
      ->with(['employee.user', 'companyFundCurrency.companyFund', 'companyFundCurrency.currency'])
      ->allowedFilters(...$filters)
      ->defaultSort('-created_at');

    if ($paginate) {
      return $query->paginate(
        perPage: $perPage,
        page: $page,
        columns: $columns,
      );
    }

    return $query->get($columns);
  }

  public function findOne(EmployeePayment $employeePayment): EmployeePayment
  {
    return $employeePayment->load(['employee.user', 'companyFundCurrency.companyFund', 'companyFundCurrency.currency']);
  }

  public function create(array $data): EmployeePayment
  {
    return DB::transaction(function () use ($data) {
      $payment = EmployeePayment::create($data);
      return $payment->load('employee.user');
    });
  }

  public function update(EmployeePayment $employeePayment, array $data): EmployeePayment
  {
    DB::transaction(function () use ($employeePayment, $data) {
      $employeePayment->update($data);
    });

    return $employeePayment->load('employee.user');
  }

  public function delete(EmployeePayment $employeePayment): bool
  {
    return DB::transaction(function () use ($employeePayment) {
      return (bool) $employeePayment->delete();
    });
  }
}
