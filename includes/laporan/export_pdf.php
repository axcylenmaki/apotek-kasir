<?php
session_start();
require '../../config/config.php';
require '../../vendor/autoload.php';

use Mpdf\Mpdf;

// Debugging penerimaan parameter
error_log("Export PDF Received - GET: " . print_r($_GET, true));

// Validasi parameter
$today = date('Y-m-d');
$start = $_GET['start'] ?? $today;
$end = $_GET['end'] ?? $today;
$range = $_GET['range'] ?? 'harian';
$tipe = $_GET['tipe'] ?? 'penjualan'; // Tambahkan ini

// Validasi ketat format tanggal
try {
    $start_date = (new DateTime($start))->format('Y-m-d');
    $end_date = (new DateTime($end))->format('Y-m-d');
    
    if ($range === 'harian') {
        $end_date = $start_date;
    }
    
    $today_obj = new DateTime($today);
    $start_obj = new DateTime($start_date);
    $end_obj = new DateTime($end_date);
    
    if ($start_obj > $today_obj) $start_date = $today;
    if ($end_obj > $today_obj) $end_date = $today;
    
} catch (Exception $e) {
    $start_date = $today;
    $end_date = $today;
}

error_log("Export PDF Using - Start: $start_date, End: $end_date");

// [PERBAIKAN] TAMBAHKAN QUERY DATABASE DI SINI
$query = "
    SELECT 
        t.*, 
        u.nama AS nama_kasir, 
        m.nama AS nama_member,
        SUM(dt.jumlah * dt.harga_satuan) as total_penjualan,
        SUM(dt.jumlah * p.harga_beli) as total_modal
    FROM transaksi t
    LEFT JOIN users u ON t.id_kasir = u.id
    LEFT JOIN member m ON t.id_member = m.id
    LEFT JOIN detail_transaksi dt ON t.id = dt.id_transaksi
    LEFT JOIN produk p ON dt.id_produk = p.id
    WHERE DATE(t.tanggal) BETWEEN '$start_date' AND '$end_date'
";

if ($_SESSION['role'] === 'kasir') {
    $userId = $_SESSION['user_id'];
    $query .= " AND t.id_kasir = $userId";
}

$query .= " GROUP BY t.id ORDER BY t.tanggal DESC";

// Eksekusi query
$result = $conn->query($query); // Inilah yang hilang sebelumnya

if (!$result) {
    die("Query error: " . $conn->error);
}

$rows = [];
$grand_total_penjualan = 0;
$grand_total_modal = 0;
$grand_total_keuntungan = 0;
$jumlah_transaksi = 0;

while ($data = $result->fetch_assoc()) {
    $rows[] = $data;
    $grand_total_penjualan += (float)$data['total_penjualan'];
    $grand_total_modal += (float)$data['total_modal'];
    $grand_total_keuntungan += ((float)$data['total_penjualan'] - (float)$data['total_modal']);
    $jumlah_transaksi++;
}

// ... (lanjutan kode Anda yang sudah ada) ...

if ($tipe !== 'penjualan') {
    die('Tipe PDF hanya untuk penjualan.');
}

// Prepare data for chart (only if there are transactions)
$chartUrl = '';
if (!empty($rows)) {
    $chart_data = [];
    foreach ($rows as $row) {
        $date = date('Y-m-d', strtotime($row['tanggal']));
        if (!isset($chart_data[$date])) {
            $chart_data[$date] = 0;
        }
        $chart_data[$date] += (float)$row['total_penjualan'];
    }
    
    ksort($chart_data);
    $labels = json_encode(array_keys($chart_data));
    $data_values = json_encode(array_values($chart_data));

    // Generate URL Chart QuickChart.io
    $chartUrl = "https://quickchart.io/chart?c=" . urlencode('{
        type: "bar",
        data: {
            labels: ' . $labels . ',
            datasets: [{
                label: "Total Penjualan",
                backgroundColor: "rgba(54, 162, 235, 0.6)",
                borderColor: "rgba(54, 162, 235, 1)",
                borderWidth: 1,
                data: ' . $data_values . '
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return "Rp " + value.toString().replace(/\\B(?=(\\d{3})+(?!\\d))/g, ".");
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return "Rp " + context.raw.toString().replace(/\\B(?=(\\d{3})+(?!\\d))/g, ".");
                        }
                    }
                }
            }
        }
    }');
}

