<?php

namespace Database\Factories;

use App\Models\Resident;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FormData>
 */
class FormDataFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_type' => 'mcls',
            'mcls_name' => fake()->name(),
            'mcls_email' => fake()->unique()->userName() . '@multiculturalcls.org',
            'site_id' => Site::factory(),
            'resident_id' => Resident::factory(),
            'shift' => 'morning',
            'log_date' => now()->toDateString(),
            'log_time' => now()->format('H:i:s'),
            'adls' => 'ADL notes',
            'medical' => 'Medical notes',
            'behavior' => 'Behaviour notes',
        ];
    }
}
