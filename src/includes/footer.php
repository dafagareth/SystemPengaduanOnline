<?php
/**
 * ============================================================================
 * PUBLIC FOOTER - LAYOUT UNTUK HALAMAN PUBLIK
 * ============================================================================
 * File: includes/footer.php
 * Deskripsi: Footer HTML untuk halaman publik dengan info aplikasi
 * Fitur:
 * - App info (nama & versi)
 * - Copyright notice
 * - Responsive layout (2 kolom di desktop, stack di mobile)
 * - Bootstrap JavaScript bundle
 * Author: Dafa al hafiz - 24_0085
 * Tanggal: 2025
 * ============================================================================
 */
?>

    <!-- ====================================================================
         FOOTER SECTION
         ====================================================================
         Footer yang ditampilkan di bottom setiap halaman publik
         
         Bootstrap Classes:
         - bg-secondary: Background color abu-abu (secondary)
         - bg-opacity-10: Opacity 10% (very light gray)
         - border-top: Border di atas footer (separator)
         - mt-auto: Margin top auto (push ke bottom jika content pendek)
         - py-3: Padding vertical 1rem (16px)
         ==================================================================== -->
    <footer class="bg-secondary bg-opacity-10 border-top mt-auto py-3">
        
        <!-- ================================================================
             CONTAINER FLUID
             ================================================================
             Container dengan full width + padding horizontal
             ================================================================ -->
        <div class="container-fluid px-4">
            
            <!-- ============================================================
                 ROW: 2 Kolom Layout
                 ============================================================
                 Layout 2 kolom di desktop, stack di mobile
                 ============================================================ -->
            <div class="row">
                
                <!-- ========================================================
                     KOLOM KIRI: Nama & Versi Aplikasi
                     ========================================================
                     col-md-6: 50% width di medium screen & up (≥768px)
                     Di mobile (<768px): 100% width (stack)
                     ======================================================== -->
                <div class="col-md-6">
                    <p class="text-muted mb-0 small">
                        <!-- ================================================
                             APP NAME & VERSION
                             ================================================
                             Menampilkan nama aplikasi dan versi
                             Format: "Sistem Pengaduan Online v1.0.0"
                             
                             Classes:
                             - text-muted: Abu-abu (tidak terlalu kontras)
                             - mb-0: Margin bottom 0 (remove default <p> margin)
                             - small: Font size lebih kecil (0.875rem / 14px)
                             ================================================ -->
                        <strong><?php echo APP_NAME; ?></strong> v<?php echo APP_VERSION; ?>
                    </p>
                </div>
                
                <!-- ========================================================
                     KOLOM KANAN: Copyright Notice
                     ========================================================
                     col-md-6: 50% width di medium screen & up
                     text-md-end: Text align right di medium screen & up
                     Di mobile: Text align left (default)
                     ======================================================== -->
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0 small">
                        <?php 
                        /**
                         * COPYRIGHT: Konstanta dari config.php
                         * Berisi copyright notice
                         * Contoh: "© 2025 Dafa al hafiz - 24_0085. All rights reserved."
                         */
                        echo COPYRIGHT; 
                        ?>
                    </p>
                </div>
                
            </div>
        </div>
    </footer>
    
    <!-- ====================================================================
         JAVASCRIPT LIBRARIES
         ====================================================================
         Script dimuat di akhir <body> untuk performa lebih baik
         
         Best Practice:
         - Load JavaScript di akhir body (bukan di <head>)
         - Alasan: HTML sudah di-render dulu sebelum load JS
         - Hasil: Page load lebih cepat (perceived performance)
         ==================================================================== -->
    
    <!-- ====================================================================
         BOOTSTRAP BUNDLE
         ====================================================================
         Bootstrap JavaScript + Popper.js (for tooltips/popovers/dropdowns)
         
         What's included:
         1. Bootstrap JS: JavaScript untuk komponen interaktif
            - Modal, Collapse, Dropdown, Alert, dll
         2. Popper.js: Library untuk positioning tooltips & popovers
         
         CDN: jsdelivr.net
         - Fast & reliable CDN
         - Auto minified (.min.js)
         - Browser caching (users mungkin sudah punya cached version)
         
         Version: 5.3.0 (sama dengan Bootstrap CSS)
         ==================================================================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- ====================================================================
         CUSTOM JAVASCRIPT (OPTIONAL)
         ====================================================================
         Jika ada custom JavaScript untuk halaman publik, bisa ditambahkan di sini
         Contoh:
         <script src="<?php echo BASE_URL; ?>/assets/js/script.js"></script>
         ==================================================================== -->
    
</body>
</html>