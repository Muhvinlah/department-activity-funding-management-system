<?php
// database/seeders/RoleSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['role_def' => 'mahasiswa'],
            ['role_def' => 'dosen'],
            ['role_def' => 'sekretaris jurusan'],
            ['role_def' => 'admin jurusan'],
            ['role_def' => 'ketua jurusan'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['role_def' => $role['role_def']], // Kondisi pencarian
                ['role_def' => $role['role_def']]  // Data yang akan diinsert/update
            );
        }
    }
}
