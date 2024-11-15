<?php

// namespace App\Http\Controllers;（変更前）
namespace App\Http\Controllers\Auth; // 名前空間を変更（メール認証用「laravel/ui」インストール時にディレクトリを変更）
use App\Http\Controllers\Controller; // 親クラスのインポート（メール認証用「laravel/ui」インストール時にディレクトリを変更）

use App\Http\Requests\LoginRequest; //フォームリクエストをインポート
use Illuminate\Support\Facades\Auth; // Authファサードをインポート（認証用）
use Illuminate\Http\Request;
use App\Models\User; // インポート

class LoginController extends Controller
{

    public function showLoginForm()
    {
        return view('auth.login'); // 登録フォームのビューを返す
    }


public function login(LoginRequest $request)
{
    $credentials = $request->only('email', 'password');

    // ユーザーを取得（認証前）
    $user = User::where('email', $request->email)->first();

    // ユーザーが存在し、かつメール認証が完了していない場合
    if ($user && !$user->hasVerifiedEmail()) {
        return back()->withErrors([
            'email' => 'メールアドレスの認証が完了していません。送付した認証メールをご確認ください。',
        ])->withInput();
    }

    if (Auth::attempt($credentials)) {
        // 認証に成功し、かつメール認証が完了している場合の処理
        $request->session()->regenerate();
        return redirect('/');
    }

    // 認証に失敗した場合の処理
    return back()->withErrors([
        'email' => 'ログイン情報が正しくありません',
    ])->withInput();
}

    public function logout(Request $request)  // POSTでのlogout処理
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }

}
