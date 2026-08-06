<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Client;
use App\Models\Craftsmen;
use App\Models\Currency;
use App\Models\DailyWorker;
use App\Models\Employee;
use App\Models\Engineer;
use App\Models\Fund;
use App\Models\Investor;
use App\Models\Supplier;
use App\Models\Trustee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
  public function run(): void
  {
    $arabicNames = [
      'محمد أحمد الأحمد',
      'أحمد محمود علي',
      'إبراهيم خالد السعيد',
      'عمر فاروق حسن',
      'يوسف عبد الله المعتز',
      'خالد عبد الرحمن النجار',
      'محمود حسن الحلاق',
      'علي حسين الشامي',
      'سعيد صالح الخطيب',
      'رائد عبد العزيز الصالح',
      'طارق زياد العمري',
      'فادي سمير القاسم',
      'سامر كمال الدسوقي',
      'مصطفى عادل الكردي',
      'باسل رفيق الطحان',
      'رامي عدنان الباشا',
      'وليد توفيق الحمصي',
      'زياد مروان البرماوي',
      'نبيل عماد العبد الله',
      'ياسر ياسين الجابر',
      'جمال الدين ناصر',
      'أيمن حامد الشهابي',
      'هشام رائد الصباغ',
      'سليمان داود الهواري',
      'جهاد منير الأيوبي',
      'علاء الدين المنصور',
      'مهند فؤاد البغدادي',
      'حسام الدين زكي',
      'تامر نايف الدسوقي',
      'مؤيد ماهر الحلبي',
      'سراج الدين البابا',
      'بلال قاسم الرفاعي',
      'معاذ إحسان الشريف',
      'صهيب مصعب الزهراوي',
      'أنس وائل النصولي',
      'هيثم رمزي البيك'
    ];

    shuffle($arabicNames);
    $nameIndex = 0;

    // جلب عملة الدولار (USD) للتأكد من وجودها
    $usdCurrency = Currency::firstOrCreate(
      ['currency' => 'USD'],
      ['symbol' => '$']
    );

    $roles = [
      ['model' => Admin::class, 'name_en' => 'admin', 'count' => 4, 'extra' => []],
      ['model' => Client::class, 'name_en' => 'client', 'count' => 4, 'extra' => []],
      ['model' => Craftsmen::class, 'name_en' => 'craftsmen', 'count' => 4, 'extra' => []],
      ['model' => DailyWorker::class, 'name_en' => 'worker', 'count' => 4, 'extra' => []],
      ['model' => Employee::class, 'name_en' => 'employee', 'count' => 4, 'extra' => ['job_title' => 'محاسب عام']],
      ['model' => Engineer::class, 'name_en' => 'engineer', 'count' => 4, 'extra' => ['job_title' => 'مهندس مدني', 'base_salary' => 1200.00]],
      ['model' => Investor::class, 'name_en' => 'investor', 'count' => 4, 'extra' => ['investment_ratio' => 25.00]],
      ['model' => Supplier::class, 'name_en' => 'supplier', 'count' => 4, 'extra' => []],
      ['model' => Trustee::class, 'name_en' => 'trustee', 'count' => 4, 'extra' => []],
    ];

    $counter = 1;

    foreach ($roles as $roleData) {
      for ($i = 0; $i < $roleData['count']; $i++) {
        $name = $arabicNames[$nameIndex++];

        $englishEmail = $roleData['name_en'] . '_' . $counter++ . '@example.com';

        $user = User::create([
          'name'              => $name,
          'email'             => $englishEmail,
          'password'          => Hash::make('password'),
          'phone_number'      => '+9639' . rand(11111111, 99999999),
          'address'           => 'سوريا - دمشق',
          'email_verified_at' => now(),
        ]);

        $data = array_merge(['user_id' => $user->id], $roleData['extra']);
        $roleData['model']::create($data);

        // إنشاء الصندوق للمستخدم
        $fund = Fund::create([
          'user_id' => $user->id,
          'name'    => 'صندوق ' . $name,
        ]);

        // ربط الصندوق بعملة الدولار مع إعطائه رصيد مبدئي 1000$
        if ($usdCurrency) {
          $fund->currencies()->attach($usdCurrency->id, [
            'balance' => 1000.00,
          ]);
        }
      }
    }
  }
}
