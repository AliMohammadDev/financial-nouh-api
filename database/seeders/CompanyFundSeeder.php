<?php

namespace Database\Seeders;

use App\Models\CompanyFund;
use Illuminate\Database\Seeder;

class CompanyFundSeeder extends Seeder
{
  public function run(): void
  {
    $companyFunds = [
      'صندوق الشركة الرئيسي (الخزنة العامة)',
    ];


    foreach ($companyFunds as $fundName) {
      $fund = CompanyFund::create([
        'name' => $fundName,
      ]);
    }
  }
}
