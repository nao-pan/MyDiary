<?php

namespace Database\Factories;

use App\Enums\EmotionState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmotionLog>
 */
class EmotionLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'emotion_state' => Arr::random(EmotionState::cases())->value,
            'score' => $this->faker->randomFloat(2, 0, 1),
            'created_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ];
    }
}
