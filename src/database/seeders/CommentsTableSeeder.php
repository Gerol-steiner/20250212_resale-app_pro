<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comment; // 追加

class CommentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $numberOfComments = 20; // 作成したいコメントの数

        // ファクトリーを使ってダミーデータを生成
        Comment::factory()->count($numberOfComments)->create();
    }
}
