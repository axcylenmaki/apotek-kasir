<?php
session_start();

// Cek apakah user login dan role-nya superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../../config/config.php';

if (!isset($_GET['id'])) {
    // Kalau tidak ada ID, kembalikan ke laporan
    header("Location: ../../superadmin/laporan.php?status=gagal&id_kosong");
    exit;
}

$id = intval($_GET['id']); // Amankan ID

// 1. Hapus data keranjang terkait
$conn->query("DELETE FROM keranjang WHERE id_transaksi = $id");

// 2. Hapus data transaksi
$conn->query("DELETE FROM transaksi WHERE id = $id");

// Redirect kembali ke laporan
header("Location: ../../superadmin/laporan.php?status=sukses&id=$id");
exit;
