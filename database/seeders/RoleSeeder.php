<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['id_role' => 'ROLE-001', 'id_role_code' => 'SADM', 'role_name' => 'Super Admin',  'description' => 'Akses penuh ke semua fitur sistem'],
            ['id_role' => 'ROLE-002', 'id_role_code' => 'ADM',  'role_name' => 'Admin',         'description' => 'Approve user dan kelola produk'],
            ['id_role' => 'ROLE-003', 'id_role_code' => 'INT',  'role_name' => 'Internal',      'description' => 'Karyawan Timur Raya - akses data lengkap'],
            ['id_role' => 'ROLE-004', 'id_role_code' => 'EXT',  'role_name' => 'External',      'description' => 'Pengguna umum - data terbatas'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(['id_role' => $role['id_role']], $role);
        }
    }
}
