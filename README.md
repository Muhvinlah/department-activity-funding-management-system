# 1.1 Judul Proyek

Department Activity Funding Management System (DAFMS)

# 1.2 Deskripsi Singkat

Sistem ini adalah aplikasi berbasis web yang dirancang untuk mendigitalkan dan menyederhanakan proses pengelolaan dana kegiatan di jurusan (Department Activity Funding). Tujuan utamanya adalah untuk memfasilitasi pengajuan Term of Reference (TOR) dan Laporan Pertanggungjawaban (LPJ) secara online, memberikan transparansi penggunaan anggaran, serta mempermudah proses review dan persetujuan berjenjang (Sekretaris, Admin, Ketua Jurusan).

# 1.3 Fitur Utama

### Alur Aplikasi

1. **Pengajuan TOR**: Dosen/Mahasiswa mengajukan proposal kegiatan (TOR) lengkap dengan anggaran.
2. **Review TOR**: Proses persetujuan berjenjang:
   - Review Sekretaris -> Verifikasi Admin -> Persetujuan Ketua Jurusan.
3. **Pelaksanaan Kegiatan**: Setelah TOR disetujui, kegiatan dilaksanakan.
4. **Pengajuan LPJ**: Reviewer mengajukan laporan (LPJ) berdasarkan TOR yang disetujui (data terisi otomatis).
5. **Review LPJ**: Proses validasi laporan keuangan dan hasil kegiatan (alur sama dengan TOR).
6. **Pencairan & Arsip**: Anggaran terupdate otomatis di dashboard.

### Fitur

- **Dashboard Interaktif**: Visualisasi real-time penggunaan anggaran, status pengajuan, dan perbandingan budget vs realisasi per tahun.
- **Manajemen TOR (Term of Reference)**:
  - Form pengajuan dengan validasi otomatis.
  - Upload dokumen pendukung (RAB, dll).
  - Notifikasi real-time untuk status review.
- **Manajemen LPJ (Laporan Pertanggungjawaban)**:
  - Pre-fill data otomatis dari TOR yang disetujui.
  - Perhitungan otomatis selisih anggaran (budget submitted vs used).
  - Upload bukti transaksi dan dokumentasi.
- **Sistem Approval Berjenjang (Role-Based)**:
  - **Mahasiswa**: Mengajukan kegiatan & revisi.
  - **Dosen/Staff**: Mengajukan kegiatan & revisi.
  - **Sekretaris Jurusan**: Review kelengkapan administrasi.
  - **Admin Jurusan**: Verifikasi anggaran dan data teknis.
  - **Ketua Jurusan**: Persetujuan akhir (Final Approval).
- **Notifikasi Sistem**:
  - Notifikasi in-app dan Email otomatis ke pihak terkait saat ada perubahan status.
- **Manajemen Anggaran Tahunan**: Kelola kuota anggaran jurusan per tahun.

# 1.4 Tech Stack

### Frontend

- **Framework**: Vue.js 3 (Composition API)
- **Language**: TypeScript
- **Styling**: Tailwind CSS v4
- **State Management**: Pinia
- **HTTP Client**: Axios
- **Charts**: Chart.js / Vue-Chartjs
- **Build Tool**: Vite

### Backend

- **Framework**: Laravel 12
- **Language**: PHP 8.2+
- **Authentication**: JWT (JSON Web Token)
- **Architecture**: RESTful API
- **Database**: PostgreSQL / MySQL (Support keduanya)
- **Queue**: Database Queue (untuk pengiriman email background)

# 1.5 Instalasi

### Persyaratan Sistem

- Node.js ^20.19.0 || >=22.12.0
- PHP >= 8.2
- Composer
- Database (PostgreSQL/MySQL)

### Langkah Instalasi

**1. Clone Repository**

```bash
git clone [repository_url]
cd department-activity-funding-management
```

**2. Instalasi Backend (Laravel)**

```bash
cd backend
composer install
cp .env.example .env
# Konfigurasi database di file .env
php artisan key:generate
php artisan migrate --seed # Jalankan migrasi dan seeder awal
php artisan jwt:secret
php artisan storage:link
```

**3. Instalasi Frontend (Vue.js)**

```bash
cd ../frontend
npm install
```

# 1.6 Cara Menjalankan

**1. Jalankan Backend**
Buka terminal baru di folder `backend`:

```bash
php artisan serve
```

_Backend akan berjalan di: http://localhost:8000_

**2. Jalankan Queue Worker (Untuk Email)**
Buka terminal baru di folder `backend`:

```bash
php artisan queue:work
```

**3. Jalankan Frontend**
Buka terminal baru di folder `frontend`:

