<?php
/**
 * ============================================================================
 * ADMIN LOGIN PAGE
 * ============================================================================
 * File: admin/login.php
 * Deskripsi: Halaman login untuk admin dengan security features
 * Fitur:
 * - Username & password authentication
 * - CSRF protection
 * - Session regeneration after login
 * - Password hashing (bcrypt)
 * - Loading state UI
 * - Auto-hide error alerts
 * - Responsive design
 * 
 * Security Features:
 * - CSRF token validation
 * - Password hashing with bcrypt
 * - Session regeneration (prevent session fixation)
 * - Prepared statements (prevent SQL injection)
 * - Input sanitization
 * - Error logging (tidak expose detail ke user)
 * 
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
$page_title = 'Login Admin';                // Title halaman
$error_message = '';                         // Pesan error login

// ============================================================================
// REDIRECT IF ALREADY LOGGED IN
// ============================================================================
// Jika user sudah login sebagai admin, langsung redirect ke dashboard
// Tidak perlu login lagi
if (isAdmin()) {
    redirect(BASE_URL . '/admin/dashboard.php');
}

// ============================================================================
// GENERATE CSRF TOKEN
// ============================================================================
// CSRF (Cross-Site Request Forgery) Protection
// 
// What is CSRF?
// Attack dimana attacker membuat user mensubmit request tanpa sadar
// 
// How it works:
// 1. Generate random token saat load form
// 2. Simpan token di session
// 3. Include token di form (hidden input)
// 4. Saat submit, validasi token di POST = token di session
// 
// Benefit:
// Attacker tidak bisa forge request karena tidak tahu token nya
if (empty($_SESSION['csrf_token'])) {
    // Generate random 32-byte token
    // bin2hex(): Convert binary ke hexadecimal string
    // random_bytes(32): Generate 32 random bytes (256 bits)
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ============================================================================
// PROCESS LOGIN (POST Request)
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ========================================================================
    // STEP 1: CSRF Token Validation
    // ========================================================================
    // Validasi CSRF token dari POST vs session
    // Jika tidak match = possible CSRF attack
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "Invalid request. Please try again.";
    } else {
        // ====================================================================
        // STEP 2: Get & Sanitize Input
        // ====================================================================
        $username = sanitizeInput($_POST['username'] ?? '');
        
        // Password tidak di-sanitize dengan htmlspecialchars
        // Karena password_verify() handle security
        // htmlspecialchars bisa break password yang ada karakter special
        $password = $_POST['password'] ?? '';
        
        // ====================================================================
        // STEP 3: Validation - Empty Check
        // ====================================================================
        if (empty($username) || empty($password)) {
            $error_message = "Username dan password harus diisi";
        } else {
            try {
                // ============================================================
                // STEP 4: Query Admin dari Database
                // ============================================================
                // Prepared statement untuk prevent SQL Injection
                $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
                $stmt->execute([$username]);
                $admin = $stmt->fetch();
                
                // ============================================================
                // STEP 5: Verify Password
                // ============================================================
                // password_verify(): Built-in PHP function untuk verify bcrypt hash
                // 
                // How it works:
                // - Compare plain text password vs hashed password
                // - Secure: Timing attack resistant
                // - Automatic: Handle salt automatically
                // 
                // Why bcrypt?
                // - Slow by design (expensive to brute force)
                // - Adaptive: Can increase cost factor over time
                // - Built-in salt
                if ($admin && password_verify($password, $admin['password'])) {
                    // ========================================================
                    // LOGIN SUCCESSFUL
                    // ========================================================
                    
                    // Store admin info di session
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_nama'] = $admin['nama_lengkap'];
                    
                    // ========================================================
                    // SESSION REGENERATION (Security Best Practice)
                    // ========================================================
                    // Regenerate session ID untuk prevent session fixation attack
                    // 
                    // What is session fixation?
                    // Attacker set session ID, user login dengan session ID tersebut,
                    // attacker bisa hijack session
                    // 
                    // How regeneration helps?
                    // Generate new session ID setelah login
                    // Old session ID jadi invalid
                    // 
                    // Parameter true: Delete old session file
                    session_regenerate_id(true);
                    
                    // Redirect ke dashboard
                    redirect(BASE_URL . '/admin/dashboard.php');
                    
                } else {
                    // ========================================================
                    // LOGIN FAILED
                    // ========================================================
                    // Generic error message
                    // Jangan spesifik "username salah" atau "password salah"
                    // Untuk prevent username enumeration attack
                    $error_message = "Username atau password salah";
                }
                
            } catch (PDOException $e) {
                // ============================================================
                // DATABASE ERROR
                // ============================================================
                // Log error ke PHP error log (jangan tampilkan ke user)
                error_log("Login error: " . $e->getMessage());
                
                // Generic error message untuk user
                $error_message = "Terjadi kesalahan sistem";
            }
        }
    }
    
    // ========================================================================
    // GENERATE NEW CSRF TOKEN
    // ========================================================================
    // Generate new token setelah failed login
    // Untuk prevent CSRF token reuse
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <!-- ====================================================================
         META TAGS
         ==================================================================== -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo APP_NAME; ?></title>
    
    <!-- ====================================================================
         CSS LIBRARIES
         ==================================================================== -->
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom Login Style -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/login-style.css">
</head>
<body class="login-page">

<!-- ============================================================================
     LOGIN CARD CONTAINER
     ============================================================================ -->
<div class="login-card">
    <div class="card">
        <div class="card-body">
            
            <!-- ================================================================
                 BRAND SECTION
                 ================================================================ -->
            <div class="brand-section">
                <!-- Icon shield-lock untuk security -->
                <i class="bi bi-shield-lock brand-icon" aria-hidden="true"></i>
                
                <!-- App name -->
                <h1 class="brand-title"><?php echo APP_NAME; ?></h1>
                
                <!-- Subtitle -->
                <p class="brand-subtitle">Admin Login</p>
            </div>
            
            <!-- ================================================================
                 ERROR ALERT
                 ================================================================
                 Tampil hanya jika ada error message
                 ================================================================ -->
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-circle me-2" aria-hidden="true"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
            <?php endif; ?>
            
            <!-- ================================================================
                 LOGIN FORM
                 ================================================================ -->
            <form method="POST" action="" class="login-form" id="loginForm">
                <!-- ============================================================
                     CSRF TOKEN (Hidden Input)
                     ============================================================
                     Token untuk CSRF protection
                     ============================================================ -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <!-- ============================================================
                     INPUT: Username
                     ============================================================ -->
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <!-- Icon person -->
                        <span class="input-group-text" aria-hidden="true">
                            <i class="bi bi-person"></i>
                        </span>
                        
                        <!-- Input field -->
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               placeholder="Masukkan username"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               autocomplete="username"
                               required 
                               autofocus>
                    </div>
                </div>
                
                <!-- ============================================================
                     INPUT: Password
                     ============================================================ -->
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <!-- Icon lock -->
                        <span class="input-group-text" aria-hidden="true">
                            <i class="bi bi-lock"></i>
                        </span>
                        
                        <!-- Input field -->
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="Masukkan password"
                               autocomplete="current-password"
                               required>
                    </div>
                </div>
                
                <!-- ============================================================
                     SUBMIT BUTTON dengan Loading State
                     ============================================================ -->
                <button type="submit" class="btn-login" id="loginBtn">
                    <!-- Normal state (default) -->
                    <span class="btn-text">
                        <i class="bi bi-box-arrow-in-right me-2" aria-hidden="true"></i>
                        Login
                    </span>
                    
                    <!-- Loading state (hidden by default) -->
                    <span class="btn-loading d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Memproses...
                    </span>
                </button>
                
            </form>
            
            <!-- ================================================================
                 FOOTER LINK
                 ================================================================ -->
            <div class="login-footer">
                <a href="<?php echo BASE_URL; ?>">
                    <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>
                    Kembali ke Halaman Utama
                </a>
            </div>
            
            <!-- ================================================================
                 DEFAULT CREDENTIALS INFO (Development Only)
                 ================================================================
                 Tampil hanya di development environment
                 HAPUS DI PRODUCTION!
                 ================================================================ -->
            <?php if (defined('APP_ENV') && APP_ENV === 'development'): ?>
            <div class="default-credentials">
                <small>
                    <strong>Default Login:</strong><br>
                    Username: <code>admin</code><br>
                    Password: <code>admin123</code>
                </small>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
    
    <!-- ========================================================================
         COPYRIGHT
         ======================================================================== -->
    <div class="login-copyright">
        <?php echo COPYRIGHT; ?>
    </div>
</div>

<!-- ============================================================================
     JAVASCRIPT LIBRARIES
     ============================================================================ -->
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- ============================================================================
     LOGIN SCRIPT
     ============================================================================ -->
<script>
/**
 * ============================================================================
 * LOGIN FORM ENHANCEMENTS
 * ============================================================================
 * Features:
 * 1. Loading state saat submit form
 * 2. Auto-hide error alert setelah 5 detik
 * ============================================================================
 */
document.addEventListener('DOMContentLoaded', function() {
    // ========================================================================
    // GET DOM ELEMENTS
    // ========================================================================
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const btnText = loginBtn.querySelector('.btn-text');
    const btnLoading = loginBtn.querySelector('.btn-loading');
    
    // ========================================================================
    // FORM SUBMIT HANDLER - Show Loading State
    // ========================================================================
    loginForm.addEventListener('submit', function() {
        // Disable button untuk prevent double submit
        loginBtn.disabled = true;
        
        // Hide normal text
        btnText.classList.add('d-none');
        
        // Show loading text dengan spinner
        btnLoading.classList.remove('d-none');
    });
    
    // ========================================================================
    // AUTO-HIDE ALERT
    // ========================================================================
    // Auto-hide error alert setelah 5 detik
    const alert = document.querySelector('.alert');
    if (alert) {
        setTimeout(function() {
            // Fade out animation
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s ease';
            
            // Remove dari DOM setelah animation selesai
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    }
});
</script>

</body>
</html>