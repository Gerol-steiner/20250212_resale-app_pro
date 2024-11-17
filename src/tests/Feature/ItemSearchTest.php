<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Item; // 追加
use App\Models\User; // 追加
use App\Models\Like; // 追加
use Database\Seeders\UsersTableSeeder; // 追加
use Database\Seeders\CategoriesTableSeeder; // 追加
use Database\Seeders\ConditionsTableSeeder; // 追加
use Database\Seeders\ItemsTableSeeder; // 追加

class ItemSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_functionality_displays_matching_items()
    {
        // テストデータベースをシードする
        $this->seed([
            UsersTableSeeder::class,
            CategoriesTableSeeder::class,
            ConditionsTableSeeder::class,
            ItemsTableSeeder::class
        ]);

        // 検索キーワードを設定
        $searchKeyword = 'テスト'; // 例: ここに実際のテスト用キーワードを入れる

        // 商品を作成し、検索キーワードに部分一致する商品を含める
        Item::factory()->create(['name' => 'テスト商品']);
        Item::factory()->create(['name' => '検索機能テスト']);
        Item::factory()->create(['name' => 'サンプル商品']);

        // 1. 検索ボタンを押して検索する（GETリクエストを送信）
        $response = $this->get('/?search=' . urlencode($searchKeyword) . '&tab=home');

        // ステータスコードが200（OK）であることを確認
        $response->assertStatus(200);

        // 検索キーワードに部分一致する商品が表示されていることを確認
        $response->assertSee('テスト商品', false);
        $response->assertSee('検索機能テスト', false);

        // 検索キーワードに一致しない商品が表示されていないことを確認
        $response->assertDontSee('サンプル商品', false);

        // デバッグ出力
        dump('検索機能が正常に動作しています。');
    }


    public function test_search_results_are_retained_in_mylist_tab()
    {
        // テストデータベースをシードする
        $this->seed([
            UsersTableSeeder::class,
            CategoriesTableSeeder::class,
            ConditionsTableSeeder::class,
            ItemsTableSeeder::class
        ]);

        // 検索キーワードを設定
        $searchKeyword = 'テスト'; // 例: ここに実際のテスト用キーワードを入れる

        // 商品を作成し、検索キーワードに部分一致する商品を含める
        $item1 = Item::factory()->create(['name' => 'テスト商品']);
        $item2 = Item::factory()->create(['name' => '検索機能テスト']);
        $item3 = Item::factory()->create(['name' => 'サンプル商品']);

        // テストユーザーを作成
        $user = User::factory()->create();

        // 商品を「いいね」してマイリストに登録
        Like::create([
            'user_id' => $user->id,
            'item_id' => $item1->id, // 「テスト商品」をいいね
        ]);
        Like::create([
            'user_id' => $user->id,
            'item_id' => $item2->id, // 「検索機能テスト」をいいね
        ]);
        Like::create([
            'user_id' => $user->id,
            'item_id' => $item3->id, // 「検索機能テスト」をいいね
        ]);

        // テストユーザーとしてログイン
        $this->actingAs($user);

        // 1. 検索ボタンを押して検索する（GETリクエストを送信）
        $response = $this->get('/?search=' . urlencode($searchKeyword) . '&tab=home');

        // ステータスコードが200（OK）であることを確認
        $response->assertStatus(200);

        // 検索キーワードに部分一致する商品が表示されていることを確認
        $response->assertSee('テスト商品', false);
        $response->assertSee('検索機能テスト', false);

        // 検索キーワードに一致しない商品が表示されていないことを確認
        $response->assertDontSee('サンプル商品', false);

        // 2. マイリストタブを開く
        $response = $this->get('/?search=' . urlencode($searchKeyword) . '&tab=mylist');

        // ステータスコードが200（OK）であることを確認
        $response->assertStatus(200);

        // マイリストに「いいね」した商品が表示されていることを確認
        $response->assertSee('テスト商品', false);
        $response->assertSee('検索機能テスト', false);

        // マイリストに表示されるべきでない商品が表示されていないことを確認
        $response->assertDontSee('サンプル商品', false);

        // デバッグ出力
        dump('検索結果がマイリストタブに正常に保持されています。');
    }

}
