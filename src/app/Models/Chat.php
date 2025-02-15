<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',  // 取引ID
        'user_id',      // 送信者ID
        'message',      // メッセージ本文
        'image_path',   // 画像パス
        'is_deleted',   // 削除フラグ
        'is_edited',    // 編集フラグ
        'is_read',      // 既読フラグ
    ];

    /**
     * 取引（purchasesテーブル）とのリレーション
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * ユーザー（usersテーブル）とのリレーション
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
