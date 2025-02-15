<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Chat;
use App\Models\User;

class ChatController extends Controller
{
    // 取引チャット画面の表示
    public function showTransactionChat($item_id)
    {
        // 認証済みユーザーかどうかを確認
        $isAuthenticated = Auth::check();
        $userId = $isAuthenticated ? Auth::id() : null;

        // 商品情報を取得
        $item = Item::with(['purchases', 'user'])->findOrFail($item_id);

        // 取引情報を取得（この商品の購入履歴）
        $purchase = Purchase::where('item_id', $item_id)->where('in_progress', 1)->first();

        // ユーザーの役割を判定
        $userRole = '未定'; // 初期値
        // 取引相手
        $partnerName = '不明なユーザー';// 初期値

        if ($purchase) {
            if ($purchase->user_id == $userId) {
                $userRole = '購入者';
                $partner = $item->user; // 出品者
            } elseif ($item->user_id == $userId) {
                $userRole = '出品者';
                $partner = $purchase->user; // 購入者
            }

            // 取引相手のユーザー名とプロフィール画像を取得
            if (isset($partner)) {
                $partnerName = $partner->profile_name;
                $partnerProfileImage = $partner->profile_image;
            }
        }

        // **ログインユーザーのプロフィール画像を取得**
        $profileImage = null;
        if ($isAuthenticated) {
            $user = Auth::user(); // 現在のログインユーザーを取得
            $profileImage = $user->profile_image; // プロフィール画像のパスを取得
        }

        return view('mypage.transaction_chat', compact(
            'item', 'isAuthenticated', 'userId', 'userRole', 'partnerName', 'partnerProfileImage', 'profileImage'
        ));
    }
}
