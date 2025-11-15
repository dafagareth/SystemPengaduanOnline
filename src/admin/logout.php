<?php
/**
 * ============================================================================
 * ADMIN LOGOUT
 * ============================================================================
 * File: admin/logout.php
 * Deskripsi: Logout admin dan destroy session
 * Fungsi:
 * - Destroy semua session data
 * - Clear session cookie
 * - Redirect ke login page
 * 
 * Security:
 * - Complete session destruction (prevent session reuse)
 * - Cookie cleanup
 * - Redirect to prevent back button access
 * 
 * Author: Dafa al hafiz - 24_0085
 * Tanggal: 2025
 * ============================================================================
 */

// ============================================================================
// INCLUDE CONFIGURATION
// ============================================================================
// Include config untuk session_start() dan konstanta
require_once '../includes/config.php';

// ============================================================================
// SESSION DESTRUCTION
// ============================================================================
// Destroy session dengan proper cleanup
// 
// Why proper cleanup important?
// - Prevent session hijacking
// - Clear sensitive data
// - Prevent back button to access admin pages

// ========================================================================
// STEP 1: Unset All Session Variables
// ========================================================================
// Clear semua data di $_SESSION array
// Alternative: $_SESSION = [];
// 
// What it does:
// Menghapus semua key-value pair di $_SESSION
// Contoh: admin_id, admin_username, admin_nama, csrf_token
foreach ($_SESSION as $key => $value) {
    unset($_SESSION[$key]);
}

// ========================================================================
// STEP 2: Destroy Session
// ========================================================================
// session_destroy(): Destroy session file di server
// 
// What it does:
// - Delete session file dari disk/storage
// - Session ID jadi invalid
// 
// Note: session_destroy() tidak unset $_SESSION variable
// Makanya perlu step 1 di atas
session_destroy();

// ========================================================================
// STEP 3: Delete Session Cookie (Optional but Recommended)
// ========================================================================
// Set cookie expiration ke masa lalu untuk delete cookie
// 
// Why?
// Browser masih punya cookie PHPSESSID
// Meskipun session ID invalid di server, lebih baik hapus cookie juga
// 
// setcookie() parameters:
// - session_name(): Nama cookie (biasanya 'PHPSESSID')
// - '': Value kosong
// - time() - 3600: Expiration 1 jam yang lalu (untuk delete)
// - '/': Path (available di semua path)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 3600,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// ============================================================================
// REDIRECT TO LOGIN PAGE
// ============================================================================
// Redirect user ke login page setelah logout
// 
// Why redirect?
// - User experience: Clear indication logout sukses
// - Security: Prevent back button to access previous admin page
// - Clean: Start fresh session saat login lagi
header('Location: login.php');

// ============================================================================
// EXIT SCRIPT
// ============================================================================
// Stop script execution setelah redirect
// PENTING! Tanpa exit(), kode setelah redirect tetap dijalankan
exit();

/**
 * ============================================================================
 * END OF LOGOUT SCRIPT
 * ============================================================================
 * 
 * Flow Summary:
 * 1. User klik logout button
 * 2. Request ke logout.php
 * 3. Unset semua session variables
 * 4. Destroy session file
 * 5. Delete session cookie
 * 6. Redirect ke login.php
 * 7. User melihat login page
 * 
 * Security Notes:
 * - Session ID yang lama jadi invalid
 * - Tidak bisa back button ke admin pages
 * - Cookie browser juga dihapus
 * - Harus login ulang untuk access admin panel
 * 
 * Testing:
 * 1. Login sebagai admin
 * 2. Click logout
 * 3. Try back button → Should redirect to login
 * 4. Try access admin page directly → Should redirect to login
 * 
 * ============================================================================
 */
?>