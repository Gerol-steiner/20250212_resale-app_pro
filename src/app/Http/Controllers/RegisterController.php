<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;  // フォームリクエスト読込み
use App\Actions\Fortify\CreateNewUser; // ユーザー作成アクションをインポート
use Illuminate\Support\Facades\Auth; // ログイン処理用に追加

class RegisterController extends Controller
{
    public function register(RegisterRequest $request)
    {
        // バリデーションが通った後、CreateNewUserを呼び出す(-> CreateNewUser.php)
        $user = (new CreateNewUser())->create($request->validated());

        // 登録したユーザーを即座にログインさせる（▶プロフィール編集画面にてログイン済みとするため）
        Auth::login($user);

        // ユーザー登録後の処理（ログイン、リダイレクトなど）
        return redirect()->route('mypage.profile')->with('success', 'ユーザー登録が完了しました。');
    }
}

