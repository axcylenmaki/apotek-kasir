<?php
session_start();

// Hapus semua sesi
session_unset();
session_destroy();

// Arahkan kembali ke halaman awal
header("Location: ../index.php");
exit;
?>
