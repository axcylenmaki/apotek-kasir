<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kasir') {
    header("Location: ../auth/login.php");
    exit;
}

include '../config/config.php';
include '../config/config.php';
include '../includes/header.php';
include '../includes/navbar.php';

$user_id = $_SESSION['user_id'];
$nama = $_SESSION['nama'];
$tglHariIni = date('Y-m-d');

// Hitung jumlah transaksi (penjualan) hari ini oleh kasir ini
$penjualanHariIni = $conn->query("
    SELECT COUNT(*) AS total 
    FROM transaksi 
    WHERE id_kasir = $user_id AND DATE(tanggal) = '$tglHariIni'
")->fetch_assoc()['total'];

// Hitung total keuntungan hari ini
$keuntunganHariIni = $conn->query("
    SELECT SUM(k.harga_total - (k.qty * p.harga_beli)) AS total_keuntungan
    FROM transaksi t
    JOIN keranjang k ON t.id = k.id_transaksi
    JOIN produk p ON k.id_produk = p.id
    WHERE t.id_kasir = $user_id AND DATE(t.tanggal) = '$tglHariIni'
")->fetch_assoc()['total_keuntungan'] ?? 0;

// Ambil 5 transaksi terakhir
$lastTransaksi = $conn->query("
    SELECT id, tanggal, total 
    FROM transaksi 
    WHERE id_kasir = $user_id 
    ORDER BY tanggal DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Kasir</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0d1b2a] text-white min-h-screen p-6">

    <h1 class="text-3xl font-bold mb-4">Halo, <?= htmlspecialchars($nama) ?> 👋</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white text-[#0d1b2a] p-6 rounded-xl shadow">
            <h2 class="text-lg font-semibold">Penjualan Hari Ini</h2>
            <p class="text-3xl font-bold"><?= $penjualanHariIni ?></p>
        </div>
        <div class="bg-white text-[#0d1b2a] p-6 rounded-xl shadow">
            <h2 class="text-lg font-semibold">Keuntungan Hari Ini</h2>
            <p class="text-3xl font-bold">Rp <?= number_format($keuntunganHariIni, 0, ',', '.') ?></p>
        </div>
    </div>

    <div class="bg-white text-[#0d1b2a] p-6 rounded-xl shadow mb-8">
        <h2 class="text-lg font-semibold mb-4">5 Transaksi Terakhir</h2>
        <table class="w-full text-left text-sm">
            <thead>
                <tr>
                    <th class="pb-2">ID</th>
                    <th class="pb-2">Tanggal</th>
                    <th class="pb-2">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $lastTransaksi->fetch_assoc()): ?>
                    <tr class="border-t">
                        <td class="py-2"><?= $row['id'] ?></td>
                        <td class="py-2"><?= date('d M Y H:i', strtotime($row['tanggal'])) ?></td>
                        <td class="py-2">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <a href="kasir.php" class="inline-block bg-blue-700 hover:bg-blue-800 text-white px-6 py-3 rounded text-lg">
        ➕ Mulai Transaksi
    </a>

</body>

<?php include '../includes/footer.php'; ?>
<script>
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const toggleBtn = document.getElementById('sidebarToggle');

    toggleBtn?.addEventListener('click', () => {
        sidebar?.classList.toggle('-translate-x-full');

        // Optional: shift konten utama (kalau sidebar muncul)
        if (!sidebar.classList.contains('-translate-x-full')) {
            mainContent.classList.add('md:ml-64');
        } else {
            mainContent.classList.remove('md:ml-64');
        }
    });
</script>

</html>
