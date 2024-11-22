<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;  // 追加

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_requires_email()
    {
        // 1. ログインページを開く
        $response = $this->get('/login');
        $response->assertStatus(200);

        // 2. メールアドレスを入力せずに他の必要項目を入力し、3. ログインボタンを押す
        $response = $this->post('/login', [
            'password' => 'password',
        ]);

        // リダイレクトされることを確認
        $response->assertRedirect('/login');

        // セッションにエラーが存在することを確認
        $response->assertSessionHasErrors('email');

        // 実際に表示されるエラーメッセージを取得
        $errors = session('errors');
        $emailError = $errors->get('email')[0];

        // エラーメッセージをデバッグ出力
        dump($emailError);

        // エラーメッセージの内容を確認
        $this->assertEquals('メールアドレスを入力してください', $emailError);

        // エラーメッセージを含むページが表示されることを確認
        $response = $this->get('/login');
        $response->assertSee('メールアドレスを入力してください');
    }

    public function test_login_requires_password()
    {
        // 1. ログインページを開く
        $response = $this->get('/login');
        $response->assertStatus(200);

        // 2. パスワードを入力せずに他の必要項目を入力し、3. ログインボタンを押す
        $response = $this->post('/login', [
            'email' => 'test@example.com',
        ]);

        // リダイレクトされることを確認
        $response->assertRedirect('/login');

        // セッションにエラーが存在することを確認
        $response->assertSessionHasErrors('password');

        // 実際に表示されるエラーメッセージを取得
        $errors = session('errors');
        $passwordError = $errors->get('password')[0];

        // エラーメッセージをデバッグ出力
        dump($passwordError);

        // エラーメッセージの内容を確認
        $this->assertEquals('パスワードを入力してください', $passwordError);

        // エラーメッセージを含むページが表示されることを確認
        $response = $this->get('/login');
        $response->assertSee('パスワードを入力してください');
    }

    public function test_login_with_invalid_credentials()
    {
        // 1. ログインページを開く
        $response = $this->get('/login');
        $response->assertStatus(200);

        // 2. 必要項目を登録されていない情報を入力し、3. ログインボタンを押す
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'invalidpassword',
        ]);

        // リダイレクトされることを確認
        $response->assertRedirect('/login');

        // セッションにエラーが存在することを確認
        $response->assertSessionHasErrors();

        // 実際に表示されるエラーメッセージを取得
        $errors = session('errors');
        $loginError = $errors->first();

        // エラーメッセージをデバッグ出力
        dump($loginError);

        // エラーメッセージの内容を確認
        $this->assertEquals('ログイン情報が登録されていません', $loginError);

        // エラーメッセージを含むページが表示されることを確認
        $response = $this->get('/login');
        $response->assertSee('ログイン情報が登録されていません');
    }

    public function test_successful_login()
    {
        // テスト用のユーザーを作成
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // 1. ログインページを開く
        $response = $this->get('/login');
        $response->assertStatus(200);

        // 2. 全ての必要項目を入力し、3. ログインボタンを押す
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // リダイレクトされることを確認
        $response->assertRedirect('/');

        // ユーザーが認証されていることを確認
        $this->assertAuthenticated();
        dump('正しい情報が入力され、正常にログインできることが確認できました');

    }
}
