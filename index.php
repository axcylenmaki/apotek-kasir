<?php
session_start();

// Jika sudah login, langsung arahkan ke dashboard sesuai role
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'kasir') {
        header("Location: kasir/dashboard.php");
    } elseif ($_SESSION['role'] === 'superadmin') {
        header("Location: superadmin/dashboard.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Selamat Datang - Apotek</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0d1b2a] text-white flex items-center justify-center min-h-screen">

    <div class="text-center max-w-md px-6">
        <h1 class="text-4xl font-bold mb-4">💊 Sistem Kasir Apotek</h1>
        <p class="text-lg text-gray-300 mb-6">Kelola transaksi, stok obat, dan laporan apotek Anda secara efisien.</p>

        <a href="auth/login.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg text-lg font-semibold transition duration-200">
            Masuk ke Sistem
        </a>
    </div>

</body>
</html>
