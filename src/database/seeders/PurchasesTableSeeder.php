<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Purchase; // 追加

class PurchasesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Purchase::factory()->count(4)->create();
    }
}
