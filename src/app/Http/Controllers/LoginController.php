<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest; //フォームリクエストをインポート
use Illuminate\Support\Facades\Auth; // Authファサードをインポート（認証用）
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        // バリデーションが成功した場合、以下が実行される
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) { // クレデンシャルをデータベースのユーザー情報と照合
            // 認証に成功した場合の処理
            return redirect('/');
        }

        // 認証に失敗した場合の処理
        return back()->withErrors([  // エラーメッセージをセッションに追加
            'email' => 'ログイン情報が正しくありません',
        ]);
    }

    public function logout(Request $request)  // POSTでのlogout処理
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }

}
