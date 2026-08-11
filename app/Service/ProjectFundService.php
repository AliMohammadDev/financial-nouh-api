<?php

namespace App\Service;

use App\Models\ProjectFund;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

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

    $filters = [
      AllowedFilter::callback('search', function ($query, $value) {
        $query->where('name', 'like', "%{$value}%")
          ->orWhereHas('project', function ($q) use ($value) {
            $q->where('name', 'like', "%{$value}%");
          });
      }),
      AllowedFilter::exact('project_id'),
    ];

    $query = QueryBuilder::for(ProjectFund::class)
      ->select('project_funds.*')
      ->with(['project', 'currencies'])
      ->allowedFilters(...$filters)
      ->allowedSorts(
        'created_at',
        'id',
        'name',
        AllowedSort::callback('project_name', function ($query, $descending) {
          $direction = $descending ? 'desc' : 'asc';
          $query->join('projects', 'project_funds.project_id', '=', 'projects.id')
            ->orderBy('projects.name', $direction);
        })
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
    if ($fund->currencies()->exists()) {
      abort(422, 'لا يمكن حذف صندوق المشروع لأنه يحتوي على أرصدة مالية مسجلة.');
    }

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
