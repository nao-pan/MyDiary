<?php

namespace Database\Factories;

use App\Models\UnlockedEmotion;
use App\Models\User;
use App\Models\Diary;
use App\Enums\EmotionState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UnlockedEmotion>
 */
class UnlockedEmotionFactory extends Factory
{
    protected $model = UnlockedEmotion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'diary_id' => Diary::factory(),
            'emotion_state' => Arr::random(EmotionState::cases())->value,
            'unlocked_at' => $this->faker->dateTimeBetween('-30days', 'now'),
        ];
    }

    /**
     * GRATEFUL感情を解禁対象に設定する
     */
    public function grateful(): static
    {
        return $this->state(fn() => [
            'emotion_state' => EmotionState::GRATEFUL->value,
        ]);
    }
}
