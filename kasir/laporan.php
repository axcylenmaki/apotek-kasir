<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kasir') {
    header("Location: ../auth/login.php");
    exit;
}

include '../config/config.php';
include '../includes/header.php';
include '../includes/navbar.php';

// Default tanggal
$today = date('Y-m-d');
$start = $_GET['start'] ?? $today;
$end = $_GET['end'] ?? $today;
$range = $_GET['range'] ?? 'harian';
$userId = $_SESSION['user_id'];

// Validasi range harian
if ($range === 'harian') {
    $end = $start;
}

// Pastikan end tidak lebih kecil dari start
if (strtotime($end) < strtotime($start)) {
    $end = $start;
}

// --- QUERY UTAMA --- //
// Query untuk total penjualan, modal, dan keuntungan
$keuntunganQuery = $conn->prepare("
    SELECT 
        IFNULL(SUM(t.total), 0) AS total_penjualan,
        IFNULL(SUM(d.jumlah * p.harga_beli), 0) AS total_modal
    FROM transaksi t
    JOIN detail_transaksi d ON t.id = d.id_transaksi
    JOIN produk p ON d.id_produk = p.id
    WHERE DATE(t.tanggal) BETWEEN ? AND ? AND t.id_kasir = ?
");

$keuntunganQuery->bind_param("ssi", $start, $end, $userId);
$keuntunganQuery->execute();
$keuntungan = $keuntunganQuery->get_result()->fetch_assoc();

$totalPenjualan = (int) $keuntungan['total_penjualan'];
$totalModal = (int) $keuntungan['total_modal'];
$totalKeuntungan = $totalPenjualan - $totalModal;


$totalPenjualan = (int) $keuntungan['total_penjualan'];
$totalModal = (int) $keuntungan['total_modal'];
$totalKeuntungan = $totalPenjualan - $totalModal;

// Query untuk data transaksi
if ($range === 'harian') {
    $query = $conn->prepare("
        SELECT t.*, u.nama as kasir, m.nama as member
        FROM transaksi t
        JOIN users u ON t.id_kasir = u.id
        LEFT JOIN member m ON t.id_member = m.id
        WHERE DATE(t.tanggal) BETWEEN ? AND ? AND t.id_kasir = ?
        ORDER BY t.tanggal DESC
    ");
    $query->bind_param("ssi", $start, $end, $userId);
} elseif ($range === 'mingguan') {
    $query = $conn->prepare("
        SELECT 
            YEAR(t.tanggal) as tahun, 
            WEEK(t.tanggal, 1) as minggu,
            COUNT(*) as jumlah_transaksi,
            SUM(t.total) as total_penjualan,
            MAX(t.tanggal) as tanggal_terakhir
        FROM transaksi t
        WHERE DATE(t.tanggal) BETWEEN ? AND ? AND t.id_kasir = ?
        GROUP BY YEAR(t.tanggal), WEEK(t.tanggal, 1)
        ORDER BY tanggal_terakhir DESC
    ");
    $query->bind_param("ssi", $start, $end, $userId);
} else { // bulanan
    $query = $conn->prepare("
        SELECT 
            YEAR(t.tanggal) as tahun, 
            MONTH(t.tanggal) as bulan,
            COUNT(*) as jumlah_transaksi,
            SUM(t.total) as total_penjualan,
            MAX(t.tanggal) as tanggal_terakhir
        FROM transaksi t
        WHERE DATE(t.tanggal) BETWEEN ? AND ? AND t.id_kasir = ?
        GROUP BY YEAR(t.tanggal), MONTH(t.tanggal)
        ORDER BY tanggal_terakhir DESC
    ");
    $query->bind_param("ssi", $start, $end, $userId);
}

$query->execute();
$result = $query->get_result();
$transactions = $result->fetch_all(MYSQLI_ASSOC);

// Query untuk grafik
$chartQuery = $conn->prepare("
    SELECT DATE(t.tanggal) as tgl, SUM(t.total) as total 
    FROM transaksi t
    WHERE DATE(t.tanggal) BETWEEN ? AND ? AND t.id_kasir = ?
    GROUP BY DATE(t.tanggal)
    ORDER BY t.tanggal
");
$chartQuery->bind_param("ssi", $start, $end, $userId);
$chartQuery->execute();
$chartResult = $chartQuery->get_result();

$chartLabels = [];
$chartData = [];
while ($row = $chartResult->fetch_assoc()) {
    $chartLabels[] = date('d M', strtotime($row['tgl']));
    $chartData[] = (int)$row['total'];
}
?>

<div class="max-w-7xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6 text-white">📊 Laporan Penjualan Kasir</h1>

    <!-- Filter Form -->
    <form method="GET" class="mb-6 flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm text-white mb-1">Dari Tanggal</label>
            <input type="date" name="start" value="<?= $start ?>" class="rounded px-3 py-2 text-sm text-black">
        </div>
        <div>
            <label class="block text-sm text-white mb-1">Sampai Tanggal</label>
            <input type="date" name="end" value="<?= $end ?>" class="rounded px-3 py-2 text-sm text-black">
        </div>
        <div>
            <label class="block text-sm text-white mb-1">Periode</label>
            <select name="range" class="rounded px-3 py-2 text-sm text-black">
                <option value="harian" <?= $range === 'harian' ? 'selected' : '' ?>>Harian</option>
                <option value="mingguan" <?= $range === 'mingguan' ? 'selected' : '' ?>>Mingguan</option>
                <option value="bulanan" <?= $range === 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
            </select>
        </div>
        <div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Filter
            </button>
        </div>
        <div>
            <a href="../includes/laporan/export_pdf.php?tipe=penjualan&start=<?= $start ?>&end=<?= $end ?>&range=<?= $range ?>&user_id=<?= $userId ?>" 
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                Download PDF
            </a>
        </div>
    </form>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-[#1e293b] text-white p-4 rounded shadow">
            <p class="text-sm">Total Penjualan</p>
            <p class="text-xl font-bold">Rp <?= number_format($totalPenjualan, 0, ',', '.') ?></p>
        </div>
        <div class="bg-[#1e293b] text-white p-4 rounded shadow">
            <p class="text-sm">Total Modal</p>
            <p class="text-xl font-bold">Rp <?= number_format($totalModal, 0, ',', '.') ?></p>
        </div>
        <div class="bg-[#1e293b] text-white p-4 rounded shadow">
            <p class="text-sm">Total Keuntungan</p>
            <p class="text-xl font-bold">Rp <?= number_format($totalKeuntungan, 0, ',', '.') ?></p>
        </div>
    </div>

    <!-- Grafik Penjualan -->
    <div class="bg-white p-6 rounded-xl shadow mb-8">
        <h2 class="text-lg font-bold text-[#0d1b2a] mb-4">Grafik Penjualan</h2>
        <canvas id="chartPenjualan" height="120"></canvas>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-lg font-bold text-[#0d1b2a] mb-4">Data Transaksi</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <?php if ($range === 'harian'): ?>
                            <th class="p-2">Tanggal</th>
                            <th class="p-2">Kasir</th>
                            <th class="p-2">Member</th>
                            <th class="p-2">Total</th>
                            <th class="p-2">Aksi</th>
                        <?php elseif ($range === 'mingguan'): ?>
                            <th class="p-2">Tahun</th>
                            <th class="p-2">Minggu</th>
                            <th class="p-2">Jumlah Transaksi</th>
                            <th class="p-2">Total Penjualan</th>
                        <?php else: ?>
                            <th class="p-2">Tahun</th>
                            <th class="p-2">Bulan</th>
                            <th class="p-2">Jumlah Transaksi</th>
                            <th class="p-2">Total Penjualan</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="text-black">
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="5" class="p-2 text-center">Tidak ada data transaksi</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $trx): ?>
                            <tr class="border-t">
                                <?php if ($range === 'harian'): ?>
                                    <td class="p-2"><?= date('d/m/Y H:i', strtotime($trx['tanggal'])) ?></td>
                                    <td class="p-2"><?= htmlspecialchars($trx['kasir']) ?></td>
                                    <td class="p-2"><?= $trx['member'] ?: '-' ?></td>
                                    <td class="p-2">Rp <?= number_format($trx['total'], 0, ',', '.') ?></td>
                                    <td class="p-2">
                                        <a href="../includes/transaksi/nota.php?id=<?= $trx['id'] ?>" 
                                           class="text-blue-600 hover:underline" target="_blank">
                                            Lihat Struk
                                        </a>
                                    </td>
                                <?php elseif ($range === 'mingguan'): ?>
                                    <td class="p-2"><?= $trx['tahun'] ?></td>
                                    <td class="p-2">Minggu <?= $trx['minggu'] ?></td>
                                    <td class="p-2"><?= $trx['jumlah_transaksi'] ?></td>
                                    <td class="p-2">Rp <?= number_format($trx['total_penjualan'], 0, ',', '.') ?></td>
                                <?php else: ?>
                                    <td class="p-2"><?= $trx['tahun'] ?></td>
                                    <td class="p-2"><?= date('F', mktime(0, 0, 0, $trx['bulan'], 1)) ?></td>
                                    <td class="p-2"><?= $trx['jumlah_transaksi'] ?></td>
                                    <td class="p-2">Rp <?= number_format($trx['total_penjualan'], 0, ',', '.') ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
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