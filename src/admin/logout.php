<?php
/**
 * Logout Admin
 */

require_once '../includes/config.php';

// Destroy session
session_destroy();

// Redirect ke login
header('Location: login.php');
exit();
?>