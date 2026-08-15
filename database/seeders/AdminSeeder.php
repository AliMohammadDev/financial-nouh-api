<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Currency;
use App\Models\Fund;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $user = User::firstOrCreate(
      ['email' => 'hatem@nouh.com'],
      [
        'name'              => 'حاتم الصالح',
        'password'          => Hash::make('password'),
        'phone_number'      => '+963911111111',
        'address'           => 'سوريا - حلب',
        'email_verified_at' => now(),
      ]
    );

    Admin::firstOrCreate(['user_id' => $user->id]);

    $fund = Fund::firstOrCreate(
      ['user_id' => $user->id],
      [
        'name' => 'صندوق حاتم الصالح',
        'type' => 'الرئيسي',
      ]
    );

    $usdCurrency = Currency::where('currency', 'USD')->first();
    if ($usdCurrency && !$fund->currencies()->where('currency_id', $usdCurrency->id)->exists()) {
      $fund->currencies()->attach($usdCurrency->id, [
        'balance' => 0,
      ]);
    }
  }
}
