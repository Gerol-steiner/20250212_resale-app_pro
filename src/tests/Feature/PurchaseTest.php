<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Address;

class PurchaseTest extends TestCase
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

    public function test_convenience_store_payment()
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

        // 条件を満たすアイテムが見つからなかった場合はテストを失敗させる
        if (!$item) {
            $this->fail('条件を満たすアイテムが見つかりませんでした。');
        }

        // 条件を満たすユーザーとアイテムをデバッグ出力
        // dump('User ID: ' . $user->id);
        // dump('Item ID: ' . $item->id);

        $address = Address::where('user_id', $user->id)
        ->latest('id')
        ->first();


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

        // ④ purchasesテーブルに購入記録が保存されたことを確認
        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method' => 'コンビニ支払い',
        ]);

            // ⑤ 最新の購入レコードを取得してデバッグ出力
        $latestPurchase = Purchase::latest('id')->first();

        // 各値をデバッグ出力
        dump('「コンビニ支払い」による購入処理が正常に完了し、purchasesテーブルへ登録されたことを確認しました。');
    }

    public function test_card_payment()
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

        // 条件を満たすアイテムが見つからなかった場合はテストを失敗させる
        if (!$item) {
            $this->fail('条件を満たすアイテムが見つかりませんでした。');
        }

        // 条件を満たすユーザーとアイテムをデバッグ出力
        // dump('User ID: ' . $user->id);
        // dump('Item ID: ' . $item->id);

        $address = Address::create([
            'id' => 55,
            'user_id' => $user->id,
            'address' => 'test_address',
            'postal_code' => '123-4567',
            'building' => 'test_building',
        ]);

        // 条件を満たすユーザーをログイン
        $this->actingAs($user);

        // ② 購入画面のリクエストを送信
        $response = $this->get("/purchase/{$item->id}");
        $response->assertStatus(200);

        // ③ 支払い方法を「カード支払い」として購入リクエストを送信
        $purchaseRequestPayload = [
            'item_id' => $item->id,
            'payment_method' => 'カード支払い',
            'address_id' => $address->id,
            'postal_code' => $address->postal_code,
            'address' => $address->address,
            'building' => $address->building,
        ];

        $response = $this->postJson(route('validate.purchase'), $purchaseRequestPayload);

        // レスポンスが成功であることを確認し、StripeのセッションIDを取得
        $response->assertJson(['success' => true]);
        $data = $response->json();
        // dump('Stripe Session ID: ' . $data['session_id']);

        // Stripeのチェックアウトセッション作成が成功したことを確認
        $this->assertArrayHasKey('session_id', $data);

        // Stripe決済が完了したと仮定してsuccess_urlを呼び出す
        $successUrl = route('purchaseComplete', [
            'item_id' => $item->id,
            'address_id' => $address->id,
        ]);

        $response = $this->get($successUrl);

        // ステータスコードが200であることを確認
        $response->assertStatus(200);

        // ④ purchasesテーブルに購入記録が保存されたことを確認
        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method' => 'カード支払い',
            'address_id' => $address->id,
        ]);


        // 各値をデバッグ出力
        dump('「カード支払い」による購入処理が正常に完了し、purchasesテーブルへ登録されたことを確認しました。');

    }

    public function test_sold_label_is_displayed_for_purchased_item()
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

        // ユーザーをログイン
        $this->actingAs($user);

        // ② 購入画面のリクエストを送信
        $this->get("/purchase/{$item->id}")->assertStatus(200);

        // ③ 購入リクエストを送信
        $purchaseRequestPayload = [
            'item_id' => $item->id,
            'payment_method' => 'コンビニ支払い',
            'address_id' => $address->id,
            'postal_code' => $address->postal_code,
            'address' => $address->address,
            'building' => $address->building,
        ];

        $this->postJson(route('validate.purchase'), $purchaseRequestPayload)
            ->assertJson(['success' => true]);

        // ④ purchasesテーブルに購入記録が保存されていることを確認
        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method' => 'コンビニ支払い',
        ]);

        // ⑤ 商品一覧ビューを取得
        $response = $this->get('/');
        $response->assertStatus(200);

        // HTML内容を取得
        $html = $response->getContent();

        // 対象商品のHTML構造を正規表現で抽出
        $pattern = sprintf(
            '/<a href="%s">(.*?)<h3 class="item-name">%s<\/h3>/s',
            preg_quote(route('item.detail', $item->id), '/'),  // プレースホルダー %s に商品詳細リンクのURLをエスケープして埋め込み
            preg_quote($item->name, '/')  // プレースホルダー %s に商品詳細リンクのURLをエスケープして埋め込み
        );

        // $pattern：動的に生成した正規表現
        // $html：検索対象のHTML全体
        // $matches：マッチした内容が格納される
        preg_match($pattern, $html, $matches);

        if (empty($matches)) {
            $this->fail('対象商品のHTML構造を取得できませんでした。');
        }

        $itemHtml = $matches[0];

        // dump('$item->id： ' . $item->id);
        // dump('$item->name： ' . $item->name);
        // デバッグ用（購入した商品の画像表示部のHTML）
        //dump($itemHtml);

        // 抽出したHTMLに「sold」ラベルが含まれていることを確認
        $this->assertStringContainsString(
            '<img src="' . asset('images/sold-label.svg') . '" alt="Sold" class="sold-label">',
            $itemHtml,
            '購入済み商品の「sold」ラベルが表示されていません。'
        );
        dump('購入した商品について、商品一覧画面にて「Sold」ラベルが表示されていることを確認しました。');
    }

    public function test_purchased_item_is_displayed_in_mypage_buy_tab()
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

        $address = Address::create([
            'id' => 80,
            'user_id' => $user->id,
            'address' => 'test_address',
            'postal_code' => '123-4567',
            'building' => 'test_building',
        ]);

        dump($address->id);
        dump($address->postal_code);

        // 条件を満たすユーザーをログイン
        $this->actingAs($user);

        // ② マイページを表示し、「購入した商品」タブを選択
        $response = $this->get('/mypage?tab=buy');
        $response->assertStatus(200);

        // ③ 現時点で購入した商品が表示されていないことを確認
        $response->assertDontSee($item->name);

        // ④ 商品購入処理を実行
        $response = $this->get("/purchase/{$item->id}");
        $response->assertStatus(200);

        $purchaseRequestPayload = [
            'item_id' => $item->id,
            'payment_method' => 'カード支払い',
            'address_id' => $address->id,
            'postal_code' => $address->postal_code,
            'address' => $address->address,
            'building' => $address->building,
        ];

        $response = $this->postJson(route('validate.purchase'), $purchaseRequestPayload);
        $response->assertJson(['success' => true]);
        $data = $response->json();
        // dump('Stripe Session ID: ' . $data['session_id']);
        $this->assertArrayHasKey('session_id', $data);

        $successUrl = route('purchaseComplete', [
            'item_id' => $item->id,
            'address_id' => $address->id,
        ]);
        $response = $this->get($successUrl);
        $response->assertStatus(200);

        // ⑤ purchasesテーブルに購入記録が保存されていることを確認
        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method' => 'カード支払い',
            'address_id' => $address->id,
        ]);

        // ⑥ 再びマイページを表示し、「購入した商品」タブを選択
        $response = $this->get('/mypage?tab=buy');
        $response->assertStatus(200);

        // ⑦ 今回購入した商品が表示されていることを確認
        $response->assertSee($item->name);
        dump('購入した商品がマイページの「購入した商品」一覧に表示されていることを確認しました');
    }


}
