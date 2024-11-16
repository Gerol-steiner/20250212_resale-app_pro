<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use App\Models\Condition;
use Illuminate\Database\Eloquent\Factories\Factory;

// PHPUnit単体テスト用のFactory
class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition()
    {
        return [
            'image_url' => $this->faker->imageUrl(),
            'user_id' => User::factory(),
            'condition_id' => Condition::factory(),
            'name' => $this->faker->lexify(str_repeat('?', 8)), // 最低8文字のランダムな文字列
            'description' => $this->faker->sentence,
            'price' => $this->faker->numberBetween(100, 10000),
            'brand' => $this->faker->optional()->company,
        ];
    }
}
