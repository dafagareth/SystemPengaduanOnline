<?php
/**
 * Kelola Pengaduan - Admin
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

$page_title = 'Kelola Pengaduan';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $pengaduan_id = (int)$_POST['pengaduan_id'];
    $new_status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE pengaduan SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $pengaduan_id]);
        
        $success_msg = "Status pengaduan berhasil diperbarui!";
    } catch (PDOException $e) {
        $error_msg = "Gagal memperbarui status: " . $e->getMessage();
    }
}

// Filter parameters
$filter_status = $_GET['status'] ?? '';
$filter_kategori = $_GET['kategori'] ?? '';
$search_query = $_GET['search'] ?? '';

// Pagination
$items_per_page = 15;
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
    SELECT * FROM pengaduan 
    {$where_sql}
    ORDER BY tanggal_dibuat DESC 
    LIMIT ? OFFSET ?
");

$params[] = $items_per_page;
$params[] = $offset;
$stmt->execute($params);
$pengaduan_list = $stmt->fetchAll();

include '../includes/admin-header.php';
?>

<!-- Page Header -->
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-2 fw-bold">
                <i class="bi bi-folder text-primary me-2"></i>
                Kelola Pengaduan
            </h2>
            <p class="text-muted mb-0">Kelola semua pengaduan yang masuk</p>
        </div>
        <div>
            <a href="export-csv.php?<?php echo http_build_query(['status' => $filter_status, 'kategori' => $filter_kategori, 'search' => $search_query]); ?>" class="btn btn-success">
                <i class="bi bi-download me-2"></i>Export CSV
            </a>
        </div>
    </div>
</div>

<!-- Alerts -->
<?php if (isset($success_msg)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>
    <?php echo $success_msg; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($error_msg)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <?php echo $error_msg; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filter & Search -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="">
            <div class="row g-3 align-items-end">
                
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
                
                <div class="col-md-4">
                    <label class="form-label small text-muted fw-semibold">Pencarian</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari judul atau tiket..." value="<?php echo htmlspecialchars($search_query); ?>">
                </div>
                
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Cari
                    </button>
                </div>
                
            </div>
            
            <?php if (!empty($filter_status) || !empty($filter_kategori) || !empty($search_query)): ?>
            <div class="mt-3">
                <a href="pengaduan.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Reset Filter
                </a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Results Info -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted">
        Menampilkan <strong><?php echo $total_items; ?></strong> pengaduan
    </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-uppercase small fw-semibold">Tiket</th>
                        <th class="text-uppercase small fw-semibold">Judul</th>
                        <th class="text-uppercase small fw-semibold">Kategori</th>
                        <th class="text-uppercase small fw-semibold">Pelapor</th>
                        <th class="text-uppercase small fw-semibold">Status</th>
                        <th class="text-uppercase small fw-semibold">Tanggal</th>
                        <th class="text-uppercase small fw-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pengaduan_list)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 48px;"></i>
                            <p class="text-muted mt-3 mb-0">Tidak ada pengaduan ditemukan</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($pengaduan_list as $item): ?>
                        <tr>
                            <td>
                                <span class="text-primary fw-semibold small">
                                    <?php echo htmlspecialchars($item['nomor_tiket']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold"><?php echo htmlspecialchars(substr($item['judul'], 0, 40)); ?><?php echo strlen($item['judul']) > 40 ? '...' : ''; ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border small">
                                    <?php echo htmlspecialchars($item['kategori']); ?>
                                </span>
                            </td>
                            <td>
                                <em class="text-muted small"><?php echo htmlspecialchars($item['nama_pelapor']); ?></em>
                            </td>
                            <td>
                                <form method="POST" action="" class="d-inline">
                                    <input type="hidden" name="pengaduan_id" value="<?php echo $item['id']; ?>">
                                    <select name="status" class="form-select form-select-sm badge-select <?php echo getStatusClass($item['status']); ?>" onchange="this.form.submit()">
                                        <option value="Menunggu" <?php echo $item['status'] == 'Menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                        <option value="Diproses" <?php echo $item['status'] == 'Diproses' ? 'selected' : ''; ?>>Diproses</option>
                                        <option value="Selesai" <?php echo $item['status'] == 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                                        <option value="Ditolak" <?php echo $item['status'] == 'Ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td>
                                <small class="text-muted"><?php echo formatTanggalIndonesia($item['tanggal_dibuat']); ?></small>
                            </td>
                            <td class="text-center">
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

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<nav aria-label="Pagination" class="mt-4">
    <ul class="pagination justify-content-center">
        
        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $filter_status; ?>&kategori=<?php echo $filter_kategori; ?>&search=<?php echo urlencode($search_query); ?>">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
        
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
        
        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $filter_status; ?>&kategori=<?php echo $filter_kategori; ?>&search=<?php echo urlencode($search_query); ?>">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
        
    </ul>
</nav>
<?php endif; ?>

<?php include '../includes/admin-footer.php'; ?>

<style>
.badge-select {
    border: 1px solid;
    font-weight: 500;
    padding: 0.35rem 0.5rem;
    border-radius: 4px;
    cursor: pointer;
}
</style>