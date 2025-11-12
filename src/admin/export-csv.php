<?php
/**
 * Export Pengaduan ke CSV
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if admin
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

// Get filter parameters
$filter_status = $_GET['status'] ?? '';
$filter_kategori = $_GET['kategori'] ?? '';
$search_query = $_GET['search'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if (!empty($filter_status)) {
    $where_conditions[] = "status = ?";
    $params[] = $filter_status;
}

if (!empty($filter_kategori)) {
    $where_conditions[] = "kategori = ?";
    $params[] = $filter_kategori;
}

if (!empty($search_query)) {
    $where_conditions[] = "(judul LIKE ? OR deskripsi LIKE ? OR nomor_tiket LIKE ?)";
    $search_param = "%{$search_query}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Fetch all data
$stmt = $pdo->prepare("
    SELECT * FROM pengaduan 
    {$where_sql}
    ORDER BY tanggal_dibuat DESC
");

$stmt->execute($params);
$data = $stmt->fetchAll();

// Export to CSV
exportToCSV($data);
?>