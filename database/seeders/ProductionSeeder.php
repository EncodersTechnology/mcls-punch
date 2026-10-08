<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * A first-run seed for a real deployment: the checklist task catalogue the application
 * needs in order to function, and exactly one administrator account. No sites, residents,
 * staff or logs -- those are entered by the organisation.
 *
 * The admin credentials come from the environment so they are never committed:
 *   ADMIN_EMAIL, ADMIN_NAME, ADMIN_PASSWORD
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'superadmin@multiculturalcls.org');
        $name = env('ADMIN_NAME', 'Super Admin');
        $password = env('ADMIN_PASSWORD');

        if (blank($password)) {
            $this->command->error('ADMIN_PASSWORD is not set -- refusing to seed an account with a guessable password.');

            return;
        }

        // Reference data: the DAY/NIGHT shift task definitions. Without these the site log
        // form and the weekly checklists have nothing to show, so this is not sample data.
        if (DB::table('xwalk_site_checklist_type')->count() === 0) {
            $this->call(XwalkSeeder::class);
        }

        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'usertype' => 'admin',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'manager_id' => null,
            ]
        );

        $this->command->info('Administrator ready: ' . $admin->email);
        $this->command->info('Checklist tasks: ' . DB::table('xwalk_site_checklist_type')->count());
        $this->command->warn('Sign in and change this password immediately.');
    }
}
