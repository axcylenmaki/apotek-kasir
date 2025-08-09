<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ../auth/login.php");
    exit;
}

include '../config/config.php';
include '../includes/header.php';
include '../includes/navbar.php';


$nama = $_SESSION['nama'];
$tglHariIni = date('Y-m-d');

// Statistik
$totalProduk = $conn->query("SELECT COUNT(*) as total FROM produk")->fetch_assoc()['total'];
$totalMember = $conn->query("SELECT COUNT(*) as total FROM member")->fetch_assoc()['total'];
$totalKasir = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='kasir'")->fetch_assoc()['total'];
$penjualanHariIni = $conn->query("SELECT COUNT(*) as total FROM transaksi WHERE DATE(tanggal) = '$tglHariIni'")->fetch_assoc()['total'];
$keuntunganHariIni = $conn->query("
    SELECT SUM(k.harga_total - (k.qty * p.harga_beli)) AS total_keuntungan
    FROM transaksi t
    JOIN keranjang k ON t.id = k.id_transaksi
    JOIN produk p ON k.id_produk = p.id
    WHERE DATE(t.tanggal) = '$tglHariIni'
")->fetch_assoc()['total_keuntungan'] ?? 0;
$chartLabels = [];
$chartData = [];

$result = $conn->query("
    SELECT DATE(tanggal) as tanggal, COUNT(*) as total
    FROM transaksi
    WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(tanggal)
    ORDER BY tanggal
");

while ($row = $result->fetch_assoc()) {
    $chartLabels[] = date('d M', strtotime($row['tanggal']));
    $chartData[] = (int)$row['total'];
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-[#0d1b2a] text-white min-h-screen p-6">

    <h1 class="text-2xl font-bold mb-6">Selamat Datang, Admin <?= htmlspecialchars($nama) ?> 👋</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
        <div class="bg-white text-[#0d1b2a] p-6 rounded-xl shadow">
            <h2 class="text-lg font-semibold">Total Produk</h2>
            <p class="text-3xl font-bold"><?= $totalProduk ?></p>
        </div>
        <div class="bg-white text-[#0d1b2a] p-6 rounded-xl shadow">
            <h2 class="text-lg font-semibold">Total Member</h2>
            <p class="text-3xl font-bold"><?= $totalMember ?></p>
        </div>
        <div class="bg-white text-[#0d1b2a] p-6 rounded-xl shadow">
            <h2 class="text-lg font-semibold">Total Kasir</h2>
            <p class="text-3xl font-bold"><?= $totalKasir ?></p>
        </div>
        <div class="bg-white text-[#0d1b2a] p-6 rounded-xl shadow">
            <h2 class="text-lg font-semibold">Penjualan Hari Ini</h2>
            <p class="text-3xl font-bold"><?= $penjualanHariIni ?></p>
        </div>
        <div class="bg-white text-[#0d1b2a] p-6 rounded-xl shadow">
            <h2 class="text-lg font-semibold">Keuntungan Hari Ini</h2>
            <p class="text-3xl font-bold">Rp <?= number_format($keuntunganHariIni, 0, ',', '.') ?></p>
        </div>
    </div>

    <div class="mt-12 bg-white rounded-xl shadow p-6 max-w-4xl mx-auto">
    <h2 class="text-[#0d1b2a] text-lg font-semibold mb-4">Grafik Penjualan (7 Hari Terakhir)</h2>
    <canvas id="chartPenjualan" height="120"></canvas>
</div>


    

</body>

<script>
    const ctx = document.getElementById('chartPenjualan').getContext('2d');
    const chartPenjualan = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [{
                label: 'Jumlah Penjualan',
                data: <?= json_encode($chartData) ?>,
                backgroundColor: '#2563eb',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>

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
