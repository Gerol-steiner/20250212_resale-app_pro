<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Item; // 追加
use App\Models\User; // 追加
use App\Models\Purchase; // 追加
use App\Models\Address; // 追加
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

    public function test_user_can_view_only_purchased_items_with_sold_label()
    {
        // 1. ユーザーを作成してログインさせる
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. ユーザーが出品した商品を作成
        $soldItemName = 'Sold Item';
        $soldItem = Item::factory()->create([
            'name' => $soldItemName,
            'user_id' => $user->id,
        ]);

        // 3. ユーザーが購入した商品を作成
        $purchasedItemName = 'Purchased Item';
        $purchasedItem = Item::factory()->create([
            'name' => $purchasedItemName,
            'user_id' => User::factory()->create()->id, // 別のユーザーが出品した商品
        ]);

        // 購入情報をpurchasesテーブルに追加
        Purchase::factory()->create([
            'user_id' => $user->id,
            'item_id' => $purchasedItem->id,
            'address_id' => Address::factory()->create(['user_id' => $user->id])->id,
            'payment_method' => 'カード支払い',
        ]);

        // 4. マイページを開く
        $response = $this->get('/mypage?tab=buy');

        // ステータスコードが200であることを確認
        $response->assertStatus(200);

        // 5. 購入済み商品のみが表示されていることを確認
        // 購入済み商品の名前が表示されていること
        $response->assertSee($purchasedItemName, false);

        // 出品商品の名前が表示されていないこと
        $response->assertDontSee($soldItemName, false);

        // 6. 購入済み商品に「Sold」ラベルが表示されていることを確認
        $response->assertSee(
            '<img src="' . asset('images/sold-label.svg') . '" alt="Sold" class="sold-label">',
            false
        );

        // テスト結果をデバッグ表示
        dump('マイページにて「購入済み商品」のみが表示され、正しくSoldラベルが表示されていることを確認しました');
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
