<?php
/**
 * Halaman Cek Status Pengaduan
 * © 2025 Dafa al hafiz - 24_0085
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = 'Cek Status Pengaduan';
$pengaduan = null;
$error_message = '';
$nomor_tiket = $_GET['tiket'] ?? '';

// Jika ada nomor tiket, cari pengaduan
if (!empty($nomor_tiket)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM pengaduan WHERE nomor_tiket = ?");
        $stmt->execute([$nomor_tiket]);
        $pengaduan = $stmt->fetch();
        
        if (!$pengaduan) {
            $error_message = "Pengaduan dengan nomor tiket <strong>{$nomor_tiket}</strong> tidak ditemukan.";
        }
    } catch (PDOException $e) {
        error_log("Error cek pengaduan: " . $e->getMessage());
        $error_message = "Terjadi kesalahan saat mencari pengaduan.";
    }
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <!-- Header -->
            <div class="content-header">
                <h1>Cek Status Pengaduan</h1>
                <p>Masukkan nomor tiket untuk melihat status pengaduan Anda</p>
            </div>
            
            <!-- Form Cek -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="mb-3">
                            <label for="tiket" class="form-label">Nomor Tiket</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-ticket-perforated"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="tiket"
                                       name="tiket" 
                                       placeholder="TKT-20251109-0001" 
                                       value="<?php echo htmlspecialchars($nomor_tiket); ?>"
                                       required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-2"></i>Cek Status
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Error Message -->
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-warning" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>
            
            <!-- Detail Pengaduan -->
            <?php if ($pengaduan): ?>
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Detail Pengaduan</span>
                        <span class="badge <?php echo getStatusClass($pengaduan['status']); ?>">
                            <?php echo $pengaduan['status']; ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    
                    <!-- Nomor Tiket -->
                    <div class="mb-4">
                        <label class="form-label text-muted">Nomor Tiket</label>
                        <div class="fw-semibold"><?php echo htmlspecialchars($pengaduan['nomor_tiket']); ?></div>
                    </div>
                    
                    <!-- Judul -->
                    <div class="mb-4">
                        <label class="form-label text-muted">Judul Pengaduan</label>
                        <div class="fw-semibold"><?php echo htmlspecialchars($pengaduan['judul']); ?></div>
                    </div>
                    
                    <!-- Kategori -->
                    <div class="mb-4">
                        <label class="form-label text-muted">Kategori</label>
                        <div>
                            <span class="badge bg-light text-dark border">
                                <?php echo htmlspecialchars($pengaduan['kategori']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Deskripsi -->
                    <div class="mb-4">
                        <label class="form-label text-muted">Deskripsi</label>
                        <div class="bg-light p-3 rounded" style="white-space: pre-wrap;"><?php echo htmlspecialchars($pengaduan['deskripsi']); ?></div>
                    </div>
                    
                    <!-- Separator -->
                    <hr class="my-4">
                    
                    <!-- Informasi Pelapor -->
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label text-muted">Nama Pelapor</label>
                            <div><?php echo htmlspecialchars($pengaduan['nama_pelapor']); ?></div>
                        </div>
                        <?php if (!empty($pengaduan['email'])): ?>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label text-muted">Email</label>
                            <div><?php echo htmlspecialchars($pengaduan['email']); ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($pengaduan['telepon'])): ?>
                        <div class="col-md-4">
                            <label class="form-label text-muted">Telepon</label>
                            <div><?php echo htmlspecialchars($pengaduan['telepon']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Separator -->
                    <hr class="my-4">
                    
                    <!-- Tanggal -->
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label text-muted">Tanggal Dibuat</label>
                            <div>
                                <i class="bi bi-calendar3 me-2"></i>
                                <?php echo formatTanggalIndonesia($pengaduan['tanggal_dibuat']); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Terakhir Diperbarui</label>
                            <div>
                                <i class="bi bi-clock-history me-2"></i>
                                <?php echo formatTanggalIndonesia($pengaduan['tanggal_diperbarui']); ?>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="card-body border-top">
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="daftar-pengaduan.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar
                        </a>
                        <a href="cek-pengaduan.php" class="btn btn-outline-primary">
                            <i class="bi bi-search me-2"></i>Cek Pengaduan Lain
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Info Box -->
            <?php if (!$pengaduan && empty($nomor_tiket)): ?>
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="mb-3">Cara Cek Status Pengaduan</h6>
                    <ol class="mb-0" style="padding-left: 1.25rem;">
                        <li class="mb-2">Masukkan nomor tiket yang Anda terima saat membuat pengaduan</li>
                        <li class="mb-2">Klik tombol "Cek Status"</li>
                        <li class="mb-2">Sistem akan menampilkan detail dan status pengaduan Anda</li>
                        <li>Jika lupa nomor tiket, Anda dapat melihatnya di daftar pengaduan</li>
                    </ol>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