// HTML Content
$html = '
<style>
    body { font-family: Arial, sans-serif; }
    h1 { color: #333; text-align: center; }
    h2 { color: #444; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    th { background-color: #f2f2f2; text-align: left; padding: 8px; }
    td { padding: 8px; border-bottom: 1px solid #ddd; }
    .summary { background-color: #f9f9f9; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
    .summary-item { margin-bottom: 10px; }
    .chart-container { text-align: center; margin: 20px 0; }
    .no-data { text-align: center; padding: 20px; color: #666; }
</style>

<h1>Laporan Penjualan</h1>

<div style="margin-bottom: 20px;">
    <p><strong>Dari Tanggal:</strong> ' . date('d/m/Y', strtotime($start_date)) . '</p>
    <p><strong>Sampai Tanggal:</strong> ' . date('d/m/Y', strtotime($end_date)) . '</p>
    <p><strong>Periode:</strong> ' . ucfirst($range) . '</p>
    <p><strong>Kasir:</strong> ' . ($_SESSION['role'] === 'kasir' ? htmlspecialchars($_SESSION['nama']) : 'Semua Kasir') . '</p>
    <p><strong>Tanggal Cetak:</strong> ' . date('d/m/Y H:i') . '</p>
</div>';

if (empty($rows)) {
    $html .= '<div class="no-data"><p>Tidak ada transaksi pada periode ini</p></div>';
} else {
    $html .= '
    <div class="summary">
        <h2>Ringkasan</h2>
        <div class="summary-item">
            <h3>Total Penjualan</h3>
            <p style="font-size: 18px; font-weight: bold;">Rp ' . number_format($grand_total_penjualan, 0, ',', '.') . '</p>
        </div>
        
        <table>
            <tr>
                <th>Total Modal</th>
                <th>Total Keuntungan</th>
                <th>Jumlah Transaksi</th>
            </tr>
            <tr>
                <td>Rp ' . number_format($grand_total_modal, 0, ',', '.') . '</td>
                <td>Rp ' . number_format($grand_total_keuntungan, 0, ',', '.') . '</td>
                <td>' . $jumlah_transaksi . '</td>
            </tr>
        </table>
    </div>';

    if (!empty($chartUrl)) {
        $html .= '
        <div class="chart-container">
            <h2>Grafik Penjualan</h2>
            <img src="' . $chartUrl . '" style="max-width: 100%; height: auto;" />
        </div>';
    }

    $html .= '
    <h2>Data Transaksi</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Kasir</th>
                <th>Member</th>
                <th>Total</th>
                <th>Metode Bayar</th>
            </tr>
        </thead>
        <tbody>';

    $no = 1;
    foreach ($rows as $row) {
        $html .= '
        <tr>
            <td>' . $no++ . '</td>
            <td>' . date('d/m/Y H:i', strtotime($row['tanggal'])) . '</td>
            <td>' . htmlspecialchars($row['nama_kasir']) . '</td>
            <td>' . ($row['nama_member'] ? htmlspecialchars($row['nama_member']) : '-') . '</td>
            <td>Rp ' . number_format($row['total_penjualan'], 0, ',', '.') . '</td>
            <td>' . ($row['metode_bayar'] ? htmlspecialchars($row['metode_bayar']) : '-') . '</td>
        </tr>';
    }

    $html .= '
        </tbody>
    </table>';
}

// Generate PDF
$mpdf = new Mpdf([
    'tempDir' => __DIR__ . '/tmp',
    'format' => 'A4',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 15,
    'margin_bottom' => 15,
    'margin_header' => 10,
    'margin_footer' => 10
]);

$mpdf->SetTitle('Laporan Penjualan ' . $start_date . ' - ' . $end_date);
$mpdf->SetAuthor('Apotek System');
$mpdf->WriteHTML($html);

// Output with proper filename
$filename = "laporan_penjualan_" . ($start_date === $end_date ? $start_date : $start_date . '_to_' . $end_date) . ".pdf";
$mpdf->Output($filename, 'I');