<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'google_sub' => (string) fake()->unique()->numerify('####################'),
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->name(),
            'school' => fake()->optional()->city(),
            'grade' => (string) fake()->numberBetween(6, 12),
            'org_unit_path' => '/Students/High School',
            'registered_at' => null,
            'reset_enabled' => true,
        ];
    }

    public function registered(): static
    {
        return $this->state(fn () => [
            'registered_at' => now(),
        ]);
    }
}
