<?php
/**
 * Halaman Login Admin
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

$page_title = 'Login Admin';
$error_message = '';

// Redirect jika sudah login
if (isAdmin()) {
    redirect(BASE_URL . '/admin/dashboard.php');
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                
                redirect(BASE_URL . '/admin/dashboard.php');
            } else {
                $error_message = "Username atau password salah";
            }
        } catch (PDOException $e) {
            $error_message = "Terjadi kesalahan sistem";
        }
    }
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
    
    <style>
        body {
            background: linear-gradient(135deg, #065fd4 0%, #0b5ed7 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            max-width: 400px;
            width: 100%;
        }
        .brand-icon {
            font-size: 48px;
            color: #065fd4;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="card border-0 shadow-lg">
        <div class="card-body p-5">
            
            <!-- Logo -->
            <div class="text-center mb-4">
                <i class="bi bi-megaphone-fill brand-icon"></i>
                <h4 class="mt-3 mb-1 fw-bold"><?php echo APP_NAME; ?></h4>
                <p class="text-muted small">Admin Login</p>
            </div>
            
            <!-- Error Alert -->
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form method="POST" action="">
                
                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold">Username</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               placeholder="Masukkan username"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               required 
                               autofocus>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="Masukkan password"
                               required>
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Login
                    </button>
                </div>
                
            </form>
            
            <!-- Info -->
            <div class="mt-4 pt-4 border-top text-center">
                <a href="<?php echo BASE_URL; ?>" class="text-decoration-none text-muted small">
                    <i class="bi bi-arrow-left me-2"></i>
                    Kembali ke Halaman Utama
                </a>
            </div>
            
            <!-- Default Credentials Info -->
            <div class="alert alert-info mt-4 mb-0" role="alert">
                <small>
                    <strong>Default Login:</strong><br>
                    Username: <code>admin</code><br>
                    Password: <code>admin123</code>
                </small>
            </div>
            
        </div>
    </div>
    
    <!-- Footer -->
    <div class="text-center mt-4 text-white small">
        <?php echo COPYRIGHT; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>