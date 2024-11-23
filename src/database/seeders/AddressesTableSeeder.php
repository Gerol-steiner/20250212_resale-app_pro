<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Address; // 追加
use App\Models\User; // 追加

class AddressesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // ユーザーをランダムに15人取得
        $users = User::inRandomOrder()->take(8)->get();

        foreach ($users as $user) {
            Address::factory()->create(['user_id' => $user->id]);
        }
    }
}
