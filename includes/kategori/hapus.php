<?php
include '../../config/config.php';
include 'functions.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    deleteKategori($conn, $id);
}

header("Location: ../../superadmin/kategori.php?delete=success");
exit;
