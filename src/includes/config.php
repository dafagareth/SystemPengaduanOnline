<?php
/**
 * ============================================================================
 * FILE KONFIGURASI UTAMA SISTEM
 * ============================================================================
 * File: includes/config.php
 * Deskripsi: File konfigurasi database dan pengaturan aplikasi utama
 * Fungsi:
 * - Mendefinisikan konstanta database (host, user, password, nama DB)
 * - Mendefinisikan konstanta aplikasi (nama, versi, copyright)
 * - Membuat koneksi PDO ke database MySQL
 * - Menginisialisasi session
 * 
 * Security Notes:
 * - Password database sebaiknya disimpan di environment variables di production
 * - Gunakan .env file atau secrets management
 * - Jangan commit file ini dengan credential asli ke Git
 * 
 * Author: Dafa al hafiz - 24_0085
 * Tanggal: 2025
 * ============================================================================
 */

// ============================================================================
// SECTION 1: KONFIGURASI DATABASE
// ============================================================================
// Konstanta untuk koneksi ke MySQL database
// 
// PENTING: Ganti nilai ini sesuai environment Anda:
// - Development: localhost atau nama service Docker
// - Production: IP/hostname database server
// ============================================================================

/**
 * DB_HOST: Hostname atau IP address MySQL server
 * 
 * Development (Docker Compose):
 * - 'mysql' = nama service di docker-compose.yml
 * - Docker DNS akan resolve nama service ke IP container
 * 
 * Development (Local):
 * - 'localhost' atau '127.0.0.1'
 * 
 * Production:
 * - IP address atau hostname database server
 * - Contoh: 'db.example.com' atau '192.168.1.100'
 */
define('DB_HOST', 'mysql');

/**
 * DB_USER: Username untuk login ke MySQL
 * 
 * Development:
 * - Gunakan user khusus aplikasi (bukan root)
 * - User ini dibuat otomatis via MYSQL_USER di docker-compose
 * 
 * Production:
 * - WAJIB gunakan user khusus dengan least privilege
 * - Berikan akses hanya ke database yang diperlukan
 * - Jangan gunakan root user!
 */
define('DB_USER', 'pengaduan_user');

/**
 * DB_PASS: Password untuk user database
 * 
 * SECURITY WARNING:
 * - Ganti password default ini di production!
 * - Gunakan password yang kuat (min 16 karakter)
 * - Kombinasi huruf besar, kecil, angka, simbol
 * - Sebaiknya gunakan password generator
 * - Jangan commit password ke Git repository
 * 
 * Best Practice:
 * - Gunakan environment variables
 * - Contoh: getenv('DB_PASSWORD') atau $_ENV['DB_PASSWORD']
 */
define('DB_PASS', 'pengaduan_pass');

/**
 * DB_NAME: Nama database yang digunakan aplikasi
 * 
 * Development:
 * - Database ini dibuat otomatis via MYSQL_DATABASE di docker-compose
 * 
 * Production:
 * - Pastikan database sudah dibuat sebelumnya
 * - Run init.sql untuk create tables & sample data
 */
define('DB_NAME', 'pengaduan_db');

// ============================================================================
// SECTION 2: INFORMASI APLIKASI
// ============================================================================
// Konstanta yang berisi metadata aplikasi
// Digunakan untuk ditampilkan di header, footer, dan halaman lainnya
// ============================================================================

/**
 * APP_NAME: Nama aplikasi
 * 
 * Digunakan di:
 * - Title tag (<title>)
 * - Navbar brand
 * - Footer
 * - Email notifications (jika ada)
 */
define('APP_NAME', 'Sistem Pengaduan Online');

/**
 * APP_VERSION: Versi aplikasi (Semantic Versioning)
 * 
 * Format: MAJOR.MINOR.PATCH
 * - MAJOR: Breaking changes
 * - MINOR: New features (backward compatible)
 * - PATCH: Bug fixes
 * 
 * Contoh: 1.0.0, 1.2.3, 2.0.0
 */
define('APP_VERSION', '1.0.0');

/**
 * COPYRIGHT: Copyright notice
 * 
 * Format umum: "© [Year] [Name/Company]. All rights reserved."
 * 
 * Digunakan di:
 * - Footer halaman publik
 * - Footer admin panel
 */
define('COPYRIGHT', '© 2025 Dafa al hafiz - 24_0085. All rights reserved.');

// ============================================================================
// SECTION 3: BASE URL
// ============================================================================
// URL dasar aplikasi untuk membuat absolute URLs
// ============================================================================

/**
 * BASE_URL: URL dasar aplikasi
 * 
 * Digunakan untuk:
 * - Link navigasi (agar tidak broken jika struktur folder berubah)
 * - Asset paths (CSS, JS, images)
 * - Redirect URLs
 * 
 * Development:
 * - http://localhost:8000 (port dari docker-compose)
 * 
 * Production:
 * - https://your-domain.com (dengan HTTPS!)
 * - Atau https://your-domain.com/subfolder (jika di subfolder)
 * 
 * PENTING: Jangan tambahkan trailing slash (/)
 */
define('BASE_URL', 'http://localhost:8000');

