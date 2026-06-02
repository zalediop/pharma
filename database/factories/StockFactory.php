<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StockFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quantity' => $this->faker->numberBetween(5, 100),
            'expiry_date' => $this->faker->dateTimeBetween('+2 months', '+2 years'),
            'alert_threshold' => 10,
        ];
    }
}
