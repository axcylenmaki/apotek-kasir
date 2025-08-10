<?php
require '../../config/config.php';
require '../../vendor/autoload.php';

use Mpdf\Mpdf;

$tipe = $_GET['tipe'] ?? '';
$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-d');
$start = date('Y-m-d', strtotime($_GET['start'] ?? date('Y-m-01')));
$end = date('Y-m-d', strtotime($_GET['end'] ?? date('Y-m-d')));

// QUERY HARUS DI SINI DULU:
$query = $conn->query("
    SELECT 
        t.*, 
        u.nama AS nama_kasir, 
        m.nama AS nama_member 
    FROM transaksi t
    LEFT JOIN users u ON t.id_kasir = u.id
    LEFT JOIN member m ON t.id_member = m.id
    WHERE t.tanggal BETWEEN '$start 00:00:00' AND '$end 23:59:59'
    ORDER BY t.tanggal DESC
");
$total_penjualan = 0;
$jumlah_transaksi = 0;
$total_modal = 0;
$total_keuntungan = 0;
$rows = [];

while ($data = $query->fetch_assoc()) {
    $rows[] = $data;
    
    $jumlah_transaksi++;
    $total_penjualan += (int)$data['total'];

    $id_transaksi = $data['id'];

    $modal_query = $conn->query("
        SELECT dt.jumlah, dt.harga_satuan, p.harga_beli 
        FROM detail_transaksi dt 
        JOIN produk p ON dt.id_produk = p.id 
        WHERE dt.id_transaksi = $id_transaksi
    ");

    while ($m = $modal_query->fetch_assoc()) {
        $modal = $m['jumlah'] * $m['harga_beli'];
        $keuntungan = ($m['harga_satuan'] * $m['jumlah']) - $modal;

        $total_modal += $modal;
        $total_keuntungan += $keuntungan;
    }
}


if ($tipe !== 'penjualan') {
    die('Tipe PDF hanya untuk penjualan.');
}


// Prepare data untuk chart
$dates = [];
$sales_per_date = [];

foreach ($rows as $r) {
    $date = substr($r['tanggal'], 0, 10);
    if (!isset($sales_per_date[$date])) {
        $sales_per_date[$date] = 0;
    }
    $sales_per_date[$date] += $r['total'];
}

ksort($sales_per_date);

$labels = json_encode(array_keys($sales_per_date));
$data_chart = json_encode(array_values($sales_per_date));

// Generate URL Chart QuickChart.io (chart batang sederhana)
$chartUrl = "https://quickchart.io/chart?c=" . urlencode('{
    type: "bar",
    data: {
        labels: ' . $labels . ',
        datasets: [{
            label: "Total Penjualan",
            backgroundColor: "rgba(54, 162, 235, 0.6)",
            borderColor: "rgba(54, 162, 235, 1)",
            borderWidth: 1,
            data: ' . $data_chart . '
        }]
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true
                }
            }]
        }
    }
}');

$html = "
<h2 style='text-align:center;'>Laporan Penjualan</h2>
<p style='text-align:center;'>Periode: $start s/d $end</p>
<hr>
<h4>Ringkasan</h4>
<table width='100%' border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>
    <tr>
        <td><strong>Total Penjualan</strong></td>
        <td>Rp " . number_format($total_penjualan, 0, ',', '.') . "</td>
    </tr>
    <tr>
        <td><strong>Jumlah Transaksi</strong></td>
        <td>$jumlah_transaksi Transaksi</td>
    </tr>
    <tr>
        <td><strong>Total Modal</strong></td>
        <td>Rp " . number_format($total_modal, 0, ',', '.') . "</td>
    </tr>
    <tr>
        <td><strong>Total Keuntungan</strong></td>
        <td>Rp " . number_format($total_keuntungan, 0, ',', '.') . "</td>
    </tr>
</table>

<h4 style='margin-top:30px;'>Grafik Penjualan</h4>
<div style='text-align:center;'>
    <img src='$chartUrl' style='max-width: 100%; height: auto;' />
</div>

<h4 style='margin-top:30px;'>Daftar Transaksi</h4>
<table width='100%' border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>
    <tr style='background:#eee;'>
        <th>No</th>
        <th>ID Transaksi</th>
        <th>Tanggal</th>
        <th>Total</th>
        <th>Metode Bayar</th>
        <th>Kasir</th>
        <th>Member</th>
    </tr>";

$no = 1;
foreach ($rows as $r) {
    $memberName = $r['nama_member'] ?: '-'; // Tampilkan '-' jika kosong/null
    $html .= "
    <tr>
        <td>$no</td>
        <td>{$r['id']}</td>
        <td>{$r['tanggal']}</td>
        <td>Rp " . number_format($r['total'], 0, ',', '.') . "</td>
        <td>{$r['metode_bayar']}</td>
        <td>{$r['nama_kasir']}</td>
        <td>{$memberName}</td>
    </tr>";
    $no++;
}

$html .= "</table>";

// Generate PDF
$mpdf = new Mpdf(['tempDir' => __DIR__ . '/tmp']); // Optional temp dir if needed
$mpdf->WriteHTML($html);
$mpdf->Output("laporan_penjualan_{$start}_{$end}.pdf", 'I');
