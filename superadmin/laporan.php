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
$range = $_GET['range'] ?? 'harian';

// Ambil semua produk
$produk = $conn->query("SELECT * FROM produk");

$stok_total = 0;
$stok_layak = 0;
$stok_kadaluarsa = 0;

$today = date('Y-m-d');

while ($row = $produk->fetch_assoc()) {
    $stok_total += $row['stok'];

    if (isset($row['expired_date']) && $row['expired_date'] && $row['expired_date'] >= $today) {
        $stok_layak += $row['stok'];
    } else {
        $stok_kadaluarsa += $row['stok'];
    }
}

// --- GRAFIK & DATA TRANSAKSI FILTER --- //
$chartLabels = [];
$chartData = [];
$modalData = [];

switch ($range) {
    case 'mingguan':
        $select = "YEAR(tanggal) as thn, WEEK(tanggal,1) as grp";
        $group = "YEAR(tanggal), WEEK(tanggal,1)";
        $labelFormat = "'Minggu ' . \$row['grp'] . ' ' . \$row['thn']";
        break;
    case 'bulanan':
        $select = "YEAR(tanggal) as thn, MONTH(tanggal) as grp";
        $group = "YEAR(tanggal), MONTH(tanggal)";
        $labelFormat = "date('M Y', strtotime(\$row['thn'].'-'.\$row['grp'].'-01'))";
        break;
    case 'tahunan':
        $select = "YEAR(tanggal) as grp";
        $group = "YEAR(tanggal)";
        $labelFormat = "\$row['grp']";
        break;
    default: // harian
        $select = "DATE(tanggal) as grp";
        $group = "DATE(tanggal)";
        $labelFormat = "date('d M', strtotime(\$row['grp']))";
        break;
}

