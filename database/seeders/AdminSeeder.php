<?php

namespace RMS\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use RMS\Core\Models\Admin;
use RMS\Core\Enums\Role;

class AdminSeeder extends Seeder
{
    public function run()
    {
        Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'mobile' => '09123456789',
            'password' => bcrypt('password123'),
            'role' => Role::SUPER_ADMIN,
            'active' => true,
        ]);
    }
}
