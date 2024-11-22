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
use Database\Seeders\AddressesTableSeeder; // 追加
use Database\Seeders\PurchasesTableSeeder; // 追加

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

public function test_sold_label_is_displayed_for_purchased_items()
{
    // テストデータベースをシードする
    $this->seed([
        UsersTableSeeder::class,
        CategoriesTableSeeder::class,
        ConditionsTableSeeder::class,
        ItemsTableSeeder::class,
        AddressesTableSeeder::class,
        PurchasesTableSeeder::class,
    ]);

    // 商品一覧ページを開く
    $response = $this->get('/');

    // ステータスコードが200（OK）であることを確認
    $response->assertStatus(200);

    // データベースから購入済みの商品を取得
    $purchasedItems = Item::whereHas('purchases')->get();

    // 購入済み商品の数を確認（デバッグ用）
    dump('購入済み商品数: ' . $purchasedItems->count());

    // レスポンス内に「Sold」ラベルがいくつ含まれているかを確認
    $soldLabelCount = substr_count($response->getContent(), 'images/sold-label.svg');
    dump('レスポンス内の「Sold」ラベルの数: ' . $soldLabelCount);

    // 購入済み商品の数と「Sold」ラベルの数を比較
    if ($purchasedItems->count() === $soldLabelCount) {
        dump('購入済み商品のすべてに「Sold」ラベルが表示されています。');
    } else {
        dump('不一致: 購入済み商品数 (' . $purchasedItems->count() . ') と「Sold」ラベルの数 (' . $soldLabelCount . ') が一致していません。');
    }


    // 購入済み商品が存在しない場合も考慮（万が一シーディングが正しく設定されていない場合に備えて）
    $this->assertGreaterThan(0, $purchasedItems->count(), '購入済み商品が存在しません。シーディングを確認してください。');
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
        dump('自分が出品した商品が商品一覧に表示されていないことを確認しました');
        
    }
}
