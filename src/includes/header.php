<?php
/**
 * ============================================================================
 * PUBLIC HEADER - LAYOUT UNTUK HALAMAN PUBLIK
 * ============================================================================
 * File: includes/header.php
 * Deskripsi: Header HTML dan navbar untuk halaman publik (non-admin)
 * Fitur:
 * - Responsive HTML5 structure
 * - Bootstrap 5 framework
 * - Public navigation bar (sticky)
 * - Dynamic page title
 * - Mobile-friendly design
 * Author: Dafa al hafiz - 24_0085
 * Tanggal: 2025
 * ============================================================================
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <!-- ====================================================================
         META TAGS - PENGATURAN DASAR HTML
         ====================================================================
         Meta tags untuk charset, viewport, dan SEO
         ==================================================================== -->
    
    <!-- Character Encoding: UTF-8 -->
    <!-- UTF-8 support karakter Indonesia (é, ñ, dll) dan emoji -->
    <meta charset="UTF-8">
    
    <!-- Viewport Meta Tag: Responsive Design -->
    <!-- width=device-width: Lebar viewport sama dengan lebar device -->
    <!-- initial-scale=1.0: Zoom level awal 1x (tidak zoom in/out) -->
    <!-- Tanpa tag ini, mobile browser akan render page seperti desktop -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- ====================================================================
         TITLE TAG - JUDUL HALAMAN
         ====================================================================
         Title dinamis yang muncul di browser tab
         Format: "Nama Halaman - Sistem Pengaduan Online"
         ==================================================================== -->
    <title>
        <?php 
        /**
         * Conditional Title:
         * - Jika variabel $page_title ada: "Nama Halaman - APP_NAME"
         * - Jika tidak ada: "APP_NAME" saja
         * 
         * isset(): Cek apakah variabel sudah di-define
         * Ternary operator (? :): if-else dalam satu baris
         */
        echo isset($page_title) ? $page_title . ' - ' . APP_NAME : APP_NAME; 
        ?>
    </title>
    
    <!-- ====================================================================
         CSS FRAMEWORKS & LIBRARIES
         ====================================================================
         Load CSS dari CDN untuk performa dan caching yang lebih baik
         ==================================================================== -->
    
    <!-- Bootstrap 5.3.0 CSS -->
    <!-- Framework CSS untuk styling dan komponen UI -->
    <!-- Fitur: Grid system, components, utilities, responsive -->
    <!-- CDN: jsdelivr (fast & reliable) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons 1.11.0 -->
    <!-- Icon library dari Bootstrap team -->
    <!-- 1900+ icons untuk UI -->
    <!-- Usage: <i class="bi bi-icon-name"></i> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <!-- Stylesheet kustom untuk override Bootstrap dan style tambahan -->
    <!-- Path relatif dari BASE_URL (defined in config.php) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>

<body>
    <!-- ====================================================================
         NAVIGATION BAR - PUBLIC
         ====================================================================
         Navbar untuk pengunjung umum (bukan admin)
         
         Bootstrap Classes:
         - navbar: Base navbar component
         - navbar-expand-lg: Collapse menu di screen < large (992px)
         - navbar-light: Light color scheme (dark text)
         - bg-white: Background putih
         - border-bottom: Border di bawah navbar
         - sticky-top: Navbar tetap di atas saat scroll
         ==================================================================== -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
        
        <!-- ================================================================
             CONTAINER FLUID
             ================================================================
             Container dengan full width + padding horizontal
             px-4: Padding horizontal 1.5rem (24px)
             ================================================================ -->
        <div class="container-fluid px-4">
            
            <!-- ============================================================
                 NAVBAR BRAND - LOGO / NAMA APLIKASI
                 ============================================================
                 Brand biasanya di kiri navbar, link ke homepage
                 ============================================================ -->
            <a class="navbar-brand fw-semibold" href="<?php echo BASE_URL; ?>">
                <?php 
                /**
                 * APP_NAME: Konstanta dari config.php
                 * Berisi nama aplikasi: "Sistem Pengaduan Online"
                 * fw-semibold: Font weight semi-bold (600)
                 */
                echo APP_NAME; 
                ?>
            </a>
            
            <!-- ============================================================
                 NAVBAR NAVIGATION LINKS
                 ============================================================
                 Link-link navigasi di kanan navbar
                 
                 d-flex: Flexbox container
                 gap-2: Spacing 0.5rem (8px) antar elemen
                 ============================================================ -->
            <div class="d-flex gap-2">
                
                <!-- ========================================================
                     LINK 1: Daftar Pengaduan
                     ========================================================
                     Halaman untuk melihat semua pengaduan
                     
                     btn: Bootstrap button component
                     btn-sm: Small size button
                     btn-outline-primary: Outlined button (border only)
                     ======================================================== -->
                <a href="<?php echo BASE_URL; ?>/daftar-pengaduan.php" 
                   class="btn btn-sm btn-outline-primary">
                    Daftar Pengaduan
                </a>
                
                <!-- ========================================================
                     LINK 2: Cek Status
                     ========================================================
                     Halaman untuk cek status pengaduan by nomor tiket
                     
                     btn-outline-secondary: Outlined button (gray border)
                     ======================================================== -->
                <a href="<?php echo BASE_URL; ?>/cek-pengaduan.php" 
                   class="btn btn-sm btn-outline-secondary">
                    Cek Status
                </a>
                
                <!-- ========================================================
                     LINK 3: Admin Login
                     ========================================================
                     Link ke halaman login admin
                     
                     btn-primary: Solid primary color button (blue)
                     Lebih prominent karena action penting
                     ======================================================== -->
                <a href="<?php echo BASE_URL; ?>/admin/login.php" 
                   class="btn btn-sm btn-primary">
                    Admin
                </a>
                
            </div>
        </div>
    </nav>
    
    <!-- ====================================================================
         MAIN CONTENT AREA
         ====================================================================
         Area ini akan diisi oleh konten dari setiap halaman
         
         Flow:
         1. header.php dibuka (sampai di sini)
         2. Content halaman di-render
         3. footer.php menutup tags yang terbuka
         
         Note: Tag <body> dan lainnya akan ditutup di footer.php
         ==================================================================== -->