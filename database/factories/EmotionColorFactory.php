<?php

namespace Database\Factories;

use App\Enums\EmotionState;
use App\Models\EmotionColor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmotionColor>
 */
class EmotionColorFactory extends Factory
{
    protected $model = EmotionColor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'emotion_state' => Arr::random(EmotionState::cases())->value,
            'color_code' => $this->faker->hexColor(),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
