<?php

namespace App\Service\User;

use App\Models\Engineer;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EngineerService
{
  public function findAll(): Collection
  {
    return Engineer::with('user')->get();
  }

  public function findOne(int $id): Engineer
  {
    return Engineer::with('user')->findOrFail($id);
  }

  public function create(array $data): Engineer
  {
    return DB::transaction(function () use ($data) {
      $data['password'] = Hash::make($data['password']);

      $user = User::create($data);

      $engineer = Engineer::create([
        'user_id'     => $user->id,
        'job_title'   => $data['job_title'],
        'base_salary' => $data['base_salary'],
      ]);

      return $engineer->load('user');
    });
  }

  public function update(int $id, array $data): Engineer
  {
    $engineer = Engineer::with('user')->findOrFail($id);

    DB::transaction(function () use ($engineer, $data) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }

      $engineer->user->update(collect($data)->except(['job_title', 'base_salary'])->toArray());

      $engineer->update(collect($data)->only(['job_title', 'base_salary'])->toArray());
    });

    return $engineer->load('user');
  }

  public function delete(int $id): bool
  {
    $engineer = Engineer::findOrFail($id);

    return DB::transaction(function () use ($engineer) {
      return (bool) $engineer->user()->delete();
    });
  }
}
