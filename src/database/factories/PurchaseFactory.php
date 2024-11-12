<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Purchase; // 追加
use App\Models\User; // 追加
use App\Models\Item; // 追加
use App\Models\Address; // 追加

class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $maxAttempts = User::count(); // 最大試行回数
        $triedBuyers = []; // 試した購入者のIDを格納する配列

        $address = null;
        $item = null;

        for ($attempts = 0; $attempts < $maxAttempts; $attempts++) {
            // $triedBuyersに含まれないidをもつUserレコードをランダムで取得
            $buyer = User::whereNotIn('id', $triedBuyers)->inRandomOrder()->first();

            // 購入者が見つからない場合はループを抜ける
            if (!$buyer) {
                break;
            }

            // 試した購入者のIDを追加
            $triedBuyers[] = $buyer->id;

            // 購入者とは異なるユーザが出品した商品を選択
            $item = Item::where('user_id', '!=', $buyer->id)->inRandomOrder()->first();

            // 有効な商品が見つからなかった場合は以降の処理に進まず次のループへ
            if (!$item) {
                continue;
            }

            // 購入者に対応する住所を取得
            $address = Address::where('user_id', $buyer->id)->inRandomOrder()->first();

            // 有効な住所が見つかった場合、ループを抜ける
            if ($address) {
                break;
            }
        }

        // 有効な住所が見つからなかった場合はエラーを出す
        if (!$address) {
            throw new \Exception("No valid address found for buyer after multiple attempts.");
        }

        return [
            'user_id' => $buyer->id, // 購入者のID
            'item_id' => $item->id,   // 出品者が異なる商品を選択
            'address_id' => $address->id, // 購入者に関連する住所を選択
            'payment_method' => $this->faker->randomElement(['コンビニ支払い', 'カード支払い']), // ランダム
        ];
    }
}
