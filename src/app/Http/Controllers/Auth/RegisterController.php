<?php

// namespace App\Http\Controllers;（変更前）
namespace App\Http\Controllers\Auth; // 名前空間を変更（メール認証用「laravel/ui」インストール時にディレクトリを変更）
use App\Http\Controllers\Controller; // 親クラスのインポート（メール認証用「laravel/ui」インストール時にディレクトリを変更）

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;  // フォームリクエスト読込み
use App\Actions\Fortify\CreateNewUser; // ユーザー作成アクションをインポート
use Illuminate\Support\Facades\Auth; // ログイン処理用に追加

class RegisterController extends Controller
{


        // 登録フォームを表示するメソッド（GET「/register」）
    public function showRegistrationForm()
    {
        return view('auth.register'); // 登録フォームのビューを返す
    }




    public function register(RegisterRequest $request)
    {
        // バリデーションが通った後、CreateNewUserを呼び出す(-> CreateNewUser.php)
        $user = (new CreateNewUser())->create($request->validated());

        // 登録したユーザーを即座にログインさせる（▶プロフィール編集画面にてログイン済みとするため）
        // Auth::login($user);

        // メール認証用の通知を送信
        $user->sendEmailVerificationNotification();

        // ユーザー登録後の処理（確認メッセージを表示するビューへリダイレクト）
        return redirect()->route('registration.pending')->with('success', 'ユーザー登録が完了しました。メールをご確認ください。');
    }

}