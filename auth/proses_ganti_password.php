<?php
require '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Validasi awal
    if (empty($token) || empty($password) || empty($confirm)) {
        die('Semua field wajib diisi.');
    }

    if ($password !== $confirm) {
        die('Password dan konfirmasi tidak cocok.');
    }

    // Cari user berdasarkan token
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die('Token tidak valid.');
    }

    $user = $result->fetch_assoc();

    // Cek apakah token sudah expired
    if (strtotime($user['reset_expiry']) < time()) {
        die('Token sudah kedaluwarsa.');
    }

    // Hash password baru
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Update password user & hapus token
    $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE email = ?");
    $update->bind_param("ss", $hashed, $user['email']);
    $update->execute();

    // Redirect ke login dengan pesan sukses
    header("Location: login.php?reset=success");
    exit;
} else {
    // Akses langsung tanpa POST
    header("Location: login.php");
    exit;
}
if ($update->execute()) {
    // sukses
} else {
    die("Gagal update password: " . $update->error);
}

?>
