<?php

namespace RMS\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use RMS\Core\Models\Admin;
use RMS\Core\Enums\Role;

/**
 * Seeder for creating a default admin user.
 */
class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        if (!Admin::where('email', 'admin@example.com')->exists()) {
            Admin::create([
                'name' => 'Super Admin',
                'email' => 'admin@example.com',
                'mobile' => '09123456789',
                'password' => bcrypt('password123'),
                'role' => Role::SUPER_ADMIN->value,
                'active' => true,
            ]);
        }
    }
}