```bash
npm run dev
```

_Frontend akan berjalan di: http://localhost:5173_

# 1.7 Struktur Folder

```
/department-activity-funding-management
├── /backend                          # Laravel API
│   ├── /app
│   │   ├── /Events                   # Event classes (LpjApproved)
│   │   ├── /Http
│   │   │   ├── /Controllers/Api      # API Controllers
│   │   │   │   ├── AuthController          # Authentication (login, register, logout)
│   │   │   │   ├── TorController           # TOR CRUD & approval flow
│   │   │   │   ├── LpjController           # LPJ CRUD, approval & pre-fill
│   │   │   │   ├── DashboardController     # Dashboard summary, charts & cache
│   │   │   │   ├── AnnualBudgetController  # Annual budget management (admin)
│   │   │   │   ├── AttachmentController    # File upload, download & delete
│   │   │   │   ├── ActivityCategoryController # Activity category lookup
│   │   │   │   ├── UserController          # User management (admin)
│   │   │   │   ├── accountController       # Account profile update
│   │   │   │   └── RiwayatStatusController # Status history
│   │   │   └── /Middleware
│   │   │       └── CheckRole               # Role-based access control
│   │   ├── /Listeners
│   │   │   └── UpdateDashboardStatistics   # Dashboard cache listener
│   │   ├── /Models                     # Eloquent Models
│   │   │   ├── User, Role, Tor, Lpj
│   │   │   ├── AnnualBudget, ActivityCategory
│   │   │   ├── Attachment, StatusHist
│   │   │   └── TorApprov, LpjApprov
│   │   └── /Notifications              # Email & DB Notifications
│   │       ├── TorStatusChanged
│   │       └── LpjStatusChanged
│   ├── /database
│   │   ├── /migrations                 # Database schema (15 migrations)
│   │   └── /seeders                    # RoleSeeder, ActivityCategorySeeder, TestDataSeeder
│   └── /routes/api.php                 # API endpoints
│
└── /frontend                         # Vue.js Application
    ├── /src
    │   ├── /assets                     # Static assets
    │   ├── /components                 # Reusable Components
    │   │   ├── AnnualBudgetModal.vue   # Annual budget CRUD modal
    │   │   └── fileUpload.vue          # File upload component
    │   ├── /constants
    │   │   └── userRoles.ts            # Role constants (mahasiswa, dosen, etc.)
    │   ├── /layouts
    │   │   └── mainLayout.vue          # Main app layout with sidebar
    │   ├── /services                   # API Services
    │   │   ├── api.ts                  # Axios instance config
    │   │   ├── authService.ts          # Authentication service
    │   │   ├── torService.ts           # TOR API service
    │   │   ├── lpjService.ts           # LPJ API service
    │   │   ├── dashboardService.ts     # Dashboard API service
    │   │   └── annualBudgetService.ts  # Annual budget API service
    │   ├── /stores
    │   │   └── authStore.ts            # Pinia auth store
    │   ├── /types
    │   │   └── approval.ts             # TypeScript type definitions
    │   ├── /views                      # Page Views
    │   │   ├── login.vue               # Login page
    │   │   ├── home.vue                # Home page (non-admin users)
    │   │   ├── dashboard.vue           # Dashboard (admin users)
    │   │   ├── submitTOR.vue           # TOR submission form
    │   │   ├── submitLPJ.vue           # LPJ submission form
    │   │   ├── reviewTOR.vue           # TOR review/approval page
    │   │   ├── reviewLPJ.vue           # LPJ review/approval page
    │   │   ├── userManagement.vue      # User management (admin only)
    │   │   ├── accountProfile.vue      # Account profile settings
    │   │   └── notFound.vue            # 404 page
    │   ├── App.vue
    │   └── main.ts
    └── package.json
```

# 1.8 Informasi Tambahan

- **Role Akun Default (Seeder)**:
  - Mahasiswa: `joe@example.com` / `password123`
  - Mahasiswa 2: `muhammad.ervin.fadillah.tik22@mhsw.pnj.ac.id` / `password321`
  - Sekretaris Jurusan: `fadillah.ervin+secretary@gmail.com` / `password123`
  - Admin Jurusan: `fadillah.ervin+admin@gmail.com` / `password123`
  - Ketua Jurusan: `fadillah.ervin+head@gmail.com` / `password123`
- **Catatan Penting**:
  - Pastikan `queue:work` berjalan agar email notifikasi terkirim.
  - Untuk file upload, pastikan konfigurasi `upload_max_filesize` di `php.ini` cukup besar jika mengunggah file > 2MB.
  - Sistem ini menggunakan `soft deletes` untuk keamanan data.

---
