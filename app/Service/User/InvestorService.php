<?php

namespace App\Service\User;

use App\Models\Investor;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InvestorService
{
  public function findAll(): Collection
  {
    return Investor::with('user')->get();
  }

  public function findOne(int $id): Investor
  {
    return Investor::with('user')->findOrFail($id);
  }

  public function create(array $data): Investor
  {
    return DB::transaction(function () use ($data) {
      $data['password'] = Hash::make($data['password']);
      $user = User::create($data);

      return Investor::create([
        'user_id' => $user->id,
        'investment_ratio' => $data['investment_ratio']
      ])->load('user');
    });
  }

  public function update(int $id, array $data): Investor
  {
    $investor = Investor::findOrFail($id);

    DB::transaction(function () use ($investor, $data) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }

      // تحديث بيانات الـ User
      $investor->user()->update(array_diff_key($data, ['investment_ratio' => 1]));

      // تحديث نسبة الاستثمار في جدول الـ Investor
      if (isset($data['investment_ratio'])) {
        $investor->update(['investment_ratio' => $data['investment_ratio']]);
      }
    });

    return $investor->load('user');
  }

  public function delete(int $id): bool
  {
    $investor = Investor::findOrFail($id);
    return DB::transaction(function () use ($investor) {
      return (bool) $investor->user()->delete();
    });
  }
}
