<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'rater_id',
        'rated_id',
        'purchase_id',
        'rating',
    ];

    /**
     * 評価をしたユーザー（rater_id に紐づく）
     */
    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    /**
     * 評価されたユーザー（rated_id に紐づく）
     */
    public function rated()
    {
        return $this->belongsTo(User::class, 'rated_id');
    }

    /**
     * 取引情報（purchase_id に紐づく）
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
