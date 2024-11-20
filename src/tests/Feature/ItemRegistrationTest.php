<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Condition;

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

    // ダミー画像パスを設定
    $dummyImageData = 'data:image/png;base64,' . base64_encode(random_bytes(128));

    // 出品フォームに入力するデータを作成
    $itemData = [
        'item_name' => 'テスト商品',
        'brand' => 'テストブランド',
        'item_description' => 'これはテスト商品の説明です。',
        'item_price' => 12345,
        'condition_id' => $condition->id,
        'category_ids' => $categories->pluck('id')->toArray(),
        'cropped_image' => $dummyImageData, // ダミー画像
    ];

    dump($itemData);

    // 商品出品ページを開く
    $response = $this->get(route('sell.show'));
    $response->assertStatus(200);

    // 商品を出品するリクエストを送信
    $response = $this->post(route('item.store'), $itemData);

    // 正しいリダイレクト先に遷移したことを確認
    $response->assertRedirect(route('item.index'));
    //$response->assertSessionHas('success', '商品が出品されました。');

    // items テーブルにデータが保存されていることを確認
    $this->assertDatabaseHas('items', [
        'user_id' => $user->id,
        'name' => $itemData['item_name'],
        'brand' => $itemData['brand'],
        'description' => $itemData['item_description'],
        //'price' => $itemData['item_price'],
        //'condition_id' => $itemData['condition_id'],
        //'image_url' => $mockedImagePath, // 画像パスが保存されているか確認
    ]);

    // カテゴリーの関連付けが正しいことを確認
    $item = Item::where('name', $itemData['item_name'])->first();
    $this->assertNotNull($item);
    $this->assertEquals($categories->pluck('id')->sort()->values(), $item->categories->pluck('id')->sort()->values());

    dump('商品出品テストが成功しました: item_id=' . $item->id);
}

}
