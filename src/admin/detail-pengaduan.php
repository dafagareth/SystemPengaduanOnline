<?php
/**
 * ============================================================================
 * ADMIN DETAIL PENGADUAN
 * ============================================================================
 * File: admin/detail-pengaduan.php
 * Deskripsi: Halaman detail pengaduan dengan fitur update status
 * Fitur:
 * - Tampil detail lengkap pengaduan
 * - Form update status (Menunggu/Diproses/Selesai/Ditolak)
 * - Tampil file bukti (foto/video)
 * - Info pelapor
 * - Timestamp (dibuat & diperbarui)
 * Author: Dafa al hafiz - 24_0085
 * Tanggal: 2025
 * ============================================================================
 */

// ============================================================================
// INCLUDE FILE DEPENDENCIES
// ============================================================================
require_once '../includes/config.php';      // Konfigurasi database & konstanta
require_once '../includes/functions.php';   // Helper functions

// ============================================================================
// INISIALISASI VARIABEL
// ============================================================================
$page_title = 'Detail Pengaduan';           // Title halaman

// Ambil ID pengaduan dari URL parameter
// (int) untuk type casting, pastikan nilai integer
$pengaduan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ============================================================================
// HANDLE STATUS UPDATE (POST Request)
// ============================================================================
// Cek jika form update status di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    
    // Ambil status baru dari form
    $new_status = $_POST['status'];
    
    try {
        // ====================================================================
        // UPDATE STATUS DI DATABASE
        // ====================================================================
        // Prepared statement untuk update status
        // WHERE id = ?: Update hanya row dengan ID yang sesuai
        $stmt = $pdo->prepare("UPDATE pengaduan SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $pengaduan_id]);
        
        // Set success message (tidak digunakan karena redirect)
        $success_msg = "Status pengaduan berhasil diperbarui menjadi: <strong>{$new_status}</strong>";
        
        // ====================================================================
        // REDIRECT DENGAN SUCCESS FLAG
        // ====================================================================
        // Redirect ke halaman yang sama dengan parameter success=1
        // PRG Pattern (Post-Redirect-Get): Prevent form resubmission
        header("Location: detail-pengaduan.php?id={$pengaduan_id}&success=1");
        exit();
        
    } catch (PDOException $e) {
        // Error handling: Set error message
        $error_msg = "Gagal memperbarui status: " . $e->getMessage();
    }
}

// ============================================================================
// FETCH PENGADUAN DETAIL
// ============================================================================
// Query untuk ambil detail pengaduan berdasarkan ID
$stmt = $pdo->prepare("SELECT * FROM pengaduan WHERE id = ?");
$stmt->execute([$pengaduan_id]);
$pengaduan = $stmt->fetch();

// ============================================================================
// VALIDATION: Pengaduan Exists?
// ============================================================================
// Jika pengaduan tidak ditemukan, redirect ke halaman kelola pengaduan
if (!$pengaduan) {
    header("Location: pengaduan.php");
    exit();
}

// ============================================================================
// INCLUDE ADMIN HEADER
// ============================================================================
include '../includes/admin-header.php';
?>

<!-- ============================================================================
     PAGE HEADER
     ============================================================================ -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <!-- Left: Title & Description -->
        <div>
            <h2 class="mb-2 fw-bold">
                <i class="bi bi-file-earmark-text text-primary me-2"></i>
                Detail Pengaduan
            </h2>
            <p class="text-muted mb-0">Informasi lengkap pengaduan</p>
        </div>
        
        <!-- Right: Back Button -->
        <div>
            <a href="pengaduan.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>
</div>

<!-- ============================================================================
     SUCCESS ALERT
     ============================================================================
     Tampil hanya jika ada parameter success=1 di URL
     ============================================================================ -->
<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>
    Status pengaduan berhasil diperbarui!
    <!-- Close button -->
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- ============================================================================
     MAIN CONTENT LAYOUT
     ============================================================================
     2 kolom layout: Main content (8 cols) & Sidebar (4 cols)
     ============================================================================ -->
