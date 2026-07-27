<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Department;
use App\Models\Project;
use Illuminate\Database\Seeder;

class DepartmentProjectSeeder extends Seeder
{
  public function run(): void
  {
    $departmentsData = [
      [
        'name' => 'قسم الهندسة المدنية والإنشاءات',
        'main_manager' => 'مهندس زياد الأحمد',
      ],
      [
        'name' => 'قسم التصميم المعماري والديكور',
        'main_manager' => 'مهندسة ريم الحمصي',
      ],
      [
        'name' => 'قسم إدارة المشاريع والبنى التحتية',
        'main_manager' => 'دكتور سامر الخطيب',
      ],
    ];

    $createdDepartments = [];
    foreach ($departmentsData as $dept) {
      $createdDepartments[] = Department::create($dept);
    }

    $clientIds = Client::pluck('id')->toArray();
    if (empty($clientIds)) {
      $clientIds = [Client::create(['user_id' => 1])->id];
    }

    $projectsData = [
      [
        'name' => 'إنشاء برج الجنان السكني (هيكل خرساني)',
        'expected_cost' => 250000.00,
        'status' => 'pending',
      ],
      [
        'name' => 'مشروع تعبيد وتزفيت الطرق الرئيسية',
        'expected_cost' => 120000.00,
        'status' => 'pending',
      ],
      [
        'name' => 'بناء جسر المشاة الحيوي',
        'expected_cost' => 85000.00,
        'status' => 'pending',
      ],

      [
        'name' => 'تصميم الواجهات الخارجية لمجمع الروضة',
        'expected_cost' => 45000.00,
        'status' => 'pending',
      ],
      [
        'name' => 'التصميم الداخلي لفلل الياسمين السكنية',
        'expected_cost' => 30000.00,
        'status' => 'pending',
      ],
      [
        'name' => 'تصميم وتنسيق الحدائق العامة (لاندسكيب)',
        'expected_cost' => 20000.00,
        'status' => 'pending',
      ],
    ];

    foreach ($projectsData as $index => $proj) {
      Project::create([
        'name'          => $proj['name'],
        'department_id' => $createdDepartments[$index % count($createdDepartments)]->id,
        'client_id'     => $clientIds[array_rand($clientIds)],
        'expected_cost' => $proj['expected_cost'],
        'status'        => $proj['status'],
      ]);
    }
  }
}
