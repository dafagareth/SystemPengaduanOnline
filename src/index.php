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
    
    // Handle file upload
    $file_bukti = null;
    if (isset($_FILES['file_bukti']) && $_FILES['file_bukti']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'video/mp4'];
        $file_type = $_FILES['file_bukti']['type'];
        $file_size = $_FILES['file_bukti']['size'];
        $max_size = 10 * 1024 * 1024; // 10MB
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Format file harus JPG, JPEG, atau MP4";
        } elseif ($file_size > $max_size) {
            $errors[] = "Ukuran file maksimal 2MB";
        } else {
            // Create uploads directory if not exists
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['file_bukti']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '_' . time() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['file_bukti']['tmp_name'], $file_path)) {
                $file_bukti = 'uploads/' . $file_name;
            } else {
                $errors[] = "Gagal mengupload file";
            }
        }
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
                INSERT INTO pengaduan (nomor_tiket, judul, deskripsi, kategori, nama_pelapor, email, telepon, file_bukti) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
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

<div class="d-flex flex-column" style="min-height: calc(100vh - 120px);">
    <div class="container-fluid px-4 py-4 flex-grow-1">
        <div class="row">
            <!-- Left Side - Info -->
            <div class="col-lg-5 mb-4 mb-lg-0">
                <div class="pe-lg-4">
                    <h1 class="display-6 fw-bold mb-3 text-start">
                        Sistem Pengaduan Online
                    </h1>
                    <p class="lead text-muted text-start mb-4">
                        Laporkan keluhan Anda dengan mudah dan cepat. Kami siap membantu menyelesaikan masalah Anda.
                    </p>
                    
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3 text-start">
                                <i class="bi bi-info-circle text-primary me-2"></i>
                                Informasi
                            </h6>
                            <ul class="mb-0 ps-3 text-start">
                                <li class="mb-2 text-muted">Pengaduan akan diproses maksimal 3x24 jam</li>
                                <li class="mb-2 text-muted">Simpan nomor tiket untuk melacak status</li>
                                <li class="mb-2 text-muted">Anda dapat mengirim secara anonim</li>
                                <li class="text-muted">Upload bukti foto (JPG/JPEG) atau video (MP4) maksimal 2MB</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Alert Success -->
                    <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-4">
                            <i class="bi bi-check-circle text-success" style="font-size: 64px;"></i>
                            <h4 class="mt-3">Pengaduan Berhasil!</h4>
                            <p class="text-muted">Simpan nomor tiket untuk melacak status</p>
                            <div class="d-flex gap-2 justify-content-center mt-4">
                                <a href="cek-pengaduan.php?tiket=<?php echo $nomor_tiket_baru; ?>" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Cek Status
                                </a>
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-plus-circle"></i> Buat Baru
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
                </div>
            </div>
            
            <!-- Right Side - Form -->
            <?php if (empty($success_message)): ?>
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-semibold text-start">
                            Form Pengaduan Baru
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="" enctype="multipart/form-data">
                            
                            <div class="row">
                                <!-- Judul -->
                                <div class="col-md-6 mb-3">
                                    <label for="judul" class="form-label fw-semibold text-start d-block">
                                        Judul Pengaduan <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="judul" 
                                           name="judul" 
                                           placeholder="Contoh: Jalan Rusak"
                                           value="<?php echo $_POST['judul'] ?? ''; ?>"
                                           required>
                                </div>
                                
                                <!-- Kategori -->
                                <div class="col-md-6 mb-3">
                                    <label for="kategori" class="form-label fw-semibold text-start d-block">
                                        Kategori <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="kategori" name="kategori" required>
                                        <option value="">Pilih Kategori</option>
                                        <option value="Infrastruktur">Infrastruktur</option>
                                        <option value="Kebersihan">Kebersihan</option>
                                        <option value="Keamanan">Keamanan</option>
                                        <option value="Pelayanan">Pelayanan</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Deskripsi -->
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label fw-semibold text-start d-block">
                                    Deskripsi <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" 
                                          id="deskripsi" 
                                          name="deskripsi" 
                                          rows="3" 
                                          placeholder="Jelaskan pengaduan secara singkat..."
                                          required><?php echo $_POST['deskripsi'] ?? ''; ?></textarea>
                            </div>
                            
                            <!-- File Upload -->
                            <div class="mb-3">
                                <label for="file_bukti" class="form-label fw-semibold text-start d-block">
                                    Bukti Foto/Video (Opsional)
                                </label>
                                <input type="file" 
                                       class="form-control" 
                                       id="file_bukti" 
                                       name="file_bukti"
                                       accept=".jpg,.jpeg,.mp4">
                                <small class="text-muted">Format: JPG, JPEG, MP4 (Maks. 2MB)</small>
                            </div>
                            
                            <!-- Checkbox Anonim -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_anonim" 
                                           name="is_anonim">
                                    <label class="form-check-label text-start" for="is_anonim">
                                        <strong>Kirim sebagai Anonim</strong>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Data Pelapor -->
                            <div id="dataPerlapor" class="border-top pt-3">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nama_pelapor" class="form-label text-start d-block">Nama Lengkap</label>
                                        <input type="text" class="form-control" id="nama_pelapor" name="nama_pelapor" placeholder="Nama Anda">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="telepon" class="form-label text-start d-block">No. Telepon</label>
                                        <input type="text" class="form-control" id="telepon" name="telepon" placeholder="08xxxxxxxxxx">
                                    </div>
                                    
                                    <div class="col-12 mb-3">
                                        <label for="email" class="form-label text-start d-block">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="email@example.com">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Submit -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-send-fill me-2"></i>
                                    Kirim Pengaduan
                                </button>
                            </div>
                            
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </div>

<?php include 'includes/footer.php'; ?>

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