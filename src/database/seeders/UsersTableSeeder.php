<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;  // 追加

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->count(10)->create();
    }
}
