<?php
require_once '../../config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;

if (!isset($_GET['id'])) {
    die("Transaksi tidak ditemukan.");
}

$idTransaksi = intval($_GET['id']);

// Ambil data transaksi + kasir + member
$result = $conn->query("
    SELECT t.*, u.nama AS nama_kasir, m.nama AS nama_member, m.no_hp 
    FROM transaksi t
    JOIN users u ON t.id_kasir = u.id
    LEFT JOIN member m ON t.id_member = m.id
    WHERE t.id = $idTransaksi
");
$transaksi = $result->fetch_assoc();
if (!$transaksi) {
    die("Data transaksi tidak ditemukan.");
}

// Ambil detail transaksi
$detail = $conn->query("
    SELECT d.*, p.nama_produk 
    FROM detail_transaksi d
    JOIN produk p ON d.id_produk = p.id 
    WHERE d.id_transaksi = $idTransaksi
");

// Hitung subtotal
$subtotal = 0;
$produkRows = '';
while ($row = $detail->fetch_assoc()) {
    $nama = htmlspecialchars($row['nama_produk']);
    $qty = $row['jumlah'];
    $harga = $row['harga_satuan'];
    $sub = $qty * $harga;
    $subtotal += $sub;

    $produkRows .= "
        <tr><td colspan='2'>$nama</td></tr>
        <tr>
            <td>$qty x Rp " . number_format($harga, 0, ',', '.') . "</td>
            <td class='right'>Rp " . number_format($sub, 0, ',', '.') . "</td>
        </tr>
    ";
}

// Siapkan HTML struk
ob_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        table { width: 100%; margin-top: 10px; border-collapse: collapse; }
        td { padding: 4px 0; vertical-align: top; }
        .right { text-align: right; }
        hr { border: 1px dashed #000; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="center">
        <h2>Apotek Sehat</h2>
        <p>Jl. Apotek Raya No.1<br>Telp: 0812-3456-7890</p>
        <hr>
    </div>

    <p><strong>ID:</strong> <?= $transaksi['id'] ?></p>
    <p><strong>Tanggal:</strong> <?= $transaksi['tanggal'] ?></p>
    <p><strong>Kasir:</strong> <?= htmlspecialchars($transaksi['nama_kasir']) ?></p>

    <?php if (!empty($transaksi['nama_member'])): ?>
        <p><strong>Member:</strong> <?= htmlspecialchars($transaksi['nama_member']) ?> (<?= htmlspecialchars($transaksi['no_hp']) ?>)</p>
    <?php endif; ?>

    <table>
        <?= $produkRows ?>
    </table>

    <hr>
    <table>
        <tr>
            <td><strong>Subtotal</strong></td>
            <td class="right">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td><strong>Diskon Poin</strong></td>
            <td class="right">Rp <?= number_format($transaksi['diskon'], 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td><strong>Total</strong></td>
            <td class="right">Rp <?= number_format($transaksi['total'], 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td><strong>Bayar</strong></td>
            <td class="right">Rp <?= number_format($transaksi['bayar'], 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td><strong>Kembali</strong></td>
            <td class="right">Rp <?= number_format($transaksi['bayar'] - $transaksi['total'], 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td><strong>Metode</strong></td>
            <td class="right"><?= ucfirst($transaksi['metode_bayar']) ?></td>
        </tr>
    </table>
    <hr>

    <div class="center">
        <p>Terima kasih!</p>
        <p>Semoga lekas sembuh 🙏</p>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A6', 'portrait');
$dompdf->render();

// Download PDF
$dompdf->stream("struk_transaksi_{$transaksi['id']}.pdf", ["Attachment" => true]);
exit;
?>
