<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Craftsmen;
use App\Models\DailyWorker;
use App\Models\Employee;
use App\Models\Engineer;
use App\Models\Supplier;
use App\Models\Trustee;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // 1. إنشاء 10 عملاء (Clients)
    User::factory(10)->create()->each(function ($user) {
      Client::create(['user_id' => $user->id]);
    });

    // 2. إنشاء 5 حرفيين (Craftsmen)
    User::factory(5)->create()->each(function ($user) {
      Craftsmen::create(['user_id' => $user->id]);
    });

    // 3. إنشاء 8 عمال مياومة (Daily Workers)
    User::factory(8)->create()->each(function ($user) {
      DailyWorker::create(['user_id' => $user->id]);
    });

    // 4. إنشاء 5 موظفين (Employees) مع عناوين وظيفية وهمية
    User::factory(5)->create()->each(function ($user) {
      Employee::create([
        'user_id'   => $user->id,
        'job_title' => fake()->randomElement(['HR Specialist', 'Accountant', 'Secretary']),
      ]);
    });

    // 5. إنشاء 4 مهندسين (Engineers) مع رواتب وعناوين وظيفية وهمية
    User::factory(4)->create()->each(function ($user) {
      Engineer::create([
        'user_id'     => $user->id,
        'job_title'   => fake()->randomElement(['Civil Engineer', 'Site Engineer', 'Project Manager']),
        'base_salary' => fake()->randomFloat(2, 1500, 4000),
      ]);
    });

    // 6. إنشاء 5 موردين (Suppliers)
    User::factory(5)->create()->each(function ($user) {
      Supplier::create(['user_id' => $user->id]);
    });

    // 7. إنشاء 3 أمناء/أوصياء (Trustees) مع تحديد صلة القرابة وهمياً
    User::factory(3)->create()->each(function ($user) {
      Trustee::create([
        'user_id'          => $user->id,
        'kinship_relation' => fake()->randomElement(['Brother', 'Father', 'Partner']),
      ]);
    });
  }
}
