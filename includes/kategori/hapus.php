<?php
include '../../config/config.php';
include 'functions.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        deleteKategori($conn, $id);
        header("Location: ../../superadmin/kategori.php?delete=success");
    } catch (Exception $e) {
        // Redirect ke halaman kategori dengan pesan error
        header("Location: ../../superadmin/kategori.php?error=" . urlencode($e->getMessage()));
    }

    exit;
} else {
    // Jika ID tidak ada di parameter
    header("Location: ../../superadmin/kategori.php?error=" . urlencode("ID kategori tidak valid."));
    exit;
}
