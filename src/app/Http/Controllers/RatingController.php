<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rating;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RatingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'rating' => 'required|integer|min:0|max:5',
        ]);

        $purchase = Purchase::findOrFail($request->purchase_id);
        $userId = Auth::id();

        DB::beginTransaction();
        try {
            // 取引相手を特定
            $ratedUserId = ($purchase->user_id === $userId)
                ? $purchase->item->user_id // 出品者が評価される
                : $purchase->user_id; // 購入者が評価される

            // `ratings` テーブルに評価を登録
            Rating::updateOrCreate(
                [
                    'rater_id' => $userId,
                    'rated_id' => $ratedUserId,
                    'purchase_id' => $purchase->id,
                ],
                [
                    'rating' => $request->rating,
                ]
            );

            // `purchases` テーブルの `buyer_rated` または `seller_rated` を更新
            if ($purchase->user_id === $userId) {
                $purchase->buyer_rated = 1;
            } else {
                $purchase->seller_rated = 1;
            }
            $purchase->save();

            DB::commit();

            return redirect()->route('item.index')->with('success', '評価が完了しました');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', '評価の送信に失敗しました');
        }
    }
}
