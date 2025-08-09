<?php
include '../../config/config.php';
include 'functions.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    if (deleteMember($conn, $id)) {
        header("Location: ../../superadmin/member.php?deleted=1");
        exit;
    } else {
        echo "Gagal menghapus member.";
    }
} else {
    echo "ID member tidak ditemukan.";
}
