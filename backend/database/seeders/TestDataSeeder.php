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

        // Create 10 approved LPJs (with their own approved TORs)
        for ($i = 1; $i <= 30; $i++) {
            // Create Approved TOR
            $tor = Tor::create([
                'user_id' => $mahasiswa->user_id,
                'budget_id' => $budget2025->budget_id,
                'category_id' => $akademik->category_id, // Alternating categories could be nice but simple is fine
                'activity_name' => "Approved Activity $i",
                'activity_background' => "Background for activity $i",
                'activity_purpose' => "Purpose for activity $i",
                'participant' => 50 + $i,
                'start_date' => now()->addDays($i),
                'end_date' => now()->addDays($i + 2),
                'budget_submitted' => 100000 * $i,
                'pic' => "PIC $i",
                'status' => 'approved_by_head',
                'current_stage' => 'approved_by_head',
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(5),
            ]);

            // Create Approved LPJ for this TOR
            $lpj = Lpj::create([
                'tor_id' => $tor->tor_id,
                'user_id' => $mahasiswa->user_id,
                'activity_result' => "Result for activity $i",
                'activity_evaluation' => "Evaluation for activity $i",
                'actual_date' => now()->addDays($i),
                'budget_used' => 90000 * $i, // Slightly less than submitted
                'status' => 'approved_by_head',
                'current_stage' => 'approved_by_head',
            ]);
        }

        // Create 3 TORs that haven't been approved yet
        // Case 1: Submitted (Waiting for Secretary)
        Tor::create([
            'user_id' => $mahasiswa->user_id,
            'budget_id' => $budget2025->budget_id,
            'category_id' => $kemahasiswaan->category_id,
            'activity_name' => "Pending Activity (Submitted)",
            'activity_background' => "Background pending 1",
            'activity_purpose' => "Purpose pending 1",
            'participant' => 30,
            'start_date' => now()->addDays(20),
            'end_date' => now()->addDays(22),
            'budget_submitted' => 5000000,
            'pic' => "PIC Pending 1",
            'status' => 'submitted',
            'current_stage' => 'submitted',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Case 2: Reviewed by Secretary (Waiting for Admin)
        Tor::create([
            'user_id' => $mahasiswa->user_id,
            'budget_id' => $budget2025->budget_id,
            'category_id' => $akademik->category_id,
            'activity_name' => "Pending Activity (Reviewed by Secretary)",
            'activity_background' => "Background pending 2",
            'activity_purpose' => "Purpose pending 2",
            'participant' => 40,
            'start_date' => now()->addDays(25),
            'end_date' => now()->addDays(27),
            'budget_submitted' => 7500000,
            'pic' => "PIC Pending 2",
            'status' => 'reviewed_by_secretary',
            'current_stage' => 'reviewed_by_secretary',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ]);

        // Case 3: Needs Revision (by Admin)
        Tor::create([
            'user_id' => $mahasiswa->user_id,
            'budget_id' => $budget2025->budget_id,
            'category_id' => $kemahasiswaan->category_id,
            'activity_name' => "Pending Activity (Needs Revision by Admin)",
            'activity_background' => "Background pending 3",
            'activity_purpose' => "Purpose pending 3",
            'participant' => 25,
            'start_date' => now()->addDays(30),
            'end_date' => now()->addDays(32),
            'budget_submitted' => 3000000,
            'pic' => "PIC Pending 3",
            'status' => 'needs_revision_by_admin',
            'current_stage' => 'needs_revision_by_admin',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);
    }
}
