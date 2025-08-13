<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DailyMetric>
 */
class DailyMetricFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => now()->subDay()->toDateString(),
            'new_users' => 0,
            'posts' => 0,
            'd1_retention' => 0.00,
            'd7_retention' => 0.00,
            'wau' => 0,
            'mau' => 0,
            'weekly_3plus_ratio' => 0.00,
        ];
    }
}
