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

        // Buat TOR
        $tor1 = Tor::create([
            'activity_name' => 'Workshop Pemrograman Web',
            'activity_background' => 'Meningkatkan skill mahasiswa di bidang web development',
            'activity_purpose' => 'Memberikan pelatihan web development',
            'participant' => '50 mahasiswa',
            'start_date' => '2025-02-01',
            'end_date' => '2025-02-03',
            'budget_submitted' => 5000000,
            'pic' => 'John Doe',
            'status' => 'approved_by_head',
            'current_stage' => 'approved_by_head',
            'category_id' => $akademik->category_id,
            'user_id' => $mahasiswa->user_id,
            'budget_id' => $budget2025->budget_id,
        ]);

        $tor2 = Tor::create([
            'activity_name' => 'Seminar Teknologi AI',
            'activity_background' => 'Mengenalkan AI kepada mahasiswa',
            'activity_purpose' => 'Memberikan wawasan tentang AI',
            'participant' => '100 mahasiswa',
            'start_date' => '2025-03-15',
            'end_date' => '2025-03-15',
            'budget_submitted' => 8000000,
            'pic' => 'Jane Doe',
            'status' => 'under_review',
            'current_stage' => 'under_review',
            'category_id' => $akademik->category_id,
            'user_id' => $mahasiswa->user_id,
            'budget_id' => $budget2025->budget_id,
        ]);

        $tor3 = Tor::create([
            'activity_name' => 'Lomba Coding Internal',
            'activity_background' => 'Kompetisi coding antar mahasiswa',
            'activity_purpose' => 'Meningkatkan kemampuan problem solving',
            'participant' => '30 mahasiswa',
            'start_date' => '2025-04-10',
            'end_date' => '2025-04-12',
            'budget_submitted' => 3000000,
            'pic' => 'John Doe',
            'status' => 'under_review',
            'current_stage' => 'under_review',
            'category_id' => $kemahasiswaan->category_id,
            'user_id' => $mahasiswa->user_id,
            'budget_id' => $budget2025->budget_id,
        ]);

        // Buat LPJ untuk tor1
        $lpj1 = Lpj::create([
            'tor_id' => $tor1->tor_id,
            'user_id' => $mahasiswa->user_id,
            'activity_result' => 'Workshop Pemrograman Web berjalan dengan sangat baik. Semua 50 peserta hadir dan aktif mengikuti pembelajaran. Materi mencakup HTML, CSS, JavaScript, dan framework modern.',
            'activity_evaluation' => 'Peserta sangat antusias dan memberikan feedback positif. Akan dilanjutkan dengan program advanced level.',
            'actual_date' => '2025-02-03',
            'budget_used' => 4500000,
            'status' => 'approved_by_head',
            'current_stage' => 'approved_by_head',
        ]);

        // Buat LPJ untuk tor2
        $lpj2 = Lpj::create([
            'tor_id' => $tor2->tor_id,
            'user_id' => $mahasiswa->user_id,
            'activity_result' => 'Seminar Teknologi AI dihadiri oleh 95 mahasiswa dari berbagai jurusan. Pembicara dari industri tech memberikan insights mengenai aplikasi AI di dunia nyata.',
            'activity_evaluation' => 'Peserta sangat tertarik dengan topik AI dan machine learning. Banyak pertanyaan interaktif dari audiens.',
            'actual_date' => '2025-03-15',
            'budget_used' => 7800000,
            'status' => 'under_review',
            'current_stage' => 'under_review',
        ]);

        // Buat LPJ untuk tor3
        $lpj3 = Lpj::create([
            'tor_id' => $tor3->tor_id,
            'user_id' => $mahasiswa->user_id,
            'activity_result' => 'Lomba Coding Internal berhasil diikuti oleh 28 peserta dari 5 jurusan. Terdapat 3 pemenang dengan hadiah masing-masing. Tingkat kesulitan soal sesuai dengan ekspektasi.',
            'activity_evaluation' => 'Event berjalan sesuai jadwal dan peserta memberikan respons positif. Ada saran untuk menambah kategori lomba di event berikutnya.',
            'actual_date' => '2025-04-12',
            'budget_used' => 2800000,
            'status' => 'under_review',
            'current_stage' => 'under_review',
        ]);
    }
}
