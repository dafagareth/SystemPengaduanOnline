<?php
/**
 * File konfigurasi database
 * Sistem Pengaduan Online
 */

// Konfigurasi database
define('DB_HOST', 'mysql');
define('DB_USER', 'pengaduan_user');
define('DB_PASS', 'pengaduan_pass');
define('DB_NAME', 'pengaduan_db');

// Informasi aplikasi
define('APP_NAME', 'Sistem Pengaduan Online');
define('APP_VERSION', '1.0.0');
define('COPYRIGHT', '© 2025 Dafa al hafiz - 24_0085. All rights reserved.');

// Base URL
define('BASE_URL', 'http://localhost:8000');

// Koneksi database menggunakan PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Start session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>