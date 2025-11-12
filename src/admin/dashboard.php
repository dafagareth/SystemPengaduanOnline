<?php
/**
 * Admin Dashboard
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

$page_title = 'Dashboard';

// Get statistik
$stats = getStatistikPengaduan($pdo);

// Get pengaduan terbaru (5 terakhir)
$stmt = $pdo->query("
    SELECT * FROM pengaduan 
    ORDER BY tanggal_dibuat DESC 
    LIMIT 5
");
$pengaduan_terbaru = $stmt->fetchAll();

include '../includes/admin-header.php';
?>

<!-- Page Header -->
<div class="content-header">
    <h2 class="mb-2 fw-bold">
        <i class="bi bi-speedometer2 text-primary me-2"></i>
        Dashboard
    </h2>
    <p class="text-muted mb-0">Ringkasan sistem pengaduan online</p>
</div>

<!-- Statistik Cards -->
<div class="row g-3 mb-4">
    
    <!-- Total Pengaduan -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1 text-uppercase fw-semibold">Total Pengaduan</p>
                        <h2 class="mb-0 fw-bold"><?php echo $stats['total']; ?></h2>
                    </div>
                    <div class="p-3 bg-primary bg-opacity-10 rounded">
                        <i class="bi bi-folder text-primary" style="font-size: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Menunggu -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1 text-uppercase fw-semibold">Menunggu</p>
                        <h2 class="mb-0 fw-bold text-warning"><?php echo $stats['by_status']['Menunggu'] ?? 0; ?></h2>
                    </div>
                    <div class="p-3 bg-warning bg-opacity-10 rounded">
                        <i class="bi bi-clock text-warning" style="font-size: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Diproses -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1 text-uppercase fw-semibold">Diproses</p>
                        <h2 class="mb-0 fw-bold text-info"><?php echo $stats['by_status']['Diproses'] ?? 0; ?></h2>
                    </div>
                    <div class="p-3 bg-info bg-opacity-10 rounded">
                        <i class="bi bi-hourglass-split text-info" style="font-size: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Selesai -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1 text-uppercase fw-semibold">Selesai</p>
                        <h2 class="mb-0 fw-bold text-success"><?php echo $stats['by_status']['Selesai'] ?? 0; ?></h2>
                    </div>
                    <div class="p-3 bg-success bg-opacity-10 rounded">
                        <i class="bi bi-check-circle text-success" style="font-size: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<!-- Breakdown Kategori -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0 fw-semibold">
            <i class="bi bi-pie-chart text-primary me-2"></i>
            Breakdown per Kategori
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php
            $kategori_icons = [
                'Infrastruktur' => 'bi-tools',
                'Kebersihan' => 'bi-recycle',
                'Keamanan' => 'bi-shield-check',
                'Pelayanan' => 'bi-people',
                'Lainnya' => 'bi-three-dots'
            ];
            
            foreach (['Infrastruktur', 'Kebersihan', 'Keamanan', 'Pelayanan', 'Lainnya'] as $kategori):
                $jumlah = $stats['by_kategori'][$kategori] ?? 0;
                $icon = $kategori_icons[$kategori];
            ?>
            <div class="col-md-6 col-lg-4 col-xl">
                <div class="d-flex align-items-center p-3 bg-light rounded">
                    <i class="bi <?php echo $icon; ?> text-primary me-3" style="font-size: 24px;"></i>
                    <div>
                        <div class="fw-semibold"><?php echo $kategori; ?></div>
                        <div class="h5 mb-0"><?php echo $jumlah; ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Pengaduan Terbaru -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-clock-history text-primary me-2"></i>
                Pengaduan Terbaru
            </h5>
            <a href="pengaduan.php" class="btn btn-sm btn-outline-primary">
                Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-uppercase small fw-semibold">Nomor Tiket</th>
                        <th class="text-uppercase small fw-semibold">Judul</th>
                        <th class="text-uppercase small fw-semibold">Kategori</th>
                        <th class="text-uppercase small fw-semibold">Status</th>
                        <th class="text-uppercase small fw-semibold">Tanggal</th>
                        <th class="text-uppercase small fw-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pengaduan_terbaru)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            Belum ada pengaduan
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($pengaduan_terbaru as $item): ?>
                        <tr>
                            <td>
                                <span class="text-primary fw-semibold">
                                    <?php echo htmlspecialchars($item['nomor_tiket']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold"><?php echo htmlspecialchars(substr($item['judul'], 0, 50)); ?><?php echo strlen($item['judul']) > 50 ? '...' : ''; ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?php echo htmlspecialchars($item['kategori']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo getStatusClass($item['status']); ?>">
                                    <i class="bi <?php echo getStatusIcon($item['status']); ?> me-1"></i>
                                    <?php echo htmlspecialchars($item['status']); ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted"><?php echo formatTanggalIndonesia($item['tanggal_dibuat']); ?></small>
                            </td>
                            <td>
                                <a href="detail-pengaduan.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?>