$result = $conn->query("
    SELECT $select, 
           COUNT(DISTINCT t.id) as transaksi,
           SUM(k.qty * p.harga_beli) as modal
    FROM transaksi t
    JOIN keranjang k ON t.id = k.id_transaksi
    JOIN produk p ON k.id_produk = p.id
    WHERE DATE(tanggal) BETWEEN '$start' AND '$end'
    GROUP BY $group
    ORDER BY $group
");

while ($row = $result->fetch_assoc()) {
    // Label sesuai range
    if ($range == 'mingguan') {
        $chartLabels[] = "Minggu {$row['grp']} {$row['thn']}";
    } elseif ($range == 'bulanan') {
        $chartLabels[] = date('M Y', strtotime($row['thn'].'-'.$row['grp'].'-01'));
    } elseif ($range == 'tahunan') {
        $chartLabels[] = $row['grp'];
    } else {
        $chartLabels[] = date('d M', strtotime($row['grp']));
    }
    $chartData[] = (int)$row['transaksi'];
    $modalData[] = (int)$row['modal'];
}

// --- DATA TRANSAKSI TABEL --- //
$tabelGroup = '';
switch ($range) {
    case 'mingguan':
        $tabelGroup = "YEAR(t.tanggal), WEEK(t.tanggal,1)";
        $tabelSelect = "YEAR(t.tanggal) as thn, WEEK(t.tanggal,1) as minggu";
        break;
    case 'bulanan':
        $tabelGroup = "YEAR(t.tanggal), MONTH(t.tanggal)";
        $tabelSelect = "YEAR(t.tanggal) as thn, MONTH(t.tanggal) as bln";
        break;
    case 'tahunan':
        $tabelGroup = "YEAR(t.tanggal)";
        $tabelSelect = "YEAR(t.tanggal) as thn";
        break;
    default:
        $tabelGroup = '';
        $tabelSelect = '';
        break;
}

if ($tabelGroup) {
    // Grouped transaksi
    $transaksi = $conn->query("
        SELECT $tabelSelect, COUNT(*) as jumlah, SUM(t.total) as total, MAX(t.tanggal) as tanggal_akhir
        FROM transaksi t
        WHERE DATE(t.tanggal) BETWEEN '$start' AND '$end'
        GROUP BY $tabelGroup
        ORDER BY tanggal_akhir DESC
    ");
} else {
    // Per transaksi
    $transaksi = $conn->query("SELECT t.*, u.nama as kasir, m.nama as member, t.metode_bayar
        FROM transaksi t
        JOIN users u ON t.id_kasir = u.id
        LEFT JOIN member m ON t.id_member = m.id
        WHERE DATE(t.tanggal) BETWEEN '$start' AND '$end'
        ORDER BY t.tanggal DESC");
}

// --- QUERY LAINNYA (TIDAK PERLU DIUBAH, TETAP SESUAI FILTER) --- //
// Penjualan Tahun Ini (dalam filter)
$penjualan_tahunan = $conn->query("
    SELECT COUNT(*) as jml 
    FROM transaksi 
    WHERE DATE(tanggal) BETWEEN '$start' AND '$end'
")->fetch_assoc();

// Penjualan Bulan Ini (dalam filter)
$penjualan_bulanan = $conn->query("
    SELECT COUNT(*) as jml 
    FROM transaksi 
    WHERE DATE(tanggal) BETWEEN '$start' AND '$end'
")->fetch_assoc();

// Penjualan Minggu Ini (dalam filter)
$penjualan_mingguan = $conn->query("
    SELECT COUNT(*) as jml 
    FROM transaksi 
    WHERE DATE(tanggal) BETWEEN '$start' AND '$end'
")->fetch_assoc();

// Penjualan Hari Ini (dalam filter)
$penjualan_harian = $conn->query("
    SELECT COUNT(*) as jml 
    FROM transaksi 
    WHERE DATE(tanggal) BETWEEN '$start' AND '$end'
")->fetch_assoc();

// Total (filter tanggal)
$penjualan_filter = array_sum($chartData);

// Tambahkan di sini
$keuntunganQuery = $conn->query("
    SELECT 
        IFNULL(SUM(dt.jumlah * dt.harga_satuan), 0) AS total_penjualan,
        IFNULL(SUM(dt.jumlah * p.harga_beli), 0) AS total_modal
    FROM detail_transaksi dt
    JOIN transaksi t ON dt.id_transaksi = t.id
    JOIN produk p ON dt.id_produk = p.id
    WHERE DATE(t.tanggal) BETWEEN '$start' AND '$end'
");

$keuntungan = $keuntunganQuery->fetch_assoc();
$totalPenjualan = (int) $keuntungan['total_penjualan'];
$totalModal = (int) $keuntungan['total_modal'];
$totalKeuntungan = $totalPenjualan - $totalModal;
?>

<div class="max-w-7xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6 text-white">📊 Laporan Penjualan Lengkap</h1>

    <!-- Filter Tanggal -->
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
            <label class="block text-sm text-white mb-1">Range</label>
            <select name="range" class="rounded px-3 py-2 text-sm text-black">
                <option value="harian" <?= (($_GET['range'] ?? '') == 'harian') ? 'selected' : '' ?>>Perhari</option>
                <option value="mingguan" <?= (($_GET['range'] ?? '') == 'mingguan') ? 'selected' : '' ?>>Perminggu</option>
                <option value="bulanan" <?= (($_GET['range'] ?? '') == 'bulanan') ? 'selected' : '' ?>>Perbulan</option>
                <option value="tahunan" <?= (($_GET['range'] ?? '') == 'tahunan') ? 'selected' : '' ?>>Pertahun</option>
            </select>
        </div>
        <div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Filter
            </button>
        </div>
    </form>

    <!-- Kotak Informasi Penjualan -->
    <div class="grid grid-cols-1 sm:grid-cols-6 gap-4 mb-8">
        <div class="bg-[#1e293b] text-white p-4 rounded shadow">
            <p class="text-sm">Penjualan Tahun Ini</p>
            <p class="text-xl font-bold"><?= $penjualan_tahunan['jml'] ?> Transaksi</p>
        </div>
        <div class="bg-[#1e293b] text-white p-4 rounded shadow">
            <p class="text-sm">Bulan Ini</p>
            <p class="text-xl font-bold"><?= $penjualan_bulanan['jml'] ?> Transaksi</p>
        </div>
        <div class="bg-[#1e293b] text-white p-4 rounded shadow">
            <p class="text-sm">Minggu Ini</p>
            <p class="text-xl font-bold"><?= $penjualan_mingguan['jml'] ?> Transaksi</p>
        </div>
        <div class="bg-[#1e293b] text-white p-4 rounded shadow">
            <p class="text-sm">Hari Ini</p>
            <p class="text-xl font-bold"><?= $penjualan_harian['jml'] ?> Transaksi</p>
        </div>
        <div class="bg-[#1e293b] text-white p-4 rounded shadow">
            <p class="text-sm">Total Penjualan (Filter)</p>
            <p class="text-xl font-bold text-blue-400">
                Rp <?= number_format($totalPenjualan, 0, ',', '.') ?>
            </p>
        </div>
        <div class="bg-[#1e293b] text-white p-4 rounded shadow">
            <p class="text-sm">Total Modal (Filter)</p>
            <p class="text-xl font-bold text-red-400">
                Rp <?= number_format($totalModal, 0, ',', '.') ?>
            </p>
        </div>
        <div class="bg-[#1e293b] text-white p-4 rounded shadow">
            <p class="text-sm">Total Keuntungan (Filter)</p>
            <p class="text-xl font-bold text-green-400">
                Rp <?= number_format($totalKeuntungan, 0, ',', '.') ?>
            </p>
        </div>
    </div>

    <!-- Tabs Navigasi Laporan -->
    <div class="mb-8 flex flex-wrap gap-2">
        <a href="laporan.php" class="px-4 py-2 rounded-lg font-semibold <?= basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'bg-blue-700 text-white' : 'bg-gray-200 text-gray-800' ?>">Penjualan</a>
        <a href="../includes/laporan/log_kegiatan.php" class="px-4 py-2 rounded-lg font-semibold <?= basename($_SERVER['PHP_SELF']) == 'laporan_kegiatan_kasir.php' ? 'bg-blue-700 text-white' : 'bg-gray-200 text-gray-800' ?>">Kegiatan Kasir</a>
    </div>

    <!-- Tombol Download PDF per kategori -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="../includes/laporan/export_pdf.php?tipe=penjualan&start=2025-08-01&end=2025-08-10" target="_blank" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Download PDF Penjualan</a>

    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-[#1e293b] text-white p-4 rounded shadow">
            <p class="text-sm">Total Stok Produk</p>
            <p class="text-xl font-bold"><?= $stok_total ?> Item</p>
        </div>
        <div class="bg-green-700 text-white p-4 rounded shadow">
            <p class="text-sm">Stok Layak Pakai</p>
            <p class="text-xl font-bold"><?= $stok_layak ?> Item</p>
        </div>
        <div class="bg-red-600 text-white p-4 rounded shadow">
            <p class="text-sm">Stok Kadaluarsa</p>
            <p class="text-xl font-bold"><?= $stok_kadaluarsa ?> Item</p>
        </div>
    </div>

    <!-- Grafik Penjualan -->
    <?php
    $chartLabels = [];
    $chartData = [];

    $query_chart = $conn->query("
        SELECT DATE(tanggal) as tgl, SUM(total) as total 
        FROM transaksi 
        WHERE tanggal BETWEEN '$start 00:00:00' AND '$end 23:59:59'
        GROUP BY DATE(tanggal)
    ");

    if (!$query_chart) {
        die("Chart Query Error: " . $conn->error);
    }

    while ($row = $query_chart->fetch_assoc()) {
        $chartLabels[] = $row['tgl'];
        $chartData[] = (int)$row['total'];
    }
    ?>

    <!-- HTML Grafik -->
    <div class="bg-white p-6 rounded-xl shadow mb-10">
        <h2 class="text-lg font-bold text-[#0d1b2a] mb-4">Grafik Penjualan</h2>
        <canvas id="chartPenjualan" height="120"></canvas>
    </div>

    <!-- Tabel Transaksi -->
    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-lg font-bold text-[#0d1b2a] mb-4">Tabel Transaksi</h2>
        <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <?php if ($range == 'mingguan'): ?>
                        <th class="p-2">Tahun</th>
                        <th class="p-2">Minggu Ke-</th>
                        <th class="p-2">Jumlah Transaksi</th>
                        <th class="p-2">Total</th>
                        <th class="p-2">Tanggal Akhir</th>
                    <?php elseif ($range == 'bulanan'): ?>
                        <th class="p-2">Tahun</th>
                        <th class="p-2">Bulan</th>
                        <th class="p-2">Jumlah Transaksi</th>
                        <th class="p-2">Total</th>
                        <th class="p-2">Tanggal Akhir</th>
                    <?php elseif ($range == 'tahunan'): ?>
                        <th class="p-2">Tahun</th>
                        <th class="p-2">Jumlah Transaksi</th>
                        <th class="p-2">Total</th>
                        <th class="p-2">Tanggal Akhir</th>
                    <?php else: ?>
                        <th class="p-2">Tanggal</th>
                        <th class="p-2">Admin</th>
                        <th class="p-2">Member</th>
                        <th class="p-2">Metode Bayar</th>
                        <th class="p-2">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="text-black">
                <?php if ($range == 'mingguan'): ?>
                    <?php while ($row = $transaksi->fetch_assoc()): ?>
                    <tr class="border-t">
                        <td class="p-2"><?= $row['thn'] ?></td>
                        <td class="p-2"><?= $row['minggu'] ?></td>
                        <td class="p-2"><?= $row['jumlah'] ?></td>
                        <td class="p-2">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                        <td class="p-2"><?= $row['tanggal_akhir'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php elseif ($range == 'bulanan'): ?>
                    <?php while ($row = $transaksi->fetch_assoc()): ?>
                    <tr class="border-t">
                        <td class="p-2"><?= $row['thn'] ?></td>
                        <td class="p-2"><?= date('F', mktime(0,0,0,$row['bln'],1)) ?></td>
                        <td class="p-2"><?= $row['jumlah'] ?></td>
                        <td class="p-2">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                        <td class="p-2"><?= $row['tanggal_akhir'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php elseif ($range == 'tahunan'): ?>
                    <?php while ($row = $transaksi->fetch_assoc()): ?>
                    <tr class="border-t">
                        <td class="p-2"><?= $row['thn'] ?></td>
                        <td class="p-2"><?= $row['jumlah'] ?></td>
                        <td class="p-2">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                        <td class="p-2"><?= $row['tanggal_akhir'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <?php while ($row = $transaksi->fetch_assoc()): ?>
                    <tr class="border-t">
                        <td class="p-2"><?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?></td>
                        <td class="p-2"><?= htmlspecialchars($row['kasir']) ?></td>
                        <td class="p-2"><?= $row['member'] ?: '-' ?></td>
                        <td class="p-2"><?= htmlspecialchars($row['metode_bayar']) ?></td>
                        <td class="p-2">
                            <a href="/apotek-kasir/includes/transaksi/nota.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:underline" target="_blank">Lihat Struk</a>
                            |
                            <a href="../includes/transaksi/hapus.php?id=<?= $row['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Yakin hapus transaksi ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
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