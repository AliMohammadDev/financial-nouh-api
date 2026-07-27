<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Material;
use Illuminate\Database\Seeder;

class ItemMaterialSeeder extends Seeder
{
  public function run(): void
  {
    $itemsData = [
      [
        'name' => 'أعمال التمديدات الصحية والمياه',
        'description' => 'يشمل كافة توريدات التركيبات الصحية وخلاطات المياه وأنابيب الصرف',
        'materials' => [
          ['name' => 'خلاط مياه حامي وبارد (مغسلة)', 'description' => 'خلاط إيطالي عالي الجودة'],
          ['name' => 'حنفية صنبور كروم', 'description' => 'حنفية مجالي مفردة'],
          ['name' => 'أنابيب صرف صحي قطر 4 إنش', 'description' => 'أنابيب بلاستيكية ضاغطة'],
          ['name' => 'كرسي حمام عربي مع الصندوق', 'description' => 'بورسلان أبيض فاخر'],
        ]
      ],
      [
        'name' => 'أعمال التمديدات الكهربائية',
        'description' => 'يشمل الأسلاك والكابلات واللوحات الكهربائية ومخارج الإضاءة',
        'materials' => [
          ['name' => 'شريط كهربائي عازل (تلق)', 'description' => 'رول شريط لاصق عازل للكهرباء'],
          ['name' => 'أسلاك نحاسية 2.5 مم', 'description' => 'سلك نحاس مرن للإنارة'],
          ['name' => 'قاطع كهربائي أوتوماتيكي 32 أمبير', 'description' => 'قاطع حماية رئيسي'],
          ['name' => 'مفتاح إنارة مفرد مع إطار', 'description' => 'ماركة ممتازة لون أبيض'],
        ]
      ],
      [
        'name' => 'أعمال العزل المائي والحراري',
        'description' => 'مواتير وعوازل الأسطح والحمامات',
        'materials' => [
          ['name' => 'رولات عزل رولات أسفلتية (رولات بتومين)', 'description' => 'عزل مائي للأسطح سمك 4 ملم'],
          ['name' => 'مادة عزل رغوية (فوم)', 'description' => 'عزل حراري ومائي للأسطح'],
        ]
      ]
    ];

    foreach ($itemsData as $itemData) {
      $materials = $itemData['materials'];
      unset($itemData['materials']);

      $item = Item::create($itemData);

      foreach ($materials as $material) {
        $item->materials()->create($material);
      }
    }
  }
}
