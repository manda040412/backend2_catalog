<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,        // 1. roles (FK untuk users)
            UserSeeder::class,        // 2. default accounts
            CategorySeeder::class,    // 3. kategori (FK untuk products)
            ProductSeeder555::class,  // 4. 389 produk + 388 OEM crosses
            MatchCarSeeder555::class, // 5. 3826 data kesesuaian kendaraan
        ]);

        $this->command->info('');
        $this->command->info('✅ Semua seeder berhasil!');
        $this->command->table(
            ['Seeder', 'Data'],
            [
                ['RoleSeeder',        '4 roles: SADM, ADM, INT, EXT'],
                ['UserSeeder',        '2 default users'],
                ['CategorySeeder',    '9 kategori'],
                ['ProductSeeder555',  '389 produk + 388 OEM crosses'],
                ['MatchCarSeeder555', '3826 match_cars'],
            ]
        );
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info('  superadmin@timurraya.com  /  SuperAdmin@123');
        $this->command->info('  admin@timurraya.com       /  Admin@12345');
    }
}
