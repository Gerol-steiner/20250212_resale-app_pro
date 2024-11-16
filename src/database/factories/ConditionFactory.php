<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Condition;

// PHPUnit単体テスト用のFactory
class ConditionFactory extends Factory
{
    protected $model = Condition::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement([
                '良好',
                '目立った傷や汚れなし',
                'やや傷や汚れあり',
                '状態が悪い'
            ]),
        ];
    }
}
