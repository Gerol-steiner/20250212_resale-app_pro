<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\EmailVerificationRequest; //追加
use Illuminate\Support\Facades\Auth; //追加
use App\Models\User; //Userモデルをインポート

class VerificationController extends Controller
{
    // 認証リンク「get('/email/verify/{id}/{hash}'」に対する処理
    public function temporaryLoginAndVerify($id, $hash)
    {
        $user = User::find($id);

        if (!$user) {
            // ■ 仮登録すらしていないユーザー
            return redirect()->route('login')->with('error', '無効な認証リンクです。');
        }

        if ($user->hasVerifiedEmail()) {
            // ■ 既に認証まで済ませたユーザー
            if (Auth::check()) {
                // ■ ユーザーが既にログインしている場合
                return redirect()->route('item.index')->with('info', 'メールアドレスは既に認証されています。');
            } else {
                // ■ ユーザーがログインしていない場合
                Auth::login($user);
                return redirect()->route('item.index')->with('info', 'メールアドレスは既に認証されています。ログインしました。');
            }
        }

        // ■ 仮登録済みなので認証を行うユーザー
        Auth::login($user);

        if (hash_equals((string) $user->getKey(), $id) && hash_equals((string) $hash, sha1($user->email))) {
            $user->markEmailAsVerified();
            return redirect('/mypage/profile')->with('success', 'メールアドレスが認証されました。');
        }

        // ■ ハッシュが一致しない不正なケース
        return redirect()->route('login');
    }

}

