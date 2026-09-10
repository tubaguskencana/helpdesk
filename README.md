# 🎫 Helpdesk & Ticketing System

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.x-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Vite](https://img.shields.io/badge/Vite-6.x-646CFF?style=for-the-badge&logo=vite&logoColor=white)](https://vitejs.dev)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=white)](https://alpinejs.dev)
[![Tests](https://img.shields.io/badge/Tests-Passing-success?style=for-the-badge&logo=checkmarx&logoColor=white)]()

Aplikasi manajemen tiket dan layanan bantuan internal (**Internal Helpdesk & Ticketing System**) modern berbasis web yang dibangun menggunakan **Laravel**, **Tailwind CSS**, dan **Alpine.js**.

Sistem ini dirancang untuk menggantikan penanganan permintaan dukungan informal (melalui pesan instan/chat pribadi) menjadi alur kerja yang terpusat, transparan, terukur, dan memiliki riwayat audit lengkap antar departemen (*IT Support, General Affairs, Human Resources, Finance, Facility Management*).

---

## 📑 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Peran Pengguna & Hak Akses](#-peran-pengguna--hak-akses)
- [Kebutuhan Sistem](#-kebutuhan-sistem)
- [Panduan Instalasi (Quick Start)](#-panduan-instalasi-quick-start)
- [Akun Demo Bawaan](#-akun-demo-bawaan-seeder)
- [Struktur Alur Tiket & SLA](#-struktur-alur-tiket--sla)
- [Struktur Direktori Proyek](#-struktur-direktori-proyek)
- [Pengujian Otomatis (Testing)](#-pengujian-otomatis-testing)
- [Lisensi](#-lisensi)

---

## ✨ Fitur Utama

- **Siklus Hidup Tiket Lengkap (Lifecycle)**:
  - Status tiket dinamis: `Open`, `In Progress`, `Pending`, `Resolved`, `Closed`.
  - Tingkat prioritas: `Low`, `Medium`, `High`, `Urgent`.
  - Format kode tiket unik otomatis (Contoh: `HD-2026-000101`).
- **SLA Tracking & Peringatan Otomatis**:
  - Batas waktu respon pertama (*First Response Target*) & batas waktu penyelesaian (*Resolution Target*).
  - Indikator status visual: `Safe` (Aman), `Near Deadline` (Mendekati Batas), dan `Overdue` (Terlambat).
- **Diskusi Interaktif & Internal Notes**:
  - Balasan publik (*Public Reply*) antara pemohon (*Requester*) dan staf penanganan (*Agent*).
  - Catatan internal rahasia (*Staff-only Private Notes*) untuk koordinasi tim teknis tanpa terlihat oleh pemohon.
- **Log Aktivitas & Audit Trail**:
  - Rekam jejak kronologis setiap perubahan status, pengalihan penanggung jawab, perubahan prioritas, dan resolusi tiket.
- **Manajemen Lampiran (File Attachments)**:
  - Unggah tangkapan layar (screenshot), dokumen, PDF dengan verifikasi tipe file dan ukuran.
  - Proteksi unduhan lampiran internal khusus staf.
- **Dashboard Khusus Sesuai Peran**:
  - **Admin**: Ringkasan performa global, rasio penyelesaian, monitoring SLA, dan aktivitas sistem.
  - **Agent**: Antrean tugas pribadi (*Assigned to Me*), tiket belum dialokasikan (*Unassigned*), dan tiket prioritas tinggi.
  - **User**: Daftar tiket yang diajukan, status penanganan terkini, dan riwayat penyelesaian.
- **Laporan & Ekspor Data (Reporting)**:
  - Filter laporan berdasarkan rentang tanggal, departemen, kategori, status, dan prioritas.
  - Ekspor instan ke format **CSV** untuk kebutuhan analisis & audit berkala.
- **Manajemen Master Data (Admin Panel)**:
  - Manajemen Pengguna & Peran (*User Management*).
  - Manajemen Departemen & Kategori Permintaan (*Multi-department support*).
  - Konfigurasi Target Waktu SLA per tingkat prioritas.

---

## 👥 Peran Pengguna & Hak Akses

| Peran | Deskripsi Hak Akses |
|---|---|
| **Administrator** | Akses penuh ke seluruh sistem, manajemen master pengguna, departemen, kategori, pengaturan SLA, dan semua tiket. |
| **Supervisor** | Memantau seluruh tiket dalam departemennya, menugaskan tiket ke Agent, mengubah prioritas, dan memonitor kepatuhan SLA. |
| **Agent / Staff** | Menangani tiket yang ditugaskan kepadanya, memperbarui status, menulis balasan publik & catatan internal rahasia, serta menyelesaikan masalah (*Resolve*). |
| **User / Requester** | Mengajukan tiket baru, memantau kemajuan penanganan tiket miliknya, membalas respon staf, dan mengunduh lampiran publik. |

---

## 💻 Kebutuhan Sistem

Pastikan lingkungan kerja / server lokal Anda telah terpasang:

- **PHP** >= 8.3 (dengan ekstensi: `pdo`, `sqlite3` atau `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `curl`)
- **Composer** >= 2.x
- **Node.js** >= 18.x & **NPM** >= 9.x
- **Basis Data**: SQLite (default & siap pakai), MySQL 8.0+, atau MariaDB 10.4+
- Web server opsional: Laragon, XAMPP, Nginx, atau bawaan Laravel Artisan Server

---

## 🚀 Panduan Instalasi (Quick Start)

Ikuti langkah-langkah berikut untuk meng-clone dan menjalankan proyek ini di mesin lokal:

### 1. Clone Repository

```bash
git clone https://github.com/tubaguskencana/helpdesk.git
cd helpdesk
```

### 2. Pasang Dependensi PHP (Composer)

```bash
composer install
```

### 3. Pasang Dependensi Frontend (NPM)

```bash
npm install
```

### 4. Konfigurasi Environment (`.env`)

Salin file contoh konfigurasi environment:

```bash
# Windows PowerShell / Command Prompt
copy .env.example .env

# Linux / macOS
cp .env.example .env
```

Generate application encryption key:

```bash
php artisan key:generate
```

### 5. Pengaturan Database & Migrasi

Aplikasi ini menggunakan **SQLite** secara bawaan sehingga Anda tidak wajib menginstal MySQL terlebih dahulu.

#### Opsi A: Menggunakan SQLite (Rekomendasi Cepat)

Pastikan file database sqlite tersedia (jika belum ada):

```bash
# Windows PowerShell
if (-not (Test-Path database/database.sqlite)) { New-Item database/database.sqlite -ItemType File }

# Linux / macOS
touch database/database.sqlite
```

Jalankan migrasi database sekaligus seeder data demo:

```bash
php artisan migrate --seed
```

#### Opsi B: Menggunakan MySQL / MariaDB

1. Buka file `.env` dan sesuaikan koneksi database:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=helpdesk_system
   DB_USERNAME=root
   DB_PASSWORD=
   ```
2. Buat database baru di MySQL dengan nama `helpdesk_system`.
3. Jalankan migrasi dan seeder:
   ```bash
   php artisan migrate --seed
   ```

### 6. Buat Symlink Storage

Perintah ini diperlukan agar file lampiran yang diunggah dapat diakses dengan baik:

```bash
php artisan storage:link
```

### 7. Kompilasi Aset Frontend

Jalankan Vite build untuk mode produksi atau development:

```bash
# Untuk kompilasi aset siap pakai
npm run build

# Atau untuk mode hot-reload selama pengembangan
npm run dev
```

### 8. Jalankan Server Lokal

Buka terminal baru dan jalankan:

```bash
php artisan serve
```

Aplikasi dapat diakses melalui peramban web di:
👉 **[http://localhost:8000](http://localhost:8000)**

---

## 🔐 Akun Demo Bawaan (Seeder)

Setelah menjalankan `php artisan migrate --seed`, Anda dapat langsung login menggunakan salah satu akun demonstrasi berikut:

> **Password untuk semua akun:** `password`

| Peran | Nama | Email | Departemen | Keterangan |
|---|---|---|---|---|
| **Admin** | Administrator System | `admin@helpdesk.test` | System / IT | Akses semua modul & pengaturan |
| **Supervisor** | Alex Pratama | `supervisor@helpdesk.test` | IT Support | Pengawas tim & assignment tiket |
| **Agent IT** | Budi Santoso | `agent.it@helpdesk.test` | IT Support | Staf teknis IT & penanganan tiket |
| **Agent IT** | Citra Lestari | `citra.it@helpdesk.test` | IT Support | Staf spesialis jaringan & sistem |
| **Agent GA** | Doni Wijaya | `agent.ga@helpdesk.test` | General Affairs | Penanganan fasilitas & logistik kantor |
| **User (Finance)** | Sarah Jenkins | `user@helpdesk.test` | Finance & Accounting | Pemohon tiket (Finance Analyst) |
| **User (HR)** | Kevin Hartanto | `kevin@helpdesk.test` | Human Resources | Pemohon tiket (HR Ops) |
| **User (Finance)** | Maya Putri | `maya@helpdesk.test` | Finance & Accounting | Pemohon tiket (AP Lead) |

---

## 🔄 Struktur Alur Tiket & SLA

### Alur Kerja Penanganan Tiket:

```text
[User / Requester]
       │  (1. Membuat Tiket Baru)
       ▼
 [Status: Open]
       │
       ├───────────────────────────────────────────────┐
       ▼                                               ▼
[Supervisor / Admin]                           [Agent Menangani]
 (2. Menugaskan Tiket)                         (3. Mengubah Status)
       │                                               │
       └───────────────────────┬───────────────────────┘
                               ▼
                    [Status: In Progress]
                               │
                 ┌─────────────┴─────────────┐
                 ▼                           ▼
          [Balasan Publik]           [Internal Notes]
          (Dilihat Pemohon)         (Hanya Dilihat Staf)
                 │                           │
                 └─────────────┬─────────────┘
                               ▼
                    [Status: Resolved]
                               │
                               ▼
                     [Status: Closed]
```

### Konfigurasi Standar SLA:

| Prioritas | Target Respon Pertama | Target Penyelesaian |
|---|---|---|
| **Urgent** | 1 Jam | 8 Jam |
| **High** | 4 Jam | 24 Jam |
| **Medium** | 8 Jam | 48 Jam |
| **Low** | 24 Jam | 72 Jam |

---

## 📁 Struktur Direktori Proyek

```text
helpdesk-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/               # Master User, Department, Category, SLA Controller
│   │   │   ├── AuthController.php   # Login, Logout, Profile Management
│   │   │   ├── DashboardController.php
│   │   │   ├── ReportController.php # Laporan performa & ekspor CSV
│   │   │   ├── TicketController.php # Manajemen tiket & status
│   │   │   └── TicketReplyController.php
│   │   └── Middleware/
│   │       ├── EnsureAdmin.php      # Proteksi route Admin
│   │       └── EnsureStaff.php      # Proteksi route Agent/Supervisor/Admin
│   ├── Models/                      # Model Eloquent (Ticket, User, SlaSetting, dll)
│   ├── Policies/                    # Laravel Authorization Policy
│   └── Services/
│       └── TicketService.php        # Business logic kalkulasi SLA & status
├── database/
│   ├── migrations/                  # Skema database relasional
│   └── seeders/                     # Seeder data master & tiket simulasi
├── resources/
│   ├── css/app.css                  # Konfigurasi Tailwind CSS
│   ├── js/app.js                    # Alpine.js setup
│   └── views/                       # Blade templates & layouts
├── routes/
│   └── web.php                      # Definisi rute aplikasi
└── tests/
    └── Feature/
        └── HelpdeskSystemTest.php   # Unit & Feature automated tests
```

---

## 🧪 Pengujian Otomatis (Testing)

Proyek ini telah dilengkapi dengan rangkaian automated test menggunakan **PHPUnit** untuk memverifikasi alur otentikasi, perizinan role, pembuatan tiket, dan proteksi catatan rahasia:

```bash
php artisan test
```

Untuk menjalankan tes tertentu:

```bash
php artisan test --filter=HelpdeskSystemTest
```

---

## 🤝 Kontribusi & Pengembangan

1. Fork repository ini
2. Buat branch fitur baru (`git checkout -b feature/fitur-keren`)
3. Lakukan commit perubahan Anda (`git commit -m 'Menambahkan fitur keren'`)
4. Push ke branch Anda (`git push origin feature/fitur-keren`)
5. Buat **Pull Request**

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah lisensi terbuka [MIT License](LICENSE).
