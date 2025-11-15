-- ============================================================================
-- DATABASE INITIALIZATION SCRIPT
-- ============================================================================
-- File: init.sql
-- Deskripsi: Script SQL untuk membuat struktur database dan data awal
-- Dijalankan: Otomatis saat container MySQL pertama kali dibuat
-- Author: Dafa al hafiz - 24_0085
-- Tanggal: 2025
-- ============================================================================

-- ============================================================================
-- PILIH DATABASE
-- ============================================================================
-- Database 'pengaduan_db' sudah dibuat otomatis oleh Docker Compose
-- via environment variable MYSQL_DATABASE
USE pengaduan_db;

-- ============================================================================
-- TABEL: pengaduan
-- ============================================================================
-- Tabel utama untuk menyimpan data pengaduan dari masyarakat
-- Fitur:
-- - Auto-increment ID
-- - Unique nomor tiket untuk tracking
-- - Support pengaduan anonim
-- - File bukti (foto/video)
-- - Status tracking (Menunggu/Diproses/Selesai/Ditolak)
-- - Timestamp otomatis
-- ============================================================================

CREATE TABLE IF NOT EXISTS pengaduan (
    -- ========================================================================
    -- KOLOM: id (Primary Key)
    -- ========================================================================
    -- ID unik untuk setiap pengaduan
    -- INT: Tipe data integer
    -- PRIMARY KEY: Kunci utama tabel
    -- AUTO_INCREMENT: Otomatis increment setiap insert baru
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    -- ========================================================================
    -- KOLOM: nomor_tiket (Unique Identifier)
    -- ========================================================================
    -- Nomor tiket untuk tracking pengaduan
    -- Format: TKT-YYYYMMDD-XXXX (contoh: TKT-20251109-0001)
    -- VARCHAR(50): String dengan max 50 karakter
    -- UNIQUE: Nilai harus unik, tidak boleh duplikat
    -- NOT NULL: Wajib diisi
    nomor_tiket VARCHAR(50) UNIQUE NOT NULL,
    
    -- ========================================================================
    -- KOLOM: judul
    -- ========================================================================
    -- Judul singkat pengaduan
    -- VARCHAR(255): String dengan max 255 karakter
    -- NOT NULL: Wajib diisi
    judul VARCHAR(255) NOT NULL,
    
    -- ========================================================================
    -- KOLOM: deskripsi
    -- ========================================================================
    -- Deskripsi detail pengaduan
    -- TEXT: Tipe data text untuk string panjang (max ~65,535 karakter)
    -- NOT NULL: Wajib diisi
    deskripsi TEXT NOT NULL,
    
    -- ========================================================================
    -- KOLOM: kategori
    -- ========================================================================
    -- Kategori pengaduan untuk klasifikasi
    -- ENUM: Hanya bisa memilih salah satu dari nilai yang ditentukan
    -- Nilai: Infrastruktur, Kebersihan, Keamanan, Pelayanan, Lainnya
    -- NOT NULL: Wajib diisi
    kategori ENUM('Infrastruktur', 'Kebersihan', 'Keamanan', 'Pelayanan', 'Lainnya') NOT NULL,
    
    -- ========================================================================
    -- KOLOM: nama_pelapor
    -- ========================================================================
    -- Nama pelapor pengaduan
    -- VARCHAR(100): String dengan max 100 karakter
    -- DEFAULT 'Anonim': Jika tidak diisi, default ke 'Anonim'
    nama_pelapor VARCHAR(100) DEFAULT 'Anonim',
    
    -- ========================================================================
    -- KOLOM: email
    -- ========================================================================
    -- Email pelapor (opsional)
    -- VARCHAR(100): String dengan max 100 karakter
    -- NULL allowed: Boleh kosong (untuk pengaduan anonim)
    email VARCHAR(100),
    
    -- ========================================================================
    -- KOLOM: telepon
    -- ========================================================================
    -- Nomor telepon pelapor (opsional)
    -- VARCHAR(20): String dengan max 20 karakter
    -- NULL allowed: Boleh kosong (untuk pengaduan anonim)
    telepon VARCHAR(20),
    
    -- ========================================================================
    -- KOLOM: file_bukti
    -- ========================================================================
    -- Path file bukti (foto/video)
    -- VARCHAR(255): String dengan max 255 karakter (untuk path file)
    -- NULL allowed: Boleh kosong (bukti opsional)
    file_bukti VARCHAR(255),
    
    -- ========================================================================
    -- KOLOM: status
    -- ========================================================================
    -- Status pemrosesan pengaduan
    -- ENUM: Hanya bisa memilih salah satu dari 4 status
    -- DEFAULT 'Menunggu': Status awal saat pengaduan dibuat
    -- Status flow: Menunggu → Diproses → Selesai/Ditolak
    status ENUM('Menunggu', 'Diproses', 'Selesai', 'Ditolak') DEFAULT 'Menunggu',
    
    -- ========================================================================
    -- KOLOM: tanggal_dibuat
    -- ========================================================================
    -- Timestamp kapan pengaduan dibuat
    -- DATETIME: Format YYYY-MM-DD HH:MM:SS
    -- DEFAULT CURRENT_TIMESTAMP: Otomatis isi dengan waktu saat insert
    tanggal_dibuat DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- ========================================================================
    -- KOLOM: tanggal_diperbarui
    -- ========================================================================
    -- Timestamp kapan pengaduan terakhir diupdate
    -- DATETIME: Format YYYY-MM-DD HH:MM:SS
    -- DEFAULT CURRENT_TIMESTAMP: Isi dengan waktu saat insert
    -- ON UPDATE CURRENT_TIMESTAMP: Otomatis update saat row di-update
    tanggal_diperbarui DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- ========================================================================
    -- INDEXES untuk Performance
    -- ========================================================================
    -- INDEX mempercepat query dengan kolom tertentu
    
    -- Index untuk kolom nomor_tiket (sering digunakan untuk WHERE clause)
    INDEX idx_nomor_tiket (nomor_tiket),
    
    -- Index untuk kolom status (untuk filter by status)
    INDEX idx_status (status),
    
    -- Index untuk kolom kategori (untuk filter by kategori)
    INDEX idx_kategori (kategori)
    
-- ============================================================================
-- TABLE OPTIONS
-- ============================================================================
) ENGINE=InnoDB                      -- InnoDB: Storage engine dengan support transactions
  DEFAULT CHARSET=utf8mb4            -- UTF-8 4-byte: Support emoji dan karakter Unicode
  COLLATE=utf8mb4_unicode_ci;        -- Case-insensitive collation untuk Unicode

