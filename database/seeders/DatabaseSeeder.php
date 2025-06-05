<?php

namespace Database\Seeders;

use App\Models\User;
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

        // Create Admin and Siteadmin users (not assigned to sites)
        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@multiculturalcls.org',
            'usertype' => 'admin',
            'password' => Hash::make('Admin@123'),
        ]);

        User::factory()->create([
            'name' => 'Site Admin',
            'email' => 'siteadmin@multiculturalcls.org',
            'usertype' => 'siteadmin',
            'password' => Hash::make('Admin@123'),
        ]);

        // Create Directors
        $directors = User::factory()->count(2)->create([
            'usertype' => 'director',
            'password' => Hash::make('Password@123')
        ]);

        // Create Managers and assign them to Directors
        $managers = collect();
        foreach ($directors as $director) {
            $managers = $managers->merge(
                User::factory()->count(2)->create([
                    'usertype' => 'manager',
                    'manager_id' => $director->id,
                    'password' => Hash::make('Password@123')
                ])
            );
        }

        // Create Supervisors and assign to Managers
        $supervisors = collect();
        foreach ($managers as $manager) {
            $supervisors = $supervisors->merge(
                User::factory()->count(2)->create([
                    'usertype' => 'supervisor',
                    'manager_id' => $manager->id,
                    'password' => Hash::make('Password@123')
                ])
            );
        }

        // Assign supervisors to random sites (1-2 sites)
        foreach ($supervisors as $supervisor) {
            $siteIds = collect(range(1, 6))->random(rand(1, 2));
            foreach ($siteIds as $siteId) {
                $supervisor->assignSite($siteId);
            }
        }

        // Create Employees and assign them to exactly one site
        $employees = User::factory()->count(10)->create([
            'usertype' => 'employee',
            'password' => Hash::make('Password@123')
        ]);

        foreach ($employees as $employee) {
            $siteId = collect(range(1, 6))->random(1)[0]; // Select exactly one site
            $employee->assignSite($siteId);
        }

        $this->call([
            XwalkSeeder::class,
        ]);

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