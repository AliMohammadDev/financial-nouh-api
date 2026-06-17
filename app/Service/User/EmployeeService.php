<?php

namespace App\Service\User;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeService
{
  public function findAll(): Collection
  {
    return Employee::with('user')->get();
  }

  public function findOne(int $id): Employee
  {
    return Employee::with('user')->findOrFail($id);
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

  public function update(int $id, array $data): Employee
  {
    $employee = Employee::with('user')->findOrFail($id);

    DB::transaction(function () use ($employee, $data) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }

      $employee->user->update(collect($data)->except(['job_title'])->toArray());

      $employee->update(collect($data)->only(['job_title'])->toArray());
    });

    return $employee->load('user');
  }

  public function delete(int $id): bool
  {
    $employee = Employee::findOrFail($id);

    return DB::transaction(function () use ($employee) {
      return (bool) $employee->user()->delete();
    });
  }
}
