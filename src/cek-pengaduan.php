<?php
/**
 * ============================================================================
 * HALAMAN CEK STATUS PENGADUAN
 * ============================================================================
 * File: cek-pengaduan.php
 * Deskripsi: Halaman untuk mengecek status & detail pengaduan berdasarkan nomor tiket
 * Fitur:
 * - Form input nomor tiket
 * - Tampilkan detail lengkap pengaduan
 * - Status badge dengan warna
 * - Info pelapor & timestamp
 * Author: Dafa al hafiz - 24_0085
 * Tanggal: 2025
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
$page_title = 'Cek Status Pengaduan';    // Title halaman (untuk <title> tag)
$pengaduan = null;                        // Menyimpan data pengaduan (null = belum ada)
$error_message = '';                      // Pesan error jika tiket tidak ditemukan

// Ambil nomor tiket dari URL parameter
// Contoh URL: cek-pengaduan.php?tiket=TKT-20251109-0001
$nomor_tiket = $_GET['tiket'] ?? '';

// ============================================================================
// PROSES PENCARIAN PENGADUAN
// ============================================================================
// Cek jika nomor tiket diisi (tidak kosong)
if (!empty($nomor_tiket)) {
    try {
        // ====================================================================
        // QUERY: Cari pengaduan berdasarkan nomor tiket
        // ====================================================================
        // SELECT * : Ambil semua kolom
        // WHERE nomor_tiket = ? : Filter by nomor tiket (prepared statement)
        $stmt = $pdo->prepare("SELECT * FROM pengaduan WHERE nomor_tiket = ?");
        $stmt->execute([$nomor_tiket]);
        
        // Fetch single row (karena nomor_tiket unique, max 1 result)
        $pengaduan = $stmt->fetch();
        
        // ====================================================================
        // ERROR HANDLING: Tiket tidak ditemukan
        // ====================================================================
        if (!$pengaduan) {
            // Set error message jika query tidak return data
            // <strong> tag untuk highlight nomor tiket di pesan error
            $error_message = "Pengaduan dengan nomor tiket <strong>{$nomor_tiket}</strong> tidak ditemukan.";
        }
        
    } catch (PDOException $e) {
        // ====================================================================
        // ERROR HANDLING: Database error
        // ====================================================================
        // Log error ke PHP error log (jangan tampilkan detail error ke user)
        error_log("Error cek pengaduan: " . $e->getMessage());
        
        // Set generic error message untuk user
        $error_message = "Terjadi kesalahan saat mencari pengaduan.";
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
<div class="container my-5">
    <div class="row justify-content-center">
        <!-- Centered column (max width 8/12 di large screen) -->
        <div class="col-lg-8">
            
            <!-- ================================================================
                 PAGE HEADER
                 ================================================================ -->
            <div class="content-header">
                <h1>Cek Status Pengaduan</h1>
                <p>Masukkan nomor tiket untuk melihat status pengaduan Anda</p>
            </div>
            
            <!-- ================================================================
                 FORM: Cek Nomor Tiket
                 ================================================================
                 Form untuk input nomor tiket yang ingin dicek
                 ================================================================ -->
            <div class="card mb-4">
                <div class="card-body">
                    <!-- Form dengan method GET -->
                    <!-- GET: Parameter dikirim via URL (bisa di-share/bookmark) -->
                    <form method="GET" action="">
                        <div class="mb-3">
                            <label for="tiket" class="form-label">Nomor Tiket</label>
                            
                            <!-- Input group: Input dengan icon di sebelah kiri -->
                            <div class="input-group">
                                <!-- Icon ticket -->
                                <span class="input-group-text">
                                    <i class="bi bi-ticket-perforated"></i>
                                </span>
                                
                                <!-- Input nomor tiket -->
                                <input type="text" 
                                       class="form-control" 
                                       id="tiket"
                                       name="tiket" 
                                       placeholder="TKT-20251109-0001" 
                                       value="<?php echo htmlspecialchars($nomor_tiket); ?>"
                                       required>
                            </div>
                        </div>
                        
                        <!-- Submit button -->
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-2"></i>Cek Status
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- ================================================================
                 ERROR MESSAGE
                 ================================================================
                 Alert warning jika tiket tidak ditemukan
                 ================================================================ -->
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-warning" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <!-- echo dengan tag HTML karena $error_message berisi <strong> -->
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>
            
            <!-- ================================================================
                 DETAIL PENGADUAN
                 ================================================================
                 Card menampilkan detail lengkap pengaduan (jika ditemukan)
                 ================================================================ -->
            <?php if ($pengaduan): ?>
            <div class="card">
                <!-- ============================================================
                     CARD HEADER: Judul & Status Badge
                     ============================================================ -->
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Detail Pengaduan</span>
                        
                        <!-- Status badge dengan warna sesuai status -->
                        <!-- getStatusClass() return CSS class (bg-warning/success/etc) -->
                        <span class="badge <?php echo getStatusClass($pengaduan['status']); ?>">
                            <?php echo $pengaduan['status']; ?>
                        </span>
                    </div>
                </div>
                
                <!-- ============================================================
                     CARD BODY: Detail Data Pengaduan
                     ============================================================ -->
                <div class="card-body">
                    
                    <!-- ========================================================
                         FIELD: Nomor Tiket
                         ======================================================== -->
                    <div class="mb-4">
                        <label class="form-label text-muted">Nomor Tiket</label>
                        <div class="fw-semibold">
                            <?php echo htmlspecialchars($pengaduan['nomor_tiket']); ?>
                        </div>
                    </div>
                    
                    <!-- ========================================================
                         FIELD: Judul Pengaduan
                         ======================================================== -->
                    <div class="mb-4">
                        <label class="form-label text-muted">Judul Pengaduan</label>
                        <div class="fw-semibold">
                            <?php echo htmlspecialchars($pengaduan['judul']); ?>
                        </div>
                    </div>
                    
                    <!-- ========================================================
                         FIELD: Kategori
                         ======================================================== -->
                    <div class="mb-4">
                        <label class="form-label text-muted">Kategori</label>
                        <div>
                            <!-- Badge untuk kategori -->
                            <span class="badge bg-light text-dark border">
                                <?php echo htmlspecialchars($pengaduan['kategori']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- ========================================================
                         FIELD: Deskripsi
                         ======================================================== -->
                    <div class="mb-4">
                        <label class="form-label text-muted">Deskripsi</label>
                        <!-- Background abu-abu untuk deskripsi -->
                        <!-- white-space: pre-wrap: Preserve line breaks dari textarea -->
                        <div class="bg-light p-3 rounded" style="white-space: pre-wrap;">
                            <?php echo htmlspecialchars($pengaduan['deskripsi']); ?>
                        </div>
                    </div>
                    
                    <!-- Separator line -->
                    <hr class="my-4">
                    
                    <!-- ========================================================
                         SECTION: Informasi Pelapor
                         ========================================================
                         Tampilkan nama, email, telepon (jika ada)
                         ======================================================== -->
                    <div class="row mb-4">
                        
                        <!-- Nama Pelapor (selalu ada) -->
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label text-muted">Nama Pelapor</label>
                            <div><?php echo htmlspecialchars($pengaduan['nama_pelapor']); ?></div>
                        </div>
                        
                        <!-- Email (hanya tampil jika ada) -->
                        <?php if (!empty($pengaduan['email'])): ?>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label text-muted">Email</label>
                            <div><?php echo htmlspecialchars($pengaduan['email']); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Telepon (hanya tampil jika ada) -->
                        <?php if (!empty($pengaduan['telepon'])): ?>
                        <div class="col-md-4">
                            <label class="form-label text-muted">Telepon</label>
                            <div><?php echo htmlspecialchars($pengaduan['telepon']); ?></div>
                        </div>
                        <?php endif; ?>
                        
                    </div>
                    
                    <!-- Separator line -->
                    <hr class="my-4">
                    
                    <!-- ========================================================
                         SECTION: Timestamp Info
                         ========================================================
                         Tanggal dibuat & terakhir diperbarui
                         ======================================================== -->
                    <div class="row">
                        
                        <!-- Tanggal Dibuat -->
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label text-muted">Tanggal Dibuat</label>
                            <div>
                                <!-- Icon calendar -->
                                <i class="bi bi-calendar3 me-2"></i>
                                <!-- Format tanggal ke format Indonesia readable -->
                                <?php echo formatTanggalIndonesia($pengaduan['tanggal_dibuat']); ?>
                            </div>
                        </div>
                        
                        <!-- Tanggal Diperbarui -->
                        <div class="col-md-6">
                            <label class="form-label text-muted">Terakhir Diperbarui</label>
                            <div>
                                <!-- Icon clock -->
                                <i class="bi bi-clock-history me-2"></i>
                                <!-- Format tanggal ke format Indonesia readable -->
                                <?php echo formatTanggalIndonesia($pengaduan['tanggal_diperbarui']); ?>
                            </div>
                        </div>
                        
                    </div>
                    
                </div>
                
                <!-- ============================================================
                     CARD FOOTER: Action Buttons
                     ============================================================ -->
                <div class="card-body border-top">
                    <div class="d-flex gap-2 flex-wrap">
                        
                        <!-- Button: Kembali ke Daftar Pengaduan -->
                        <a href="daftar-pengaduan.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar
                        </a>
                        
                        <!-- Button: Cek Pengaduan Lain -->
                        <!-- Link ke halaman ini tanpa parameter (reset form) -->
                        <a href="cek-pengaduan.php" class="btn btn-outline-primary">
                            <i class="bi bi-search me-2"></i>Cek Pengaduan Lain
                        </a>
                        
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- ================================================================
                 INFO BOX: Cara Cek Status
                 ================================================================
                 Tampil hanya jika belum ada pengaduan yang dicek
                 ================================================================ -->
            <?php if (!$pengaduan && empty($nomor_tiket)): ?>
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="mb-3">Cara Cek Status Pengaduan</h6>
                    
                    <!-- Ordered list: Langkah-langkah -->
                    <ol class="mb-0" style="padding-left: 1.25rem;">
                        <li class="mb-2">
                            Masukkan nomor tiket yang Anda terima saat membuat pengaduan
                        </li>
                        <li class="mb-2">
                            Klik tombol "Cek Status"
                        </li>
                        <li class="mb-2">
                            Sistem akan menampilkan detail dan status pengaduan Anda
                        </li>
                        <li>
                            Jika lupa nomor tiket, Anda dapat melihatnya di daftar pengaduan
                        </li>
                    </ol>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<!-- ============================================================================
     INCLUDE FOOTER
     ============================================================================ -->
<?php include 'includes/footer.php'; ?>