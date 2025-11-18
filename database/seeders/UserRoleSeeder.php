<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample users with different roles
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@kmg.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'John Manager',
                'email' => 'manager@kmg.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Sarah QC Lead',
                'email' => 'qc@kmg.com',
                'password' => Hash::make('password'),
                'role' => 'qc_team',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Mike Operator',
                'email' => 'operator@kmg.com',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Lisa Viewer',
                'email' => 'viewer@kmg.com',
                'password' => Hash::make('password'),
                'role' => 'viewer',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}