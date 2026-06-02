<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'phone' => $this->faker->phoneNumber(),
            'chronic_medication' => json_encode($this->faker->randomElements(
                ['Hypertension', 'Diabète', 'Asthme', 'Arthrite', 'Cholestérol'],
                rand(0, 2)
            )),
        ];
    }
}
