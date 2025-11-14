<?php
/**
 * Halaman Login Admin
 * © 2025 Dafa al hafiz - 24_0085
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

$page_title = 'Login Admin';
$error_message = '';

// Redirect jika sudah login
if (isAdmin()) {
    redirect(BASE_URL . '/admin/dashboard.php');
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "Invalid request. Please try again.";
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error_message = "Username dan password harus diisi";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
                $stmt->execute([$username]);
                $admin = $stmt->fetch();
                
                if ($admin && password_verify($password, $admin['password'])) {
                    // Login berhasil
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_nama'] = $admin['nama_lengkap'];
                    
                    // Regenerate session ID untuk keamanan
                    session_regenerate_id(true);
                    
                    redirect(BASE_URL . '/admin/dashboard.php');
                } else {
                    $error_message = "Username atau password salah";
                }
            } catch (PDOException $e) {
                error_log("Login error: " . $e->getMessage());
                $error_message = "Terjadi kesalahan sistem";
            }
        }
    }
    
    // Generate new CSRF token after failed login
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Login Style -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/login-style.css">
</head>
<body class="login-page">

<div class="login-card">
    <div class="card">
        <div class="card-body">
            
            <!-- Brand -->
            <div class="brand-section">
                <i class="bi bi-shield-lock brand-icon" aria-hidden="true"></i>
                <h1 class="brand-title"><?php echo APP_NAME; ?></h1>
                <p class="brand-subtitle">Admin Login</p>
            </div>
            
            <!-- Error Alert -->
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-circle me-2" aria-hidden="true"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form method="POST" action="" class="login-form" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text" aria-hidden="true">
                            <i class="bi bi-person"></i>
                        </span>
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
                
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text" aria-hidden="true">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="Masukkan password"
                               autocomplete="current-password"
                               required>
                    </div>
                </div>
                
                <button type="submit" class="btn-login" id="loginBtn">
                    <span class="btn-text">
                        <i class="bi bi-box-arrow-in-right me-2" aria-hidden="true"></i>
                        Login
                    </span>
                    <span class="btn-loading d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Memproses...
                    </span>
                </button>
                
            </form>
            
            <!-- Footer Link -->
            <div class="login-footer">
                <a href="<?php echo BASE_URL; ?>">
                    <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>
                    Kembali ke Halaman Utama
                </a>
            </div>
            
            <!-- Default Credentials Info (Hapus di production!) -->
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
    
    <!-- Copyright -->
    <div class="login-copyright">
        <?php echo COPYRIGHT; ?>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Login Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const btnText = loginBtn.querySelector('.btn-text');
    const btnLoading = loginBtn.querySelector('.btn-loading');
    
    loginForm.addEventListener('submit', function() {
        // Show loading state
        loginBtn.disabled = true;
        btnText.classList.add('d-none');
        btnLoading.classList.remove('d-none');
    });
    
    // Auto-hide alert after 5 seconds
    const alert = document.querySelector('.alert');
    if (alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s ease';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    }
});
</script>

</body>
</html>