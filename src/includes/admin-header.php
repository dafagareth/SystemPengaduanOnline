<?php
/**
 * ============================================================================
 * ADMIN HEADER - LAYOUT ADMIN PANEL
 * ============================================================================
 * File: admin-header.php
 * Deskripsi: Header layout untuk halaman admin dengan sidebar navigation
 * Fitur:
 * - Sidebar menu navigasi
 * - Top bar dengan user info
 * - Mobile responsive (toggle sidebar)
 * - Active menu highlighting
 * Security: Memastikan user sudah login sebagai admin
 * Author: Dafa al hafiz - 24_0085
 * ============================================================================
 */

// ============================================================================
// SECURITY CHECK: Pastikan User Sudah Login
// ============================================================================
// Redirect ke login page jika belum login sebagai admin
if (!isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}

// ============================================================================
// FETCH ADMIN DATA
// ============================================================================
// Ambil data admin dari database berdasarkan session ID
// Data ini digunakan untuk menampilkan nama admin di top bar

$stmt = $pdo->prepare("SELECT * FROM admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin_data = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <!-- ====================================================================
         META TAGS
         ==================================================================== -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- ====================================================================
         TITLE TAG
         ==================================================================== -->
    <!-- Title dinamis: "Nama Halaman - Admin - Sistem Pengaduan Online" -->
    <title><?php echo isset($page_title) ? $page_title . ' - Admin' : 'Admin'; ?> - <?php echo APP_NAME; ?></title>
    
    <!-- ====================================================================
         CSS LIBRARIES
         ==================================================================== -->
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom Admin CSS -->
    <!-- admin-style.css berisi style khusus untuk admin panel (sidebar, layout, etc) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin-style.css">
</head>

<body class="admin-body">
    
    <!-- ====================================================================
         SIDEBAR NAVIGATION
         ====================================================================
         Sidebar untuk navigasi admin panel
         Features:
         - Fixed position di kiri
         - Active menu highlighting
         - Mobile toggle (show/hide)
         ==================================================================== -->
    <div class="admin-sidebar">
        
        <!-- ================================================================
             SIDEBAR HEADER: Brand/Logo
             ================================================================ -->
        <div class="sidebar-header">
            <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="sidebar-brand">
                <!-- Icon megaphone untuk logo -->
                <i class="bi bi-megaphone-fill text-primary"></i>
                <span class="brand-text">Admin Panel</span>
            </a>
        </div>
        
        <!-- ================================================================
             SIDEBAR NAVIGATION LINKS
             ================================================================ -->
        <nav class="sidebar-nav">
            
            <!-- ============================================================
                 MENU: Dashboard
                 ============================================================ -->
            <!-- Active class ditambahkan jika current page = dashboard.php -->
            <!-- basename($_SERVER['PHP_SELF']): Ambil nama file saat ini -->
            <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" 
               class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            
            <!-- ============================================================
                 MENU: Kelola Pengaduan
                 ============================================================ -->
            <!-- Active class jika current page = pengaduan.php -->
            <a href="<?php echo BASE_URL; ?>/admin/pengaduan.php" 
               class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'pengaduan.php' ? 'active' : ''; ?>">
                <i class="bi bi-folder"></i>
                <span>Kelola Pengaduan</span>
            </a>
            
            <!-- Divider line -->
            <hr class="sidebar-divider">
            
            <!-- ============================================================
                 MENU: Lihat Website (New Tab)
                 ============================================================ -->
            <!-- target="_blank": Buka di tab baru -->
            <a href="<?php echo BASE_URL; ?>" 
               class="sidebar-link" 
               target="_blank">
                <i class="bi bi-house"></i>
                <span>Lihat Website</span>
            </a>
            
            <!-- ============================================================
                 MENU: Logout
                 ============================================================ -->
            <!-- text-danger: Red color untuk logout button -->
            <a href="<?php echo BASE_URL; ?>/admin/logout.php" 
               class="sidebar-link text-danger">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
            
        </nav>
    </div>
    
    <!-- ====================================================================
         MAIN CONTENT AREA
         ====================================================================
         Area utama untuk konten halaman admin
         Layout: Sidebar (fixed) + Main (flexible width)
         ==================================================================== -->
    <div class="admin-main">
        
        <!-- ================================================================
             TOP NAVIGATION BAR
             ================================================================
             Top bar dengan toggle button (mobile) dan user info
             ================================================================ -->
        <nav class="admin-topbar">
            <div class="d-flex justify-content-between align-items-center w-100">
                
                <!-- ========================================================
                     LEFT SIDE: Toggle Button (Mobile Only)
                     ======================================================== -->
                <div>
                    <!-- Sidebar toggle button -->
                    <!-- d-lg-none: Display none di large screen (≥992px) -->
                    <!-- id="sidebarToggle": Untuk JavaScript handler -->
                    <button class="btn btn-sm btn-light d-lg-none" id="sidebarToggle">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
                
                <!-- ========================================================
                     RIGHT SIDE: User Info
                     ======================================================== -->
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted">
                        <!-- Icon user circle -->
                        <i class="bi bi-person-circle me-2"></i>
                        <!-- Nama lengkap admin dari database -->
                        <?php echo htmlspecialchars($admin_data['nama_lengkap']); ?>
                    </span>
                </div>
                
            </div>
        </nav>
        
        <!-- ================================================================
             CONTENT AREA
             ================================================================
             Area untuk konten halaman (akan diisi oleh setiap halaman)
             Tag <div> ini akan ditutup di admin-footer.php
             ================================================================ -->
        <div class="admin-content">
            <!-- Content dari setiap halaman akan ditampilkan di sini -->