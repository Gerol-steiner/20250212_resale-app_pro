<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User; // 追加
use App\Models\Like; // 追加
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

class LikeTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed([
            UsersTableSeeder::class,
            CategoriesTableSeeder::class,
            ConditionsTableSeeder::class,
            ItemsTableSeeder::class,
            LikesTableSeeder::class,
        ]);
    }

    public function test_user_can_like_an_item()
    {

    // 1. テスト用のユーザーと商品を準備
    $user = User::factory()->create(); // テスト用ユーザーを作成

    // このユーザーがまだ「いいね」していない商品を準備
    $item = Item::factory()->create([
        'user_id' => User::factory()->create()->id, // 別のユーザーが出品した商品
    ]);

    // 商品が少なくとも1つは必要なので、条件を満たさない場合に追加作成
    if (!$item) {
        $item = Item::factory()->create([
            'user_id' => User::factory()->create()->id,
        ]);
    }

        $this->actingAs($user);

        // 2. 選択されたユーザーがまだいいねしていない、かつ自分が出品していない商品を取得
        $item = Item::where('user_id', '!=', $user->id)
            ->whereDoesntHave('likes', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->first();

        // 該当商品が見つからない場合は処理を終了
        if (!$item) {
            dump('test_user_can_like_an_item()において条件に一致する商品が見つかりませんでした。もう一度実行してください');
        }

        // 選ばれたuserとitemを出力
        // dump('選ばれたuserのid: ' . $user->id);
        // dump('選ばれたitemのid: ' . $item->id);

        // 3. 商品詳細ページを開く
        $response = $this->get("/item/{$item->id}");
        $response->assertStatus(200);

        // レスポンスからHTMLを取得（<script>部を含む）
        $htmlContent = $response->getContent();

        // 正規表現で<main>タグの中身を抽出
        preg_match('/<main[^>]*>(.*?)<\/main>/s', $htmlContent, $matches);
        $mainContent = $matches[1] ?? ''; // <main>の中身が見つからなければ空文字

        // mainタグ内でのみ検出を行う
        $this->assertStringContainsString('star-outline.svg', $mainContent);
        $this->assertStringNotContainsString('star-filled.svg', $mainContent);



        // コントローラのメソッドから渡された 'item' 変数を取得
        $itemFromView = $response->viewData('item');

        $initialLikesCount = $itemFromView->likes_count;

        // dump('選ばれたitemのいいね数（いいね押下前）: ' . $initialLikesCount);


        // 4. いいねアイコンを押下（AJAXリクエストをシミュレート）
        $response = $this->postJson("/like/{$item->id}", ['liked' => true]);
        $response->assertJson(['success' => true]);

        // likesテーブルの最新レコードを取得
        $latestLike = Like::latest('id')->first();

        // dump('【likesテーブルの最新レコード（いいね押下後）】');
        // dump('ID: ' . $latestLike->id);
        // dump('User ID: ' . $latestLike->user_id);
        // dump('Item ID: ' . $latestLike->item_id);


        // 5. likesテーブルに登録されたか確認
        $this->assertEquals($user->id, $latestLike->user_id, 'The latest like should be from the test user');
        $this->assertEquals($item->id, $latestLike->item_id, 'The latest like should be for the test item');

        // 6. いいねの合計値が更新されたか確認
        $updatedItem = Item::withCount(['likes'])->find($item->id);

        // dump('いいね数: ' . $updatedItem->likes_count);

        $this->assertEquals($initialLikesCount + 1, $updatedItem->likes_count);

        // 7. 再度商品詳細ページを開いて、更新された「いいね」数が表示されているか確認
        $response = $this->get("/item/{$item->id}");
        $response->assertSee(strval($initialLikesCount + 1), false);

        // デバッグ出力
        dump('「いいねによるlikesテーブルへの登録」と、「いいね合計値の増加」を確認しました');

        // レスポンスからHTMLを取得（<script>部を含む）
        $htmlContent = $response->getContent();

        // 正規表現で<main>タグの中身を抽出
        preg_match('/<main[^>]*>(.*?)<\/main>/s', $htmlContent, $matches);
        $mainContent = $matches[1] ?? ''; // <main>の中身が見つからなければ空文字

        // mainタグ内でのみ検出を行う
        $this->assertStringContainsString('star-filled.svg', $mainContent);
        $this->assertStringNotContainsString('star-outline.svg', $mainContent);

        dump('いいね押下により「star-filled.svg（色付きアイコン）」の表示を確認しました');
    }

    public function test_user_can_unlike_an_item_and_likes_count_decreases()
    {

        // 1. likesテーブルに登録されている「いいね」をしているユーザーを取得
        $user = User::whereHas('likes')->inRandomOrder()->first();

        // 2. 選択されたユーザーがいいねしている商品を取得し、自分が出品していないものを選ぶ
        $item = Item::where('user_id', '!=', $user->id)
            ->whereHas('likes', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->first();

        // 3. likesテーブルから該当するレコードを取得
        $initialLike = Like::where('user_id', $user->id)
            ->where('item_id', $item->id)
            ->first();

        // 4．ログイン
        $this->actingAs($user);

        // 選ばれたuserとitemを出力
        // dump('選ばれたuserのid: ' . $user->id);
        // dump('選ばれたitemのid: ' . $item->id);


        // 最新のいいねレコードの情報をダンプ
        // dump('【該当likeレコード（いいね押下前）】');
        // dump('ID: ' . $initialLike->id);
        // dump('User ID: ' . $initialLike->user_id);
        // dump('Item ID: ' . $initialLike->item_id);


        //  商品詳細ページを開く
        $response = $this->get("/item/{$item->id}");
        $response->assertStatus(200);

        // コントローラのメソッドから渡された 'item' 変数を取得
        $itemFromView = $response->viewData('item');

        $initialLikesCount = $itemFromView->likes_count;

        // dump('該当itemのいいね数（ボタン押下前）: ' . $initialLikesCount);


        // 4. いいねアイコンを押下（AJAXリクエストをシミュレート）
        $response = $this->postJson("/like/{$item->id}", ['liked' => true]);
        $response->assertJson(['success' => true]);

        // いいね押下により該当するlikeレコードがテーブルから削除されていることを確認
        $updatedLike = Like::where('user_id', $user->id)->where('item_id', $item->id)->withoutGlobalScopes()->first();
        $this->assertNull($updatedLike, 'The like record should be null after unliking the item.');
        dump('いいね押下により該当likesレコードが削除されたことを確認しました');

        // 6. いいねの合計値が更新されたか確認
        $updatedItem = Item::withCount(['likes'])->find($item->id);

        // dump('該当itemのいいね数（ボタン押下後）:  ' . $updatedItem->likes_count);

        $this->assertEquals($initialLikesCount - 1, $updatedItem->likes_count);

        // 7. 再度商品詳細ページを開いて、更新された「いいね」数が表示されているか確認
        $response = $this->get("/item/{$item->id}");
        $response->assertSee(strval($initialLikesCount - 1), false);
        dump('再度いいねすることで、いい値合計値が減少表示されることを確認しました');

        // レスポンスからHTMLを取得（<script>部を含む）
        $htmlContent = $response->getContent();

        // 正規表現で<main>タグの中身を抽出
        preg_match('/<main[^>]*>(.*?)<\/main>/s', $htmlContent, $matches);
        $mainContent = $matches[1] ?? ''; // <main>の中身が見つからなければ空文字

        // mainタグ内でのみ検出を行う
        $this->assertStringContainsString('star-outline.svg', $mainContent);
        $this->assertStringNotContainsString('star-filled.svg', $mainContent);

        dump('いいね押下により「star-outline.svg（白抜きアイコン）」の表示を確認しました');
    }
}

