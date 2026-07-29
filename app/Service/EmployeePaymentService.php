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

  public function __construct(
    private AuditLogService $auditLogService
  ) {}


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
      $payment->load('employee.user');

      $employeeName = $payment->employee?->user?->name ?? 'موظف';
      $amount = $payment->amount ?? 'مبلغ';

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بإنشاء دفعة مالية جديدة للموظف ({$employeeName}) بقيمة: {$amount}",
        affectedTable: 'employee_payments'
      );

      return $payment;
    });
  }

  public function update(EmployeePayment $employeePayment, array $data): EmployeePayment
  {
    DB::transaction(function () use ($employeePayment, $data) {
      $employeePayment->update($data);
      $employeePayment->load('employee.user');

      $employeeName = $employeePayment->employee?->user?->name ?? 'موظف';

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات الدفعة المالية للموظف: {$employeeName}",
        affectedTable: 'employee_payments'
      );
    });

    return $employeePayment->load('employee.user');
  }

  public function delete(EmployeePayment $employeePayment): bool
  {
    return DB::transaction(function () use ($employeePayment) {
      $employeePayment->load('employee.user');
      $employeeName = $employeePayment->employee?->user?->name ?? 'موظف';

      $deleted = (bool) $employeePayment->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف الدفعة المالية الخاصة بالموضف: {$employeeName}",
          affectedTable: 'employee_payments'
        );
      }

      return $deleted;
    });
  }
}