<div class="row">
    
    <!-- ========================================================================
         MAIN CONTENT: Detail Pengaduan
         ======================================================================== -->
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm">
            
            <!-- ================================================================
                 CARD HEADER: Title & Status Badge
                 ================================================================ -->
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">Informasi Pengaduan</h5>
                    
                    <!-- Status badge dengan warna & icon -->
                    <span class="badge <?php echo getStatusClass($pengaduan['status']); ?> px-3 py-2">
                        <i class="bi <?php echo getStatusIcon($pengaduan['status']); ?> me-1"></i>
                        <?php echo $pengaduan['status']; ?>
                    </span>
                </div>
            </div>
            
            <!-- ================================================================
                 CARD BODY: Detail Information
                 ================================================================ -->
            <div class="card-body p-4">
                
                <!-- ============================================================
                     FIELD: Nomor Tiket
                     ============================================================ -->
                <div class="mb-4">
                    <label class="small text-muted text-uppercase fw-semibold d-block mb-2">
                        Nomor Tiket
                    </label>
                    <h4 class="mb-0 text-primary fw-bold">
                        <?php echo htmlspecialchars($pengaduan['nomor_tiket']); ?>
                    </h4>
                </div>
                
                <hr>
                
                <!-- ============================================================
                     FIELD: Judul Pengaduan
                     ============================================================ -->
                <div class="mb-4">
                    <label class="small text-muted text-uppercase fw-semibold d-block mb-2">
                        Judul Pengaduan
                    </label>
                    <h5 class="mb-0"><?php echo htmlspecialchars($pengaduan['judul']); ?></h5>
                </div>
                
                <!-- ============================================================
                     FIELD: Kategori
                     ============================================================ -->
                <div class="mb-4">
                    <label class="small text-muted text-uppercase fw-semibold d-block mb-2">
                        Kategori
                    </label>
                    <span class="badge bg-light text-dark border px-3 py-2">
                        <?php echo htmlspecialchars($pengaduan['kategori']); ?>
                    </span>
                </div>
                
                <!-- ============================================================
                     FIELD: Deskripsi
                     ============================================================ -->
                <div class="mb-4">
                    <label class="small text-muted text-uppercase fw-semibold d-block mb-2">
                        Deskripsi
                    </label>
                    <div class="bg-light p-3 rounded">
                        <!-- white-space: pre-wrap: Preserve line breaks -->
                        <p class="mb-0" style="white-space: pre-wrap;">
                            <?php echo htmlspecialchars($pengaduan['deskripsi']); ?>
                        </p>
                    </div>
                </div>
                
                <!-- ============================================================
                     FIELD: File Bukti (Optional)
                     ============================================================
                     Tampil hanya jika ada file bukti
                     ============================================================ -->
                <?php if (!empty($pengaduan['file_bukti'])): ?>
                <div class="mb-4">
                    <label class="small text-muted text-uppercase fw-semibold d-block mb-2">
                        Bukti Lampiran
                    </label>
                    
                    <?php 
                    /**
                     * ========================================================
                     * DETECT FILE TYPE
                     * ========================================================
                     * Cek extension file untuk tentukan cara display
                     * - JPG/JPEG: Display sebagai image
                     * - MP4: Display sebagai video player
                     * ========================================================
                     */
                    $file_ext = pathinfo($pengaduan['file_bukti'], PATHINFO_EXTENSION);
                    
                    // Jika file adalah gambar (JPG/JPEG)
                    if (in_array(strtolower($file_ext), ['jpg', 'jpeg'])): 
                    ?>
                        <!-- Display image -->
                        <img src="<?php echo BASE_URL . '/' . $pengaduan['file_bukti']; ?>" 
                             alt="Bukti" 
                             class="img-fluid rounded border" 
                             style="max-height: 400px;">
                    
                    <?php 
                    // Jika file adalah video (MP4)
                    elseif (strtolower($file_ext) == 'mp4'): 
                    ?>
                        <!-- Display video player -->
                        <video controls 
                               class="w-100 rounded border" 
                               style="max-height: 400px;">
                            <source src="<?php echo BASE_URL . '/' . $pengaduan['file_bukti']; ?>" 
                                    type="video/mp4">
                            Browser Anda tidak mendukung video player.
                        </video>
                    <?php endif; ?>
                    
                    <!-- Download button -->
                    <div class="mt-2">
                        <a href="<?php echo BASE_URL . '/' . $pengaduan['file_bukti']; ?>" 
                           target="_blank" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download me-2"></i>Download File
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <hr>
                
                <!-- ============================================================
                     SECTION: Timestamp Information
                     ============================================================ -->
                <div class="row">
                    <!-- Tanggal Dibuat -->
                    <div class="col-md-6 mb-3">
                        <label class="small text-muted text-uppercase fw-semibold d-block mb-2">
                            Tanggal Dibuat
                        </label>
                        <p class="mb-0">
                            <i class="bi bi-calendar3 text-muted me-2"></i>
                            <?php echo formatTanggalIndonesia($pengaduan['tanggal_dibuat']); ?>
                        </p>
                    </div>
                    
                    <!-- Terakhir Diperbarui -->
                    <div class="col-md-6 mb-3">
                        <label class="small text-muted text-uppercase fw-semibold d-block mb-2">
                            Terakhir Diperbarui
                        </label>
                        <p class="mb-0">
                            <i class="bi bi-clock-history text-muted me-2"></i>
                            <?php echo formatTanggalIndonesia($pengaduan['tanggal_diperbarui']); ?>
                        </p>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    <!-- ========================================================================
         SIDEBAR: Update Status & Info Pelapor
         ======================================================================== -->
    <div class="col-lg-4">
        
        <!-- ================================================================
             CARD 1: Update Status
             ================================================================ -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-gear text-primary me-2"></i>
                    Ubah Status
                </h6>
            </div>
            <div class="card-body">
                <!-- ========================================================
                     FORM: Update Status
                     ======================================================== -->
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="status" class="form-label fw-semibold">
                            Status Pengaduan
                        </label>
                        
                        <!-- Select dropdown untuk pilih status baru -->
                        <select name="status" id="status" class="form-select" required>
                            <!-- Option: Menunggu -->
                            <option value="Menunggu" <?php echo $pengaduan['status'] == 'Menunggu' ? 'selected' : ''; ?>>
                                🟡 Menunggu
                            </option>
                            
                            <!-- Option: Diproses -->
                            <option value="Diproses" <?php echo $pengaduan['status'] == 'Diproses' ? 'selected' : ''; ?>>
                                🔵 Diproses
                            </option>
                            
                            <!-- Option: Selesai -->
                            <option value="Selesai" <?php echo $pengaduan['status'] == 'Selesai' ? 'selected' : ''; ?>>
                                🟢 Selesai
                            </option>
                            
                            <!-- Option: Ditolak -->
                            <option value="Ditolak" <?php echo $pengaduan['status'] == 'Ditolak' ? 'selected' : ''; ?>>
                                🔴 Ditolak
                            </option>
                        </select>
                    </div>
                    
                    <!-- Submit button -->
                    <button type="submit" name="update_status" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle me-2"></i>
                        Perbarui Status
                    </button>
                </form>
            </div>
        </div>
        
        <!-- ================================================================
             CARD 2: Informasi Pelapor
             ================================================================ -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person text-primary me-2"></i>
                    Informasi Pelapor
                </h6>
            </div>
            <div class="card-body">
                
                <!-- ========================================================
                     FIELD: Nama Pelapor (Always shown)
                     ======================================================== -->
                <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-semibold d-block mb-2">
                        Nama
                    </label>
                    <p class="mb-0"><?php echo htmlspecialchars($pengaduan['nama_pelapor']); ?></p>
                </div>
                
                <!-- ========================================================
                     FIELD: Email (Optional)
                     ========================================================
                     Tampil hanya jika email tidak kosong
                     ======================================================== -->
                <?php if (!empty($pengaduan['email'])): ?>
                <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-semibold d-block mb-2">
                        Email
                    </label>
                    <p class="mb-0">
                        <!-- Mailto link -->
                        <a href="mailto:<?php echo htmlspecialchars($pengaduan['email']); ?>" 
                           class="text-decoration-none">
                            <i class="bi bi-envelope me-2"></i>
                            <?php echo htmlspecialchars($pengaduan['email']); ?>
                        </a>
                    </p>
                </div>
                <?php endif; ?>
                
                <!-- ========================================================
                     FIELD: Telepon (Optional)
                     ========================================================
                     Tampil hanya jika telepon tidak kosong
                     ======================================================== -->
                <?php if (!empty($pengaduan['telepon'])): ?>
                <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-semibold d-block mb-2">
                        Telepon
                    </label>
                    <p class="mb-0">
                        <!-- Tel link (untuk call langsung di mobile) -->
                        <a href="tel:<?php echo htmlspecialchars($pengaduan['telepon']); ?>" 
                           class="text-decoration-none">
                            <i class="bi bi-telephone me-2"></i>
                            <?php echo htmlspecialchars($pengaduan['telepon']); ?>
                        </a>
                    </p>
                </div>
                <?php endif; ?>
                
                <!-- ========================================================
                     ALERT: Pengaduan Anonim
                     ========================================================
                     Tampil hanya jika nama pelapor = "Anonim"
                     ======================================================== -->
                <?php if ($pengaduan['nama_pelapor'] === 'Anonim'): ?>
                <div class="alert alert-info mb-0">
                    <small>
                        <i class="bi bi-info-circle me-2"></i>
                        Pengaduan ini dikirim secara anonim
                    </small>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
        
    </div>
</div>

<!-- ============================================================================
     INCLUDE ADMIN FOOTER
     ============================================================================ -->
<?php include '../includes/admin-footer.php'; ?>