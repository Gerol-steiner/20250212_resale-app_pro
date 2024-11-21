<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Condition;
use Illuminate\Http\UploadedFile;

class ItemRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // シーダーを実行してテスト用データを用意
        $this->seed([
            \Database\Seeders\UsersTableSeeder::class,
            \Database\Seeders\CategoriesTableSeeder::class,
            \Database\Seeders\ConditionsTableSeeder::class,
        ]);
    }
public function test_item_can_be_stored_correctly_with_mocked_image()
{
    // ① シードデータから適当なユーザーを取得
    $user = User::inRandomOrder()->first();

    if (!$user) {
        $this->fail('ユーザーが見つかりませんでした。');
    }

    // ユーザーをログイン
    $this->actingAs($user);

    // ② シードデータからカテゴリーと商品の状態を取得
    $categories = Category::inRandomOrder()->take(3)->get(); // ランダムに3つのカテゴリーを取得
    $condition = Condition::inRandomOrder()->first(); // ランダムに1つの状態を取得

    if ($categories->isEmpty() || !$condition) {
        $this->fail('カテゴリーまたは商品の状態が見つかりませんでした。');
    }

    // テスト用ダミー画像ファイルを作成
    $dummyFilePath = sys_get_temp_dir() . '/test_image.png';
    file_put_contents($dummyFilePath, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/wIAAgkBAUbWfwAAAABJRU5ErkJggg=='));
    $dummyImageFile = new UploadedFile(
        $dummyFilePath,
        'test_image.png',
        'image/png',
        null,
        true // テストファイルとしてマーク
    );

    // 出品フォームに入力するデータを作成
    $itemData = [
        'item_name' => 'テスト商品',
        'brand' => 'テストブランド',
        'item_description' => 'これはテスト商品の説明です。',
        'item_price' => 12345,
        'condition_id' => $condition->id,
        'category_ids' => $categories->pluck('id')->toArray(), // カテゴリIDの配列
        'cropped_image' => 'data:image/png;base64,' . base64_encode('dummy_image_data'), // クロップ後のダミーデータ
        'item_image' => $dummyImageFile, // ダミー画像ファイル
    ];

    // 商品出品ページを開く
    $response = $this->get(route('sell.show'));
    $response->assertStatus(200);

    dump('■ $itemData: ' . json_encode($itemData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    dump('■ item_image properties: ', [
        'path' => $itemData['item_image']->getPath(),
        'filename' => $itemData['item_image']->getClientOriginalName(),
        'mimeType' => $itemData['item_image']->getClientMimeType(),
        'isValid' => $itemData['item_image']->isValid(),
    ]);

    // 商品を出品するリクエストを送信
    $response = $this->post(route('item.store'), $itemData);

        $response->assertSessionHasNoErrors();

    // 正しいリダイレクト先に遷移したことを確認
    $response->assertRedirect(route('item.index'));
    $response->assertSessionHas('success', '商品が出品されました。');

    // items テーブルにデータが保存されていることを確認
    $this->assertDatabaseHas('items', [
        'user_id' => $user->id,
        'name' => $itemData['item_name'],
        'brand' => $itemData['brand'],
        'description' => $itemData['item_description'],
        'price' => $itemData['item_price'],
        'condition_id' => $itemData['condition_id'],
    ]);

    // カテゴリーの関連付けが正しいことを確認
    $item = Item::where('name', $itemData['item_name'])->first();
    $this->assertNotNull($item);
    $this->assertEqualsCanonicalizing($categories->pluck('id')->toArray(), $item->categories->pluck('id')->toArray());
    dump('■ $item: ' . json_encode($item, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    dump('商品出品画面に入力した情報がデータベースに正常に登録されていることを確認しました');
    }
}