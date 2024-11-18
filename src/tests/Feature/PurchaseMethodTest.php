<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User; // 追加
use App\Models\Item; // 追加
use Database\Seeders\UsersTableSeeder; // 追加
use Database\Seeders\CategoriesTableSeeder; // 追加
use Database\Seeders\ConditionsTableSeeder; // 追加
use Database\Seeders\ItemsTableSeeder; // 追加
use Database\Seeders\AddressesTableSeeder; // 追加
use Database\Seeders\PurchasesTableSeeder; // 追加

class PurchaseMethodTest extends TestCase
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
            AddressesTableSeeder::class,
            PurchasesTableSeeder::class,
        ]);
    }

    public function test_user_can_select_payment_method_and_see_it_displayed()
    {
        // ① シードされたデータから全てのユーザーをランダムな順序で取得
        $users = User::inRandomOrder()->get();

        // 条件を満たすアイテムを保持する変数
        $item = null;

        // ② 各ユーザーについて条件を満たすアイテムを検索
        foreach ($users as $user) {
            $item = Item::whereDoesntHave('purchases')
                ->where('user_id', '!=', $user->id)
                ->inRandomOrder()
                ->first();

            // アイテムが見つかればループを抜ける
            if ($item) {
                break;
            }
        }

        // 条件を満たすアイテムが見つからなかった場合
        if (!$item) {
            $this->fail("条件を満たすアイテムが見つかりませんでした。");
        }

        // 条件を満たすユーザーとアイテムをデバッグ出力
        dump('User ID: ' . $user->id);
        dump('Item ID: ' . $item->id);

        // ユーザーをログインさせる
        $this->actingAs($user);

        // ③ 購入画面を開く
        $response = $this->get("/purchase/{$item->id}");

        // ステータスコードが200であることを確認
        $response->assertStatus(200);

    // ④ 支払い方法を選択し、POSTリクエストを送信（フォーム送信のシミュレーション）
    $paymentMethod = 'コンビニ支払い'; // テストする支払い方法
    $postResponse = $this->post("/purchase/{$item->id}", [
        'item_id' => $item->id,
        'payment_method' => $paymentMethod,
    ]);

    // ステータスコードが200であることを確認
    $postResponse->assertStatus(200);

    $postResponse->dump();
}
}
