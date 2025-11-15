# System Pengaduan Online

    Sistem Informasi Pengaduan Masyarakat Online (SIPMO) berbasis PHP dan MySQL yang dirancang untuk memfasilitasi pelaporan dan tindak lanjut pengaduan dari masyarakat secara efisien.

    Shutterstock

# ⚙️ Fitur Utama

    Pengaduan Anonim/Terautentikasi: Masyarakat dapat membuat laporan dengan atau tanpa akun.

    Status Laporan: Pengguna dapat melacak status pengaduan mereka (Pending, Proses, Selesai).

    Admin Dashboard: Halaman khusus untuk petugas/admin untuk memproses dan menanggapi laporan.

    Manajemen Data: CRUD (Create, Read, Update, Delete) untuk data pengaduan dan tanggapan.

    Otentikasi Aman: Login dan session handling yang sederhana.

# 🛠️ Persyaratan Sistem

Untuk menjalankan project ini di lingkungan lokal, Anda memerlukan:

    PHP (Versi 7.4 atau lebih tinggi disarankan)

    MySQL / MariaDB

    Web Server (Apache atau Nginx)

    Composer (Opsional, jika ada dependensi PHP)

Rekomendasi: Gunakan Docker untuk lingkungan pengembangan yang cepat dan konsisten.

# 🚀 Panduan Instalasi (Development)

Karena project ini telah menyertakan Docker Compose, proses instalasi lokal menjadi sangat cepat dan mudah.

1. Kloning Repositori

Buka Terminal/Git Bash, lalu download project ke komputer Anda:
Bash

git clone https://github.com/dafagareth/SystemPengaduanOnline.git
cd SystemPengaduanOnline

2. Konfigurasi Lingkungan

Salin file konfigurasi lingkungan.
Bash

cp .env.example .env

    CATATAN: Buka file .env dan sesuaikan variabel koneksi database (DB_HOST, DB_USER, DB_PASS) jika diperlukan.

3. Jalankan dengan Docker Compose

Perintah ini akan secara otomatis membangun image Docker (Dockerfile), menjalankan web server (PHP/Apache) dan database server (MySQL/MariaDB) sesuai konfigurasi di docker-compose.yml.
Bash

docker-compose up -d --build

4. Setup Database

Anda perlu mengimpor skema database dan data awal.

    Akses container database (lihat layanan di docker-compose.yml Anda):
    Bash

docker exec -it <NAMA_CONTAINER_DB> mysql -u <DB_USER> -p <DB_PASS> <DB_NAME>

Di dalam MySQL, source file SQL Anda:
SQL

    SOURCE /path/to/sql/init.sql;

    (Atau, Anda dapat menggunakan alat seperti phpMyAdmin yang mungkin sudah disiapkan di dalam Docker Compose Anda, atau mengimpor secara manual).

5. Akses Aplikasi

Aplikasi sekarang dapat diakses melalui browser:

    Aplikasi Publik: http://localhost:8000 (Biasanya port 80 atau 8000, tergantung konfigurasi docker-compose.yml)

    Halaman Admin: http://localhost:8000/src/admin/login.php

# 📦 Struktur Project

Berikut adalah struktur direktori utama project ini:

    SystemPengaduanOnline/
├── sql/
│   └── init.sql ✅                        # Database schema
├── src/
│   ├── admin/
│   │   ├── dashboard.php                  # Admin dashboard
│   │   ├── pengaduan.php                  # Kelola pengaduan
│   │   ├── detail-pengaduan.php           # Detail & update
│   │   ├── export-csv.php                 # Export data
│   │   ├── login.php                      # Login page
│   │   └── logout.php                     # Logout handler
│   ├── assets/
│   │   ├── css/
│   │   │   ├── style.css                  # Public styles
│   │   │   ├── admin-style.css            # Admin styles
│   │   │   └── login-style.css            # Login styles
│   │   └── js/
│   ├── includes/
│   │   ├── config.php                     # Configuration
│   │   ├── functions.php                  # Helper functions
│   │   ├── header.php                     # Public header
│   │   ├── footer.php                     # Public footer
│   │   ├── admin-header.php               # Admin header
│   │   └── admin-footer.php               # Admin footer
│   ├── uploads/                           # File uploads
│   ├── index.php                          # Form pengaduan
│   ├── cek-pengaduan.php                  # Cek status
│   └── daftar-pengaduan.php               # Daftar pengaduan
├── Dockerfile                             # PHP image config
├── docker-compose.yml                     # Multi-container setup
└── README.md                              # Project docs

# 👥 Kontribusi

Jika Anda menemukan bug atau memiliki saran fitur, silakan:

    Fork repositori ini.

    Buat branch baru (git checkout -b fitur/nama-fitur).

    Commit perubahan Anda (git commit -m 'Menambahkan fitur baru: X').

    Push ke branch Anda (git push origin fitur/nama-fitur).

    Buka Pull Request ke branch main.