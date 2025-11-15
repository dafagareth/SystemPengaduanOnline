<?php
/**
 * ============================================================================
 * HALAMAN DAFTAR PENGADUAN PUBLIK
 * ============================================================================
 * File: daftar-pengaduan.php
 * Deskripsi: Halaman untuk menampilkan daftar semua pengaduan
 * Fitur:
 * - Tampilan tabel pengaduan
 * - Filter by status & kategori
 * - Search by judul/nomor tiket
 * - Pagination (10 items per page)
 * - Link ke detail pengaduan
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
$page_title = 'Daftar Pengaduan';

// ============================================================================
// AMBIL PARAMETER FILTER DARI URL
// ============================================================================
// Operator ?? '' : Gunakan empty string jika parameter tidak ada

// Filter status: Menunggu/Diproses/Selesai/Ditolak
$filter_status = $_GET['status'] ?? '';

// Filter kategori: Infrastruktur/Kebersihan/Keamanan/Pelayanan/Lainnya
$filter_kategori = $_GET['kategori'] ?? '';

// Search query: Untuk search judul/deskripsi/nomor tiket
$search_query = $_GET['search'] ?? '';

// ============================================================================
// SETUP PAGINATION
// ============================================================================
// Pagination: Membagi data menjadi beberapa halaman untuk performa lebih baik

// Items per page: Jumlah pengaduan yang ditampilkan per halaman
$items_per_page = 10;

// Current page: Ambil dari URL, default ke halaman 1
// (int) untuk memastikan nilai integer (type casting)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Offset: Posisi awal data yang diambil dari database
// Contoh: page 1 = offset 0, page 2 = offset 10, page 3 = offset 20
$offset = ($page - 1) * $items_per_page;

// ============================================================================
// BUILD SQL QUERY DENGAN DYNAMIC WHERE CLAUSE
// ============================================================================
// WHERE clause dibuat secara dinamis berdasarkan filter yang aktif

// Array untuk menyimpan kondisi WHERE
$where_conditions = [];

// Array untuk menyimpan parameter prepared statement
$params = [];

// ========================================================================
// Filter: Status
// ========================================================================
if (!empty($filter_status)) {
    // Tambahkan kondisi: status = ?
    $where_conditions[] = "status = ?";
    // Tambahkan parameter untuk placeholder
    $params[] = $filter_status;
}

// ========================================================================
// Filter: Kategori
// ========================================================================
if (!empty($filter_kategori)) {
    // Tambahkan kondisi: kategori = ?
    $where_conditions[] = "kategori = ?";
    $params[] = $filter_kategori;
}

// ========================================================================
// Filter: Search Query
// ========================================================================
if (!empty($search_query)) {
    // LIKE operator untuk partial matching
    // Search di 3 kolom: judul, deskripsi, nomor_tiket
    // % = wildcard (match any characters)
    $where_conditions[] = "(judul LIKE ? OR deskripsi LIKE ? OR nomor_tiket LIKE ?)";
    
    // Format search param dengan wildcard
    $search_param = "%{$search_query}%";
    
    // Tambahkan 3x parameter (untuk 3 kolom)
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

// ========================================================================
// Gabungkan WHERE conditions
// ========================================================================
// implode: Gabungkan array menjadi string dengan separator ' AND '
// Contoh: ['status = ?', 'kategori = ?'] → 'status = ? AND kategori = ?'
$where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// ============================================================================
// EXECUTE DATABASE QUERIES
// ============================================================================
try {
    // ========================================================================
    // Query 1: Hitung total items (untuk pagination)
    // ========================================================================
    $count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM pengaduan {$where_sql}");
    $count_stmt->execute($params);
    $total_items = $count_stmt->fetch()['total'];
    
    // Hitung total halaman
    // ceil: Pembulatan ke atas (contoh: 25 items / 10 per page = 3 pages)
    $total_pages = ceil($total_items / $items_per_page);
    
    // ========================================================================
    // Query 2: Fetch data pengaduan dengan pagination
    // ========================================================================
    $stmt = $pdo->prepare("
        SELECT nomor_tiket, judul, kategori, status, nama_pelapor, tanggal_dibuat 
        FROM pengaduan 
        {$where_sql}
        ORDER BY tanggal_dibuat DESC 
        LIMIT ? OFFSET ?
    ");
    
    // Tambahkan parameter LIMIT dan OFFSET
    // LIMIT: Jumlah row yang diambil
    // OFFSET: Skip berapa row dari awal
    $params[] = $items_per_page;
    $params[] = $offset;
    
    // Execute query
    $stmt->execute($params);
    
    // Fetch all results sebagai array
    $pengaduan_list = $stmt->fetchAll();
    
} catch (PDOException $e) {
    // ========================================================================
    // Error Handling
    // ========================================================================
    // Log error ke PHP error log (jangan tampilkan ke user di production)
    error_log("Error fetching pengaduan: " . $e->getMessage());
    
    // Set default values jika query gagal
    $pengaduan_list = [];
    $total_items = 0;
    $total_pages = 0;
}

// ============================================================================
// INCLUDE HEADER
// ============================================================================
include 'includes/header.php';
?>

<!-- ============================================================================
     MAIN CONTENT
     ============================================================================ -->
<div class="container my-4">
    
    <!-- ========================================================================
         PAGE HEADER
         ======================================================================== -->
    <div class="content-header">
        <h1>Daftar Pengaduan</h1>
        <p>Lihat semua pengaduan yang masuk</p>
    </div>
    
    <!-- ========================================================================
         FILTER & SEARCH CARD
         ========================================================================
         Card berisi form untuk filter dan search pengaduan
         ======================================================================== -->
    <div class="card mb-4">
        <div class="card-body">
            <!-- Form dengan method GET -->
            <!-- GET: Parameter dikirim via URL (bisa di-bookmark) -->
            <form method="GET" action="">
                <div class="row g-3">
                    
                    <!-- ============================================================
                         FILTER: Status
                         ============================================================ -->
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <!-- Option default: Semua status -->
                            <option value="">Semua Status</option>
                            
                            <!-- Option: Menunggu -->
                            <!-- selected attribute ditambahkan jika filter aktif -->
                            <option value="Menunggu" <?php echo $filter_status == 'Menunggu' ? 'selected' : ''; ?>>
                                Menunggu
                            </option>
                            
                            <!-- Option: Diproses -->
                            <option value="Diproses" <?php echo $filter_status == 'Diproses' ? 'selected' : ''; ?>>
                                Diproses
                            </option>
                            
                            <!-- Option: Selesai -->
                            <option value="Selesai" <?php echo $filter_status == 'Selesai' ? 'selected' : ''; ?>>
                                Selesai
                            </option>
                            
                            <!-- Option: Ditolak -->
                            <option value="Ditolak" <?php echo $filter_status == 'Ditolak' ? 'selected' : ''; ?>>
                                Ditolak
                            </option>
                        </select>
                    </div>
                    
                    <!-- ============================================================
                         FILTER: Kategori
                         ============================================================ -->
                    <div class="col-md-3">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" class="form-select">
                            <!-- Option default: Semua kategori -->
                            <option value="">Semua Kategori</option>
                            
                            <!-- Option: Infrastruktur -->
                            <option value="Infrastruktur" <?php echo $filter_kategori == 'Infrastruktur' ? 'selected' : ''; ?>>
                                Infrastruktur
                            </option>
                            
                            <!-- Option: Kebersihan -->
                            <option value="Kebersihan" <?php echo $filter_kategori == 'Kebersihan' ? 'selected' : ''; ?>>
                                Kebersihan
                            </option>
                            
                            <!-- Option: Keamanan -->
                            <option value="Keamanan" <?php echo $filter_kategori == 'Keamanan' ? 'selected' : ''; ?>>
                                Keamanan
                            </option>
                            
                            <!-- Option: Pelayanan -->
                            <option value="Pelayanan" <?php echo $filter_kategori == 'Pelayanan' ? 'selected' : ''; ?>>
                                Pelayanan
                            </option>
                            
                            <!-- Option: Lainnya -->
                            <option value="Lainnya" <?php echo $filter_kategori == 'Lainnya' ? 'selected' : ''; ?>>
                                Lainnya
                            </option>
                        </select>
                    </div>
                    
                    <!-- ============================================================
                         SEARCH: Text Input
                         ============================================================ -->
                    <div class="col-md-4">
                        <label class="form-label">Pencarian</label>
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Cari judul atau nomor tiket" 
                               value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                    
                    <!-- ============================================================
                         BUTTON: Submit Filter
                         ============================================================ -->
                    <div class="col-md-2">
                        <!-- Label kosong untuk alignment dengan input lain -->
                        <label class="form-label d-none d-md-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>Cari
                        </button>
                    </div>
                    
                </div>
                
                <!-- ============================================================
                     BUTTON: Reset Filter
                     ============================================================
                     Hanya tampil jika ada filter/search yang aktif
                     ============================================================ -->
                <?php if (!empty($filter_status) || !empty($filter_kategori) || !empty($search_query)): ?>
                <div class="mt-3">
                    <!-- Link ke halaman tanpa parameter (reset semua filter) -->
                    <a href="daftar-pengaduan.php" class="btn btn-sm btn-secondary">
                        <i class="bi bi-x-circle me-1"></i>Reset Filter
                    </a>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <!-- ========================================================================
         RESULTS INFO
         ========================================================================
         Menampilkan jumlah total pengaduan yang ditemukan
         ======================================================================== -->
    <div class="mb-3 text-muted">
        <!-- number_format: Format angka dengan thousand separator -->
        Menampilkan <?php echo number_format($total_items); ?> pengaduan
    </div>
    
    <!-- ========================================================================
         TABLE: Daftar Pengaduan
         ========================================================================
         Tabel responsive untuk menampilkan data pengaduan
         ======================================================================== -->
    <div class="table-responsive">
        <table class="table table-hover">
            <!-- ================================================================
                 TABLE HEADER
                 ================================================================ -->
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
            
            <!-- ================================================================
                 TABLE BODY
                 ================================================================ -->
            <tbody>
                <?php if (empty($pengaduan_list)): ?>
                <!-- ============================================================
                     EMPTY STATE: Tidak ada data
                     ============================================================ -->
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <!-- Icon inbox (empty) -->
                        <div class="mb-3">
                            <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.3;"></i>
                        </div>
                        Tidak ada pengaduan ditemukan
                    </td>
                </tr>
                
                <?php else: ?>
                <!-- ============================================================
                     DATA ROWS: Loop setiap pengaduan
                     ============================================================ -->
                    <?php foreach ($pengaduan_list as $item): ?>
                    <tr>
                        <!-- Kolom: Nomor Tiket (dengan link ke detail) -->
                        <td>
                            <a href="cek-pengaduan.php?tiket=<?php echo $item['nomor_tiket']; ?>" 
                               class="text-decoration-none">
                                <?php echo htmlspecialchars($item['nomor_tiket']); ?>
                            </a>
                        </td>
                        
                        <!-- Kolom: Judul -->
                        <td><?php echo htmlspecialchars($item['judul']); ?></td>
                        
                        <!-- Kolom: Kategori (dengan badge) -->
                        <td>
                            <span class="badge bg-light text-dark border">
                                <?php echo htmlspecialchars($item['kategori']); ?>
                            </span>
                        </td>
                        
                        <!-- Kolom: Status (dengan badge berwarna) -->
                        <td>
                            <!-- getStatusClass() return CSS class sesuai status -->
                            <span class="badge <?php echo getStatusClass($item['status']); ?>">
                                <?php echo htmlspecialchars($item['status']); ?>
                            </span>
                        </td>
                        
                        <!-- Kolom: Nama Pelapor -->
                        <td class="text-muted">
                            <?php echo htmlspecialchars($item['nama_pelapor']); ?>
                        </td>
                        
                        <!-- Kolom: Tanggal Dibuat -->
                        <td class="text-muted">
                            <!-- formatTanggalIndonesia() convert ke format readable -->
                            <small><?php echo formatTanggalIndonesia($item['tanggal_dibuat']); ?></small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- ========================================================================
         PAGINATION
         ========================================================================
         Navigasi halaman, hanya tampil jika lebih dari 1 halaman
         ======================================================================== -->
    <?php if ($total_pages > 1): ?>
    <nav aria-label="Pagination" class="mt-4">
        <ul class="pagination justify-content-center">
            
            <!-- ================================================================
                 PREVIOUS BUTTON
                 ================================================================ -->
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <!-- Preserve filter parameters di URL -->
                <a class="page-link" 
                   href="?page=<?php echo $page - 1; ?>&status=<?php echo $filter_status; ?>&kategori=<?php echo $filter_kategori; ?>&search=<?php echo urlencode($search_query); ?>"
                   aria-label="Previous">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
            
            <!-- ================================================================
                 PAGE NUMBERS
                 ================================================================
                 Tampilkan: halaman pertama, terakhir, dan 2 halaman di sekitar current page
                 Contoh: 1 ... 4 5 [6] 7 8 ... 15
                 ================================================================ -->
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php 
                // Tampilkan halaman jika:
                // - Halaman pertama (i == 1)
                // - Halaman terakhir (i == $total_pages)
                // - Dalam range 2 halaman dari current page (abs($i - $page) <= 2)
                if ($i == 1 || $i == $total_pages || abs($i - $page) <= 2): 
                ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" 
                       href="?page=<?php echo $i; ?>&status=<?php echo $filter_status; ?>&kategori=<?php echo $filter_kategori; ?>&search=<?php echo urlencode($search_query); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                
                <?php 
                // Tampilkan ellipsis (...) jika ada gap
                // Gap terjadi jika jarak = 3 dari current page
                elseif (abs($i - $page) == 3): 
                ?>
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
                <?php endif; ?>
            <?php endfor; ?>
            
            <!-- ================================================================
                 NEXT BUTTON
                 ================================================================ -->
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

<!-- ============================================================================
     INCLUDE FOOTER
     ============================================================================ -->
<?php include 'includes/footer.php'; ?>