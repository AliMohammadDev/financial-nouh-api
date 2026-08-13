<?php

namespace App\Service;

use App\Models\CompanyFund;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CompanyFundService
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
        $query->where('name', 'like', "%{$value}%");
      }),
      AllowedFilter::partial('name'),
    ];

    $query = QueryBuilder::for(CompanyFund::class)
      ->select('company_funds.*')
      ->with(['currencies'])
      ->allowedFilters(...$filters)
      ->allowedSorts(
        'created_at',
        'id',
        'name'
      )
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

  public function findOne(int $id): CompanyFund
  {
    return CompanyFund::with(['currencies'])->findOrFail($id);
  }

  public function create(array $data): CompanyFund
  {
    return DB::transaction(function () use ($data) {
      $fund = CompanyFund::create($data);

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بإنشاء صندوق شركة جديد: {$fund->name}",
        affectedTable: 'company_funds'
      );

      return $fund;
    });
  }

  public function update(int $id, array $data): CompanyFund
  {
    $fund = CompanyFund::findOrFail($id);

    DB::transaction(function () use ($fund, $data) {
      $fund->update($data);

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات صندوق الشركة: {$fund->name}",
        affectedTable: 'company_funds'
      );
    });

    return $fund;
  }

  public function delete(int $id): bool
  {
    $fund = CompanyFund::findOrFail($id);
    if ($fund->currencies()->exists()) {
      abort(422, 'لا يمكن حذف صندوق الشركة لأنه يحتوي على أرصدة مالية مسجلة.');
    }
    return DB::transaction(function () use ($fund) {
      $fundName = $fund->name ?? 'صندوق الشركة';

      $deleted = (bool) $fund->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف صندوق الشركة: {$fundName}",
          affectedTable: 'company_funds'
        );
      }

      return $deleted;
    });
  }

  public function attachCurrency(CompanyFund $companyFund, array $data): CompanyFund
  {
    return DB::transaction(function () use ($companyFund, $data) {
      $companyFund->currencies()->syncWithoutDetaching([
        $data['currency_id'] => ['balance' => $data['balance']]
      ]);

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بربط عملة جديدة (برصيد: {$data['balance']}) بصندوق الشركة: {$companyFund->name}",
        affectedTable: 'company_fund_currencies'
      );

      return $companyFund->load(['currencies']);
    });
  }

  public function detachCurrency(CompanyFund $companyFund, array $data): CompanyFund
  {
    return DB::transaction(function () use ($companyFund, $data) {
      $companyFund->currencies()->detach($data['currency_id']);

      $this->auditLogService->log(
        actionType: 'حذف',
        description: "قام بفك ارتباط العملة عن صندوق الشركة: {$companyFund->name}",
        affectedTable: 'company_fund_currencies'
      );

      return $companyFund->load(['currencies']);
    });
  }
}
