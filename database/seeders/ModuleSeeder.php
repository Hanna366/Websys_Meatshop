<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Module;
class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run()
{
    Module::factory()->count(50)->create();
}
}
