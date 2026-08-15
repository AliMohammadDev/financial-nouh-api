<?php

namespace Database\Seeders;

use App\Models\CompanyFund;
use App\Models\Currency;
use Illuminate\Database\Seeder;

class CompanyFundSeeder extends Seeder
{
  public function run(): void
  {
    $usdCurrency = Currency::firstOrCreate(
      ['currency' => 'USD'],
      ['symbol' => '$']
    );

    $companyFunds = [
      'صندوق الشركة الرئيسي (الخزنة العامة)',
    ];

    foreach ($companyFunds as $fundName) {
      $fund = CompanyFund::create([
        'name' => $fundName,
        'type' => 'الرئيسي',
      ]);
      $fund->currencies()->attach($usdCurrency->id, [
        'balance' => 10000.00,
      ]);
    }
  }
}
