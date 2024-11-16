<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;  // 追加

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_requires_name()
    {
        // 1. 会員登録ページを開く
        $response = $this->get('/register');
        $response->assertStatus(200);

        // 2. 名前を入力せずに他の必要項目を入力し、3. 登録ボタンを押す
        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // リダイレクトされることを確認
        $response->assertRedirect('/register');

        // セッションにエラーが存在することを確認
        $response->assertSessionHasErrors('name');

        // 実際に表示されるエラーメッセージを取得
        $errors = session('errors');
        $nameError = $errors->get('name')[0];

        // エラーメッセージをデバッグ出力
        dump($nameError);

        // エラーメッセージの内容を確認
        $this->assertEquals('お名前を入力してください', $nameError);

        // エラーメッセージを含むページが表示されることを確認
        $response = $this->get('/register');
        // レスポンスの内容に指定した文字列が含まれているかを確認
        $response->assertSee('お名前を入力してください');

    }

    public function test_register_requires_email()
    {
        // 1. 会員登録ページを開く
        $response = $this->get('/register');
        $response->assertStatus(200);

        // 2. メールアドレスを入力せずに他の必要項目を入力し、3. 登録ボタンを押す
        $response = $this->post('/register', [
            'name' => 'Test User',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // リダイレクトされることを確認
        $response->assertRedirect('/register');

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
        $response = $this->get('/register');
        // レスポンスの内容に指定した文字列が含まれているかを確認
        $response->assertSee('メールアドレスを入力してください');
    }

    public function test_register_requires_password()
    {
        // 1. 会員登録ページを開く
        $response = $this->get('/register');
        $response->assertStatus(200);

        // 2. パスワードを入力せずに他の必要項目を入力し、3. 登録ボタンを押す
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password_confirmation' => 'password',
        ]);

        // リダイレクトされることを確認
        $response->assertRedirect('/register');

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
        $response = $this->get('/register');
        // レスポンスの内容に指定した文字列が含まれているかを確認
        $response->assertSee('パスワードを入力してください');
    }

    public function test_register_password_minimum_length()
    {
        // 1. 会員登録ページを開く
        $response = $this->get('/register');
        $response->assertStatus(200);

        // 2. 7文字以下のパスワードと他の必要項目を入力し、3. 登録ボタンを押す
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        // リダイレクトされることを確認
        $response->assertRedirect('/register');

        // セッションにエラーが存在することを確認
        $response->assertSessionHasErrors('password');

        // 実際に表示されるエラーメッセージを取得
        $errors = session('errors');
        $passwordError = $errors->get('password')[0];

        // エラーメッセージをデバッグ出力
        dump($passwordError);

        // エラーメッセージの内容を確認
        $this->assertEquals('パスワードは8文字以上で入力してください', $passwordError);

        // エラーメッセージを含むページが表示されることを確認
        $response = $this->get('/register');
        // レスポンスの内容に指定した文字列が含まれているかを確認
        $response->assertSee('パスワードは8文字以上で入力してください');
    }

    public function test_register_password_confirmation()
    {
        // 1. 会員登録ページを開く
        $response = $this->get('/register');
        $response->assertStatus(200);

        // 2. 確認用パスワードと異なるパスワードを入力し、他の必要項目も入力する、3. 登録ボタンを押す
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different',
        ]);

        // リダイレクトされることを確認
        $response->assertRedirect('/register');

        // セッションにエラーが存在することを確認
        $response->assertSessionHasErrors('password_confirmation');

        // 実際に表示されるエラーメッセージを取得
        $errors = session('errors');
        $passwordError = $errors->get('password_confirmation')[0];

        // エラーメッセージをデバッグ出力
        dump($passwordError);

        // エラーメッセージの内容を確認
        $this->assertEquals('パスワードと一致しません', $passwordError);

        // エラーメッセージを含むページが表示されることを確認
        $response = $this->get('/register');
        // レスポンスの内容に指定した文字列が含まれているかを確認
        $response->assertSee('パスワードと一致しません');
    }

    public function test_successful_registration()
    {
        // 1. 会員登録ページを開く
        $response = $this->get('/register');
        $response->assertStatus(200);

        // 2. 全ての必要項目を正しく入力し、3. 登録ボタンを押す
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // ログイン画面にリダイレクトされることを確認
        $response->assertRedirect('/register/pending');

        // データベースに新しいユーザーが登録されたことを確認
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}