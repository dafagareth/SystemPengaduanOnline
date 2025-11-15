<?php
/**
 * ============================================================================
 * HELPER FUNCTIONS - FUNGSI PEMBANTU SISTEM
 * ============================================================================
 * File: includes/functions.php
 * Deskripsi: Kumpulan fungsi helper yang digunakan di seluruh aplikasi
 * 
 * Categories:
 * 1. Data Generation (generateNomorTiket)
 * 2. Input Validation (sanitizeInput, validateEmail)
 * 3. Formatting (formatTanggalIndonesia)
 * 4. UI Helpers (getStatusClass, getStatusIcon)
 * 5. Authentication (isAdmin)
 * 6. Navigation (redirect)
 * 7. Statistics (getStatistikPengaduan)
 * 8. Export (exportToCSV)
 * 
 * Author: Dafa al hafiz - 24_0085
 * Tanggal: 2025
 * ============================================================================
 */

// ============================================================================
// CATEGORY 1: DATA GENERATION FUNCTIONS
// ============================================================================

/**
 * ============================================================================
 * FUNCTION: generateNomorTiket()
 * ============================================================================
 * Generate nomor tiket unik untuk setiap pengaduan
 * 
 * @param PDO $pdo - Instance PDO untuk koneksi database
 * @return string - Nomor tiket yang ter-generate
 * 
 * Format Output: TKT-YYYYMMDD-XXXX
 * Contoh: TKT-20251109-0001, TKT-20251109-0002, dst
 * 
 * Algoritma:
 * 1. Ambil tanggal hari ini (YYYYMMDD format)
 * 2. Buat prefix: TKT-{tanggal}-
 * 3. Cari nomor tiket terakhir dengan prefix yang sama dari database
 * 4. Jika ada, increment nomor urut
 * 5. Jika tidak ada, mulai dari 0001
 * 6. Return nomor tiket lengkap
 * 
 * Why this format?
 * - TKT: Prefix untuk identify tiket
 * - YYYYMMDD: Tanggal untuk sorting & grouping
 * - XXXX: Nomor urut (4 digit, max 9999 tiket per hari)
 * 
 * Thread-safe?
 * - No, not completely thread-safe
 * - Bisa race condition jika 2 request bersamaan
 * - Solution: Use database auto-increment atau transaction
 * ============================================================================
 */
