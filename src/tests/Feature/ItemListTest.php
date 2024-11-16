<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Item; // 追加
use App\Models\User; // 追加
use Database\Seeders\UsersTableSeeder; // 追加
use Database\Seeders\CategoriesTableSeeder; // 追加
use Database\Seeders\ConditionsTableSeeder; // 追加
use Database\Seeders\ItemsTableSeeder; // 追加

class ItemListTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_items_are_displayed_on_index_page()
    {
        // テストデータベースをシードする
        $this->seed([
            UsersTableSeeder::class,
            CategoriesTableSeeder::class,
            ConditionsTableSeeder::class,
            ItemsTableSeeder::class
        ]);

        // 1. 商品一覧ページを開く
        $response = $this->get('/');

        // ステータスコードが200（OK）であることを確認
        $response->assertStatus(200);

        // データベースから全ての商品を取得
        $items = Item::all();

        // 全ての商品が表示されていることを確認
        foreach ($items as $item) {
            $response->assertSee($item->name, false);
        }

        // 商品の数を確認
        $this->assertEquals($items->count(), substr_count($response->getContent(), 'class="item-card"'));

        // デバッグ出力
        dump('全ての商品（' . $items->count() . '件）が正常に表示されています。');

    }

    public function test_authenticated_user_does_not_see_own_items()
    {
        // テストデータベースをシードする
        $this->seed([
            UsersTableSeeder::class,
            CategoriesTableSeeder::class,
            ConditionsTableSeeder::class,
            ItemsTableSeeder::class
        ]);

        // テストユーザーを作成
        $user = User::factory()->create();

        // テストユーザーの商品を作成
        $ownItem = Item::factory()->create(['user_id' => $user->id]);

        // 他のユーザーの商品を作成
        $otherItem = Item::factory()->create();

        // テストユーザーとしてログイン
        $this->actingAs($user);

        // 1. 商品一覧ページを開く
        $response = $this->get('/');

        // ステータスコードが200（OK）であることを確認
        $response->assertStatus(200);

        // 2. 自分の商品が表示されていないことを確認
        $response->assertDontSee($ownItem->name);

        // 3. 他のユーザーの商品が表示されていることを確認
        $response->assertSee($otherItem->name);

        // デバッグ出力
        dump('認証済みユーザーの商品一覧テストが正常に完了しました。');
    }
}
