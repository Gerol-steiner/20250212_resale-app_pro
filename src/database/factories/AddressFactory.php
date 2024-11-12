<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'is_default' => true, // 常にtrue
            'address' => $this->faker->address,
            'postal_code' => $this->generatePostalCode(), // カスタムメソッドで郵便番号を生成
            'building' => $this->faker->word,
        ];
    }

    /**
     * 郵便番号を「xxx-xxxx」の形式で生成するカスタムメソッド
     *
     * @return string
     */
    protected function generatePostalCode()
    {
        $firstPart = $this->faker->numberBetween(100, 999); // 100-999の間の数値
        $secondPart = $this->faker->numberBetween(1000, 9999); // 1000-9999の間の数値
        return "{$firstPart}-{$secondPart}"; // フォーマットに合わせて結合
    }
}
