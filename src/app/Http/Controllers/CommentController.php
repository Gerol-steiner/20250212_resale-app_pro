<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;  // 認証済みかどうか確認用
use App\Models\Comment; // Commentモデルのインポート
use App\Http\Requests\CommentRequest; // フォームリクエスト

class CommentController extends Controller
{
    public function addComment(CommentRequest $request, $item_id)
    {
        // バリデーションエラーが発生した場合は自動でJSONでエラーが返る
        $validated = $request->validated();

        // コメントを保存
        $comment = new Comment();
        $comment->user_id = Auth::id(); // 現在の認証ユーザーのID
        $comment->item_id = $item_id;
        $comment->content = $validated['content'];
        $comment->save();

        // ユーザー情報を取得
        $user = Auth::user();

        // プロフィール名がnullの場合は"guest user"を設定
        $profileName = $user->profile_name ?? 'guest user';

        // プロフィール画像のURLを生成
        $profileImageUrl = $user->profile_image ? asset('storage/uploads/profiles/' . basename($user->profile_image)) : asset('images/user_icon_default.png');

        return response()->json([
            'success' => true,
            'comment' => $comment,
            'user' => [
                'profile_name' => $profileName,
                'profile_image' => $profileImageUrl,
            ]
        ]);
    }
}
