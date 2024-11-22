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
use Database\Seeders\CommentsTableSeeder; // 追加
use App\Http\Requests\CommentRequest; // 追加

class CommentTest extends TestCase
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
            CommentsTableSeeder::class,
        ]);
    }

    public function test_user_can_add_comment_and_comments_count_increases()
    {
        // シードされたデータからランダムにユーザーを取得
        $user = User::inRandomOrder()->first();

        // シードされたデータからランダムにアイテムを取得
        $item = Item::inRandomOrder()->first();

                $this->actingAs($user);

        // dump('選ばれたuserのid: ' . $user->id);
        // dump('選ばれたitemのid: ' . $item->id);

        // 3. 商品詳細ページを開く
        $response = $this->get("/item/{$item->id}");
        $response->assertStatus(200);

        // コントローラのメソッドから渡された 'item' 変数を取得
        $itemFromView = $response->viewData('item');
        // コントローラのメソッドにて取得されたコメント数を取得
        $initialCommentsCount = $itemFromView->comments_count;

        // dump('選ばれたitemのコメント数（コメント投稿前）: ' . $initialCommentsCount);

                // コメント内容
        $commentContent = 'テストコメント';

        // AJAXリクエストをシミュレートしてコメントを送信
        $response = $this->postJson("/item/{$item->id}/comments", [
            'content' => $commentContent,
        ]);

        // コメントがcommentテーブルに保存されていることを確認
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'content' => $commentContent,
        ]);

        dump('コメント投稿によりcommentsテーブルへ登録されたことを確認しました');

                // コメント数の取得
        $updatedItem = Item::withCount(['comments'])->find($item->id);

        // dump('選ばれたitemのコメント数（コメント投稿後）' . $updatedItem->comments_count);

        // 期待されるコメント数（元のコメント数 + 1）
        $expectedCommentsCount = Comment::where('item_id', $item->id)->count();

        // コメント数が正しく更新されていることを確認
        $this->assertEquals($expectedCommentsCount, $updatedItem->comments_count);

                // ビューの中のmainタグ内を正規表現で取得し、コメント数が正しく表示されているか確認
        $viewResponse = $this->get("/item/{$item->id}");

        // mainタグ内のHTMLコンテンツを取得
        preg_match('/<main.*?>(.*?)<\/main>/s', $viewResponse->getContent(), $matches);

                // mainタグ内のコンテンツが存在するか確認
        $this->assertNotEmpty($matches[1], 'Main content should not be empty.');

        // コメント数が正しく表示されているか確認
        $this->assertStringContainsString("コメント({$updatedItem->comments_count})", $matches[1]);

        dump('コメントが保存されコメント数が増加することが確認できました');
    }

    public function test_guest_user_cannot_add_comment()
    {
        // シードされたデータからランダムにアイテムを取得
        $item = Item::inRandomOrder()->first();

        // コメント内容
        $commentContent = 'テストコメント';

        // AJAXリクエストをシミュレートしてコメントを送信（ログインしていない状態）
        $response = $this->postJson("/item/{$item->id}/comments", [
            'content' => $commentContent,
        ]);

        // レスポンスが401 Unauthorizedであることを確認
        $response->assertStatus(401); // 401エラーが期待される

        // コメントがcommentテーブルに保存されていないことを確認
        $this->assertDatabaseMissing('comments', [
            'item_id' => $item->id,
            'content' => $commentContent,
        ]);

        dump('ログインしていないユーザーによるコメント投稿が拒否され、コメントは保存されませんでした。');
    }

    public function test_user_cannot_add_empty_comment()
    {
        // シードされたデータからランダムにユーザーを取得
        $user = User::inRandomOrder()->first();

        // シードされたデータからランダムにアイテムを取得
        $item = Item::inRandomOrder()->first();

        // ユーザーをログインさせる
        $this->actingAs($user);

        // AJAXリクエストをシミュレートして空のコメントを送信
        $response = $this->postJson("/item/{$item->id}/comments", [
            'content' => '',  // 空のコメント
        ]);

        // バリデーションエラーメッセージが返ってくることを確認
        $response->assertStatus(422);  // HTTPステータスコード422 Unprocessable Entity
        $response->assertJsonValidationErrors(['content']);  // 'content'フィールドにバリデーションエラーが含まれていることを確認

        // バリデーションメッセージの内容を取得
        $validationErrors = $response->json('errors.content');

        // CommentRequestから期待されるバリデーションメッセージを取得
        $expectedMessage = (new CommentRequest())->messages()['content.required'];

        // コマンドラインにバリデーションメッセージを出力
        dump('バリデーションエラーメッセージ: ' . implode(', ', $validationErrors));
        // バリデーションメッセージが期待通りであることを確認
        $this->assertEquals($expectedMessage, $validationErrors[0]);
        dump('空コメントに対するバリデーションメッセージが表示されることが確認できました');

    }

    public function test_user_cannot_add_comment_exceeding_max_length()
    {
        // シードされたデータからランダムにユーザーを取得
        $user = User::inRandomOrder()->first();

        // シードされたデータからランダムにアイテムを取得
        $item = Item::inRandomOrder()->first();

        // ユーザーをログインさせる
        $this->actingAs($user);

        // 256文字以上のコメント内容（例: 256文字の"あ"）
        $longCommentContent = str_repeat('あ', 256); // 256文字のコメント

        // AJAXリクエストをシミュレートして長いコメントを送信
        $response = $this->postJson("/item/{$item->id}/comments", [
            'content' => $longCommentContent,
        ]);

        // バリデーションエラーメッセージが返ってくることを確認
        $response->assertStatus(422);  // HTTPステータスコード422 Unprocessable Entity
        $response->assertJsonValidationErrors(['content']);  // 'content'フィールドにバリデーションエラーが含まれていることを確認

        // バリデーションメッセージの内容を取得
        $validationErrors = $response->json('errors.content');

        // CommentRequestから期待されるバリデーションメッセージを取得
        $expectedMessage = (new CommentRequest())->messages()['content.max'];

        // コマンドラインにバリデーションメッセージを出力
        dump('バリデーションエラーメッセージ: ' . implode(', ', $validationErrors));

        // バリデーションメッセージが期待通りであることを確認
        $this->assertEquals($expectedMessage, $validationErrors[0]);

        dump('256文字以上のコメントに対するバリデーションメッセージが表示されることが確認できました');
    }
}

