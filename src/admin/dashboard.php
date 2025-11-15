<?php
/**
 * ============================================================================
 * ADMIN DASHBOARD - HALAMAN UTAMA ADMIN PANEL
 * ============================================================================
 * File: admin/dashboard.php
 * Deskripsi: Dashboard admin dengan statistik dan pengaduan terbaru
 * Fitur:
 * - Statistik cards (Total, Menunggu, Diproses, Selesai)
 * - Breakdown per kategori dengan icons
 * - Tabel 5 pengaduan terbaru
 * - Link cepat ke detail pengaduan
 * Author: Dafa al hafiz - 24_0085
 * Tanggal: 2025
 * ============================================================================
 */

// ============================================================================
// INCLUDE FILE DEPENDENCIES
// ============================================================================
require_once '../includes/config.php';      // Konfigurasi database & konstanta
require_once '../includes/functions.php';   // Helper functions

// ============================================================================
// INISIALISASI VARIABEL
// ============================================================================
$page_title = 'Dashboard';                   // Title halaman untuk <title> tag

// ============================================================================
// FETCH STATISTICS
// ============================================================================
// Ambil statistik pengaduan menggunakan helper function
// Return: ['total' => int, 'by_status' => array, 'by_kategori' => array]
$stats = getStatistikPengaduan($pdo);

