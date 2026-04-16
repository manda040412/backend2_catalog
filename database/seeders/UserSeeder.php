<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin default
        DB::table('users')->updateOrInsert(
            ['email' => 'superadmin@timurraya.com'],
            [
                'role_id'     => 'ROLE-001',
                'name'        => 'Super Admin',
                'email'       => 'superadmin@timurraya.com',
                'password'    => Hash::make('SuperAdmin@123'),
                'is_approved' => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );

        // Admin default
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@timurraya.com'],
            [
                'role_id'     => 'ROLE-002',
                'name'        => 'Admin Timur Raya',
                'email'       => 'admin@timurraya.com',
                'password'    => Hash::make('Admin@123'),
                'is_approved' => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'admintrad@timurraya.com'],
            [
                'role_id'     => 'ROLE-002',
                'name'        => 'Admin Timur Raya',
                'email'       => 'admintrad@timurraya.com',
                'password'    => Hash::make('Admin@123'),
                'is_approved' => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );
    }
}