function generateNomorTiket($pdo) {
    // Ambil tanggal hari ini dalam format YYYYMMDD
    // date('Ymd'): Y=year 4 digit, m=month 2 digit, d=day 2 digit
    // Contoh: 20251109 (9 November 2025)
    $tanggal = date('Ymd');
    
    // Buat prefix untuk nomor tiket hari ini
    // Format: TKT-YYYYMMDD-
    // Contoh: TKT-20251109-
    $prefix = "TKT-{$tanggal}-";
    
    // Query untuk mencari nomor tiket terakhir hari ini
    // LIKE: Pattern matching, % = wildcard (match any characters)
    // Contoh query: WHERE nomor_tiket LIKE 'TKT-20251109-%'
    // ORDER BY DESC: Sorting descending (terbesar dulu)
    // LIMIT 1: Ambil 1 row saja (yang terbesar)
    $stmt = $pdo->prepare("
        SELECT nomor_tiket 
        FROM pengaduan 
        WHERE nomor_tiket LIKE ? 
        ORDER BY nomor_tiket DESC 
        LIMIT 1
    ");
    
    // Execute query dengan parameter
    // $prefix . '%': Contoh: 'TKT-20251109-%'
    $stmt->execute([$prefix . '%']);
    
    // Fetch single row
    // Return array atau false jika tidak ada data
    $last = $stmt->fetch();
    
    // Tentukan nomor urut baru
    if ($last) {
        // Jika ada tiket sebelumnya hari ini
        
        // substr($string, $start): Ambil substring dari posisi $start
        // -4: Ambil 4 karakter terakhir (nomor urut)
        // Contoh: 'TKT-20251109-0005' → '0005'
        $lastNumber = intval(substr($last['nomor_tiket'], -4));
        
        // Increment nomor urut
        // Contoh: 5 → 6
        $newNumber = $lastNumber + 1;
    } else {
        // Jika belum ada tiket hari ini
        // Mulai dari nomor 1
        $newNumber = 1;
    }
    
    // Format nomor urut menjadi 4 digit dengan leading zeros
    // str_pad($input, $length, $pad_string, $pad_type)
    // - $newNumber: Angka yang akan di-pad
    // - 4: Panjang target (4 digit)
    // - '0': Character untuk padding
    // - STR_PAD_LEFT: Pad di sebelah kiri
    // Contoh: 1 → '0001', 23 → '0023', 456 → '0456'
    $formattedNumber = str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    
    // Gabungkan prefix dengan nomor urut
    // Contoh: 'TKT-20251109-' + '0001' = 'TKT-20251109-0001'
    return $prefix . $formattedNumber;
}

// ============================================================================
// CATEGORY 2: INPUT VALIDATION FUNCTIONS
// ============================================================================

/**
 * ============================================================================
 * FUNCTION: sanitizeInput()
 * ============================================================================
 * Membersihkan input dari user untuk keamanan
 * 
 * @param string $data - Data input yang akan dibersihkan
 * @return string - Data yang sudah dibersihkan
 * 
 * Purpose:
 * - Mencegah XSS (Cross-Site Scripting) attack
 * - Membersihkan whitespace yang tidak perlu
 * - Menghapus backslashes yang tidak perlu
 * 
 * When to use:
 * - SEMUA input dari user (form POST, GET, COOKIE)
 * - Sebelum simpan ke database
 * - Sebelum display di HTML
 * 
 * Security Layers:
 * 1. trim(): Remove leading/trailing whitespace
 * 2. stripslashes(): Remove backslashes
 * 3. htmlspecialchars(): Convert special chars to HTML entities
 * ============================================================================
 */
function sanitizeInput($data) {
    // STEP 1: trim() - Hapus whitespace di awal dan akhir string
    // Whitespace: space, tab, newline, carriage return, dll
    // Contoh: '  hello  ' → 'hello'
    $data = trim($data);
    
    // STEP 2: stripslashes() - Hapus backslashes (\) dari string
    // Berguna untuk data yang di-escape secara otomatis
    // Contoh: "It\'s" → "It's"
    // Note: Pada PHP modern (magic_quotes sudah deprecated), 
    //       fungsi ini mostly untuk backward compatibility
    $data = stripslashes($data);
    
    // STEP 3: htmlspecialchars() - Convert karakter khusus ke HTML entities
    // 
    // What it converts:
    // - & → &amp;
    // - " → &quot;
    // - ' → &#039;
    // - < → &lt;
    // - > → &gt;
    // 
    // Why important?
    // Mencegah XSS attack!
    // 
    // XSS Example (Without htmlspecialchars):
    // User input: <script>alert('hacked')</script>
    // Display: Script akan dijalankan! 🚨
    // 
    // With htmlspecialchars:
    // Display: &lt;script&gt;alert('hacked')&lt;/script&gt;
    // Result: Ditampilkan as text, tidak dijalankan ✅
    $data = htmlspecialchars($data);
    
    return $data;
}

/**
 * ============================================================================
 * FUNCTION: validateEmail()
 * ============================================================================
 * Validasi format email
 * 
 * @param string $email - Email yang akan divalidasi
 * @return bool - true jika valid, false jika tidak
 * 
 * Validation Rules (RFC 822):
 * - Must have @ symbol
 * - Must have domain part
 * - Must have TLD (top-level domain)
 * - Valid characters only
 * 
 * Examples:
 * Valid:
 * - user@domain.com ✅
 * - user.name@sub.domain.co.id ✅
 * - user+tag@domain.com ✅
 * 
 * Invalid:
 * - userdomain.com (no @) ❌
 * - user@domain (no TLD) ❌
 * - user @domain.com (space) ❌
 * ============================================================================
 */
function validateEmail($email) {
    // filter_var(): PHP built-in function untuk validasi & sanitasi
    // FILTER_VALIDATE_EMAIL: Filter untuk validasi email format
    // 
    // How it works:
    // - Check syntax sesuai RFC 822 standard
    // - Return email jika valid
    // - Return false jika invalid
    // 
    // Cast to bool:
    // - Valid email: (bool) "user@domain.com" → true
    // - Invalid: (bool) false → false
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// ============================================================================
// CATEGORY 3: FORMATTING FUNCTIONS
// ============================================================================

/**
 * ============================================================================
 * FUNCTION: formatTanggalIndonesia()
 * ============================================================================
 * Format tanggal ke format Indonesia yang lebih readable
 * 
 * @param string $datetime - String datetime dari database (Y-m-d H:i:s)
 * @return string - Tanggal format Indonesia
 * 
 * Input Example: "2025-11-09 14:30:00"
 * Output Example: "9 Nov 2025, 14:30"
 * 
 * Why?
 * - Format database (2025-11-09) tidak user-friendly
 * - Format Indonesia lebih mudah dibaca
 * - Konsisten dengan kebiasaan lokal
 * ============================================================================
 */
function formatTanggalIndonesia($datetime) {
    // Array nama bulan dalam bahasa Indonesia (singkat 3 huruf)
    // Index 1-12 (bukan 0-11) karena date('n') return 1-12
    $bulan = [
        1 => 'Jan',  2 => 'Feb',  3 => 'Mar',  4 => 'Apr',
        5 => 'Mei',  6 => 'Jun',  7 => 'Jul',  8 => 'Agu',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];
    
    // Convert string datetime ke Unix timestamp (integer)
    // strtotime(): Parse string datetime ke timestamp
    // Timestamp: Jumlah detik sejak Unix Epoch (1 Jan 1970 00:00:00 UTC)
    // Contoh: "2025-11-09 14:30:00" → 1731163800 (example)
    $timestamp = strtotime($datetime);
    
    // Ekstrak komponen tanggal dari timestamp
    
    // date('j', $timestamp): Tanggal tanpa leading zero (1-31)
    // Contoh: 9, bukan 09
    $tanggal = date('j', $timestamp);
    
    // date('n', $timestamp): Bulan dalam angka tanpa leading zero (1-12)
    // Contoh: 11 (November)
    $bulanAngka = date('n', $timestamp);
    
    // date('Y', $timestamp): Tahun 4 digit
    // Contoh: 2025
    $tahun = date('Y', $timestamp);
    
    // date('H:i', $timestamp): Jam:Menit (24-hour format)
    // H: Hour dengan leading zero (00-23)
    // i: Minute dengan leading zero (00-59)
    // Contoh: 14:30
    $waktu = date('H:i', $timestamp);
    
    // Gabungkan semua komponen menjadi format Indonesia
    // String interpolation: "{$var}" di dalam double quotes
    // Contoh output: "9 Nov 2025, 14:30"
    return "{$tanggal} {$bulan[$bulanAngka]} {$tahun}, {$waktu}";
}

// ============================================================================
// CATEGORY 4: UI HELPER FUNCTIONS
// ============================================================================

/**
 * ============================================================================
 * FUNCTION: getStatusClass()
 * ============================================================================
 * Mendapatkan CSS class untuk badge status
 * 
 * @param string $status - Status pengaduan
 * @return string - CSS class untuk styling badge
 * 
 * Purpose:
 * Memberi warna berbeda pada badge status untuk visual distinction
 * 
 * Status → Color:
 * - Menunggu → Yellow (warning) 🟡
 * - Diproses → Blue (info) 🔵
 * - Selesai → Green (success) 🟢
 * - Ditolak → Red (danger) 🔴
 * 
 * Usage:
 * <span class="badge <?php echo getStatusClass($status); ?>">
 *     <?php echo $status; ?>
 * </span>
 * ============================================================================
 */
function getStatusClass($status) {
    // Mapping status ke CSS class
    // Array associative: status sebagai key, class sebagai value
    $classes = [
        'Menunggu' => 'status-pending',      // Yellow/Orange badge
        'Diproses' => 'status-in-progress',  // Blue badge
        'Selesai'  => 'status-resolved',     // Green badge
        'Ditolak'  => 'status-rejected'      // Red badge
    ];
    
    // Return class yang sesuai
    // Null coalescing operator (??): Return left jika tidak null, else right
    // Jika status tidak ada di array, return default 'status-pending'
    // 
    // Contoh:
    // - getStatusClass('Menunggu') → 'status-pending'
    // - getStatusClass('Unknown') → 'status-pending' (fallback)
    return $classes[$status] ?? 'status-pending';
}

/**
 * ============================================================================
 * FUNCTION: getStatusIcon()
 * ============================================================================
 * Mendapatkan icon Bootstrap Icons untuk status
 * 
 * @param string $status - Status pengaduan
 * @return string - Class icon Bootstrap Icons
 * 
 * Purpose:
 * Visual representation status dengan icon yang sesuai
 * 
 * Status → Icon:
 * - Menunggu → Clock ⏰ (waiting)
 * - Diproses → Hourglass ⏳ (in progress)
 * - Selesai → Check Circle ✅ (completed)
 * - Ditolak → X Circle ❌ (rejected)
 * 
 * Usage:
 * <i class="<?php echo getStatusIcon($status); ?>"></i>
 * ============================================================================
 */
function getStatusIcon($status) {
    // Mapping status ke Bootstrap Icons class
    $icons = [
        'Menunggu' => 'bi-clock',              // Clock icon
        'Diproses' => 'bi-hourglass-split',    // Hourglass icon
        'Selesai'  => 'bi-check-circle',       // Check circle icon
        'Ditolak'  => 'bi-x-circle'            // X circle icon
    ];
    
    // Return icon class, default ke clock jika status tidak dikenali
    return $icons[$status] ?? 'bi-clock';
}

// ============================================================================
// CATEGORY 5: AUTHENTICATION FUNCTIONS
// ============================================================================

/**
 * ============================================================================
 * FUNCTION: isAdmin()
 * ============================================================================
 * Cek apakah user saat ini adalah admin yang sudah login
 * 
 * @return bool - true jika admin sudah login, false jika belum
 * 
 * How it works:
 * Mengecek apakah session 'admin_id' ada dan tidak kosong
 * Session ini di-set saat admin berhasil login
 * 
 * Usage:
 * if (!isAdmin()) {
 *     redirect('/admin/login.php');
 * }
 * 
 * Security Note:
 * - Session ID disimpan di cookie (PHPSESSID)
 * - Use HTTPS di production (prevent session hijacking)
 * - Regenerate session ID after login (prevent session fixation)
 * ============================================================================
 */
function isAdmin() {
    // Cek 2 kondisi:
    // 1. isset($_SESSION['admin_id']): Cek apakah key 'admin_id' ada di session
    // 2. !empty($_SESSION['admin_id']): Cek apakah valuenya tidak kosong
    // 
    // Both must be true:
    // - isset() = true, empty() = false → return true (admin logged in)
    // - isset() = false OR empty() = true → return false (not logged in)
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// ============================================================================
// CATEGORY 6: NAVIGATION FUNCTIONS
// ============================================================================

/**
 * ============================================================================
 * FUNCTION: redirect()
 * ============================================================================
 * Helper function untuk redirect ke halaman lain
 * 
 * @param string $url - URL tujuan redirect
 * @return void - Function tidak return value (langsung exit)
 * 
 * How it works:
 * 1. Kirim HTTP header "Location: $url"
 * 2. Browser akan navigate ke URL tersebut
 * 3. Stop script execution dengan exit()
 * 
 * Usage:
 * redirect('/admin/dashboard.php');
 * redirect(BASE_URL . '/login.php');
 * 
 * Important:
 * - Must be called BEFORE any output (echo, HTML, whitespace)
 * - exit() WAJIB dipanggil (prevent code after redirect dari execute)
 * ============================================================================
 */
function redirect($url) {
    // Kirim HTTP header Location
    // Header Location: Instruksi browser untuk navigate ke URL baru
    // HTTP status: 302 Found (temporary redirect) by default
    header("Location: $url");
    
    // Hentikan eksekusi script
    // Why exit()?
    // Tanpa exit(), kode setelah redirect() tetap dijalankan!
    // 
    // Example bahaya:
    // redirect('/login.php');
    // // Kode ini masih jalan jika tidak ada exit()!
    // $secret = getSecretData(); // MASIH DIJALANKAN! 🚨
    // 
    // Dengan exit():
    // redirect('/login.php');
    // // Kode di bawah tidak jalan ✅
    exit();
}

// ============================================================================
// CATEGORY 7: STATISTICS FUNCTIONS
// ============================================================================

/**
 * ============================================================================
 * FUNCTION: getStatistikPengaduan()
 * ============================================================================
 * Mengambil statistik pengaduan untuk dashboard admin
 * 
 * @param PDO $pdo - Instance PDO
 * @return array - Array berisi statistik pengaduan
 * 
 * Return Structure:
 * [
 *   'total' => 100,
 *   'by_status' => [
 *     'Menunggu' => 30,
 *     'Diproses' => 20,
 *     'Selesai' => 40,
 *     'Ditolak' => 10
 *   ],
 *   'by_kategori' => [
 *     'Infrastruktur' => 40,
 *     'Kebersihan' => 25,
 *     'Keamanan' => 15,
 *     'Pelayanan' => 10,
 *     'Lainnya' => 10
 *   ]
 * ]
 * 
 * Usage:
 * $stats = getStatistikPengaduan($pdo);
 * echo "Total: " . $stats['total'];
 * echo "Menunggu: " . $stats['by_status']['Menunggu'];
 * ============================================================================
 */
function getStatistikPengaduan($pdo) {
    // ========================================================================
    // QUERY 1: Total Semua Pengaduan
    // ========================================================================
    // COUNT(*): Hitung jumlah semua row di table pengaduan
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pengaduan");
    $total = $stmt->fetch()['total'];
    
    // ========================================================================
    // QUERY 2: Jumlah Pengaduan per Status
    // ========================================================================
    // GROUP BY status: Kelompokkan data berdasarkan kolom status
    // COUNT(*): Hitung jumlah row per group
    // 
    // Result Example:
    // | status    | jumlah |
    // |-----------|--------|
    // | Menunggu  | 30     |
    // | Diproses  | 20     |
    // | Selesai   | 40     |
    // | Ditolak   | 10     |
    $stmt = $pdo->query("
        SELECT status, COUNT(*) as jumlah 
        FROM pengaduan 
        GROUP BY status
    ");
    
    // Build associative array: status sebagai key, jumlah sebagai value
    $byStatus = [];
    while ($row = $stmt->fetch()) {
        $byStatus[$row['status']] = $row['jumlah'];
    }
    
    // ========================================================================
    // QUERY 3: Jumlah Pengaduan per Kategori
    // ========================================================================
    // Similar dengan query status, tapi GROUP BY kategori
    $stmt = $pdo->query("
        SELECT kategori, COUNT(*) as jumlah 
        FROM pengaduan 
        GROUP BY kategori
    ");
    
    // Build associative array: kategori sebagai key, jumlah sebagai value
    $byKategori = [];
    while ($row = $stmt->fetch()) {
        $byKategori[$row['kategori']] = $row['jumlah'];
    }
    
    // Return semua statistik dalam satu array
    return [
        'total' => $total,
        'by_status' => $byStatus,
        'by_kategori' => $byKategori
    ];
}

// ============================================================================
// CATEGORY 8: EXPORT FUNCTIONS
// ============================================================================

/**
 * ============================================================================
 * FUNCTION: exportToCSV()
 * ============================================================================
 * Export data pengaduan ke file CSV
 * 
 * @param array $data - Array data pengaduan yang akan di-export
 * @param string|null $filename - Nama file CSV (optional)
 * @return void - Function langsung output file, tidak return value
 * 
 * CSV Format:
 * - Encoding: UTF-8 dengan BOM (Excel compatible)
 * - Delimiter: Comma (,)
 * - Enclosure: Double quote (")
 * - Line ending: CRLF (\r\n)
 * 
 * Usage:
 * $data = $pdo->query("SELECT * FROM pengaduan")->fetchAll();
 * exportToCSV($data);
 * // Browser akan download file CSV
 * ============================================================================
 */
function exportToCSV($data, $filename = null) {
    // ========================================================================
    // STEP 1: Generate Filename
    // ========================================================================
    if (!$filename) {
        // Auto-generate filename dengan timestamp
        // Format: pengaduan_export_YYYYMMDD_HHMMSS.csv
        // Contoh: pengaduan_export_20251109_143000.csv
        $filename = 'pengaduan_export_' . date('Ymd_His') . '.csv';
    }
    
    // ========================================================================
    // STEP 2: Set HTTP Headers
    // ========================================================================
    
    // Header 1: Content-Type
    // Memberitahu browser ini file CSV dengan encoding UTF-8
    header('Content-Type: text/csv; charset=utf-8');
    
    // Header 2: Content-Disposition
    // Memaksa browser untuk download (bukan buka di browser)
    // attachment: Force download
    // filename: Nama file yang akan di-download
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // ========================================================================
    // STEP 3: Output UTF-8 BOM (Byte Order Mark)
    // ========================================================================
    // BOM untuk Excel compatibility
    // 
    // Why BOM?
    // - Excel di Windows butuh BOM untuk detect UTF-8
    // - Tanpa BOM: Karakter Indonesia/emoji tampil aneh (���)
    // - Dengan BOM: Karakter tampil benar
    // 
    // \xEF\xBB\xBF: UTF-8 BOM (3 bytes)
    echo "\xEF\xBB\xBF";
    
    // ========================================================================
    // STEP 4: Open Output Stream
    // ========================================================================
    // php://output: Special stream yang langsung output ke browser
    // Tidak create file di server (langsung stream ke client)
    // 'w': Write mode
    $output = fopen('php://output', 'w');
    
    // ========================================================================
    // STEP 5: Write CSV Header (Column Names)
    // ========================================================================
    // fputcsv(): PHP function untuk write CSV row
    // - Auto handle escaping (comma, quotes, newlines dalam data)
    // - Auto add enclosure quotes jika perlu
    // - Auto add delimiter
    fputcsv($output, [
        'Nomor Tiket',
        'Judul',
        'Deskripsi',
        'Kategori',
        'Nama Pelapor',
        'Email',
        'Telepon',
        'Status',
        'Tanggal Dibuat',
        'Tanggal Diperbarui'
    ]);
    
    // ========================================================================
    // STEP 6: Write Data Rows
    // ========================================================================
    // Loop setiap row data dan write ke CSV
    foreach ($data as $row) {
        fputcsv($output, [
            $row['nomor_tiket'],
            $row['judul'],
            $row['deskripsi'],
            $row['kategori'],
            $row['nama_pelapor'],
            $row['email'] ?? '',           // Use empty string if NULL
            $row['telepon'] ?? '',         // Use empty string if NULL
            $row['status'],
            $row['tanggal_dibuat'],
            $row['tanggal_diperbarui']
        ]);
    }
    
    // ========================================================================
    // STEP 7: Close Stream & Exit
    // ========================================================================
    // Close file handle
    fclose($output);
    
    // Exit untuk stop script execution
    // Penting! Agar tidak ada output tambahan setelah CSV
    // Output tambahan akan corrupt CSV file
    exit();
}

/**
 * ============================================================================
 * END OF FUNCTIONS
 * ============================================================================
 * 
 * Total Functions: 10
 * 1. generateNomorTiket() - Generate unique ticket number
 * 2. sanitizeInput() - Clean user input (XSS prevention)
 * 3. validateEmail() - Validate email format
 * 4. formatTanggalIndonesia() - Format date to Indonesian
 * 5. getStatusClass() - Get CSS class for status badge
 * 6. getStatusIcon() - Get icon class for status
 * 7. isAdmin() - Check if user is logged in admin
 * 8. redirect() - Redirect to another page
 * 9. getStatistikPengaduan() - Get complaint statistics
 * 10. exportToCSV() - Export data to CSV file
 * 
 * All functions are documented with:
 * - Purpose & description
 * - Parameters & return types
 * - Usage examples
 * - Security considerations
 * - Algorithm explanation
 * ============================================================================
 */
?>