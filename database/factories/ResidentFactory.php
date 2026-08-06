<?php

namespace Database\Factories;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Resident>
 */
class ResidentFactory extends Factory
{
    protected $model = Resident::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'first_name' => strtoupper($this->faker->firstName()),
            'last_name' => strtoupper($this->faker->lastName()),
            'middle_name' => strtoupper($this->faker->lastName()),
            'suffix' => null,
            'birthdate' => $this->faker->date('Y-m-d', '2000-01-01'),
            'age' => $this->faker->numberBetween(18, 80),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'civil_status' => $this->faker->randomElement(['single', 'married']),
            'nationality' => 'FILIPINO',
            'occupation' => strtoupper($this->faker->jobTitle()),
            'barangay' => 'MOLINO I',
            'city' => 'BACOOR CITY',
            'province' => 'CAVITE',
            'contact_number' => '0917-'.$this->faker->numerify('###-####'),
            'emergency_contact' => '0922-'.$this->faker->numerify('###-####'),
            'category' => 'regular',
        ];
    }
}