-- ============================================================================
-- TABEL: admin
-- ============================================================================
-- Tabel untuk menyimpan akun admin sistem
-- Fitur:
-- - Username unik
-- - Password ter-hash (bcrypt)
-- - Info admin (nama, email)
-- - Timestamp pembuatan akun
-- ============================================================================

CREATE TABLE IF NOT EXISTS admin (
    -- ========================================================================
    -- KOLOM: id (Primary Key)
    -- ========================================================================
    -- ID unik untuk setiap admin
    -- INT: Tipe data integer
    -- PRIMARY KEY: Kunci utama tabel
    -- AUTO_INCREMENT: Otomatis increment setiap insert baru
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    -- ========================================================================
    -- KOLOM: username
    -- ========================================================================
    -- Username untuk login admin
    -- VARCHAR(50): String dengan max 50 karakter
    -- UNIQUE: Username harus unik (tidak boleh duplikat)
    -- NOT NULL: Wajib diisi
    username VARCHAR(50) UNIQUE NOT NULL,
    
    -- ========================================================================
    -- KOLOM: password
    -- ========================================================================
    -- Password ter-hash dengan bcrypt
    -- VARCHAR(255): String dengan max 255 karakter (bcrypt hash ~60 char)
    -- NOT NULL: Wajib diisi
    -- SECURITY: Jangan pernah simpan password plain text!
    password VARCHAR(255) NOT NULL,
    
    -- ========================================================================
    -- KOLOM: nama_lengkap
    -- ========================================================================
    -- Nama lengkap admin
    -- VARCHAR(100): String dengan max 100 karakter
    -- NOT NULL: Wajib diisi
    nama_lengkap VARCHAR(100) NOT NULL,
    
    -- ========================================================================
    -- KOLOM: email
    -- ========================================================================
    -- Email admin
    -- VARCHAR(100): String dengan max 100 karakter
    -- NOT NULL: Wajib diisi
    email VARCHAR(100) NOT NULL,
    
    -- ========================================================================
    -- KOLOM: dibuat_pada
    -- ========================================================================
    -- Timestamp kapan akun admin dibuat
    -- DATETIME: Format YYYY-MM-DD HH:MM:SS
    -- DEFAULT CURRENT_TIMESTAMP: Otomatis isi dengan waktu saat insert
    dibuat_pada DATETIME DEFAULT CURRENT_TIMESTAMP
    
-- ============================================================================
-- TABLE OPTIONS
-- ============================================================================
) ENGINE=InnoDB                      -- InnoDB: Storage engine dengan support transactions
  DEFAULT CHARSET=utf8mb4            -- UTF-8 4-byte: Support emoji dan karakter Unicode
  COLLATE=utf8mb4_unicode_ci;        -- Case-insensitive collation untuk Unicode

