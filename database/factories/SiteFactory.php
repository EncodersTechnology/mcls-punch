<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Site>
 */
class SiteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Site ' . fake()->unique()->numberBetween(1, 100000),
            'shift_1' => 'morning',
            'shift_2' => 'night',
        ];
    }
}
