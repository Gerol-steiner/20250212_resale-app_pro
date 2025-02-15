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

        // 対応するpurchaseレコードを取得
        $purchase = Purchase::where('item_id', $item_id)->where('in_progress', 1)->first();

        $userRole = '未定'; // 初期値
        $partnerName = '不明なユーザー'; // 初期値
        $purchaseId = null; // 初期値
        $partnerProfileImage = null; // 取引相手のプロフィール画像
        $chatMessages = collect(); // チャット履歴（デフォルト空）


        if ($purchase) {
            $purchaseId = $purchase->id; // purchase_id を取得
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

            // 過去のチャット履歴を取得（作成日時の昇順）
            $chatMessages = Chat::where('purchase_id', $purchaseId)
                ->where('is_deleted', 0) // 削除されていないメッセージのみ取得
                ->orderBy('created_at', 'asc') // 古い順に表示
                ->get();
        }

        // ログインユーザーのプロフィール画像を取得
        $profileImage = null;
        if ($isAuthenticated) {
            $user = Auth::user(); // 現在のログインユーザーを取得
            $profileImage = $user->profile_image; // プロフィール画像のパスを取得
        }

        return view('mypage.transaction_chat', compact(
            'item', 'isAuthenticated', 'userId', 'userRole', 'partnerName', 'partnerProfileImage', 'profileImage', 'purchaseId', 'chatMessages'
        ));
    }

    // チャットの送信・登録
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:400',
            'purchase_id' => 'required|exists:purchases,id',
        ]);

        $chat = Chat::create([
            'purchase_id' => $request->purchase_id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'is_read' => 0, // 初期値：未読
            'is_deleted' => 0,
            'is_edited' => 0,
        ]);

        return response()->json([
            'message' => $chat->message,
            'time' => $chat->created_at->format('H:i'),
        ]);
    }
}
