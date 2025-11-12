<?php
/**
 * Halaman Daftar Pengaduan Publik
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

include 'includes/header.php';
?>

<div class="d-flex flex-column min-vh-100 bg-light">
    <div class="container my-4 flex-grow-1">
        
        <!-- Page Header -->
        <div class="bg-white rounded shadow-sm p-4 mb-4">
            <h2 class="mb-2 fw-bold">
                <i class="bi bi-list-ul text-primary me-2"></i>
                Daftar Pengaduan
            </h2>
            <p class="text-muted mb-0">Kelola semua pengaduan yang masuk</p>
        </div>
        
        <!-- Filter & Search -->
        <div class="bg-white rounded shadow-sm p-4 mb-4">
            <form method="GET" action="">
                <div class="row g-3 align-items-end">
                    
                    <!-- Filter Status -->
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Status</label>
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
                        <label class="form-label small text-muted fw-semibold">Kategori</label>
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
                        <label class="form-label small text-muted fw-semibold">Pencarian</label>
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Cari judul atau deskripsi..." 
                               value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                    
                    <!-- Button -->
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                    
                </div>
                
                <?php if (!empty($filter_status) || !empty($filter_kategori) || !empty($search_query)): ?>
                <div class="mt-3">
                    <a href="daftar-pengaduan.php" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Reset Filter
                    </a>
                </div>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Results Info -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="text-muted">
                Menampilkan <?php echo $total_items; ?> pengaduan
            </div>
        </div>
        
        <!-- Table -->
        <div class="bg-white rounded shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-uppercase small fw-semibold">Nomor Tiket</th>
                            <th class="text-uppercase small fw-semibold">Judul</th>
                            <th class="text-uppercase small fw-semibold">Kategori</th>
                            <th class="text-uppercase small fw-semibold">Status</th>
                            <th class="text-uppercase small fw-semibold">Pelapor</th>
                            <th class="text-uppercase small fw-semibold">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pengaduan_list)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-inbox text-muted" style="font-size: 48px;"></i>
                                <p class="text-muted mt-3 mb-0">Tidak ada pengaduan ditemukan</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($pengaduan_list as $item): ?>
                            <tr>
                                <td>
                                    <a href="cek-pengaduan.php?tiket=<?php echo $item['nomor_tiket']; ?>" 
                                       class="text-primary text-decoration-none fw-semibold">
                                        <?php echo htmlspecialchars($item['nomor_tiket']); ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($item['judul']); ?></div>
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
                                    <em class="text-muted"><?php echo htmlspecialchars($item['nama_pelapor']); ?></em>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo formatTanggalIndonesia($item['tanggal_dibuat']); ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                
                <!-- Previous -->
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $filter_status; ?>&kategori=<?php echo $filter_kategori; ?>&search=<?php echo urlencode($search_query); ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                
                <!-- Pages -->
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == 1 || $i == $total_pages || abs($i - $page) <= 2): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $filter_status; ?>&kategori=<?php echo $filter_kategori; ?>&search=<?php echo urlencode($search_query); ?>">
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
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $filter_status; ?>&kategori=<?php echo $filter_kategori; ?>&search=<?php echo urlencode($search_query); ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                
            </ul>
        </nav>
        <?php endif; ?>
        
    </div>

<?php include 'includes/footer.php'; ?>