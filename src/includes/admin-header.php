<?php
/**
 * Admin Header dengan Sidebar
 * Dipanggil setelah cek login
 */

// Pastikan sudah login
if (!isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}

// Get admin data
$stmt = $pdo->prepare("SELECT * FROM admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin_data = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Admin' : 'Admin'; ?> - <?php echo APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin-style.css">
</head>
<body class="admin-body">

<!-- Sidebar -->
<div class="admin-sidebar">
    <div class="sidebar-header">
        <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="sidebar-brand">
            <i class="bi bi-megaphone-fill text-primary"></i>
            <span class="brand-text">Admin Panel</span>
        </a>
    </div>
    
    <nav class="sidebar-nav">
        <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
        
        <a href="<?php echo BASE_URL; ?>/admin/pengaduan.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'pengaduan.php' ? 'active' : ''; ?>">
            <i class="bi bi-folder"></i>
            <span>Kelola Pengaduan</span>
        </a>
        
        <hr class="sidebar-divider">
        
        <a href="<?php echo BASE_URL; ?>" class="sidebar-link" target="_blank">
            <i class="bi bi-house"></i>
            <span>Lihat Website</span>
        </a>
        
        <a href="<?php echo BASE_URL; ?>/admin/logout.php" class="sidebar-link text-danger">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </nav>
</div>

<!-- Main Content -->
<div class="admin-main">
    
    <!-- Top Navigation -->
    <nav class="admin-topbar">
        <div class="d-flex justify-content-between align-items-center w-100">
            <div>
                <button class="btn btn-sm btn-light d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted">
                    <i class="bi bi-person-circle me-2"></i>
                    <?php echo htmlspecialchars($admin_data['nama_lengkap']); ?>
                </span>
            </div>
        </div>
    </nav>
    
    <!-- Content Area -->
    <div class="admin-content">