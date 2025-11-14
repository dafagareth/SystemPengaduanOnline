<?php
/**
 * Halaman Daftar Pengaduan Publik
 * © 2025 Dafa al hafiz - 24_0085
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = 'Daftar Pengaduan';

// Filter parameters
$filter_status = $_GET['status'] ?? '';
$filter_kategori = $_GET['kategori'] ?? '';
$search_query = $_GET['search'] ?? '';

// Pagination
$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Build query
$where_conditions = [];
$params = [];

if (!empty($filter_status)) {
    $where_conditions[] = "status = ?";
    $params[] = $filter_status;
}

if (!empty($filter_kategori)) {
    $where_conditions[] = "kategori = ?";
    $params[] = $filter_kategori;
}

if (!empty($search_query)) {
    $where_conditions[] = "(judul LIKE ? OR deskripsi LIKE ? OR nomor_tiket LIKE ?)";
    $search_param = "%{$search_query}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Count total
try {
    $count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM pengaduan {$where_sql}");
    $count_stmt->execute($params);
    $total_items = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_items / $items_per_page);
    
    // Fetch data
    $stmt = $pdo->prepare("
        SELECT nomor_tiket, judul, kategori, status, nama_pelapor, tanggal_dibuat 
        FROM pengaduan 
        {$where_sql}
        ORDER BY tanggal_dibuat DESC 
        LIMIT ? OFFSET ?
    ");
    
    $params[] = $items_per_page;
    $params[] = $offset;
    $stmt->execute($params);
    $pengaduan_list = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching pengaduan: " . $e->getMessage());
    $pengaduan_list = [];
    $total_items = 0;
    $total_pages = 0;
}

include 'includes/header.php';
?>

<div class="container my-4">
    
    <!-- Page Header -->
    <div class="content-header">
        <h1>Daftar Pengaduan</h1>
        <p>Lihat semua pengaduan yang masuk</p>
    </div>
    
    <!-- Filter & Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="">
                <div class="row g-3">
                    
                    <!-- Filter Status -->
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="Menunggu" <?php echo $filter_status == 'Menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                            <option value="Diproses" <?php echo $filter_status == 'Diproses' ? 'selected' : ''; ?>>Diproses</option>
                            <option value="Selesai" <?php echo $filter_status == 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                            <option value="Ditolak" <?php echo $filter_status == 'Ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                        </select>
                    </div>
                    
                    <!-- Filter Kategori -->
                    <div class="col-md-3">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" class="form-select">
                            <option value="">Semua Kategori</option>
                            <option value="Infrastruktur" <?php echo $filter_kategori == 'Infrastruktur' ? 'selected' : ''; ?>>Infrastruktur</option>
                            <option value="Kebersihan" <?php echo $filter_kategori == 'Kebersihan' ? 'selected' : ''; ?>>Kebersihan</option>
                            <option value="Keamanan" <?php echo $filter_kategori == 'Keamanan' ? 'selected' : ''; ?>>Keamanan</option>
                            <option value="Pelayanan" <?php echo $filter_kategori == 'Pelayanan' ? 'selected' : ''; ?>>Pelayanan</option>
                            <option value="Lainnya" <?php echo $filter_kategori == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                        </select>
                    </div>
                    
                    <!-- Search -->
                    <div class="col-md-4">
                        <label class="form-label">Pencarian</label>
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Cari judul atau nomor tiket" 
                               value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                    
                    <!-- Button -->
                    <div class="col-md-2">
                        <label class="form-label d-none d-md-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>Cari
                        </button>
                    </div>
                    
                </div>
                
                <?php if (!empty($filter_status) || !empty($filter_kategori) || !empty($search_query)): ?>
                <div class="mt-3">
                    <a href="daftar-pengaduan.php" class="btn btn-sm btn-secondary">
                        <i class="bi bi-x-circle me-1"></i>Reset Filter
                    </a>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <!-- Results Info -->
    <div class="mb-3 text-muted">
        Menampilkan <?php echo number_format($total_items); ?> pengaduan
    </div>
    
    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nomor Tiket</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>Pelapor</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pengaduan_list)): ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <div class="mb-3">
                            <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.3;"></i>
                        </div>
                        Tidak ada pengaduan ditemukan
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($pengaduan_list as $item): ?>
                    <tr>
                        <td>
                            <a href="cek-pengaduan.php?tiket=<?php echo $item['nomor_tiket']; ?>" 
                               class="text-decoration-none">
                                <?php echo htmlspecialchars($item['nomor_tiket']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($item['judul']); ?></td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <?php echo htmlspecialchars($item['kategori']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?php echo getStatusClass($item['status']); ?>">
                                <?php echo htmlspecialchars($item['status']); ?>
                            </span>
                        </td>
                        <td class="text-muted"><?php echo htmlspecialchars($item['nama_pelapor']); ?></td>
                        <td class="text-muted">
                            <small><?php echo formatTanggalIndonesia($item['tanggal_dibuat']); ?></small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <nav aria-label="Pagination" class="mt-4">
        <ul class="pagination justify-content-center">
            
            <!-- Previous -->
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" 
                   href="?page=<?php echo $page - 1; ?>&status=<?php echo $filter_status; ?>&kategori=<?php echo $filter_kategori; ?>&search=<?php echo urlencode($search_query); ?>"
                   aria-label="Previous">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
            
            <!-- Pages -->
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == 1 || $i == $total_pages || abs($i - $page) <= 2): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" 
                       href="?page=<?php echo $i; ?>&status=<?php echo $filter_status; ?>&kategori=<?php echo $filter_kategori; ?>&search=<?php echo urlencode($search_query); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php elseif (abs($i - $page) == 3): ?>
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
                <?php endif; ?>
            <?php endfor; ?>
            
            <!-- Next -->
            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link" 
                   href="?page=<?php echo $page + 1; ?>&status=<?php echo $filter_status; ?>&kategori=<?php echo $filter_kategori; ?>&search=<?php echo urlencode($search_query); ?>"
                   aria-label="Next">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
            
        </ul>
    </nav>
    <?php endif; ?>
    
</div>

<?php include 'includes/footer.php'; ?>