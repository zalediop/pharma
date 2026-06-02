<?php

namespace Database\Factories;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'specialty' => $this->faker->randomElement([
                'Généraliste',
                'Cardiologue',
                'Dermatologue',
                'Pédiatre',
                'Neurologue',
                'ORL',
                'Gynécologue',
            ]),
            'license_number' => 'LIC' . $this->faker->unique()->numerify('######'),
        ];
    }
}
