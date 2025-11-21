<?php
/**
 * ============================================================================
 * HALAMAN UTAMA - FORM BUAT PENGADUAN
 * ============================================================================
 * File: index.php
 * Deskripsi: Halaman homepage dengan form untuk membuat pengaduan baru
 * Fitur:
 * - Form input pengaduan (judul, deskripsi, kategori)
 * - Upload file bukti (foto/video)
 * - Opsi pengiriman anonim
 * - Validasi input
 * - Generate nomor tiket otomatis
 * - CSRF Protection
 * - Enhanced security untuk file upload
 * - Improved layout & UX
 * Author: Dafa al hafiz - 24_0085
 * Version: 2.0 (Enhanced Layout & Security)
 * ============================================================================
 */

// ============================================================================
// INCLUDE FILE DEPENDENCIES
// ============================================================================
require_once 'includes/config.php';      // Konfigurasi database & konstanta
require_once 'includes/functions.php';   // Helper functions

// ============================================================================
// SESSION & CSRF TOKEN
// ============================================================================
// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ============================================================================
// INISIALISASI VARIABEL
// ============================================================================
$page_title = 'Buat Pengaduan';          // Title halaman (untuk <title> tag)
$success_message = '';                    // Pesan sukses setelah submit
$error_message = '';                      // Pesan error jika ada
$nomor_tiket_baru = '';                  // Menyimpan nomor tiket yang ter-generate

// ============================================================================
// GET STATISTICS (untuk sidebar)
// ============================================================================
$stats = [
    'total' => 0,
    'resolved' => 0,
    'in_progress' => 0
];

try {
    // Query total pengaduan
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pengaduan");
    $stats['total'] = $stmt->fetch()['total'];
    
    // Query pengaduan terselesaikan
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pengaduan WHERE status = 'Selesai'");
    $stats['resolved'] = $stmt->fetch()['total'];
    
    // Query pengaduan dalam proses
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pengaduan WHERE status IN ('Diproses', 'Diterima')");
    $stats['in_progress'] = $stmt->fetch()['total'];
} catch (PDOException $e) {
    // Jika error, gunakan default values
    error_log("Error fetching stats: " . $e->getMessage());
}

// ============================================================================
// HANDLE SUCCESS REDIRECT
// ============================================================================
// Cek apakah ada parameter success dari redirect
if (isset($_GET['success']) && !empty($_GET['success'])) {
    $nomor_tiket_baru = sanitizeInput($_GET['success']);
    $success_message = "Pengaduan berhasil dibuat dengan nomor tiket: {$nomor_tiket_baru}";
}

