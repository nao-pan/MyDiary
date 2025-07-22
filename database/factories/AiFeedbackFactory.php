<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AiFeedback>
 */
class AiFeedbackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'summary' => $this->faker->paragraph(),
            'advice' => $this->faker->paragraph(),
            'raw_response' =>  json_encode([
                'model' => 'gpt-4',
                'content' => $this->faker->paragraph(2),
            ]),
            'created_at' => $this->faker->now(),
            'updated_at' => $this->faker->now(),
        ];
    }
}
