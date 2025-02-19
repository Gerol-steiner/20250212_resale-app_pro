<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Like; // 追加
use App\Models\User; // 追加
use App\Models\Item; // 追加

class LikeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

    public function definition()
    {
        // 最大試行回数
        $maxAttempts = 3;

        for ($attempts = 0; $attempts < $maxAttempts; $attempts++) {
            // ランダムなユーザーを選択
            $userId = User::inRandomOrder()->first()->id;

            // ランダムなアイテムを選択
            $itemId = Item::inRandomOrder()->first()->id;

            // 同じ user_id と item_id の組み合わせが存在しないことを確認
            if (!Like::where('user_id', $userId)->where('item_id', $itemId)->exists()) {
                return [
                    'user_id' => $userId,
                    'item_id' => $itemId,
                ];
            }
        }

        // 最大試行回数を超えた場合はエラーを出す
        throw new \Exception("Unable to find a unique user-item combination for like.");
    }
}
