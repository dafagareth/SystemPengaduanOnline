<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . APP_NAME : APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
    
<!-- Public Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-semibold" href="<?php echo BASE_URL; ?>">
            <?php echo APP_NAME; ?>
        </a>
        
        <div class="d-flex gap-2">
            <a href="<?php echo BASE_URL; ?>/daftar-pengaduan.php" class="btn btn-sm btn-outline-primary">
                Daftar Pengaduan
            </a>
            <a href="<?php echo BASE_URL; ?>/cek-pengaduan.php" class="btn btn-sm btn-outline-secondary">
                Cek Status
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/login.php" class="btn btn-sm btn-primary">
                Admin
            </a>
        </div>
    </div>
</nav>