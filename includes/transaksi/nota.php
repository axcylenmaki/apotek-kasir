<?php
session_start();
require_once '../../config/config.php';

if (!isset($_GET['id'])) {
    die("Transaksi tidak ditemukan.");
}

$idTransaksi = intval($_GET['id']);

// Ambil data transaksi + kasir + member (jika ada)
$result = $conn->query("
    SELECT t.*, 
           u.nama AS nama_kasir, 
           m.nama AS nama_member, 
           m.no_hp 
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Transaksi</title>
    <style>
        body { font-family: monospace; width: 300px; margin: auto; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        table { width: 100%; margin-top: 10px; }
        td { padding: 2px 0; }
        hr { border: 1px dashed #000; }
        .right { text-align: right; }
        @media print {
            button { display: none; }
        }
        a.button-link {
            display: inline-block;
            margin: 4px 2px;
            padding: 6px 16px;
            background: #2563eb;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
        }
        .whatsapp-btn {
            background: #25D366;
        }
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
        <?php while ($row = $detail->fetch_assoc()): ?>
            <tr>
                <td colspan="2"><?= htmlspecialchars($row['nama_produk']) ?></td>
            </tr>
            <tr>
                <td><?= $row['jumlah'] ?> x Rp <?= number_format($row['harga_satuan'], 0, ',', '.') ?></td>
                <td class="right">Rp <?= number_format($row['jumlah'] * $row['harga_satuan'], 0, ',', '.') ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <hr>

    <?php
        $subtotal = $transaksi['total'] + $transaksi['diskon'];
        $metode = strtolower($transaksi['metode_bayar']);
        $bayar = $transaksi['bayar'];
        $kembalian = $bayar - $transaksi['total'];

        // Metode bayar QRIS atau Debit → otomatis pas
        if ($metode === 'qris' || $metode === 'debit') {
            $bayar = $transaksi['total'];
            $kembalian = 0;
        }
    ?>

    <p class="right">Subtotal: Rp <?= number_format($subtotal, 0, ',', '.') ?></p>
    <?php if ($transaksi['diskon'] > 0): ?>
        <p class="right">Diskon Poin: Rp <?= number_format($transaksi['diskon'], 0, ',', '.') ?></p>
    <?php endif; ?>
    <p class="bold right">Total: Rp <?= number_format($transaksi['total'], 0, ',', '.') ?></p>
    <p class="right">Bayar: Rp <?= number_format($bayar, 0, ',', '.') ?></p>
    <p class="right">Kembalian: Rp <?= number_format($kembalian, 0, ',', '.') ?></p>
    <p class="right">Metode Bayar: <?= ucfirst($transaksi['metode_bayar']) ?></p>

    <hr>

    <div class="center">
        <p>Terima kasih!</p>
        <p>Semoga lekas sembuh 🙏</p>

        <button onclick="window.print()">🖨️ Cetak Struk</button>

        <a href="nota_pdf.php?id=<?= $transaksi['id'] ?>" target="_blank">
            <button>⬇️ Download PDF</button>
        </a>

        <?php if (!empty($transaksi['no_hp'])): ?>
            <br><br>
            <a class="button-link whatsapp-btn" target="_blank" 
               href="kirim_wa.php?id=<?= $transaksi['id'] ?>&wa=<?= urlencode($transaksi['no_hp']) ?>">
               📲 Kirim via WhatsApp
            </a>
        <?php endif; ?>

        <br><br>
        <a href="../../kasir/transaksi.php" class="button-link">← Kembali ke Transaksi</a>
    </div>
</body>
</html>
