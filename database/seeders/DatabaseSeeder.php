<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'site.admin@multiculturalcls.org',
            'password' => Hash::make('Simcoe@123')
        ]);

        // 1. Seed 5-6 sites
        $sites = [];
        for ($i = 1; $i <= 6; $i++) {
            $siteId = DB::table('sites')->insertGetId([
                'name' => 'Site ' . $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $sites[] = $siteId;
        }

        // 2. Seed 8-9 residents and map them to random sites
        $residents = [];
        for ($i = 1; $i <= 9; $i++) {
            $siteId = $sites[array_rand($sites)];

            $residentId = DB::table('residents')->insertGetId([
                'name' => 'Resident ' . $i,
                'site_id' => $siteId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $residents[] = [
                'id' => $residentId,
                'site_id' => $siteId,
            ];
        }

        // 3. Seed 50 form_data entries
        $employeeTypes = ['mcls', 'agency'];
        $shifts = ['morning', 'night'];

        $formData = [];

        for ($i = 0; $i < 50; $i++) {
            $resident = $residents[array_rand($residents)];
            $employeeType = $employeeTypes[array_rand($employeeTypes)];

            $formData[] = [
                'employee_type' => $employeeType,
                'mcls_name' => 'MCLS Name ' . Str::random(5),
                'mcls_email' => 'mcls' . rand(100, 999) . '@example.com',
                'agency_name' => 'Agency ' . Str::random(4),
                'agency_employee_name' => 'Agency Emp ' . Str::random(5),
                'site_id' => $resident['site_id'],
                'resident_id' => $resident['id'],
                'shift' => $shifts[array_rand($shifts)],
                'log_date' => Carbon::now()->subDays(rand(0, 30))->toDateString(),
                'log_time' => Carbon::createFromTime(rand(7, 22), rand(0, 59))->toTimeString(),
                'adls' => 'ADL content ' . Str::random(10),
                'medical' => 'Medical notes ' . Str::random(10),
                'behavior' => 'Behavior details ' . Str::random(10),
                'activities' => 'Activities ' . Str::random(10),
                'nutrition' => 'Nutrition ' . Str::random(10),
                'sleep' => 'Sleep details ' . Str::random(10),
                'notes' => 'Notes ' . Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('form_data')->insert($formData);
    }
}
