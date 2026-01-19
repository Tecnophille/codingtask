<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'items' => [$this->faker->word, $this->faker->word],
            'pickup_time' => $this->faker->dateTimeBetween('now', '+1 hour'),
            'is_vip' => false,
            'status' => 'active',
        ];
    }
}
