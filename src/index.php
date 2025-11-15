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
 * Author: Dafa al hafiz - 24_0085
 * ============================================================================
 */

// ============================================================================
// INCLUDE FILE DEPENDENCIES
// ============================================================================
require_once 'includes/config.php';      // Konfigurasi database & konstanta
require_once 'includes/functions.php';   // Helper functions

// ============================================================================
// INISIALISASI VARIABEL
// ============================================================================
$page_title = 'Buat Pengaduan';          // Title halaman (untuk <title> tag)
$success_message = '';                    // Pesan sukses setelah submit
$error_message = '';                      // Pesan error jika ada
$nomor_tiket_baru = '';                  // Menyimpan nomor tiket yang ter-generate

// ============================================================================
// PROSES FORM SUBMISSION
// ============================================================================
// Cek apakah form di-submit dengan method POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ========================================================================
    // 1. AMBIL & SANITIZE INPUT DATA
    // ========================================================================
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
    
    // ========================================================================
    // 2. VALIDASI INPUT
    // ========================================================================
    // Array untuk menyimpan pesan error
    $errors = [];
    
    // Validasi: Judul harus diisi
    if (empty($judul)) {
        $errors[] = "Judul pengaduan harus diisi";
    }
    
    // Validasi: Deskripsi harus diisi
    if (empty($deskripsi)) {
        $errors[] = "Deskripsi pengaduan harus diisi";
    }
    
    // Validasi: Kategori harus dipilih
    if (empty($kategori)) {
        $errors[] = "Kategori harus dipilih";
    }
    
    // Validasi: Email (hanya jika tidak anonim dan email diisi)
    if (!$is_anonim && !empty($email) && !validateEmail($email)) {
        $errors[] = "Format email tidak valid";
    }
    
    // ========================================================================
    // 3. HANDLE FILE UPLOAD (Bukti Foto/Video)
    // ========================================================================
    $file_bukti = null;
    
    // Cek apakah ada file yang di-upload
    // UPLOAD_ERR_OK = 0, artinya upload sukses tanpa error
    if (isset($_FILES['file_bukti']) && $_FILES['file_bukti']['error'] === UPLOAD_ERR_OK) {
        
        // Tipe file yang diperbolehkan
        $allowed_types = ['image/jpeg', 'image/jpg', 'video/mp4'];
        
        // Ambil informasi file
        $file_type = $_FILES['file_bukti']['type'];    // MIME type
        $file_size = $_FILES['file_bukti']['size'];    // Size in bytes
        
        // Maksimal ukuran file: 2MB
        $max_size = 2 * 1024 * 1024; // 2MB dalam bytes
        
        // Validasi tipe file
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Format file harus JPG, JPEG, atau MP4";
        } 
        // Validasi ukuran file
        elseif ($file_size > $max_size) {
            $errors[] = "Ukuran file maksimal 2MB";
        } 
        // Jika validasi lolos, proses upload
        else {
            // Buat folder uploads jika belum ada
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) {
                // mkdir: Create directory
                // 0755: Permission (owner: rwx, group: rx, other: rx)
                // true: Create parent directories if needed
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate nama file unik untuk menghindari konflik
            // Format: {unique_id}_{timestamp}.{extension}
            // pathinfo: Ekstrak informasi dari path file
            $file_extension = pathinfo($_FILES['file_bukti']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '_' . time() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;
            
            // Pindahkan file dari temporary location ke uploads folder
            // move_uploaded_file: Fungsi PHP untuk memindahkan uploaded file
            if (move_uploaded_file($_FILES['file_bukti']['tmp_name'], $file_path)) {
                // Simpan path relatif (untuk disimpan di database)
                $file_bukti = 'uploads/' . $file_name;
            } else {
                $errors[] = "Gagal mengupload file";
            }
        }
    }
    
    // ========================================================================
    // 4. PROSES PENYIMPANAN KE DATABASE
    // ========================================================================
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
            
            // Set success message
            $success_message = "Pengaduan berhasil dibuat dengan nomor tiket: {$nomor_tiket_baru}";
            
            // Reset form data (clear $_POST)
            // Agar form kosong setelah submit sukses
            $_POST = [];
            
        } catch (PDOException $e) {
            // Catch database error
            // Pada production, sebaiknya log ke file, jangan tampilkan detail error
            $error_message = "Gagal menyimpan pengaduan: " . $e->getMessage();
        }
    } else {
        // Jika ada error validasi, gabungkan semua pesan error
        // implode: Gabungkan array menjadi string dengan separator
        $error_message = implode("<br>", $errors);
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
                        <a href="cek-pengaduan.php?tiket=<?php echo $nomor_tiket_baru; ?>" class="btn btn-primary">
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
             LEFT COLUMN - Informasi & Alert
             ==================================================================== -->
        <div class="col-lg-4 mb-4">
            <div class="pe-lg-3">
                
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
                        
                        <div class="row">
                            <!-- ================================================
                                 INPUT: Judul Pengaduan
                                 ================================================ -->
                            <div class="col-md-7 mb-3">
                                <label for="judul" class="form-label">
                                    Judul Pengaduan <span class="text-muted">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="judul" 
                                       name="judul" 
                                       placeholder="Contoh: Jalan berlubang di Jl. Sudirman"
                                       value="<?php echo htmlspecialchars($_POST['judul'] ?? ''); ?>"
                                       required>
                            </div>
                            
                            <!-- ================================================
                                 SELECT: Kategori
                                 ================================================ -->
                            <div class="col-md-5 mb-3">
                                <label for="kategori" class="form-label">
                                    Kategori <span class="text-muted">*</span>
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
                        
                        <!-- ====================================================
                             TEXTAREA: Deskripsi
                             ==================================================== -->
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">
                                Deskripsi <span class="text-muted">*</span>
                            </label>
                            <textarea class="form-control" 
                                      id="deskripsi" 
                                      name="deskripsi" 
                                      rows="4" 
                                      placeholder="Jelaskan masalah yang Anda laporkan secara detail..."
                                      required><?php echo htmlspecialchars($_POST['deskripsi'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- ====================================================
                             FILE INPUT: Upload Bukti
                             ==================================================== -->
                        <div class="mb-3">
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
                        
                        <!-- Divider -->
                        <hr class="my-4">
                        
                        <!-- ====================================================
                             CHECKBOX: Kirim Anonim
                             ==================================================== -->
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
                        
                        <!-- ====================================================
                             SECTION: Data Pelapor (Hidden jika anonim)
                             ==================================================== -->
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
                                           value="<?php echo htmlspecialchars($_POST['telepon'] ?? ''); ?>">
                                </div>
                                
                                <!-- Input: Email -->
                                <div class="col-12 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email" 
                                           name="email" 
                                           placeholder="email@example.com"
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
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
    } else {
        // Jika tidak, tampilkan form
        dataPerlapor.style.display = 'block';
    }
});
</script>