// ============================================================================
// FETCH LATEST PENGADUAN (5 Terbaru)
// ============================================================================
// Query untuk ambil 5 pengaduan terbaru
// ORDER BY tanggal_dibuat DESC: Urutkan dari yang terbaru
// LIMIT 5: Ambil hanya 5 row
$stmt = $pdo->query("
    SELECT * FROM pengaduan 
    ORDER BY tanggal_dibuat DESC 
    LIMIT 5
");

// Fetch all results sebagai array
$pengaduan_terbaru = $stmt->fetchAll();

// ============================================================================
// INCLUDE ADMIN HEADER
// ============================================================================
// Admin header berisi sidebar, topbar, dan opening <body> tag
include '../includes/admin-header.php';
?>

<!-- ============================================================================
     PAGE HEADER
     ============================================================================
     Header halaman dengan icon dan deskripsi
     ============================================================================ -->
<div class="content-header">
    <h2 class="mb-2 fw-bold">
        <!-- Icon speedometer untuk dashboard -->
        <i class="bi bi-speedometer2 text-primary me-2"></i>
        Dashboard
    </h2>
    <p class="text-muted mb-0">Ringkasan sistem pengaduan online</p>
</div>

<!-- ============================================================================
     STATISTICS CARDS
     ============================================================================
     4 cards untuk menampilkan statistik utama
     Layout: 4 kolom di XL screen, 2 kolom di MD screen, 1 kolom di mobile
     ============================================================================ -->
<div class="row g-3 mb-4">
    
    <!-- ========================================================================
         CARD 1: Total Pengaduan
         ======================================================================== -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <!-- Flexbox layout: number di kiri, icon di kanan -->
                <div class="d-flex justify-content-between align-items-start">
                    
                    <!-- Left side: Label & Number -->
                    <div>
                        <!-- Label dengan uppercase & small font -->
                        <p class="text-muted small mb-1 text-uppercase fw-semibold">Total Pengaduan</p>
                        <!-- Number dengan font besar & bold -->
                        <h2 class="mb-0 fw-bold"><?php echo $stats['total']; ?></h2>
                    </div>
                    
                    <!-- Right side: Icon dengan background -->
                    <div class="p-3 bg-primary bg-opacity-10 rounded">
                        <!-- Icon folder dengan warna primary -->
                        <i class="bi bi-folder text-primary" style="font-size: 24px;"></i>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    
    <!-- ========================================================================
         CARD 2: Menunggu (Pending)
         ======================================================================== -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1 text-uppercase fw-semibold">Menunggu</p>
                        <!-- Number dengan warna warning (yellow/orange) -->
                        <!-- ?? 0: Default ke 0 jika key tidak ada di array -->
                        <h2 class="mb-0 fw-bold text-warning">
                            <?php echo $stats['by_status']['Menunggu'] ?? 0; ?>
                        </h2>
                    </div>
                    <!-- Icon clock dengan background warning -->
                    <div class="p-3 bg-warning bg-opacity-10 rounded">
                        <i class="bi bi-clock text-warning" style="font-size: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ========================================================================
         CARD 3: Diproses (In Progress)
         ======================================================================== -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1 text-uppercase fw-semibold">Diproses</p>
                        <!-- Number dengan warna info (blue) -->
                        <h2 class="mb-0 fw-bold text-info">
                            <?php echo $stats['by_status']['Diproses'] ?? 0; ?>
                        </h2>
                    </div>
                    <!-- Icon hourglass dengan background info -->
                    <div class="p-3 bg-info bg-opacity-10 rounded">
                        <i class="bi bi-hourglass-split text-info" style="font-size: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ========================================================================
         CARD 4: Selesai (Completed)
         ======================================================================== -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1 text-uppercase fw-semibold">Selesai</p>
                        <!-- Number dengan warna success (green) -->
                        <h2 class="mb-0 fw-bold text-success">
                            <?php echo $stats['by_status']['Selesai'] ?? 0; ?>
                        </h2>
                    </div>
                    <!-- Icon check-circle dengan background success -->
                    <div class="p-3 bg-success bg-opacity-10 rounded">
                        <i class="bi bi-check-circle text-success" style="font-size: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<!-- ============================================================================
     BREAKDOWN PER KATEGORI
     ============================================================================
     Card menampilkan jumlah pengaduan per kategori dengan icon
     ============================================================================ -->
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
            /**
             * ================================================================
             * MAPPING KATEGORI KE ICON
             * ================================================================
             * Setiap kategori punya icon Bootstrap Icons yang sesuai
             * ================================================================
             */
            $kategori_icons = [
                'Infrastruktur' => 'bi-tools',          // Icon tools/palu
                'Kebersihan'    => 'bi-recycle',        // Icon recycle
                'Keamanan'      => 'bi-shield-check',   // Icon shield
                'Pelayanan'     => 'bi-people',         // Icon people/users
                'Lainnya'       => 'bi-three-dots'      // Icon three dots
            ];
            
            /**
             * ================================================================
             * LOOP SETIAP KATEGORI
             * ================================================================
             * Generate card untuk setiap kategori dengan icon & jumlah
             * ================================================================
             */
            foreach (['Infrastruktur', 'Kebersihan', 'Keamanan', 'Pelayanan', 'Lainnya'] as $kategori):
                // Ambil jumlah untuk kategori ini, default 0 jika tidak ada
                $jumlah = $stats['by_kategori'][$kategori] ?? 0;
                
                // Ambil icon untuk kategori ini
                $icon = $kategori_icons[$kategori];
            ?>
            
            <!-- ============================================================
                 KATEGORI CARD
                 ============================================================
                 Responsive columns:
                 - col-md-6: 2 kolom di medium screen
                 - col-lg-4: 3 kolom di large screen
                 - col-xl: Auto width di XL (5 kolom karena ada 5 items)
                 ============================================================ -->
            <div class="col-md-6 col-lg-4 col-xl">
                <div class="d-flex align-items-center p-3 bg-light rounded">
                    <!-- Icon -->
                    <i class="bi <?php echo $icon; ?> text-primary me-3" style="font-size: 24px;"></i>
                    
                    <!-- Text: Nama kategori & jumlah -->
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

<!-- ============================================================================
     PENGADUAN TERBARU (RECENT COMPLAINTS)
     ============================================================================
     Tabel menampilkan 5 pengaduan terbaru dengan link ke detail
     ============================================================================ -->
<div class="card border-0 shadow-sm">
    
    <!-- ========================================================================
         CARD HEADER dengan Link "Lihat Semua"
         ======================================================================== -->
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex justify-content-between align-items-center">
            <!-- Left: Title dengan icon -->
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-clock-history text-primary me-2"></i>
                Pengaduan Terbaru
            </h5>
            
            <!-- Right: Link ke halaman kelola pengaduan -->
            <a href="pengaduan.php" class="btn btn-sm btn-outline-primary">
                Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    
    <!-- ========================================================================
         CARD BODY: Table Container
         ======================================================================== -->
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                
                <!-- ============================================================
                     TABLE HEADER
                     ============================================================ -->
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
                
                <!-- ============================================================
                     TABLE BODY: Data Rows
                     ============================================================ -->
                <tbody>
                    <?php if (empty($pengaduan_terbaru)): ?>
                    <!-- ========================================================
                         EMPTY STATE
                         ======================================================== -->
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            Belum ada pengaduan
                        </td>
                    </tr>
                    
                    <?php else: ?>
                    <!-- ========================================================
                         DATA ROWS: Loop setiap pengaduan
                         ======================================================== -->
                        <?php foreach ($pengaduan_terbaru as $item): ?>
                        <tr>
                            <!-- Kolom: Nomor Tiket -->
                            <td>
                                <span class="text-primary fw-semibold">
                                    <?php echo htmlspecialchars($item['nomor_tiket']); ?>
                                </span>
                            </td>
                            
                            <!-- Kolom: Judul (truncated to 50 chars) -->
                            <td>
                                <div class="fw-semibold">
                                    <?php 
                                    // Truncate judul jika lebih dari 50 karakter
                                    echo htmlspecialchars(substr($item['judul'], 0, 50)); 
                                    
                                    // Tambahkan "..." jika judul di-truncate
                                    echo strlen($item['judul']) > 50 ? '...' : ''; 
                                    ?>
                                </div>
                            </td>
                            
                            <!-- Kolom: Kategori (dengan badge) -->
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?php echo htmlspecialchars($item['kategori']); ?>
                                </span>
                            </td>
                            
                            <!-- Kolom: Status (dengan badge berwarna & icon) -->
                            <td>
                                <span class="badge <?php echo getStatusClass($item['status']); ?>">
                                    <!-- Icon status -->
                                    <i class="bi <?php echo getStatusIcon($item['status']); ?> me-1"></i>
                                    <!-- Text status -->
                                    <?php echo htmlspecialchars($item['status']); ?>
                                </span>
                            </td>
                            
                            <!-- Kolom: Tanggal Dibuat -->
                            <td>
                                <small class="text-muted">
                                    <?php echo formatTanggalIndonesia($item['tanggal_dibuat']); ?>
                                </small>
                            </td>
                            
                            <!-- Kolom: Aksi (Link ke detail) -->
                            <td>
                                <a href="detail-pengaduan.php?id=<?php echo $item['id']; ?>" 
                                   class="btn btn-sm btn-outline-primary"
                                   title="Lihat Detail">
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

<!-- ============================================================================
     INCLUDE ADMIN FOOTER
     ============================================================================
     Footer berisi closing tags dan JavaScript
     ============================================================================ -->
<?php include '../includes/admin-footer.php'; ?>
