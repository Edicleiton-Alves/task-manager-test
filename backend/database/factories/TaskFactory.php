<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(['todo', 'in_progress', 'done']);

        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => $status,
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'due_date' => fake()->dateTimeBetween('-10 days', '+20 days'),
        ];
    }
}