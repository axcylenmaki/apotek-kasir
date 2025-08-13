<?php
session_start();
require_once '../../config/config.php';
require_once 'functions.php';

// Cek login & role (hanya superadmin yang boleh hapus)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    echo "<script>alert('Akses ditolak.'); window.location.href='../../auth/login.php';</script>";
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<script>alert('ID produk tidak valid.'); window.location.href='../../superadmin/produk.php';</script>";
    exit;
}

// Cek stok produk
$stmt = $conn->prepare("SELECT stok, gambar FROM produk WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$produk = $result->fetch_assoc();
$stmt->close();

if (!$produk) {
    echo "<script>alert('Produk tidak ditemukan.'); window.location.href='../../superadmin/produk.php';</script>";
    exit;
}

// Jika stok masih > 0, jangan hapus
if ($produk['stok'] > 0) {
    echo "<script>alert('Produk masih memiliki stok, tidak dapat dihapus.'); window.location.href='../../superadmin/produk.php';</script>";
    exit;
}

// Proses penghapusan
if (hapusProduk($id)) {
    // Hapus gambar jika ada
    if (!empty($produk['gambar'])) {
        $path = "../../assets/img/produk/" . $produk['gambar'];
        if (file_exists($path)) {
            unlink($path);
        }
    }

    echo "<script>alert('Produk berhasil dihapus.'); window.location.href='../../superadmin/produk.php';</script>";
} else {
    echo "<script>alert('Gagal menghapus produk.'); window.location.href='../../superadmin/produk.php';</script>";
}
?>