// ============================================================================
// SECTION 4: KONEKSI DATABASE MENGGUNAKAN PDO
// ============================================================================
// Membuat koneksi ke MySQL database menggunakan PDO (PHP Data Objects)
// 
// Why PDO?
// 1. Security: Support prepared statements (SQL injection prevention)
// 2. Flexibility: Support multiple database types (MySQL, PostgreSQL, etc)
// 3. Error handling: Better error handling dengan exceptions
// 4. Modern: Recommended by PHP community
// ============================================================================

try {
    /**
     * Membuat instance PDO
     * 
     * Constructor PDO:
     * new PDO($dsn, $username, $password, $options)
     * 
     * $dsn (Data Source Name):
     * - Format: "mysql:host=HOST;dbname=DBNAME;charset=CHARSET"
     * - mysql: Database driver
     * - host: Server hostname/IP
     * - dbname: Database name
     * - charset=utf8mb4: Character set (support emoji & unicode)
     */
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            /**
             * ATTR_ERRMODE => ERRMODE_EXCEPTION
             * 
             * Error mode: Throw PDOException jika ada error SQL
             * 
             * Options:
             * - ERRMODE_SILENT: Tidak throw error (default, tidak recommended)
             * - ERRMODE_WARNING: Trigger PHP warning
             * - ERRMODE_EXCEPTION: Throw exception (RECOMMENDED)
             * 
             * Benefit: Bisa catch error dengan try-catch
             */
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            
            /**
             * ATTR_DEFAULT_FETCH_MODE => FETCH_ASSOC
             * 
             * Default fetch mode: Return hasil query sebagai associative array
             * 
             * Options:
             * - FETCH_ASSOC: ['column' => 'value'] (RECOMMENDED)
             * - FETCH_NUM: [0 => 'value'] (indexed array)
             * - FETCH_BOTH: Both assoc & num (redundant, tidak efficient)
             * - FETCH_OBJ: object->column
             * 
             * Benefit: Code lebih clean dengan array associative
             */
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            
            /**
             * ATTR_EMULATE_PREPARES => false
             * 
             * Use native prepared statements (bukan emulasi)
             * 
             * false (RECOMMENDED):
             * - Database server yang handle prepare statement
             * - Lebih aman dari SQL injection
             * - Better type handling
             * 
             * true:
             * - PHP yang emulate prepared statements
             * - Tidak se-aman native
             * - Compatibility dengan old MySQL versions
             * 
             * Benefit: Security maksimal
             */
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
} catch (PDOException $e) {
    /**
     * Error Handling: Jika koneksi database gagal
     * 
     * PDOException berisi:
     * - $e->getMessage(): Pesan error (contoh: "Access denied for user")
     * - $e->getCode(): Error code
     * 
     * die(): Stop script execution dan tampilkan pesan
     * 
     * PRODUCTION WARNING:
     * - Jangan tampilkan detail error ke user (security risk)
     * - Log error ke file: error_log($e->getMessage())
     * - Tampilkan generic error: "Database connection failed"
     */
    die("Koneksi database gagal: " . $e->getMessage());
}

// ============================================================================
// SECTION 5: SESSION MANAGEMENT
// ============================================================================
// Memulai PHP session jika belum dimulai
// Session digunakan untuk menyimpan data user (contoh: login admin)
// ============================================================================

/**
 * Check & Start Session
 * 
 * session_status() return values:
 * - PHP_SESSION_DISABLED: Session disabled di php.ini
 * - PHP_SESSION_NONE: Session enabled tapi belum started
 * - PHP_SESSION_ACTIVE: Session sudah started
 * 
 * Kondisi: Hanya start session jika belum started
 * Alasan: Avoid "session already started" warning
 */
if (session_status() === PHP_SESSION_NONE) {
    /**
     * session_start(): Inisialisasi session
     * 
     * What it does:
     * 1. Create/resume session
     * 2. Generate/use session ID
     * 3. Make $_SESSION superglobal available
     * 
     * $_SESSION usage:
     * - Set: $_SESSION['key'] = 'value'
     * - Get: $value = $_SESSION['key']
     * - Unset: unset($_SESSION['key'])
     * - Destroy all: session_destroy()
     * 
     * Security Notes:
     * - Session ID disimpan di cookie PHPSESSID
     * - Sebaiknya regenerate session ID setelah login
     * - Gunakan HTTPS di production (prevent session hijacking)
     */
    session_start();
}

/**
 * ============================================================================
 * END OF CONFIGURATION
 * ============================================================================
 * 
 * Variabel yang available setelah include config.php:
 * - Konstanta: DB_HOST, DB_USER, DB_PASS, DB_NAME
 * - Konstanta: APP_NAME, APP_VERSION, COPYRIGHT, BASE_URL
 * - Object: $pdo (PDO instance untuk database operations)
 * - Superglobal: $_SESSION (untuk session management)
 * 
 * Usage di file lain:
 * require_once 'includes/config.php';
 * 
 * Kemudian bisa langsung gunakan:
 * - echo APP_NAME;
 * - $stmt = $pdo->prepare("SELECT * FROM ...");
 * - $_SESSION['user_id'] = 123;
 * ============================================================================
 */
?>