<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit;
}
include '../config/config.php';
include '../includes/header.php';
include '../includes/navbar.php';

$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-d');

$chartLabels = [];
$chartData = [];
$modalData = [];
$result = $conn->query("SELECT DATE(tanggal) as tgl, 
    COUNT(DISTINCT t.id) as transaksi,
    SUM(k.qty * p.harga_beli) as modal
    FROM transaksi t
    JOIN keranjang k ON t.id = k.id_transaksi
    JOIN produk p ON k.id_produk = p.id
    WHERE DATE(tanggal) BETWEEN '$start' AND '$end'
    GROUP BY DATE(tanggal)
    ORDER BY tgl");
while ($row = $result->fetch_assoc()) {
    $chartLabels[] = date('d M', strtotime($row['tgl']));
    $chartData[] = (int)$row['transaksi'];
    $modalData[] = (int)$row['modal'];
}

$transaksi = $conn->query("SELECT t.*, u.nama as kasir, m.nama as member
    FROM transaksi t
    JOIN users u ON t.id_kasir = u.id
    LEFT JOIN member m ON t.id_member = m.id
    WHERE DATE(t.tanggal) BETWEEN '$start' AND '$end'
    ORDER BY t.tanggal DESC");
?>

<div class="max-w-7xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6 text-white">📊 Laporan Penjualan Lengkap</h1>

    <form method="GET" class="mb-6 bg-[#1e293b] p-4 rounded shadow flex flex-col sm:flex-row gap-2 items-center text-white">
        <div class="flex items-center gap-2">
            <label for="start">Dari:</label>
            <input type="date" name="start" id="start" value="<?= $start ?>" class="border px-2 py-1 rounded text-black">
        </div>
        <div class="flex items-center gap-2">
            <label for="end">Sampai:</label>
            <input type="date" name="end" id="end" value="<?= $end ?>" class="border px-2 py-1 rounded text-black">
        </div>
        <button class="bg-blue-700 text-white px-4 py-2 rounded hover:bg-blue-800" type="submit">Filter</button>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-[#1e293b] text-white p-4 rounded shadow">
            <p class="text-sm">Hari Ini</p>
            <p class="text-xl font-bold">
                <?php
                $today = date('Y-m-d');
                $harian = $conn->query("SELECT COUNT(*) as jml FROM transaksi WHERE DATE(tanggal) = '$today'")->fetch_assoc();
                echo $harian['jml'];
                ?> Transaksi
            </p>
        </div>
        <div class="bg-[#1e293b] text-white p-4 rounded shadow">
            <p class="text-sm">Total Modal (Filter)</p>
            <p class="text-xl font-bold text-red-400">Rp <?= number_format(array_sum($modalData),0,',','.') ?></p>
        </div>
        <div class="bg-[#1e293b] text-white p-4 rounded shadow">
            <p class="text-sm">Total Transaksi (Filter)</p>
            <p class="text-xl font-bold text-green-400"><?= array_sum($chartData) ?> Transaksi</p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow mb-10">
        <h2 class="text-lg font-bold text-[#0d1b2a] mb-4">Grafik Penjualan</h2>
        <canvas id="chartPenjualan" height="120"></canvas>
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-lg font-bold text-[#0d1b2a] mb-4">Tabel Transaksi</h2>
        <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="p-2">Tanggal</th>
                    <th class="p-2">Admin</th>
                    <th class="p-2">Member</th>
                    <th class="p-2">Metode Bayar</th>
                    <th class="p-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $transaksi->fetch_assoc()): ?>
                <tr class="border-t">
                    <td class="p-2 text-gray-700"><?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?></td>
                    <td class="p-2"><?= htmlspecialchars($row['kasir']) ?></td>
                    <td class="p-2"><?= $row['member'] ?: '-' ?></td>
                    <td class="p-2"><?= htmlspecialchars($row['metode_bayar']) ?></td>
                    <td class="p-2">
                        <a href="lihat_struk.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:underline">Lihat Struk</a>
                        |
                        <a href="hapus_transaksi.php?id=<?= $row['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Yakin hapus transaksi?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('chartPenjualan').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [{
                label: 'Penjualan',
                data: <?= json_encode($chartData) ?>,
                backgroundColor: '#2563eb'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
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


<?php include '../includes/footer.php'; ?>