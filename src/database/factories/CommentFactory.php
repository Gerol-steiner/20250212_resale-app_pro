<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Comment; // 追加
use App\Models\User;    // 追加
use App\Models\Item;    // 追加

class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // 最大試行回数
        $maxAttempts = 10;

        for ($attempts = 0; $attempts < $maxAttempts; $attempts++) {
            // ランダムなユーザーを選択
            $user = User::inRandomOrder()->first();

            // ランダムなアイテムを選択
            $item = Item::inRandomOrder()->first();

            // 同じ user_id と item_id の組み合わせが存在しないことを確認
            if (!Comment::where('user_id', $user->id)->where('item_id', $item->id)->exists()) {
                return [
                    'user_id' => $user->id,
                    'item_id' => $item->id,
                    'content' => $this->faker->realText(255), // 255文字以内のランダムなテキスト
                ];
            }
        }

        // 最大試行回数を超えた場合はエラーを出す
        throw new \Exception("Unable to find a unique user-item combination for comment.");
    }
}
