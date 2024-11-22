<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User; // 追加
use App\Models\Item; // 追加
use App\Models\Category; // 追加
use App\Models\Condition; // 追加
use App\Models\Comment; // 追加
use Database\Seeders\UsersTableSeeder; // 追加
use Database\Seeders\CategoriesTableSeeder; // 追加
use Database\Seeders\ConditionsTableSeeder; // 追加
use Database\Seeders\ItemsTableSeeder; // 追加
use Database\Seeders\LikesTableSeeder; // 追加
use Database\Seeders\CommentsTableSeeder; // 追加

class ItemDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_item_detail_page_displays_all_item_information()
    {
        // テストデータベースをシードする
        $this->seed([
            UsersTableSeeder::class,
            CategoriesTableSeeder::class,
            ConditionsTableSeeder::class,
            ItemsTableSeeder::class,
            LikesTableSeeder::class,
            CommentsTableSeeder::class,
        ]);

        // テストユーザーを作成
        $user = User::first(); // 最初のユーザーを取得
        // 商品を取得（ItemsTableSeederで作成された商品を使用）
        // コメントが存在する最初の商品を取得
        $item = Item::withCount(['likes', 'comments'])->has('comments')->first();


        // テストユーザーとしてログイン
        $this->actingAs($user);

        // 商品詳細ページを開く
        $response = $this->get('/item/' . $item->id);

        // ステータスコードが200（OK）であることを確認
        $response->assertStatus(200);

        // 商品情報がビュー内に表示されていることを確認
        $response->assertSee('<img src="' . asset($item->image_url) . '"', false);
        $response->assertSee($item->name, false);
        $response->assertSee($item->brand, false);
        $response->assertSee(number_format($item->price), false);
        $response->assertSee($item->description, false);
        // いいね数とコメント数が正しいか確認
        $response->assertSee((string) $item->likes_count, false);
        $response->assertSee((string) $item->comments_count, false);

        // カテゴリーを確認
        foreach ($item->categories as $category) {
            $response->assertSee($category->name, false);
        }

        // デバッグ出力
        dump('複数選択されたカテゴリが商品詳細ページに表示されていることを確認しました');

        // 商品の状態を確認
        $response->assertSee($item->condition->name ?? '状態情報がありません', false);

        // コメントを取得（CommentsTableSeederで作成されたものを使用）
        // item_idが一致する最初のコメントを取得
        $comment = Comment::where('item_id', $item->id)->first();

        // コメントに関連する情報を確認
        $response->assertSee($comment->user->profile_name, false);
        // 最初の10文字を比較
        $response->assertSee(e(substr($comment->content, 0, 30)), false);

        // デバッグ出力
        dump('商品詳細ページが正常に表示され、全ての情報が正しく表示されていることを確認しました。');
    }


}
