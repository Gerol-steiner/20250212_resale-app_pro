<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;  // 追加

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_logout()
    {
        // テスト用のユーザーを作成
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // 1. ユーザーにログインをする
        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // ユーザーが認証されていることを確認
        $this->assertAuthenticated();

        // 2. ログアウトボタンを押す
        $response = $this->post('/logout');

        // リダイレクトされることを確認
        $response->assertRedirect('/login');

        // ユーザーが認証されていないことを確認
        $this->assertGuest();
        dump('正常にログアウト可能なことを確認しました');
    }
}
