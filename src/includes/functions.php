<?php
/**
 * Helper functions untuk Sistem Pengaduan Online
 */

/**
 * Generate nomor tiket unik
 * Format: TKT-YYYYMMDD-XXXX
 */
function generateNomorTiket($pdo) {
    $tanggal = date('Ymd');
    $prefix = "TKT-{$tanggal}-";
    
    // Cari nomor terakhir hari ini
    $stmt = $pdo->prepare("SELECT nomor_tiket FROM pengaduan WHERE nomor_tiket LIKE ? ORDER BY nomor_tiket DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $last = $stmt->fetch();
    
    if ($last) {
        $lastNumber = intval(substr($last['nomor_tiket'], -4));
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }
    
    return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validasi email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Format tanggal Indonesia
 */
function formatTanggalIndonesia($datetime) {
    $bulan = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];
    
    $timestamp = strtotime($datetime);
    $tanggal = date('j', $timestamp);
    $bulanAngka = date('n', $timestamp);
    $tahun = date('Y', $timestamp);
    $waktu = date('H:i', $timestamp);
    
    return "{$tanggal} {$bulan[$bulanAngka]} {$tahun}, {$waktu}";
}

/**
 * Get status badge class
 */
function getStatusClass($status) {
    $classes = [
        'Menunggu' => 'status-pending',
        'Diproses' => 'status-in-progress',
        'Selesai' => 'status-resolved',
        'Ditolak' => 'status-rejected'
    ];
    return $classes[$status] ?? 'status-pending';
}

/**
 * Get status icon
 */
function getStatusIcon($status) {
    $icons = [
        'Menunggu' => 'bi-clock',
        'Diproses' => 'bi-hourglass-split',
        'Selesai' => 'bi-check-circle',
        'Ditolak' => 'bi-x-circle'
    ];
    return $icons[$status] ?? 'bi-clock';
}

/**
 * Check if user is admin (logged in)
 */
function isAdmin() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Get statistik pengaduan
 */
function getStatistikPengaduan($pdo) {
    // Total pengaduan
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pengaduan");
    $total = $stmt->fetch()['total'];
    
    // By status
    $stmt = $pdo->query("SELECT status, COUNT(*) as jumlah FROM pengaduan GROUP BY status");
    $byStatus = [];
    while ($row = $stmt->fetch()) {
        $byStatus[$row['status']] = $row['jumlah'];
    }
    
    // By kategori
    $stmt = $pdo->query("SELECT kategori, COUNT(*) as jumlah FROM pengaduan GROUP BY kategori");
    $byKategori = [];
    while ($row = $stmt->fetch()) {
        $byKategori[$row['kategori']] = $row['jumlah'];
    }
    
    return [
        'total' => $total,
        'by_status' => $byStatus,
        'by_kategori' => $byKategori
    ];
}

/**
 * Export pengaduan ke CSV
 */
function exportToCSV($data, $filename = null) {
    if (!$filename) {
        $filename = 'pengaduan_export_' . date('Ymd_His') . '.csv';
    }
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Output UTF-8 BOM untuk kompatibilitas Excel
    echo "\xEF\xBB\xBF";
    
    $output = fopen('php://output', 'w');
    
    // Header CSV
    fputcsv($output, [
        'Nomor Tiket',
        'Judul',
        'Deskripsi',
        'Kategori',
        'Nama Pelapor',
        'Email',
        'Telepon',
        'Status',
        'Tanggal Dibuat',
        'Tanggal Diperbarui'
    ]);
    
    // Data
    foreach ($data as $row) {
        fputcsv($output, [
            $row['nomor_tiket'],
            $row['judul'],
            $row['deskripsi'],
            $row['kategori'],
            $row['nama_pelapor'],
            $row['email'] ?? '',
            $row['telepon'] ?? '',
            $row['status'],
            $row['tanggal_dibuat'],
            $row['tanggal_diperbarui']
        ]);
    }
    
    fclose($output);
    exit();
}
?>