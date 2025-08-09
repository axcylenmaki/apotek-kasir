<?php
session_start();
require_once '../../config/config.php';
require_once 'functions.php';

// Cek jika bukan superadmin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../../login.php');
    exit;
}

// Validasi ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../../superadmin/kasir.php');
    exit;
}

$id = intval($_GET['id']);

// Cek role user yang ingin dihapus
$user = getKasirById($id);

if (!$user) {
    header('Location: ../../superadmin/kasir.php?delete=failed');
    exit;
}

if ($user['role'] === 'superadmin') {
    // Tidak boleh hapus superadmin
    header('Location: ../../superadmin/kasir.php?delete=forbidden');
    exit;
}

// Lakukan penghapusan
$success = hapusKasir($id);

if ($success) {
    header('Location: ../../superadmin/kasir.php?delete=success');
} else {
    header('Location: ../../superadmin/kasir.php?delete=failed');
}
exit;