-- ============================================================================
-- DATA AWAL: Default Admin Accounts
-- ============================================================================
-- Insert 2 akun admin default untuk testing
-- Username: admin & dafa
-- Password: admin123 (sudah di-hash dengan bcrypt)
-- Hash: $2y$10$YJdHVdzKaOCnE7Ql8N9pweF7HtJhGmJcLqI7lTT.5O8xhxMh.XF3O
-- 
-- PENTING: Ganti password ini di production!
-- ============================================================================

INSERT INTO admin (username, password, nama_lengkap, email) VALUES
-- Admin account #1
(
    'admin',                                                                    -- Username
    '$2y$10$YJdHVdzKaOCnE7Ql8N9pweF7HtJhGmJcLqI7lTT.5O8xhxMh.XF3O',          -- Password hash (admin123)
    'Administrator',                                                             -- Nama lengkap
    'admin@pengaduan.local'                                                      -- Email
),

-- Admin account #2
(
    'dafa',                                                                      -- Username
    '$2y$10$YJdHVdzKaOCnE7Ql8N9pweF7HtJhGmJcLqI7lTT.5O8xhxMh.XF3O',          -- Password hash (admin123)
    'Dafa al hafiz',                                                             -- Nama lengkap
    'dafa@pengaduan.local'                                                       -- Email
);

-- ============================================================================
-- DATA AWAL: Sample Pengaduan
-- ============================================================================
-- Insert beberapa data pengaduan untuk testing
-- Berguna untuk:
-- - Testing tampilan daftar pengaduan
-- - Testing filter & search
-- - Testing status berbeda
-- ============================================================================

INSERT INTO pengaduan (nomor_tiket, judul, deskripsi, kategori, nama_pelapor, email, status, tanggal_dibuat) VALUES

-- Pengaduan #1: Test pengaduan
(
    'TKT-20251109-0001',                                    -- Nomor tiket
    'Test Pengaduan',                                        -- Judul
    'Ini adalah pengaduan test untuk sistem',               -- Deskripsi
    'Infrastruktur',                                         -- Kategori
    'Test User',                                             -- Nama pelapor
    'test@email.com',                                        -- Email
    'Menunggu',                                              -- Status
    '2025-11-09 06:41:00'                                    -- Tanggal dibuat (manual)
),

-- Pengaduan #2: Pengaduan anonim
(
    'TKT-20251109-0002',                                    -- Nomor tiket
    'Premanisme',                                            -- Judul
    'Ada premanisme di area sekitar',                        -- Deskripsi
    'Keamanan',                                              -- Kategori
    'Anonim',                                                -- Nama pelapor (anonim)
    NULL,                                                    -- Email (NULL untuk anonim)
    'Menunggu',                                              -- Status
    '2025-11-09 06:46:00'                                    -- Tanggal dibuat (manual)
),

-- Pengaduan #3: Jalan berlubang
(
    'TKT-20250109-0001',                                    -- Nomor tiket
    'Jalan Berlubang di Depan Sekolah',                     -- Judul
    'Jalan di depan sekolah banyak lubang, berbahaya untuk anak-anak',  -- Deskripsi
    'Infrastruktur',                                         -- Kategori
    'Budi Santoso',                                          -- Nama pelapor
    'budi@email.com',                                        -- Email
    'Menunggu',                                              -- Status
    '2025-11-08 19:28:00'                                    -- Tanggal dibuat (manual)
),

-- Pengaduan #4: Sampah menumpuk (status: Diproses)
(
    'TKT-20250109-0002',                                    -- Nomor tiket
    'Sampah Menumpuk di TPS',                                -- Judul
    'TPS di daerah saya sudah penuh, sampah tidak diangkut', -- Deskripsi
    'Kebersihan',                                            -- Kategori
    'Anonim',                                                -- Nama pelapor (anonim)
    NULL,                                                    -- Email (NULL untuk anonim)
    'Diproses',                                              -- Status (sedang diproses)
    '2025-11-08 19:28:00'                                    -- Tanggal dibuat (manual)
);

-- ============================================================================
-- END OF INITIALIZATION SCRIPT
-- ============================================================================
-- Database siap digunakan dengan:
-- ✓ 2 tabel (pengaduan & admin)
-- ✓ 2 akun admin default
-- ✓ 4 sample pengaduan
-- ============================================================================