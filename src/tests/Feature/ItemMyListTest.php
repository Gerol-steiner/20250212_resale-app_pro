<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Item; // 追加
use App\Models\User; // 追加
use App\Models\Like; // 追加
use App\Models\Purchase; // 追加
use App\Models\Address; // 追加
use Database\Seeders\UsersTableSeeder; // 追加
use Database\Seeders\CategoriesTableSeeder; // 追加
use Database\Seeders\ConditionsTableSeeder; // 追加
use Database\Seeders\ItemsTableSeeder; // 追加

class ItemMyListTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_sees_only_liked_items_in_mylist()
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

        // ユーザーが「いいね」した商品を作成
        $likedItem = Item::factory()->create(); // いいねされた商品
        Like::create([
            'user_id' => $user->id,
            'item_id' => $likedItem->id,
        ]);

        // ユーザーが「いいね」をしていない商品を作成
        $unlikedItem = Item::factory()->create(); // いいねされていない商品

        // テストユーザーとしてログイン
        $this->actingAs($user);

        // 1. マイリストページを開く（$tab === 'mylist'）
        $response = $this->get('/?tab=mylist');

        // ステータスコードが200（OK）であることを確認
        $response->assertStatus(200);

        // 2. ユーザーが「いいね」した商品が表示されていることを確認
        $response->assertSee($likedItem->name);

        // 3. ユーザーが「いいね」をしていない商品が表示されていないことを確認
        $response->assertDontSee($unlikedItem->name);

        // デバッグ出力
        dump('マイリストタブのテストが正常に完了しました。');
    }

    public function test_authenticated_user_sees_sold_label_for_liked_and_purchased_items_in_mylist()
    {
        // テストデータベースをシードする
        $this->seed([
            UsersTableSeeder::class,
            CategoriesTableSeeder::class,
            ConditionsTableSeeder::class,
            ItemsTableSeeder::class
        ]);

        // テストユーザーを作成
        $user1 = User::factory()->create(); // 購入済みのアイテムを持つユーザー
        $user2 = User::factory()->create(); // 購入していないアイテムを持つユーザー

        // ユーザー1が「いいね」した商品を作成
        $likedItem = Item::factory()->create(); // いいねされた商品
        Like::create([
            'user_id' => $user1->id,
            'item_id' => $likedItem->id,
        ]);

        // 購入済みの商品を作成
        $address = Address::factory()->create(['user_id' => $user1->id]);
        Purchase::create([
            'user_id' => $user1->id,
            'item_id' => $likedItem->id, // 購入済みとして登録
            'address_id' => $address->id,
            'payment_method' => 'カード支払い',
        ]);

        // ユーザー2が購入していない商品を作成
        $unpurchasedItem = Item::factory()->create(); // いいねされていない商品
        Like::create([
            'user_id' => $user2->id,
            'item_id' => $unpurchasedItem->id, // この商品も「いいね」される
        ]);

        // ユーザー1としてログイン
        $this->actingAs($user1);

        // 1. マイリストページを開く（$tab === 'mylist'）
        $response = $this->get('/?tab=mylist');

        // ステータスコードが200（OK）であることを確認
        $response->assertStatus(200);


        // 2. 購入済みで「いいね」された商品に「Sold」ラベルが表示されていることを確認
        $response->assertSee('<img src="' . asset('images/sold-label.svg') . '" alt="Sold" class="sold-label">', false);


        // ユーザー2としてログイン
        $this->actingAs($user2);

        // 1. マイリストページを開く（$tab === 'mylist'）
        $response = $this->get('/?tab=mylist');

        // ステータスコードが200（OK）であることを確認
        $response->assertStatus(200);

        // 3. 購入していない商品には「Sold」ラベルが表示されないことを確認
        // ユーザー2のアイテムは表示されないため、ここで確認できます
        $response->assertDontSee('<img src="' . asset('images/sold-label.svg') . '" alt="Sold" class="sold-label">', false);

        // デバッグ出力
        dump('マイリストタブの「Sold」ラベルのテスト正常に完了しました。');
    }


    public function test_unauthenticated_user_sees_no_items_in_mylist()
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

        // ユーザーが「いいね」した商品を作成
        $likedItem = Item::factory()->create(); // いいねされた商品
        Like::create([
            'user_id' => $user->id,
            'item_id' => $likedItem->id,
        ]);

        // 未認証ユーザーなのでコメントアウトによりログインさせない
        //$this->actingAs($user);

        // 1. ログインしない状態でマイリストページを開く（$tab === 'mylist'）
        $response = $this->get('/?tab=mylist');

        // ステータスコードが200（OK）であることを確認
        $response->assertStatus(200);

        // 2. 未認証ユーザーのため、「いいね」されている商品はあるが、何も表示されていないことを確認
        $response->assertDontSee($likedItem->name);

        // デバッグ出力
        dump('未認証ユーザーのマイリストタブのテストが正常に完了しました。');
    }
}


