-- Database: pengaduan_db
-- Dibuat otomatis saat docker compose up

USE pengaduan_db;

-- Tabel Pengaduan
CREATE TABLE IF NOT EXISTS pengaduan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nomor_tiket VARCHAR(50) UNIQUE NOT NULL,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT NOT NULL,
    kategori ENUM('Infrastruktur', 'Kebersihan', 'Keamanan', 'Pelayanan', 'Lainnya') NOT NULL,
    nama_pelapor VARCHAR(100) DEFAULT 'Anonim',
    email VARCHAR(100),
    telepon VARCHAR(20),
    status ENUM('Menunggu', 'Diproses', 'Selesai', 'Ditolak') DEFAULT 'Menunggu',
    tanggal_dibuat DATETIME DEFAULT CURRENT_TIMESTAMP,
    tanggal_diperbarui DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nomor_tiket (nomor_tiket),
    INDEX idx_status (status),
    INDEX idx_kategori (kategori)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Admin
CREATE TABLE IF NOT EXISTS admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    dibuat_pada DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin
-- Username: admin
-- Password: admin123
INSERT INTO admin (username, password, nama_lengkap, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@pengaduan.local');

-- Insert sample data pengaduan untuk testing
INSERT INTO pengaduan (nomor_tiket, judul, deskripsi, kategori, nama_pelapor, email, status, tanggal_dibuat) VALUES
('TKT-20251109-0001', 'Test Pengaduan', 'Ini adalah pengaduan test untuk sistem', 'Infrastruktur', 'Test User', 'test@email.com', 'Menunggu', '2025-11-09 06:41:00'),
('TKT-20251109-0002', 'Premanisme', 'Ada premanisme di area sekitar', 'Keamanan', 'Anonim', NULL, 'Menunggu', '2025-11-09 06:46:00'),
('TKT-20250109-0001', 'Jalan Berlubang di Depan Sekolah', 'Jalan di depan sekolah banyak lubang, berbahaya untuk anak-anak', 'Infrastruktur', 'Budi Santoso', 'budi@email.com', 'Menunggu', '2025-11-08 19:28:00'),
('TKT-20250109-0002', 'Sampah Menumpuk di TPS', 'TPS di daerah saya sudah penuh, sampah tidak diangkut', 'Kebersihan', 'Anonim', NULL, 'Diproses', '2025-11-08 19:28:00');