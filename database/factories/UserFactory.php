<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'              => $this->faker->name(),
            'nickname'          => $this->faker->userName(),
            'faculty'           => $this->faker->randomElement(['Science', 'Arts', 'Engineering', 'Business']),
            'language'          => $this->faker->randomElement(['English', 'Thai', 'Burmese']),
            'email'             => $this->faker->unique()->safeEmail(),
            'password'          => bcrypt('password'), // default password
            'role'              => 'student', // overridden for admin/lecturer in seeder
            'line_id'           => $this->faker->optional()->userName(),
            'phone_number'      => $this->faker->optional()->phoneNumber(),
            'email_verified_at' => now(),
            'remember_token'    => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => 'admin']);
    }

    public function lecturer(): static
    {
        return $this->state(fn () => ['role' => 'lecturer']);
    }

    public function student(): static
    {
        return $this->state(fn () => ['role' => 'student']);
    }
}

