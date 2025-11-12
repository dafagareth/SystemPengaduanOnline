<?php
/**
 * Detail Pengaduan - Admin
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

$page_title = 'Detail Pengaduan';

$pengaduan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE pengaduan SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $pengaduan_id]);
        
        $success_msg = "Status pengaduan berhasil diperbarui menjadi: <strong>{$new_status}</strong>";
        
        // Refresh data
        header("Location: detail-pengaduan.php?id={$pengaduan_id}&success=1");
        exit();
    } catch (PDOException $e) {
        $error_msg = "Gagal memperbarui status: " . $e->getMessage();
    }
}

// Fetch pengaduan detail
$stmt = $pdo->prepare("SELECT * FROM pengaduan WHERE id = ?");
$stmt->execute([$pengaduan_id]);
$pengaduan = $stmt->fetch();

if (!$pengaduan) {
    header("Location: pengaduan.php");
    exit();
}

include '../includes/admin-header.php';
?>

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-2 fw-bold">
                <i class="bi bi-file-earmark-text text-primary me-2"></i>
                Detail Pengaduan
            </h2>
            <p class="text-muted mb-0">Informasi lengkap pengaduan</p>
        </div>
        <div>
            <a href="pengaduan.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>
</div>

<!-- Success Alert -->
<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>
    Status pengaduan berhasil diperbarui!
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">Informasi Pengaduan</h5>
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
                    <h4 class="mb-0 text-primary fw-bold"><?php echo htmlspecialchars($pengaduan['nomor_tiket']); ?></h4>
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
                
                <!-- File Bukti -->
                <?php if (!empty($pengaduan['file_bukti'])): ?>
                <div class="mb-4">
                    <label class="small text-muted text-uppercase fw-semibold d-block mb-2">Bukti Lampiran</label>
                    <?php 
                    $file_ext = pathinfo($pengaduan['file_bukti'], PATHINFO_EXTENSION);
                    if (in_array(strtolower($file_ext), ['jpg', 'jpeg'])): 
                    ?>
                        <img src="<?php echo BASE_URL . '/' . $pengaduan['file_bukti']; ?>" alt="Bukti" class="img-fluid rounded border" style="max-height: 400px;">
                    <?php elseif (strtolower($file_ext) == 'mp4'): ?>
                        <video controls class="w-100 rounded border" style="max-height: 400px;">
                            <source src="<?php echo BASE_URL . '/' . $pengaduan['file_bukti']; ?>" type="video/mp4">
                            Browser Anda tidak mendukung video player.
                        </video>
                    <?php endif; ?>
                    <div class="mt-2">
                        <a href="<?php echo BASE_URL . '/' . $pengaduan['file_bukti']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download me-2"></i>Download File
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <hr>
                
                <!-- Tanggal -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="small text-muted text-uppercase fw-semibold d-block mb-2">Tanggal Dibuat</label>
                        <p class="mb-0">
                            <i class="bi bi-calendar3 text-muted me-2"></i>
                            <?php echo formatTanggalIndonesia($pengaduan['tanggal_dibuat']); ?>
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="small text-muted text-uppercase fw-semibold d-block mb-2">Terakhir Diperbarui</label>
                        <p class="mb-0">
                            <i class="bi bi-clock-history text-muted me-2"></i>
                            <?php echo formatTanggalIndonesia($pengaduan['tanggal_diperbarui']); ?>
                        </p>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        
        <!-- Update Status -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-gear text-primary me-2"></i>
                    Ubah Status
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="status" class="form-label fw-semibold">Status Pengaduan</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="Menunggu" <?php echo $pengaduan['status'] == 'Menunggu' ? 'selected' : ''; ?>>🟡 Menunggu</option>
                            <option value="Diproses" <?php echo $pengaduan['status'] == 'Diproses' ? 'selected' : ''; ?>>🔵 Diproses</option>
                            <option value="Selesai" <?php echo $pengaduan['status'] == 'Selesai' ? 'selected' : ''; ?>>🟢 Selesai</option>
                            <option value="Ditolak" <?php echo $pengaduan['status'] == 'Ditolak' ? 'selected' : ''; ?>>🔴 Ditolak</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle me-2"></i>
                        Perbarui Status
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Informasi Pelapor -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person text-primary me-2"></i>
                    Informasi Pelapor
                </h6>
            </div>
            <div class="card-body">
                
                <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-semibold d-block mb-2">Nama</label>
                    <p class="mb-0"><?php echo htmlspecialchars($pengaduan['nama_pelapor']); ?></p>
                </div>
                
                <?php if (!empty($pengaduan['email'])): ?>
                <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-semibold d-block mb-2">Email</label>
                    <p class="mb-0">
                        <a href="mailto:<?php echo htmlspecialchars($pengaduan['email']); ?>" class="text-decoration-none">
                            <i class="bi bi-envelope me-2"></i><?php echo htmlspecialchars($pengaduan['email']); ?>
                        </a>
                    </p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($pengaduan['telepon'])): ?>
                <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-semibold d-block mb-2">Telepon</label>
                    <p class="mb-0">
                        <a href="tel:<?php echo htmlspecialchars($pengaduan['telepon']); ?>" class="text-decoration-none">
                            <i class="bi bi-telephone me-2"></i><?php echo htmlspecialchars($pengaduan['telepon']); ?>
                        </a>
                    </p>
                </div>
                <?php endif; ?>
                
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

<?php include '../includes/admin-footer.php'; ?>