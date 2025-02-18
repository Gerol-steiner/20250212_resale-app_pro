<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Http\Requests\MessageRequest;

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

            // 取引チャット画面を開いたときに相手の未読メッセージを既読にする
            Chat::where('purchase_id', $purchaseId)
                ->where('user_id', '!=', $userId) // 相手のメッセージ
                ->where('is_read', 0) // まだ未読
                ->update(['is_read' => 1]); // 既読に更新
            }

        // ログインユーザーのプロフィール情報を取得
        $profileImage = null;
        $profileName = null;
        if ($isAuthenticated) {
            $user = Auth::user(); // 現在のログインユーザーを取得
            $profileImage = $user->profile_image; // 自分のプロフィール画像のパスを取得
            $profileName = $user->profile_name; // 自分のユーザー名
        }

        // ユーザーの取引中の商品一覧を取得（サイドバー用）
        $ongoingTransactions = Purchase::where('in_progress', 1)
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('item', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    });
            })
            ->with('item')
            ->get();

        return view('mypage.transaction_chat', compact(
            'item', 'isAuthenticated', 'userId', 'userRole', 'partnerName', 'partnerProfileImage', 'profileImage', 'purchaseId', 'chatMessages', 'profileName', 'ongoingTransactions'
        ));
    }

    // チャットの送信・登録
    public function sendMessage(MessageRequest $request)
    {
        Log::info('リクエストデータ:', $request->all());
        Log::info('バリデーション通過');

        $chat = new Chat();
        $chat->purchase_id = $request->purchase_id;
        $chat->user_id = Auth::id();
        $chat->message = $request->message ?? null;

        // 画像の保存
        if ($request->hasFile('image')) {
            Log::info('画像がアップロードされました');
            $path = $request->file('image')->store('uploads/chats', 'public');
            $chat->image_path = 'storage/' . $path; // DBには "storage/uploads/chats/filename.png" を保存
        }

        $chat->save();

        return response()->json([
            'message_id' => $chat->id,
            'message' => $chat->message,
            'time' => $chat->created_at->format('H:i'),
            'image_path' => $chat->image_path ? asset($chat->image_path) : null, // 画像パスを `asset()` で変換
            'user_id' => $chat->user_id,
        ]);
    }
    /**
     * ポーリングを用いて、一定の間隔でデータベースからメッセージを取得する関数
     *
     * @param Request $request クライアントからのリクエスト（purchase_id, last_time）
     * @return \Illuminate\Http\JsonResponse 新しいメッセージのリストと最新のメッセージ時刻
     */
    public function getMessages(Request $request)
    {
        $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'last_time' => 'nullable|string', // ISO 8601 または Laravel 形式の文字列
        ]);

        $userId = Auth::id(); // ログインユーザーのID

        // last_time を Laravel のタイムゾーンに合わせて変換
        $lastTime = $request->last_time
        ? Carbon::parse($request->last_time)->setTimezone(config('app.timezone'))->toDateTimeString()
        : null;

        // メッセージを取得
        $messages = Chat::where('purchase_id', $request->purchase_id)
            ->where('is_deleted', 0)
            ->where('created_at', '>', $lastTime)
            ->orderBy('created_at', 'asc')
            ->get();

        // 自分が送信したメッセージで、相手がまだ読んでいないものを既読にする
        Chat::where('purchase_id', $request->purchase_id)
            ->where('user_id', '!=', $userId) // 相手のメッセージ
            ->where('is_read', 0) // まだ未読
            ->update(['is_read' => 1]); // 既読に更新

        return response()->json([
            // messagesキー
            'messages' => $messages->map(function ($message) {
                return [
                    'message_id' => $message->id,
                    'message' => $message->message,
                    'user_id' => $message->user_id,
                    'is_read' => $message->is_read,
                    'time' => $message->created_at->format('H:i'),
                    'image_path' => $message->image_path ? asset($message->image_path) : null,
                ];
            }),
            // latest_timeキー
            'latest_time' => $messages->isNotEmpty()
                ? $messages->last()->created_at->toISOString()
                : $request->last_time,
        ]);
    }

    // チャットの削除
    public function deleteMessage(Request $request)
    {
        $request->validate([
            'message_id' => 'required|exists:chats,id',
        ]);

        $chat = Chat::where('id', $request->message_id)
            ->where('user_id', Auth::id()) // 自分のメッセージのみ削除可能
            ->first();

        if (!$chat) {
            return response()->json(['error' => 'メッセージが見つかりません'], 404);
        }

        // is_deleted を 1 にする
        $chat->is_deleted = 1;
        $chat->save();

        return response()->json(['success' => 'メッセージを削除しました']);
    }

    // チャットの編集
    public function editMessage(Request $request)
    {
        $request->validate([
            'message_id' => 'required|exists:chats,id',
            'message' => 'required|string|max:400',
        ]);

        $chat = Chat::where('id', $request->message_id)
            ->where('user_id', Auth::id()) // 自分のメッセージのみ編集可能
            ->first();

        if (!$chat) {
            return response()->json(['error' => 'メッセージが見つかりません'], 404);
        }

        // メッセージを更新
        $chat->message = $request->message;
        $chat->is_edited = 1;
        $chat->save();

        return response()->json([
            'success' => 'メッセージを編集しました',
            'message' => $chat->message,
            'is_edited' => $chat->is_edited,
        ]);
    }
}
