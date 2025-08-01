<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Diary>
 */
class DiaryFactory extends Factory
{
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
}
