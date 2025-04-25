<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
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
            'name' => 'Test Admin User',
            'email' => 'site.admin@multiculturalcls.org',
            'password' => Hash::make('Admin@123'),
            'usertype' => 'admin'
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Test Employee User',
            'email' => 'employee@multiculturalcls.org',
            'password' => Hash::make('Employee@123'),
            'usertype' => 'employee'
        ]);

        $this->call([
            XwalkSeeder::class,
        ]);
    }
}
