<?php
include '../../config/config.php';
include 'functions.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Cek apakah member masih punya point > 0
    $stmt = $conn->prepare("SELECT poin FROM member WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($point);
    $stmt->fetch();
    $stmt->close();

    if ($point > 0) {
        echo "<script>alert('Member tidak dapat dihapus karena masih memiliki point.'); window.location.href='../../superadmin/member.php';</script>";
        exit;
    }

    // Kalau point = 0, hapus member
    if (deleteMember($conn, $id)) {
        header("Location: ../../superadmin/member.php?deleted=1");
        exit;
    } else {
        echo "Gagal menghapus member.";
    }
} else {
    echo "ID member tidak ditemukan.";
}
