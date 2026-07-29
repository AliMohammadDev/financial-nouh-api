<?php

namespace App\Service;

use App\Models\ProjectFund;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProjectFundService
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

    $query = ProjectFund::with(['project', 'currencies'])
      ->latest();

    if ($paginate) {
      return $query->paginate(
        perPage: $perPage,
        page: $page,
        columns: $columns,
      );
    }

    return $query->get($columns);
  }

  public function findOne(int $id): ProjectFund
  {
    return ProjectFund::with(['project', 'currencies'])->findOrFail($id);
  }

  public function create(array $data): ProjectFund
  {
    return DB::transaction(function () use ($data) {
      $fund = ProjectFund::create($data);

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بإنشاء صندوق مشروع جديد: {$fund->name}",
        affectedTable: 'project_funds'
      );

      return $fund;
    });
  }

  public function update(int $id, array $data): ProjectFund
  {
    $fund = ProjectFund::findOrFail($id);

    DB::transaction(function () use ($fund, $data) {
      $fund->update($data);

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات صندوق المشروع: {$fund->name}",
        affectedTable: 'project_funds'
      );
    });

    return $fund;
  }

  public function delete(int $id): bool
  {
    $fund = ProjectFund::findOrFail($id);

    return DB::transaction(function () use ($fund) {
      $fundName = $fund->name ?? 'صندوق';

      $deleted = (bool) $fund->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف صندوق المشروع: {$fundName}",
          affectedTable: 'project_funds'
        );
      }

      return $deleted;
    });
  }
  public function attachCurrency(ProjectFund $projectFund, array $data): ProjectFund
  {
    return DB::transaction(function () use ($projectFund, $data) {
      $projectFund->currencies()->syncWithoutDetaching([
        $data['currency_id'] => ['balance' => $data['balance']]
      ]);
      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بربط عملة جديدة (برصيد: {$data['balance']}) بالصندوق: {$projectFund->name}",
        affectedTable: 'project_fund_currencies' 
      );


      return $projectFund->load(['project', 'currencies']);
    });
  }
}