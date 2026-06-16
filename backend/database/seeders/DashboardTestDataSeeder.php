<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Tor;
use App\Models\Lpj;
use App\Models\AnnualBudget;
use App\Models\ActivityCategory;
use App\Models\StatusHist;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DashboardTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates diverse test data for dashboard visualization
     */
    public function run(): void
    {
        // Get roles
        $mahasiswaRole = Role::where('role_def', 'mahasiswa')->first();
        $dosenRole = Role::where('role_def', 'dosen')->first();
        $sekretarisRole = Role::where('role_def', 'sekretaris jurusan')->first();
        $adminRole = Role::where('role_def', 'admin jurusan')->first();
        $ketuaRole = Role::where('role_def', 'ketua jurusan')->first();

        // Get categories
        $categories = ActivityCategory::all();

        // Create annual budgets for current and previous year
        $currentYear = date('Y');
        $previousYear = $currentYear - 1;

        $currentBudget = AnnualBudget::firstOrCreate(
            ['tahun' => $currentYear],
            ['budget' => 500000000] // 500 million
        );

        $previousBudget = AnnualBudget::firstOrCreate(
            ['tahun' => $previousYear],
            ['budget' => 450000000] // 450 million
        );

        // Create test users if they don't exist
        $students = [];
        for ($i = 1; $i <= 5; $i++) {
            $students[] = User::firstOrCreate(
                ['user_id' => "2341760{$i}"],
                [
                    'full_name' => "Mahasiswa Test {$i}",
                    'email' => "mahasiswa{$i}@test.com",
                    'password' => Hash::make('password'),
                    'role_id' => $mahasiswaRole->role_id
                ]
            );
        }

        $dosen = User::firstOrCreate(
            ['user_id' => 'D001'],
            [
                'full_name' => 'Dosen Test',
                'email' => 'dosen@test.com',
                'password' => Hash::make('password'),
                'role_id' => $dosenRole->role_id
            ]
        );

        $sekretaris = User::firstOrCreate(
            ['user_id' => 'S001'],
            [
                'full_name' => 'Sekretaris Test',
                'email' => 'sekretaris@test.com',
                'password' => Hash::make('password'),
                'role_id' => $sekretarisRole->role_id
            ]
        );

        $admin = User::firstOrCreate(
            ['user_id' => 'A001'],
            [
                'full_name' => 'Admin Test',
                'email' => 'admin@test.com',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->role_id
            ]
        );

        $ketua = User::firstOrCreate(
            ['user_id' => 'K001'],
            [
                'full_name' => 'Ketua Jurusan Test',
                'email' => 'ketua@test.com',
                'password' => Hash::make('password'),
                'role_id' => $ketuaRole->role_id
            ]
        );

        // TOR statuses for variety
        $torStatuses = [
            'submitted',
            'needs_revision',
            'reviewed_by_secretary',
            'verified_by_admin',
            'approved_by_head',
            'rejected'
        ];

        // LPJ statuses for variety
        $lpjStatuses = [
            'submitted',
            'needs_revision',
            'reviewed_by_secretary',
            'verified_by_admin',
            'approved_by_head',
            'rejected'
        ];

        // Activity names
        $activityNames = [
            'Seminar Nasional Teknologi Informasi',
            'Workshop Machine Learning',
            'Pelatihan Web Development',
            'Lomba Programming Competition',
            'Study Excursie ke Perusahaan IT',
            'Webinar Cyber Security',
            'Hackathon 48 Jam',
            'Training Data Science',
            'Kompetisi UI/UX Design',
            'Kunjungan Industri',
            'Seminar Artificial Intelligence',
            'Workshop Mobile App Development',
            'Pelatihan Cloud Computing',
            'Lomba Game Development',
            'Seminar Blockchain Technology'
        ];

        echo "Creating TORs and LPJs with variety...\n";

        // Create 50 TORs across different months and statuses
        for ($i = 0; $i < 50; $i++) {
            // Random month in current year (spread across the year)
            $month = rand(1, 12);
            $day = rand(1, 28);
            $createdAt = Carbon::create($currentYear, $month, $day);

            // Random student
            $student = $students[array_rand($students)];

            // Random category
            $category = $categories->random();

            // Random budget between 5 million and 50 million
            $budgetSubmitted = rand(5000000, 50000000);

            // Random status
            $status = $torStatuses[array_rand($torStatuses)];

            // Random activity name
            $activityName = $activityNames[array_rand($activityNames)] . " " . $currentYear;

            // Start and end dates
            $startDate = $createdAt->copy()->addDays(rand(30, 90));
            $endDate = $startDate->copy()->addDays(rand(1, 3));

            $tor = Tor::create([
                'user_id' => $student->user_id,
                'budget_id' => $currentBudget->budget_id,
                'category_id' => $category->category_id,
                'activity_name' => $activityName,
                'activity_background' => 'Background for ' . $activityName,
                'activity_purpose' => 'Purpose: Meningkatkan kompetensi mahasiswa',
                'participant' => rand(50, 200),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'budget_submitted' => $budgetSubmitted,
                'pic' => 'PIC ' . $student->full_name,
                'status' => $status,
                'current_stage' => $status,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Create status history for the TOR
            $this->createTorStatusHistory($tor, $createdAt, $sekretaris, $admin, $ketua);

            // If TOR is approved, create LPJ with random status
            if ($status === 'approved_by_head' && rand(0, 100) > 30) { // 70% chance
                $lpjCreatedAt = $createdAt->copy()->addDays(rand(60, 120));
                $lpjStatus = $lpjStatuses[array_rand($lpjStatuses)];

                // Budget used is between 80% and 100% of submitted budget
                $budgetUsed = $budgetSubmitted * (rand(80, 100) / 100);

                $lpj = Lpj::create([
                    'tor_id' => $tor->tor_id,
                    'user_id' => $student->user_id,
                    'activity_result' => 'Kegiatan berjalan dengan lancar dan sukses',
                    'activity_evaluation' => 'Evaluasi: Peserta sangat antusias dan mendapat manfaat',
                    'actual_date' => $startDate,
                    'budget_used' => $budgetUsed,
                    'status' => $lpjStatus,
                    'current_stage' => $lpjStatus,
                    'created_at' => $lpjCreatedAt,
                    'updated_at' => $lpjCreatedAt,
                ]);

                // Create status history for the LPJ
                $this->createLpjStatusHistory($lpj, $lpjCreatedAt, $sekretaris, $admin, $ketua);
            }
        }

        // Create some TORs from previous year
        for ($i = 0; $i < 20; $i++) {
            $month = rand(1, 12);
            $day = rand(1, 28);
            $createdAt = Carbon::create($previousYear, $month, $day);

            $student = $students[array_rand($students)];
            $category = $categories->random();
            $budgetSubmitted = rand(5000000, 40000000);

            $startDate = $createdAt->copy()->addDays(rand(30, 90));
            $endDate = $startDate->copy()->addDays(rand(1, 3));

            $tor = Tor::create([
                'user_id' => $student->user_id,
                'budget_id' => $previousBudget->budget_id,
                'category_id' => $category->category_id,
                'activity_name' => $activityNames[array_rand($activityNames)] . " " . $previousYear,
                'activity_background' => 'Background for activity ' . $i,
                'activity_purpose' => 'Purpose: Meningkatkan kompetensi mahasiswa',
                'participant' => rand(50, 150),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'budget_submitted' => $budgetSubmitted,
                'pic' => 'PIC ' . $student->full_name,
                'status' => 'approved_by_head', // All previous year TORs are approved
                'current_stage' => 'approved_by_head',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $this->createTorStatusHistory($tor, $createdAt, $sekretaris, $admin, $ketua);

            // Create LPJ for previous year TORs (90% have LPJs)
            if (rand(0, 100) > 10) {
                $lpjCreatedAt = $createdAt->copy()->addDays(rand(60, 120));
                $budgetUsed = $budgetSubmitted * (rand(85, 100) / 100);

                $lpj = Lpj::create([
                    'tor_id' => $tor->tor_id,
                    'user_id' => $student->user_id,
                    'activity_result' => 'Kegiatan berjalan dengan lancar',
                    'activity_evaluation' => 'Evaluasi: Target tercapai dengan baik',
                    'actual_date' => $startDate,
                    'budget_used' => $budgetUsed,
                    'status' => 'approved_by_head', // Most previous year LPJs are approved
                    'current_stage' => 'approved_by_head',
                    'created_at' => $lpjCreatedAt,
                    'updated_at' => $lpjCreatedAt,
                ]);

                $this->createLpjStatusHistory($lpj, $lpjCreatedAt, $sekretaris, $admin, $ketua);
            }
        }

        echo "✓ Created 50 current year TORs with various statuses\n";
        echo "✓ Created 20 previous year TORs (all approved)\n";
        echo "✓ Created corresponding LPJs with various statuses\n";
        echo "✓ Dashboard test data seeding completed!\n";
    }

    /**
     * Create status history for TOR based on its current status
     */
    private function createTorStatusHistory($tor, $createdAt, $sekretaris, $admin, $ketua)
    {
        $statusFlow = [
            'submitted' => ['submitted'],
            'needs_revision' => ['submitted', 'needs_revision'],
            'reviewed_by_secretary' => ['submitted', 'reviewed_by_secretary'],
            'verified_by_admin' => ['submitted', 'reviewed_by_secretary', 'verified_by_admin'],
            'approved_by_head' => ['submitted', 'reviewed_by_secretary', 'verified_by_admin', 'approved_by_head'],
            'rejected' => ['submitted', 'rejected']
        ];

        $statuses = $statusFlow[$tor->status];
        $currentTime = $createdAt->copy();

        foreach ($statuses as $index => $status) {
            $userId = match($status) {
                'submitted' => $tor->user_id,
                'needs_revision', 'reviewed_by_secretary' => $sekretaris->user_id,
                'verified_by_admin' => $admin->user_id,
                'approved_by_head', 'rejected' => $ketua->user_id,
                default => $tor->user_id
            };

            StatusHist::create([
                'tor_id' => $tor->tor_id,
                'user_id' => $userId,
                'status' => $status,
                'catatan' => $index === 0 ? null : 'Processed by system',
                'timestamp_aksi' => $currentTime,
            ]);

            // Add random days between status changes (1-5 days)
            $currentTime = $currentTime->copy()->addDays(rand(1, 5));
        }
    }

    /**
     * Create status history for LPJ based on its current status
     */
    private function createLpjStatusHistory($lpj, $createdAt, $sekretaris, $admin, $ketua)
    {
        $statusFlow = [
            'submitted' => ['submitted'],
            'needs_revision' => ['submitted', 'needs_revision'],
            'reviewed_by_secretary' => ['submitted', 'reviewed_by_secretary'],
            'verified_by_admin' => ['submitted', 'reviewed_by_secretary', 'verified_by_admin'],
            'approved_by_head' => ['submitted', 'reviewed_by_secretary', 'verified_by_admin', 'approved_by_head'],
            'rejected' => ['submitted', 'rejected']
        ];

        $statuses = $statusFlow[$lpj->status];
        $currentTime = $createdAt->copy();

        foreach ($statuses as $index => $status) {
            $userId = match($status) {
                'submitted' => $lpj->user_id,
                'needs_revision', 'reviewed_by_secretary' => $sekretaris->user_id,
                'verified_by_admin' => $admin->user_id,
                'approved_by_head', 'rejected' => $ketua->user_id,
                default => $lpj->user_id
            };

            StatusHist::create([
                'lpj_id' => $lpj->lpj_id,
                'user_id' => $userId,
                'status' => $status,
                'catatan' => $index === 0 ? null : 'Processed by system',
                'timestamp_aksi' => $currentTime,
            ]);

            $currentTime = $currentTime->copy()->addDays(rand(1, 5));
        }
    }
}
