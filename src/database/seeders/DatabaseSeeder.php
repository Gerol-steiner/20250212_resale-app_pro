<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // DBファサードをインポート
use Illuminate\Support\Facades\Schema;  // 追記

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 外部キー制約を解除
        Schema::disableForeignKeyConstraints();

        // シーディング前に全レコードを削除
        DB::table('conditions')->truncate();
        DB::table('categories')->truncate();
        DB::table('items')->truncate();
        DB::table('item_category')->truncate();
        DB::table('users')->truncate();
        DB::table('addresses')->truncate();
        DB::table('purchases')->truncate();
        DB::table('likes')->truncate();
        DB::table('comments')->truncate();
        DB::table('chats')->truncate();
        DB::table('ratings')->truncate();

        // 外部キー制約を有効化
        Schema::enableForeignKeyConstraints();

        // ダミーデータ作成
        $this->call(UsersTableSeeder::class);
        $this->call(CategoriesTableSeeder::class);
        $this->call(ConditionsTableSeeder::class);
        $this->call(ItemsTableSeeder::class);
        $this->call(AddressesTableSeeder::class);
    }
}
