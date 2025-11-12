<?php
/**
 * Halaman Utama - Form Buat Pengaduan
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = 'Buat Pengaduan';
$success_message = '';
$error_message = '';
$nomor_tiket_baru = '';

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = sanitizeInput($_POST['judul'] ?? '');
    $deskripsi = sanitizeInput($_POST['deskripsi'] ?? '');
    $kategori = sanitizeInput($_POST['kategori'] ?? '');
    $nama_pelapor = sanitizeInput($_POST['nama_pelapor'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $telepon = sanitizeInput($_POST['telepon'] ?? '');
    $is_anonim = isset($_POST['is_anonim']) ? true : false;
    
    // Validasi
    $errors = [];
    
    if (empty($judul)) {
        $errors[] = "Judul pengaduan harus diisi";
    }
    
    if (empty($deskripsi)) {
        $errors[] = "Deskripsi pengaduan harus diisi";
    }
    
    if (empty($kategori)) {
        $errors[] = "Kategori harus dipilih";
    }
    
    // Jika tidak anonim, validasi email
    if (!$is_anonim && !empty($email) && !validateEmail($email)) {
        $errors[] = "Format email tidak valid";
    }
    
    if (empty($errors)) {
        try {
            // Generate nomor tiket
            $nomor_tiket_baru = generateNomorTiket($pdo);
            
            // Jika anonim, set nama default
            if ($is_anonim) {
                $nama_pelapor = 'Anonim';
                $email = null;
                $telepon = null;
            }
            
            // Insert ke database
            $stmt = $pdo->prepare("
                INSERT INTO pengaduan (nomor_tiket, judul, deskripsi, kategori, nama_pelapor, email, telepon) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $nomor_tiket_baru,
                $judul,
                $deskripsi,
                $kategori,
                $nama_pelapor,
                $email,
                $telepon
            ]);
            
            $success_message = "Pengaduan berhasil dibuat! Nomor tiket Anda: <strong>{$nomor_tiket_baru}</strong>";
            
            // Reset form
            $_POST = [];
            
        } catch (PDOException $e) {
            $error_message = "Gagal menyimpan pengaduan: " . $e->getMessage();
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

include 'includes/header.php';
?>

<div class="d-flex flex-column min-vh-100">
    <!-- Hero Section -->
    <div class="bg-primary text-white py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="display-5 fw-bold mb-3">
                        <i class="bi bi-megaphone-fill me-3"></i>
                        Sistem Pengaduan Online
                    </h1>
                    <p class="lead mb-0">
                        Laporkan keluhan Anda dengan mudah dan cepat. Kami siap membantu menyelesaikan masalah Anda.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container my-5 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <!-- Alert Success -->
                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-check-circle text-success" style="font-size: 64px;"></i>
                        <h4 class="mt-3">Pengaduan Berhasil Dibuat!</h4>
                        <p class="text-muted">Simpan nomor tiket Anda untuk melacak status pengaduan</p>
                        <div class="d-flex gap-2 justify-content-center mt-4">
                            <a href="cek-pengaduan.php?tiket=<?php echo $nomor_tiket_baru; ?>" class="btn btn-primary">
                                <i class="bi bi-search"></i> Cek Status
                            </a>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-plus-circle"></i> Buat Pengaduan Baru
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Alert Error -->
                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <!-- Form Pengaduan -->
                <?php if (empty($success_message)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-semibold">
                            <i class="bi bi-pencil-square text-primary me-2"></i>
                            Form Pengaduan Baru
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="">
                            
                            <!-- Judul Pengaduan -->
                            <div class="mb-3">
                                <label for="judul" class="form-label fw-semibold">
                                    Judul Pengaduan <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="judul" 
                                       name="judul" 
                                       placeholder="Contoh: Jalan Rusak di Depan Sekolah"
                                       value="<?php echo $_POST['judul'] ?? ''; ?>"
                                       required>
                            </div>
                            
                            <!-- Kategori -->
                            <div class="mb-3">
                                <label for="kategori" class="form-label fw-semibold">
                                    Kategori <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg" id="kategori" name="kategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="Infrastruktur" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Infrastruktur') ? 'selected' : ''; ?>>Infrastruktur</option>
                                    <option value="Kebersihan" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Kebersihan') ? 'selected' : ''; ?>>Kebersihan</option>
                                    <option value="Keamanan" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Keamanan') ? 'selected' : ''; ?>>Keamanan</option>
                                    <option value="Pelayanan" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Pelayanan') ? 'selected' : ''; ?>>Pelayanan</option>
                                    <option value="Lainnya" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                                </select>
                            </div>
                            
                            <!-- Deskripsi -->
                            <div class="mb-4">
                                <label for="deskripsi" class="form-label fw-semibold">
                                    Deskripsi Pengaduan <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" 
                                          id="deskripsi" 
                                          name="deskripsi" 
                                          rows="5" 
                                          placeholder="Jelaskan pengaduan Anda secara detail..."
                                          required><?php echo $_POST['deskripsi'] ?? ''; ?></textarea>
                                <small class="text-muted">Jelaskan masalah dengan lengkap agar dapat ditindaklanjuti dengan baik</small>
                            </div>
                            
                            <!-- Checkbox Anonim -->
                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_anonim" 
                                           name="is_anonim"
                                           <?php echo isset($_POST['is_anonim']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_anonim">
                                        <strong>Kirim sebagai Anonim</strong>
                                        <small class="d-block text-muted">Identitas Anda tidak akan ditampilkan</small>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Data Pelapor (Hidden jika anonim) -->
                            <div id="dataPerlapor" class="border-top pt-4" style="display: <?php echo isset($_POST['is_anonim']) ? 'none' : 'block'; ?>;">
                                <h6 class="mb-3 fw-semibold text-muted">Data Pelapor (Opsional)</h6>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nama_pelapor" class="form-label">Nama Lengkap</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="nama_pelapor" 
                                               name="nama_pelapor" 
                                               placeholder="Nama Anda"
                                               value="<?php echo $_POST['nama_pelapor'] ?? ''; ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" 
                                               class="form-control" 
                                               id="email" 
                                               name="email" 
                                               placeholder="email@example.com"
                                               value="<?php echo $_POST['email'] ?? ''; ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="telepon" class="form-label">Nomor Telepon</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="telepon" 
                                               name="telepon" 
                                               placeholder="08xxxxxxxxxx"
                                               value="<?php echo $_POST['telepon'] ?? ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-send-fill me-2"></i>
                                    Kirim Pengaduan
                                </button>
                            </div>
                            
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Info Box -->
                <div class="card border-0 bg-light mt-4">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">
                            <i class="bi bi-info-circle text-primary me-2"></i>
                            Informasi
                        </h6>
                        <ul class="mb-0 ps-3">
                            <li class="mb-2">Pengaduan Anda akan diproses maksimal 3x24 jam</li>
                            <li class="mb-2">Simpan nomor tiket untuk melacak status pengaduan</li>
                            <li class="mb-2">Anda dapat mengirim pengaduan secara anonim</li>
                            <li>Untuk informasi lebih lanjut, hubungi admin sistem</li>
                        </ul>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>

<!-- Toggle tampilan form pelapor berdasarkan checkbox anonim -->
<script>
document.getElementById('is_anonim').addEventListener('change', function() {
    const dataPerlapor = document.getElementById('dataPerlapor');
    if (this.checked) {
        dataPerlapor.style.display = 'none';
    } else {
        dataPerlapor.style.display = 'block';
    }
});
</script>

</div>