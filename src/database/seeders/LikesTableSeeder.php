<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Like; // 追加

class LikesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $numberOfLikes = 5; // 作成したい「いいね」の数

        // ファクトリーを使ってダミーデータを生成
        Like::factory()->count($numberOfLikes)->create();
    }
}
