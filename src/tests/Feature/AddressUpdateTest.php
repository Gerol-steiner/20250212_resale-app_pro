<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Address;


class AddressUpdateTest extends TestCase
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
            \Database\Seeders\ItemsTableSeeder::class,
            \Database\Seeders\AddressesTableSeeder::class,
            \Database\Seeders\PurchasesTableSeeder::class,
        ]);
    }

    public function test_address_update_and_display_in_purchase_screen()
    {
        // ① シードデータから条件を満たすユーザーとアイテムを取得
        $users = User::inRandomOrder()->get();
        $item = null;

        foreach ($users as $user) {
            $item = Item::whereDoesntHave('purchases') // 購入されていないアイテム
                ->where('user_id', '!=', $user->id)   // ログインユーザー以外が出品
                ->inRandomOrder()
                ->first();

            if ($item) {
                break; // 条件を満たすアイテムが見つかったらループを抜ける
            }
        }

        if (!$item) {
            $this->fail('条件を満たすアイテムが見つかりませんでした。');
        }

        $address = Address::where('user_id', $user->id)->latest('id')->first();

        // 条件を満たすユーザーをログイン
        $this->actingAs($user);

        // 商品購入画面のリクエスト
        $this->post(route('purchase', ['id' => $item->id]))
            ->assertStatus(200);

        // ② 住所変更画面のリクエスト
        $response = $this->get(route('address.edit', ['item_id' => $item->id]));
        $response->assertStatus(200);

            // ③ 住所変更リクエストを送信
        $newAddressData = [
            'postal_code' => '123-4567',
            'address' => '新しい住所',
            'building' => '新しい建物名',
            'item_id' => $item->id,
        ];

        $this->post(route('address.update'), $newAddressData)
            ->assertRedirect(route('purchase.show', ['id' => $item->id]));

        // ④ データベースに新しい住所が保存されていることを確認
        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'postal_code' => $newAddressData['postal_code'],
            'address' => $newAddressData['address'],
            'building' => $newAddressData['building'],
            'is_default' => 0,
        ]);

            // 商品購入画面に戻り、新しい住所が表示されていることを確認
        $response = $this->get(route('purchase.show', ['id' => $item->id]));
        $response->assertStatus(200);

        // 商品購入画面ビューの検証
        $response->assertSee('郵便番号: ' . $newAddressData['postal_code']);
        $response->assertSee('住所: ' . $newAddressData['address']);
        $response->assertSee('建物名: ' . $newAddressData['building']);

        dump('送付先住所変更画面にて登録した住所がデータベースに登録され、内容が商品購入画面に表示されていることを確認しました');
    }

    public function test_address_id_is_registered_in_purchases_table()
    {
        // ユーザーとアイテムを取得
        $users = User::inRandomOrder()->get();
        $item = null;

        foreach ($users as $user) {
            $item = Item::whereDoesntHave('purchases') // 購入されていないアイテム
                ->where('user_id', '!=', $user->id)   // ログインユーザー以外が出品
                ->inRandomOrder()
                ->first();

            if ($item) {
                break; // 条件を満たすアイテムが見つかったらループを抜ける
            }
        }

        if (!$item) {
            $this->fail('条件を満たすアイテムが見つかりませんでした。');
        }

        $address = Address::create([
            'user_id' => $user->id,
            'is_default' => true,
            'address' => 'test_address',
            'postal_code' => '123-4567',
            'building' => 'test_building',
        ]);

        // 条件を満たすユーザーをログイン
        $this->actingAs($user);

        // ② 購入画面のリクエストを送信
        $response = $this->get("/purchase/{$item->id}");
        $response->assertStatus(200);

        // ③ 支払い方法を「コンビニ支払い」として購入リクエストを送信
        $purchaseRequestPayload = [
            'item_id' => $item->id,
            'payment_method' => 'コンビニ支払い',
            'address_id' => $address->id,
            'postal_code' => $address->postal_code,
            'address' => $address->address,
            'building' => $address->building,
        ];

        $response = $this->postJson(route('validate.purchase'), $purchaseRequestPayload);

        // レスポンスが成功であることを確認
        $response->assertJson(['success' => true]);

        // ④ purchasesテーブルに購入記録が保存されていることを確認
        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method' => 'コンビニ支払い',
            'address_id' => $address->id, // 住所IDが正しく登録されているか確認
        ]);

        // ⑤ 登録された購入データを取得し、デバッグ出力
        $purchase = Purchase::where('user_id', $user->id)
            ->where('item_id', $item->id)
            ->latest('id')
            ->first();

        // 各フィールドを確認
        $this->assertEquals($user->id, $purchase->user_id);
        $this->assertEquals($item->id, $purchase->item_id);
        $this->assertEquals($address->id, $purchase->address_id);
        $this->assertEquals('コンビニ支払い', $purchase->payment_method);

        dump('購入した商品がpurchasesテーブルに登録され、address_idカラムに購入ユーザーの住所idが登録されていることを確認しました');
    }

}
