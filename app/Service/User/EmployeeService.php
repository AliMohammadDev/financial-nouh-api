<?php

namespace App\Service\User;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmployeeService
{
  public function findAll(
    bool $paginate = false,
    int $perPage = 10,
    int $page = 1,
    array $columns = ["*"]
  ): LengthAwarePaginator|Collection {

    $filters = [
      AllowedFilter::callback('search', function ($query, $value) {
        $query->whereHas('user', function ($q) use ($value) {
          $q->where('name', 'like', "%{$value}%")
            ->orWhere('email', 'like', "%{$value}%")
            ->orWhere('phone_number', 'like', "%{$value}%");
        })->orWhere('job_title', 'like', "%{$value}%");
      }),
    ];

    $query = QueryBuilder::for(Employee::class)
      ->with([
        'user.funds.currencies',
      ])
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

  public function findOne(Employee $employee): Employee
  {
    return $employee->load('user.funds.currencies');
  }

  public function create(array $data): Employee
  {
    return DB::transaction(function () use ($data) {
      $data['password'] = Hash::make($data['password']);

      $user = User::create($data);

      $employee = Employee::create([
        'user_id'   => $user->id,
        'job_title' => $data['job_title']
      ]);

      return $employee->load('user');
    });
  }

  public function update(Employee $employee, array $data): Employee
  {
    DB::transaction(function () use ($employee, $data) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }

      $employee->user->update(collect($data)->except(['job_title'])->toArray());

      if (isset($data['job_title'])) {
        $employee->update(collect($data)->only(['job_title'])->toArray());
      }
    });

    return $employee->load('user');
  }

  public function delete(Employee $employee): bool
  {
    return DB::transaction(function () use ($employee) {
      return (bool) $employee->user()->delete();
    });
  }
}
