<?php
/**
 * ============================================================================
 * ADMIN EXPORT CSV
 * ============================================================================
 * File: admin/export-csv.php
 * Deskripsi: Export data pengaduan ke file CSV dengan filter
 * Fitur:
 * - Export semua pengaduan atau dengan filter
 * - Filter by status (Menunggu/Diproses/Selesai/Ditolak)
 * - Filter by kategori
 * - Filter by search query
 * - Format CSV dengan UTF-8 BOM (Excel compatible)
 * - Download langsung ke browser
 * 
 * Usage:
 * - Direct: /admin/export-csv.php
 * - With filters: /admin/export-csv.php?status=Menunggu&kategori=Infrastruktur
 * 
 * Security:
 * - Memerlukan login admin (session check)
 * - Prepared statements untuk prevent SQL Injection
 * 
 * Author: Dafa al hafiz - 24_0085
 * Tanggal: 2025
 * ============================================================================
 */

// ============================================================================
// INCLUDE FILE DEPENDENCIES
// ============================================================================
require_once '../includes/config.php';      // Konfigurasi database & konstanta
require_once '../includes/functions.php';   // Helper functions (termasuk exportToCSV)

// ============================================================================
// SECURITY CHECK: Admin Authentication
// ============================================================================
// Cek apakah user sudah login sebagai admin
// Jika belum, redirect ke halaman login
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

// ============================================================================
// GET FILTER PARAMETERS FROM URL
// ============================================================================
// Ambil parameter filter dari URL (jika ada)
// Operator ?? '' : Default ke empty string jika parameter tidak ada

// Filter status: Menunggu/Diproses/Selesai/Ditolak
$filter_status = $_GET['status'] ?? '';

// Filter kategori: Infrastruktur/Kebersihan/Keamanan/Pelayanan/Lainnya
$filter_kategori = $_GET['kategori'] ?? '';

// Search query: Untuk search judul/deskripsi/nomor tiket
$search_query = $_GET['search'] ?? '';

// ============================================================================
// BUILD DYNAMIC SQL QUERY
// ============================================================================
// Buat WHERE clause secara dinamis berdasarkan filter yang aktif
// Menggunakan prepared statements untuk keamanan (SQL Injection prevention)

// Array untuk menyimpan kondisi WHERE
$where_conditions = [];

// Array untuk menyimpan parameter prepared statement
$params = [];

// ============================================================================
// FILTER 1: Status
// ============================================================================
if (!empty($filter_status)) {
    // Tambahkan kondisi: status = ?
    $where_conditions[] = "status = ?";
    
    // Tambahkan parameter untuk placeholder ?
    $params[] = $filter_status;
}

// ============================================================================
// FILTER 2: Kategori
// ============================================================================
if (!empty($filter_kategori)) {
    // Tambahkan kondisi: kategori = ?
    $where_conditions[] = "kategori = ?";
    
    // Tambahkan parameter
    $params[] = $filter_kategori;
}

// ============================================================================
// FILTER 3: Search Query
// ============================================================================
if (!empty($search_query)) {
    // LIKE operator untuk partial matching
    // Search di 3 kolom: judul, deskripsi, nomor_tiket
    // OR: Salah satu match = include row
    $where_conditions[] = "(judul LIKE ? OR deskripsi LIKE ? OR nomor_tiket LIKE ?)";
    
    // Format search parameter dengan wildcard %
    // % = match any characters (sebelum & sesudah)
    // Contoh: %test% akan match "testing", "latest", "protest", dll
    $search_param = "%{$search_query}%";
    
    // Tambahkan parameter 3x (untuk 3 kolom)
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

// ============================================================================
// ASSEMBLE WHERE CLAUSE
// ============================================================================
// Gabungkan semua kondisi WHERE dengan operator AND
// implode(): Gabungkan array menjadi string dengan separator

// Jika ada kondisi WHERE
if (!empty($where_conditions)) {
    // Gabungkan dengan ' AND '
    // Contoh: "status = ? AND kategori = ?"
    $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
} else {
    // Jika tidak ada filter, kosongkan WHERE clause
    $where_sql = '';
}

// ============================================================================
// FETCH DATA FROM DATABASE
// ============================================================================
// Query untuk ambil semua data pengaduan (dengan filter jika ada)

// Prepare statement
// SELECT *: Ambil semua kolom
// ORDER BY tanggal_dibuat DESC: Urutkan dari yang terbaru
$stmt = $pdo->prepare("
    SELECT * FROM pengaduan 
    {$where_sql}
    ORDER BY tanggal_dibuat DESC
");

// Execute query dengan parameters
// $params berisi nilai untuk placeholder ? di WHERE clause
$stmt->execute($params);

// Fetch all results sebagai array
// Array ini akan dikirim ke function exportToCSV()
$data = $stmt->fetchAll();

// ============================================================================
// EXPORT TO CSV
// ============================================================================
// Panggil function exportToCSV() dari functions.php
// 
// What it does:
// 1. Set HTTP headers untuk force download
// 2. Output UTF-8 BOM (untuk Excel compatibility)
// 3. Write CSV header (column names)
// 4. Write data rows
// 5. Exit (stop script execution)
// 
// Note: Function ini langsung output file dan exit
//       Tidak ada code yang dijalankan setelah ini
// 
// Parameters:
// - $data: Array data pengaduan dari database
// - No second parameter: Auto-generate filename dengan timestamp
//   Format: pengaduan_export_YYYYMMDD_HHMMSS.csv
exportToCSV($data);

/**
 * ============================================================================
 * END OF SCRIPT
 * ============================================================================
 * 
 * Script berhenti di sini karena exportToCSV() memanggil exit()
 * 
 * Tidak ada HTML yang di-render, tidak ada footer yang diinclude
 * Pure CSV output ke browser
 * 
 * Browser akan menerima file CSV dan trigger download dialog
 * 
 * ============================================================================
 */
?>