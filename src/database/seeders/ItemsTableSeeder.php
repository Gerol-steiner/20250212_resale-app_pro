<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // 追記
use App\Models\User; // 追加
use App\Models\Category; // 追加
use App\Models\Condition; // 追加

class ItemsTableSeeder extends Seeder
{

    public function run()
    {
        // ユーザーID、カテゴリーID、コンディションIDを取得
        $userIds = User::pluck('id');
        $categoryIds = Category::pluck('id', 'name');
        $conditionIds = Condition::pluck('id', 'name');

        $items = [
            [
                'image_url' => 'storage/uploads/items/Armani+Mens+Clock.jpg',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['ファッション'], $categoryIds['メンズ'], $categoryIds['アクセサリー']],
                'condition_id' => $conditionIds['良好'],
                'name' => '腕時計',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'price' => 15000,
                'brand' => 'ブランドA'
            ],
            [
                'image_url' => 'storage/uploads/items/HDD+Hard+Disk.jpg',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['家電']],
                'condition_id' => $conditionIds['目立った傷や汚れなし'],
                'name' => 'HDD',
                'description' => '高速で信頼性の高いハードディスク',
                'price' => 5000,
                'brand' => 'ブランドB'
            ],
            [
                'image_url' => 'storage/uploads/items/iLoveIMG+d.jpg',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['キッチン']],
                'condition_id' => $conditionIds['やや傷や汚れあり'],
                'name' => '玉ねぎ3束',
                'description' => '新鮮な玉ねぎ3束のセット',
                'price' => 300,
                'brand' => null
            ],
            [
                'image_url' => 'storage/uploads/items/Leather+Shoes+Product+Photo.jpg',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['ファッション'], $categoryIds['メンズ']],
                'condition_id' => $conditionIds['状態が悪い'],
                'name' => '革靴',
                'description' => 'クラシックなデザインの革靴',
                'price' => 4000,
                'brand' => 'ブランドC'
            ],
            [
                'image_url' => 'storage/uploads/items/Living+Room+Laptop.jpg',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['家電']],
                'condition_id' => $conditionIds['良好'],
                'name' => 'ノートPC',
                'description' => '高性能なノートパソコン',
                'price' => 45000,
                'brand' => 'ブランドD'
            ],
            [
                'image_url' => 'storage/uploads/items/Music+Mic+4632231.jpg',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['家電']],
                'condition_id' => $conditionIds['目立った傷や汚れなし'],
                'name' => 'マイク',
                'description' => '高音質のレコーディング用マイク',
                'price' => 8000,
                'brand' => null
            ],
            [
                'image_url' => 'storage/uploads/items/Purse+fashion+pocket.jpg',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['ファッション'], $categoryIds['レディース']],
                'condition_id' => $conditionIds['やや傷や汚れあり'],
                'name' => 'ショルダーバッグ',
                'description' => 'おしゃれなショルダーバッグ',
                'price' => 3500,
                'brand' => 'ブランドE'
            ],
            [
                'image_url' => 'storage/uploads/items/Tumbler+souvenir.jpg',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['キッチン']],
                'condition_id' => $conditionIds['状態が悪い'],
                'name' => 'タンブラー',
                'description' => '使いやすいタンブラー',
                'price' => 500,
                'brand' => 'ブランドF'
            ],
            [
                'image_url' => 'storage/uploads/items/Waitress+with+Coffee+Grinder.jpg',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['インテリア'], $categoryIds['キッチン']],
                'condition_id' => $conditionIds['良好'],
                'name' => 'コーヒーミル',
                'description' => '手動のコーヒーミル',
                'price' => 4000,
                'brand' => 'ブランドG'
            ],
            [
                'image_url' => 'storage/uploads/items/外出メイクアップセット.jpg',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['レディース']],
                'condition_id' => $conditionIds['目立った傷や汚れなし'],
                'name' => 'メイクセット',
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'brand' => 'ブランドH'
            ],
            [
                'image_url' => 'storage/uploads/items/11.png',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['レディース']],
                'condition_id' => $conditionIds['目立った傷や汚れなし'],
                'name' => '革 メ あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよわん11',
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'brand' => 'ブランドH'
            ],
            [
                'image_url' => 'storage/uploads/items/12.png',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['レディース']],
                'condition_id' => $conditionIds['目立った傷や汚れなし'],
                'name' => '革 メ あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよわん12',
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'brand' => 'ブランドH'
            ],
            [
                'image_url' => 'storage/uploads/items/13.png',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['レディース']],
                'condition_id' => $conditionIds['目立った傷や汚れなし'],
                'name' => '革 メ あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよわん13',
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'brand' => 'ブランドH'
            ],
            [
                'image_url' => 'storage/uploads/items/14.png',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['レディース']],
                'condition_id' => $conditionIds['目立った傷や汚れなし'],
                'name' => '革 メ あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよわん14',
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'brand' => 'ブランドH'
            ],
            [
                'image_url' => 'storage/uploads/items/15.png',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['レディース']],
                'condition_id' => $conditionIds['目立った傷や汚れなし'],
                'name' => '革 メ あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよわん15',
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'brand' => 'ブランドH'
            ],
            [
                'image_url' => 'storage/uploads/items/16.png',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['レディース']],
                'condition_id' => $conditionIds['目立った傷や汚れなし'],
                'name' => '革 メ あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよわん16',
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'brand' => 'ブランドH'
            ],
            [
                'image_url' => 'storage/uploads/items/17.png',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['レディース']],
                'condition_id' => $conditionIds['目立った傷や汚れなし'],
                'name' => '革 メ あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよわん17',
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'brand' => 'ブランドH'
            ],
            [
                'image_url' => 'storage/uploads/items/18.png',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['レディース']],
                'condition_id' => $conditionIds['目立った傷や汚れなし'],
                'name' => '革 メ あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよわん18',
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'brand' => 'ブランドH'
            ],
            [
                'image_url' => 'storage/uploads/items/19.png',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['レディース']],
                'condition_id' => $conditionIds['目立った傷や汚れなし'],
                'name' => '革 メ あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよわん19',
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'brand' => 'ブランドH'
            ],
            [
                'image_url' => 'storage/uploads/items/20.png',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['レディース']],
                'condition_id' => $conditionIds['目立った傷や汚れなし'],
                'name' => '革 メ あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよわん20',
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'brand' => 'ブランドH'
            ],
            [
                'image_url' => 'storage/uploads/items/21.png',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['レディース']],
                'condition_id' => $conditionIds['目立った傷や汚れなし'],
                'name' => '革 メ あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよわん21',
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'brand' => 'ブランドH'
            ],
            [
                'image_url' => 'storage/uploads/items/22.png',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['レディース']],
                'condition_id' => $conditionIds['目立った傷や汚れなし'],
                'name' => '革 メ あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよわん22',
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'brand' => 'ブランドH'
            ],
            [
                'image_url' => 'storage/uploads/items/23.png',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['レディース']],
                'condition_id' => $conditionIds['目立った傷や汚れなし'],
                'name' => '革 メ あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよわん23',
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'brand' => 'ブランドH'
            ],
            [
                'image_url' => 'storage/uploads/items/24.png',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['レディース']],
                'condition_id' => $conditionIds['目立った傷や汚れなし'],
                'name' => '革 メ あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよわん24',
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'brand' => 'ブランドH'
            ],
            [
                'image_url' => 'storage/uploads/items/25.png',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['レディース']],
                'condition_id' => $conditionIds['目立った傷や汚れなし'],
                'name' => '革 メ あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよわん25',
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'brand' => 'ブランドH'
            ],
            [
                'image_url' => 'storage/uploads/items/26.jpg',
                'user_id' => $userIds->random(),
                'category_id' => [$categoryIds['レディース']],
                'condition_id' => $conditionIds['目立った傷や汚れなし'],
                'name' => '革 メ あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよわん26',
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'brand' => 'ブランドH'
            ],
        ];

        foreach ($items as $item) {
            // カテゴリーIDを一時的に保存
            $categoryIds = $item['category_id'];
            unset($item['category_id']);

            // itemsテーブルにデータを挿入
            $itemId = DB::table('items')->insertGetId($item);

            // item_categoryテーブルにデータを挿入
            foreach ($categoryIds as $categoryId) {
                DB::table('item_category')->insert([
                    'item_id' => $itemId,
                    'category_id' => $categoryId
                ]);
            }
        }
    }
}
