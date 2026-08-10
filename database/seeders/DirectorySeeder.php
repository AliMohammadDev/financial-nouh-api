<?php

namespace Database\Seeders;

use App\Models\Directory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DirectorySeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    Directory::create([
      'dir_name'      => 'المستخدمين',
      'dir_path'      => '/',
      'parent_dir_id' => null,
      'is_locked'     => true,
    ]);

    Directory::create([
      'dir_name'      => 'المشاريع',
      'dir_path'      => '/',
      'parent_dir_id' => null,
      'is_locked'     => true,
    ]);
  }
}