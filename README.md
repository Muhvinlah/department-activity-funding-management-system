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
  - **Submitter**: Mengajukan & Revisi.
  - **Sekretaris**: Review kelengkapan adminstrasi.
  - **Admin**: Verifikasi anggaran dan data teknis.
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

- Node.js >= 20.x
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
├── /backend                 # Laravel API
│   ├── /app
│   │   ├── /Http/Controllers/Api  # Logic Controller (Tor, Lpj, Dashboard)
│   │   ├── /Models                # Eloquent Models
│   │   └── /Notifications         # Email & DB Notifications
│   ├── /database/migrations       # Schema Database
│   └── /routes/api.php            # Endpoint API
│
└── /frontend                # Vue.js Application
    ├── /src
    │   ├── /assets
    │   ├── /components      # Reusable Components (Modal, Charts)
    │   ├── /services        # API Services (torService, dashboardService)
    │   ├── /stores          # Pinia Stores (Auth, Data)
    │   ├── /views           # Page Views (Dashboard, Review, Submit)
    │   └──App.vue
    └── package.json
```

# 1.8 Informasi Tambahan

- **Role Akun Default (Seeder)**:
  - Ketua Jurusan: `head@example.com` / `password`
  - Admin: `admin@example.com` / `password`
  - Sekretaris: `secretary@example.com` / `password`
  - Dosen/Staff: `lecturer@example.com` / `password`
- **Catatan Penting**:
  - Pastikan `queue:work` berjalan agar email notifikasi terkirim.
  - Untuk file upload, pastikan konfigurasi `upload_max_filesize` di `php.ini` cukup besar jika mengunggah file > 2MB.
  - Sistem ini menggunakan `soft deletes` untuk keamanan data.

---
