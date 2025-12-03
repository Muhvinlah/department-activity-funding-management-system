<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\ActivityCategory;
use App\Models\AnnualBudget;
use App\Models\Tor;
use App\Models\Lpj;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        // Buat users
        $mahasiswaRole = Role::where('role_def', 'mahasiswa')->first();
        $dosenRole = Role::where('role_def', 'dosen')->first();
        $sekretarisRole = Role::where('role_def', 'sekretaris jurusan')->first();
        $adminRole = Role::where('role_def', 'admin jurusan')->first();
        $ketuaRole = Role::where('role_def', 'ketua jurusan')->first();

        $mahasiswa = User::create([
            'user_id' => '1234567890',
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'role_id' => $mahasiswaRole->role_id,
        ]);

        $mahasiswa = User::create([
            'user_id' => '2207412014',
            'full_name' => 'Muhammad Ervin Fadillah',
            'email' => 'muhammad.ervin.fadillah.tik22@mhsw.pnj.ac.id',
            'password' => bcrypt('password321'),
            'role_id' => $mahasiswaRole->role_id,
        ]);

        $sekretaris = User::create([
            'user_id' => '1234567891',
            'full_name' => 'Jane Secretary',
            'email' => 'secretary@example.com',
            'password' => bcrypt('password123'),
            'role_id' => $sekretarisRole->role_id,
        ]);

        $admin = User::create([
            'user_id' => '1234567892',
            'full_name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role_id' => $adminRole->role_id,
        ]);

        $ketua = User::create([
            'user_id' => '1234567893',
            'full_name' => 'Head Department',
            'email' => 'head@example.com',
            'password' => bcrypt('password123'),
            'role_id' => $ketuaRole->role_id,
        ]);

        // Buat annual budget
        $budget2024 = AnnualBudget::create([
            'tahun' => '2024',
            'budget' => 100000000,
        ]);

        $budget2025 = AnnualBudget::create([
            'tahun' => '2025',
            'budget' => 150000000,
        ]);

        // Get categories
        $akademik = ActivityCategory::where('category_def', 'Akademik')->first();
        $kemahasiswaan = ActivityCategory::where('category_def', 'Kemahasiswaan')->first();
    }
}
