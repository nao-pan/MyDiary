<?php

namespace Database\Factories;

use App\Enums\EmotionState;
use App\Models\Diary;
use App\Models\EmotionLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Diary>
 */
class DiaryFactory extends Factory
{
    protected $model = Diary::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $createdAt = $this->faker->dateTimeBetween('-1 year', 'now');

        return [
            'title' => $this->faker->sentence(5),
            'content' => $this->faker->paragraph(3),
            'created_at' => $createdAt,
            'updated_at' => $this->faker->dateTimeBetween($createdAt, 'now'),
        ];
    }

    public function withEmotionLog(?EmotionState $emotion = null): static
    {
        return $this->has(
            EmotionLog::factory()->state([
                'emotion_state' => ($emotion ?? EmotionState::HAPPY)->value,
            ])
        );
    }
}