// ============================================================================
// PROSES FORM SUBMISSION
// ============================================================================
// Cek apakah form di-submit dengan method POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ========================================================================
    // 1. VALIDASI CSRF TOKEN
    // ========================================================================
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "Invalid security token. Silakan refresh halaman dan coba lagi.";
    } else {
        
        // ====================================================================
        // 2. AMBIL & SANITIZE INPUT DATA
        // ====================================================================
        // Ambil data dari $_POST dan bersihkan dengan sanitizeInput()
        // Operator ?? '' : Gunakan empty string jika key tidak ada (null coalescing)
        
        $judul = sanitizeInput($_POST['judul'] ?? '');
        $deskripsi = sanitizeInput($_POST['deskripsi'] ?? '');
        $kategori = sanitizeInput($_POST['kategori'] ?? '');
        $nama_pelapor = sanitizeInput($_POST['nama_pelapor'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $telepon = sanitizeInput($_POST['telepon'] ?? '');
        
        // Cek checkbox anonim
        // isset() return true jika checkbox di-check
        $is_anonim = isset($_POST['is_anonim']) ? true : false;
        
        // ====================================================================
        // 3. VALIDASI INPUT
        // ====================================================================
        // Array untuk menyimpan pesan error
        $errors = [];
        
        // Validasi: Judul harus diisi dan memenuhi panjang minimal/maksimal
        if (empty($judul)) {
            $errors[] = "Judul pengaduan harus diisi";
        } elseif (strlen($judul) < 10) {
            $errors[] = "Judul minimal 10 karakter";
        } elseif (strlen($judul) > 200) {
            $errors[] = "Judul maksimal 200 karakter";
        }
        
        // Validasi: Deskripsi harus diisi dan memenuhi panjang minimal
        if (empty($deskripsi)) {
            $errors[] = "Deskripsi pengaduan harus diisi";
        } elseif (strlen($deskripsi) < 20) {
            $errors[] = "Deskripsi minimal 20 karakter";
        } elseif (strlen($deskripsi) > 2000) {
            $errors[] = "Deskripsi maksimal 2000 karakter";
        }
        
        // Validasi: Kategori harus dipilih
        if (empty($kategori)) {
            $errors[] = "Kategori harus dipilih";
        } else {
            // Validasi kategori harus dari pilihan yang tersedia
            $valid_categories = ['Infrastruktur', 'Kebersihan', 'Keamanan', 'Pelayanan', 'Lainnya'];
            if (!in_array($kategori, $valid_categories)) {
                $errors[] = "Kategori tidak valid";
            }
        }
        
        // Validasi: Email (hanya jika tidak anonim dan email diisi)
        if (!$is_anonim && !empty($email) && !validateEmail($email)) {
            $errors[] = "Format email tidak valid";
        }
        
        // Validasi: Telepon (hanya jika tidak anonim dan telepon diisi)
        if (!$is_anonim && !empty($telepon)) {
            // Format: 10-13 digit angka
            if (!preg_match('/^[0-9]{10,13}$/', $telepon)) {
                $errors[] = "Format telepon tidak valid (10-13 digit angka)";
            }
        }
        
        // ====================================================================
        // 4. HANDLE FILE UPLOAD (Bukti Foto/Video)
        // ====================================================================
        $file_bukti = null;
        $file_uploaded = false;
        $file_path = '';
        
        // Cek apakah ada file yang di-upload
        // UPLOAD_ERR_OK = 0, artinya upload sukses tanpa error
        if (isset($_FILES['file_bukti']) && $_FILES['file_bukti']['error'] === UPLOAD_ERR_OK) {
            
            // ================================================================
            // 4.1 VALIDASI EKSTENSI FILE
            // ================================================================
            // Ambil ekstensi file dari nama file
            $file_extension = strtolower(pathinfo($_FILES['file_bukti']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'mp4'];
            
            // ================================================================
            // 4.2 VALIDASI MIME TYPE (dari file actual, bukan header)
            // ================================================================
            // Gunakan finfo untuk cek MIME type yang sebenarnya
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $actual_mime = finfo_file($finfo, $_FILES['file_bukti']['tmp_name']);
            finfo_close($finfo);
            
            $allowed_mimes = ['image/jpeg', 'video/mp4'];
            
            // ================================================================
            // 4.3 VALIDASI UKURAN FILE
            // ================================================================
            $file_size = $_FILES['file_bukti']['size'];
            $max_size = 2 * 1024 * 1024; // 2MB dalam bytes
            
            // ================================================================
            // 4.4 CEK SEMUA VALIDASI FILE
            // ================================================================
            // Validasi ekstensi file
            if (!in_array($file_extension, $allowed_extensions)) {
                $errors[] = "Format file harus JPG, JPEG, atau MP4";
            } 
            // Validasi MIME type actual
            elseif (!in_array($actual_mime, $allowed_mimes)) {
                $errors[] = "Tipe file tidak valid. Hanya gambar JPEG dan video MP4 yang diperbolehkan";
            }
            // Validasi ukuran file
            elseif ($file_size > $max_size) {
                $errors[] = "Ukuran file maksimal 2MB";
            } 
            // Jika semua validasi lolos, proses upload
            else {
                // ============================================================
                // 4.5 PROSES UPLOAD FILE
                // ============================================================
                // Buat folder uploads jika belum ada
                $upload_dir = __DIR__ . '/uploads/';
                if (!is_dir($upload_dir)) {
                    // mkdir: Create directory
                    // 0755: Permission (owner: rwx, group: rx, other: rx)
                    // true: Create parent directories if needed
                    mkdir($upload_dir, 0755, true);
                }
                
                // Generate nama file unik untuk menghindari konflik
                // Format: {unique_id}_{timestamp}_{safe_original_name}.{extension}
                // Sanitize nama file asli
                $safe_filename = preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['file_bukti']['name']));
                $safe_filename = substr($safe_filename, 0, 50); // Batasi panjang nama
                $file_name = uniqid() . '_' . time() . '_' . $safe_filename;
                $file_path = $upload_dir . $file_name;
                
                // Pindahkan file dari temporary location ke uploads folder
                // move_uploaded_file: Fungsi PHP untuk memindahkan uploaded file
                if (move_uploaded_file($_FILES['file_bukti']['tmp_name'], $file_path)) {
                    // Simpan path relatif (untuk disimpan di database)
                    $file_bukti = 'uploads/' . $file_name;
                    $file_uploaded = true;
                } else {
                    $errors[] = "Gagal mengupload file";
                }
            }
        }
        
        // ====================================================================
        // 5. PROSES PENYIMPANAN KE DATABASE
        // ====================================================================
        // Lanjut proses hanya jika tidak ada error
        if (empty($errors)) {
            try {
                // Generate nomor tiket unik
                $nomor_tiket_baru = generateNomorTiket($pdo);
                
                // Jika anonim, set default values
                if ($is_anonim) {
                    $nama_pelapor = 'Anonim';
                    $email = null;
                    $telepon = null;
                }
                
                // Prepare SQL statement
                // Gunakan prepared statement untuk mencegah SQL Injection
                // Placeholder (?) akan diganti dengan nilai dari array execute()
                $stmt = $pdo->prepare("
                    INSERT INTO pengaduan (nomor_tiket, judul, deskripsi, kategori, nama_pelapor, email, telepon, file_bukti) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                // Execute statement dengan data
                // Array index sesuai urutan placeholder (?)
                $stmt->execute([
                    $nomor_tiket_baru,
                    $judul,
                    $deskripsi,
                    $kategori,
                    $nama_pelapor,
                    $email,
                    $telepon,
                    $file_bukti
                ]);
                
                // Regenerate CSRF token untuk keamanan
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                
                // Redirect ke halaman yang sama dengan success parameter
                // Ini mencegah form resubmit saat user refresh halaman
                header("Location: index.php?success=" . urlencode($nomor_tiket_baru));
                exit;
                
            } catch (PDOException $e) {
                // ============================================================
                // ERROR HANDLING - Database Error
                // ============================================================
                // Log error ke file (untuk debugging oleh developer)
                // JANGAN tampilkan detail error ke user (security risk)
                error_log("Database error pada " . date('Y-m-d H:i:s') . ": " . $e->getMessage());
                
                // Cleanup: Hapus file yang sudah di-upload jika simpan database gagal
                if ($file_uploaded && file_exists($file_path)) {
                    unlink($file_path);
                }
                
                // Tampilkan pesan generic ke user
                $error_message = "Terjadi kesalahan sistem. Silakan coba lagi dalam beberapa saat.";
            }
        } else {
            // ================================================================
            // ERROR HANDLING - Validasi Error
            // ================================================================
            // Cleanup: Hapus file yang sudah di-upload jika ada error validasi
            if ($file_uploaded && file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Jika ada error validasi, gabungkan semua pesan error
            // implode: Gabungkan array menjadi string dengan separator
            $error_message = implode("<br>", $errors);
        }
    }
}

// ============================================================================
// INCLUDE HEADER
// ============================================================================
include 'includes/header.php';
?>

<!-- ============================================================================
     MAIN CONTENT
     ============================================================================ -->
<div class="container py-4" style="max-width: 1200px;">
    
    <?php if (!empty($success_message)): ?>
    <!-- ========================================================================
         SUCCESS STATE - Ditampilkan setelah pengaduan berhasil dibuat
         ======================================================================== -->
    <div class="row justify-content-center">
        <div class="col-lg-6">
            
            <!-- Alert success -->
            <div class="alert alert-success" role="alert">
                <?php echo $success_message; ?>
            </div>
            
            <!-- Success card dengan action buttons -->
            <div class="card">
                <div class="card-body text-center py-5">
                    
                    <!-- Success icon (checkmark) -->
                    <div class="mb-4">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    
                    <!-- Success message -->
                    <h5 class="mb-2">Pengaduan Berhasil Dibuat</h5>
                    <p class="text-muted mb-4">Simpan nomor tiket untuk melacak status pengaduan Anda</p>
                    
                    <!-- Action buttons -->
                    <div class="d-flex gap-2 justify-content-center">
                        <!-- Button: Cek status pengaduan yang baru dibuat -->
                        <a href="cek-pengaduan.php?tiket=<?php echo htmlspecialchars($nomor_tiket_baru); ?>" class="btn btn-primary">
                            Cek Status
                        </a>
                        <!-- Button: Buat pengaduan baru lagi -->
                        <a href="index.php" class="btn btn-outline-secondary">
                            Buat Pengaduan Baru
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php else: ?>
    <!-- ========================================================================
         FORM STATE - Ditampilkan jika belum ada pengaduan yang dibuat
         ======================================================================== -->
    
    <div class="row">
        <!-- ====================================================================
             LEFT COLUMN - Informasi, Stats & Tips (Sticky Sidebar)
             ==================================================================== -->
        <div class="col-lg-4 mb-4">
            <div class="pe-lg-3 info-sidebar">
                
                <!-- Page title & description -->
                <h1 class="h3 mb-2">Sistem Pengaduan Online</h1>
                <p class="text-muted mb-4">
                    Laporkan keluhan Anda dengan mudah. Kami siap membantu menyelesaikan masalah Anda.
                </p>
                
                <!-- Error alert (jika ada) -->
                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger mb-4" role="alert">
                    <?php echo $error_message; ?>
                </div>
                <?php endif; ?>
                
                <!-- Statistics Card -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="mb-3">Statistik</h6>
                        <div class="small">
                            <div class="stat-item">
                                <span class="text-muted">Total Pengaduan</span>
                                <strong class="stat-value"><?php echo number_format($stats['total']); ?></strong>
                            </div>
                            <div class="stat-item">
                                <span class="text-muted">Terselesaikan</span>
                                <strong class="stat-value success"><?php echo number_format($stats['resolved']); ?></strong>
                            </div>
                            <div class="stat-item">
                                <span class="text-muted">Dalam Proses</span>
                                <strong class="stat-value warning"><?php echo number_format($stats['in_progress']); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Info card -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="mb-3">Informasi</h6>
                        <ul class="list-unstyled mb-0 small text-muted">
                            <li class="mb-2">→ Pengaduan diproses maksimal 3x24 jam</li>
                            <li class="mb-2">→ Simpan nomor tiket untuk tracking</li>
                            <li class="mb-2">→ Bisa mengirim secara anonim</li>
                            <li class="mb-0">→ Upload bukti JPG/JPEG/MP4 (maks. 2MB)</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Tips Card -->
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-3">Tips Pengaduan Efektif</h6>
                        <ul class="list-unstyled mb-0 small text-muted">
                            <li class="mb-2">✓ Gunakan judul yang spesifik dan jelas</li>
                            <li class="mb-2">✓ Sertakan lokasi detail kejadian</li>
                            <li class="mb-2">✓ Upload foto/video sebagai bukti</li>
                            <li class="mb-0">✓ Cek status pengaduan secara berkala</li>
                        </ul>
                    </div>
                </div>
                
            </div>
        </div>
        
        <!-- ====================================================================
             RIGHT COLUMN - Form Pengaduan
             ==================================================================== -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    Form Pengaduan Baru
                </div>
                <div class="card-body">
                    <!-- ============================================================
                         FORM PENGADUAN
                         ============================================================
                         method="POST": Data dikirim via POST request
                         enctype="multipart/form-data": Required untuk file upload
                         ============================================================ -->
                    <form method="POST" action="" enctype="multipart/form-data">
                        
                        <!-- ====================================================
                             CSRF TOKEN (Hidden Field)
                             ==================================================== -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        
                        <!-- ====================================================
                             SECTION: Detail Pengaduan
                             ==================================================== -->
                        <div class="mb-4">
                            <h6 class="form-section-header">Detail Pengaduan</h6>
                            
                            <div class="row">
                                <!-- ============================================
                                     INPUT: Judul Pengaduan
                                     ============================================ -->
                                <div class="col-md-7 mb-3">
                                    <label for="judul" class="form-label">
                                        Judul Pengaduan <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="judul" 
                                           name="judul" 
                                           placeholder="Contoh: Jalan berlubang di Jl. Sudirman"
                                           value="<?php echo htmlspecialchars($_POST['judul'] ?? ''); ?>"
                                           minlength="10"
                                           maxlength="200"
                                           required>
                                    <small class="text-muted d-block mt-1">Minimal 10 karakter, maksimal 200 karakter</small>
                                </div>
                                
                                <!-- ============================================
                                     SELECT: Kategori
                                     ============================================ -->
                                <div class="col-md-5 mb-3">
                                    <label for="kategori" class="form-label">
                                        Kategori <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="kategori" name="kategori" required>
                                        <option value="">Pilih kategori</option>
                                        <option value="Infrastruktur" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Infrastruktur') ? 'selected' : ''; ?>>Infrastruktur</option>
                                        <option value="Kebersihan" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Kebersihan') ? 'selected' : ''; ?>>Kebersihan</option>
                                        <option value="Keamanan" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Keamanan') ? 'selected' : ''; ?>>Keamanan</option>
                                        <option value="Pelayanan" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Pelayanan') ? 'selected' : ''; ?>>Pelayanan</option>
                                        <option value="Lainnya" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- ================================================
                                 TEXTAREA: Deskripsi
                                 ================================================ -->
                            <div class="mb-0">
                                <label for="deskripsi" class="form-label">
                                    Deskripsi <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" 
                                          id="deskripsi" 
                                          name="deskripsi" 
                                          rows="4" 
                                          placeholder="Jelaskan masalah yang Anda laporkan secara detail..."
                                          minlength="20"
                                          maxlength="2000"
                                          required><?php echo htmlspecialchars($_POST['deskripsi'] ?? ''); ?></textarea>
                                <small class="text-muted d-block mt-1">Minimal 20 karakter, maksimal 2000 karakter</small>
                            </div>
                        </div>
                        
                        <!-- ====================================================
                             SECTION: Bukti Pendukung
                             ==================================================== -->
                        <div class="mb-4">
                            <h6 class="form-section-header">Bukti Pendukung</h6>
                            
                            <!-- ================================================
                                 FILE INPUT: Upload Bukti
                                 ================================================ -->
                            <div class="mb-0">
                                <label for="file_bukti" class="form-label">
                                    Bukti Foto/Video <span class="text-muted">(opsional)</span>
                                </label>
                                <input type="file" 
                                       class="form-control" 
                                       id="file_bukti" 
                                       name="file_bukti"
                                       accept=".jpg,.jpeg,.mp4">
                                <small class="text-muted d-block mt-1">Format: JPG, JPEG, MP4 · Maksimal 2MB</small>
                            </div>
                        </div>
                        
                        <!-- ====================================================
                             SECTION: Informasi Pelapor
                             ==================================================== -->
                        <div class="mb-4">
                            <h6 class="form-section-header">Informasi Pelapor</h6>
                            
                            <!-- ================================================
                                 CHECKBOX: Kirim Anonim
                                 ================================================ -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_anonim" 
                                           name="is_anonim"
                                           <?php echo isset($_POST['is_anonim']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_anonim">
                                        Kirim sebagai anonim
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-1">Identitas Anda tidak akan ditampilkan</small>
                            </div>
                            
                            <!-- ================================================
                                 DATA PELAPOR (Hidden jika anonim)
                                 ================================================ -->
                            <div id="dataPerlapor" style="display: <?php echo isset($_POST['is_anonim']) ? 'none' : 'block'; ?>;">
                                <div class="row">
                                    <!-- Input: Nama Lengkap -->
                                    <div class="col-md-6 mb-3">
                                        <label for="nama_pelapor" class="form-label">Nama Lengkap</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="nama_pelapor" 
                                               name="nama_pelapor" 
                                               placeholder="Nama Anda"
                                               maxlength="100"
                                               value="<?php echo htmlspecialchars($_POST['nama_pelapor'] ?? ''); ?>">
                                    </div>
                                    
                                    <!-- Input: Telepon -->
                                    <div class="col-md-6 mb-3">
                                        <label for="telepon" class="form-label">No. Telepon</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="telepon" 
                                               name="telepon" 
                                               placeholder="08xxxxxxxxxx"
                                               pattern="[0-9]{10,13}"
                                               maxlength="13"
                                               value="<?php echo htmlspecialchars($_POST['telepon'] ?? ''); ?>">
                                        <small class="text-muted d-block mt-1">10-13 digit angka</small>
                                    </div>
                                    
                                    <!-- Input: Email -->
                                    <div class="col-12 mb-0">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" 
                                               class="form-control" 
                                               id="email" 
                                               name="email" 
                                               placeholder="email@example.com"
                                               maxlength="100"
                                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- ====================================================
                             SUBMIT BUTTON
                             ==================================================== -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Kirim Pengaduan
                            </button>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
        
    </div>
    
    <?php endif; ?>
    
</div>

<!-- ============================================================================
     INCLUDE FOOTER
     ============================================================================ -->
<?php include 'includes/footer.php'; ?>

<!-- ============================================================================
     JAVASCRIPT
     ============================================================================ -->
<script>
/**
 * Toggle visibility data pelapor berdasarkan checkbox anonim
 * Jika checkbox anonim di-check, sembunyikan form data pelapor
 * Jika tidak, tampilkan form data pelapor
 */
document.getElementById('is_anonim').addEventListener('change', function() {
    // Ambil element container data pelapor
    const dataPerlapor = document.getElementById('dataPerlapor');
    
    // Toggle display based on checkbox state
    if (this.checked) {
        // Jika anonim di-check, sembunyikan form
        dataPerlapor.style.display = 'none';
        
        // Clear fields saat anonim untuk keamanan
        document.getElementById('nama_pelapor').value = '';
        document.getElementById('email').value = '';
        document.getElementById('telepon').value = '';
    } else {
        // Jika tidak, tampilkan form
        dataPerlapor.style.display = 'block';
    }
});

/**
 * Form submission dengan loading state
 * Menampilkan loading indicator saat form di-submit
 */
document.querySelector('form').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    
    // Tambah class loading untuk menampilkan spinner
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;
});
</script>