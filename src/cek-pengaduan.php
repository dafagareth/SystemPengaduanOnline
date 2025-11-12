<?php
/**
 * Halaman Cek Status Pengaduan
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
        $error_message = "Terjadi kesalahan saat mencari pengaduan.";
    }
}

include 'includes/header.php';
?>

<div class="d-flex flex-column min-vh-100 bg-light">
    <div class="container my-5 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <!-- Header -->
                <div class="text-center mb-4">
                    <h2 class="fw-bold">
                        <i class="bi bi-search text-primary me-2"></i>
                        Cek Status Pengaduan
                    </h2>
                    <p class="text-muted">Masukkan nomor tiket untuk melihat status pengaduan Anda</p>
                </div>
                
                <!-- Form Cek -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <form method="GET" action="">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-ticket-perforated text-primary"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       name="tiket" 
                                       placeholder="Masukkan nomor tiket (contoh: TKT-20251109-0001)" 
                                       value="<?php echo htmlspecialchars($nomor_tiket); ?>"
                                       required>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-search me-2"></i>Cek Status
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Error Message -->
                <?php if (!empty($error_message)): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <!-- Detail Pengaduan -->
                <?php if ($pengaduan): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-semibold">Detail Pengaduan</h5>
                            <span class="badge <?php echo getStatusClass($pengaduan['status']); ?> px-3 py-2">
                                <i class="bi <?php echo getStatusIcon($pengaduan['status']); ?> me-1"></i>
                                <?php echo $pengaduan['status']; ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- Nomor Tiket -->
                        <div class="mb-4">
                            <label class="small text-muted text-uppercase fw-semibold d-block mb-2">Nomor Tiket</label>
                            <div class="d-flex align-items-center">
                                <h4 class="mb-0 text-primary fw-bold"><?php echo htmlspecialchars($pengaduan['nomor_tiket']); ?></h4>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <!-- Judul -->
                        <div class="mb-4">
                            <label class="small text-muted text-uppercase fw-semibold d-block mb-2">Judul Pengaduan</label>
                            <h5 class="mb-0"><?php echo htmlspecialchars($pengaduan['judul']); ?></h5>
                        </div>
                        
                        <!-- Kategori -->
                        <div class="mb-4">
                            <label class="small text-muted text-uppercase fw-semibold d-block mb-2">Kategori</label>
                            <span class="badge bg-light text-dark border px-3 py-2">
                                <?php echo htmlspecialchars($pengaduan['kategori']); ?>
                            </span>
                        </div>
                        
                        <!-- Deskripsi -->
                        <div class="mb-4">
                            <label class="small text-muted text-uppercase fw-semibold d-block mb-2">Deskripsi</label>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-0" style="white-space: pre-wrap;"><?php echo htmlspecialchars($pengaduan['deskripsi']); ?></p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <!-- Informasi Pelapor -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="small text-muted text-uppercase fw-semibold d-block mb-2">Nama Pelapor</label>
                                <p class="mb-0"><?php echo htmlspecialchars($pengaduan['nama_pelapor']); ?></p>
                            </div>
                            <?php if (!empty($pengaduan['email'])): ?>
                            <div class="col-md-4">
                                <label class="small text-muted text-uppercase fw-semibold d-block mb-2">Email</label>
                                <p class="mb-0"><?php echo htmlspecialchars($pengaduan['email']); ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($pengaduan['telepon'])): ?>
                            <div class="col-md-4">
                                <label class="small text-muted text-uppercase fw-semibold d-block mb-2">Telepon</label>
                                <p class="mb-0"><?php echo htmlspecialchars($pengaduan['telepon']); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <hr>
                        
                        <!-- Tanggal -->
                        <div class="row">
                            <div class="col-md-6">
                                <label class="small text-muted text-uppercase fw-semibold d-block mb-2">Tanggal Dibuat</label>
                                <p class="mb-0">
                                    <i class="bi bi-calendar3 text-muted me-2"></i>
                                    <?php echo formatTanggalIndonesia($pengaduan['tanggal_dibuat']); ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted text-uppercase fw-semibold d-block mb-2">Terakhir Diperbarui</label>
                                <p class="mb-0">
                                    <i class="bi bi-clock-history text-muted me-2"></i>
                                    <?php echo formatTanggalIndonesia($pengaduan['tanggal_diperbarui']); ?>
                                </p>
                            </div>
                        </div>
                        
                    </div>
                    <div class="card-footer bg-light border-top py-3">
                        <div class="d-flex gap-2">
                            <a href="daftar-pengaduan.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar
                            </a>
                            <a href="cek-pengaduan.php" class="btn btn-primary">
                                <i class="bi bi-search me-2"></i>Cek Pengaduan Lain
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Info Box -->
                <?php if (!$pengaduan && empty($nomor_tiket)): ?>
                <div class="card border-0 bg-light">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-info-circle text-primary" style="font-size: 64px;"></i>
                        <h5 class="mt-4 mb-3">Cara Cek Status Pengaduan</h5>
                        <div class="text-start mx-auto" style="max-width: 500px;">
                            <ol class="ps-3">
                                <li class="mb-2">Masukkan nomor tiket yang Anda terima saat membuat pengaduan</li>
                                <li class="mb-2">Klik tombol "Cek Status"</li>
                                <li class="mb-2">Sistem akan menampilkan detail dan status pengaduan Anda</li>
                                <li>Jika lupa nomor tiket, Anda dapat melihatnya di daftar pengaduan</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>