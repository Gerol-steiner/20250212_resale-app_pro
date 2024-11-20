<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Address;
class UserInformationTest extends TestCase
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

    public function test_mypage_displays_user_information_and_items_correctly()
    {
        // ① ユーザーを取得（出品した商品と購入した商品があるユーザー）
        $user = User::whereHas('items') // 出品した商品がある
            ->whereHas('purchases') // 購入した商品がある
            ->first();

        if (!$user) {
            $this->fail('条件を満たすユーザーが見つかりませんでした。');
        }

        // ② ログイン
        $this->actingAs($user);

        // プロフィール画像を2パターンでテスト
        $defaultProfileImage = asset('images/user_icon_default.png');
        $customProfileImage = 'images/custom_profile_image.png';

        // プロフィール画像がnullの場合のテスト
        $user->update(['profile_image' => null]);

        // ③ マイページを表示
        $response = $this->get(route('mypage.index'));
        $response->assertStatus(200);


        // プロフィール画像（デフォルト）が表示されていることを確認
        $response->assertSee($defaultProfileImage);
        $response->assertSee($user->profile_name);

        // プロフィール画像がカスタム画像の場合のテスト
        if (!$user->update(['profile_image' => $customProfileImage])) {
            $this->fail('Failed to update profile_image.');
        }

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'profile_image' => $customProfileImage,
        ]);

        // ④ 再びマイページを表示
        $response = $this->get(route('mypage.index'));
        $response->assertStatus(200);

        // カスタムプロフィール画像が表示されていることを確認
        $response->assertSee(asset($customProfileImage));
        $response->assertSee($user->profile_name);

        // ③ 「出品した商品」の表示を確認
        $response = $this->get('/mypage?tab=sell');
        $response->assertStatus(200);

        // 出品した商品を確認
        $sellItems = Item::where('user_id', $user->id)->get();
        foreach ($sellItems as $item) {
            $response->assertSee($item->name);
        }

        // ④ 「購入した商品」の表示を確認
        $response = $this->get('/mypage?tab=buy');
        $response->assertStatus(200);

        // 購入した商品を取得
        $buyItems = Item::whereIn('id', function ($query) use ($user) {
            $query->select('item_id')->from('purchases')->where('user_id', $user->id);
        })->get();


        // ビューへの表示を確認
        foreach ($buyItems as $item) {
            $response->assertSee($item->name);
        }

        dump('マイページにてユーザー情報と商品情報が正しく表示されていることを確認しました');
    }

    public function test_profile_page_displays_user_and_address_information_correctly()
    {
        // ① ユーザーをランダムに1人取得（関連する住所データを持つユーザー）
        $user = User::has('addresses')->inRandomOrder()->first();

        if (!$user) {
            $this->fail('条件を満たすユーザーが見つかりませんでした。');
        }

        // ログイン
        $this->actingAs($user);

        // プロフィール画像を2パターンでテスト
        $defaultProfileImage = asset('images/user_icon_default.png');
        $customProfileImage = 'images/custom_profile_image.png';

        // ② プロフィール設定画面を開く（profile_imageがnullの場合）
        $user->update(['profile_image' => null]); // profile_imageを空に設定
        $response = $this->get(route('mypage.profile'));
        $response->assertStatus(200);

        // デフォルトプロフィール画像がHTML内に設定されていることを確認
        $html = $response->getContent();
        $this->assertStringContainsString(e($defaultProfileImage) . '"', $html);

        // ③ プロフィール設定画面を開く（profile_imageに値がある場合）
        $user->update(['profile_image' => $customProfileImage]); // profile_imageに値を設定
        $response = $this->get(route('mypage.profile'));
        $response->assertStatus(200);

        // カスタムプロフィール画像がHTML内に設定されていることを確認
        $html = $response->getContent();
        $this->assertStringContainsString(e(asset($customProfileImage)) . '"', $html);

        // ④ コントローラがビューに渡しているデータを確認
        $response->assertViewHas('user', function ($viewUser) use ($user) {
            return $viewUser->id === $user->id;
        });

        $response->assertViewHas('address', function ($address) use ($user) {
            return $address && $address->user_id === $user->id && $address->is_default === 1;
        });

        // ビューに渡されたuserを取得
        $userFromView = $response->viewData('user');
        // ビューに渡されたaddressを取得
        $addressFromView = $response->viewData('address');

        // ⑤ ビューのHTML内容を確認
        // 1. ユーザー名
        $this->assertStringContainsString('value="' . e($userFromView->profile_name) . '"', $html);

        // 2. 郵便番号
        $this->assertStringContainsString('value="' . e($addressFromView->postal_code) . '"', $html);

        // 3. 住所
        $this->assertStringContainsString('value="' . e($addressFromView->address) . '"', $html);

        // 4. 建物名
        $this->assertStringContainsString('value="' . e($addressFromView->building) . '"', $html);

        dump('プロフィール設定画面にて各項目の初期値が正しく表示されていることを確認しました');
    }

}

