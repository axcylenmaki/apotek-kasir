<?php
session_start();
require_once '../../config/config.php';

if (!isset($_GET['id']) || !isset($_GET['wa'])) {
    die("Parameter id transaksi dan nomor WhatsApp wajib diisi.");
}

$idTransaksi = intval($_GET['id']);
$nomorWA = preg_replace('/[^0-9]/', '', $_GET['wa']); // hanya angka

// Ambil data transaksi
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
$produkList = "";
while ($row = $detail->fetch_assoc()) {
    $nama = $row['nama_produk'];
    $jml = $row['jumlah'];
    $harga = $row['harga_satuan'];
    $sub = $jml * $harga;
    $subtotal += $sub;

    $produkList .= "$nama\n";
    $produkList .= "$jml x Rp " . number_format($harga, 0, ',', '.') . " = Rp " . number_format($sub, 0, ',', '.') . "\n";
}

// Format struk
$pesan = "===== STRUK TRANSAKSI =====\n";
$pesan .= "ID: {$transaksi['id']}\n";
$pesan .= "Tanggal: {$transaksi['tanggal']}\n";
$pesan .= "Kasir: {$transaksi['nama_kasir']}\n";

if (!empty($transaksi['nama_member'])) {
    $pesan .= "Member: {$transaksi['nama_member']} ({$transaksi['no_hp']})\n";
}

$pesan .= "--------------------------\n";
$pesan .= $produkList;
$pesan .= "--------------------------\n";

$pesan .= "Subtotal : Rp " . number_format($subtotal, 0, ',', '.') . "\n";
$pesan .= "Diskon Poin: Rp " . number_format($transaksi['diskon'], 0, ',', '.') . "\n";
$pesan .= "Total   : Rp " . number_format($transaksi['total'], 0, ',', '.') . "\n";
$pesan .= "Bayar   : Rp " . number_format($transaksi['bayar'], 0, ',', '.') . "\n";
$kembali = $transaksi['bayar'] - $transaksi['total'];
$pesan .= "Kembali : Rp " . number_format($kembali, 0, ',', '.') . "\n";
$pesan .= "Metode  : " . ucfirst($transaksi['metode_bayar']) . "\n";

$pesan .= "==========================\n";
$pesan .= "Terima kasih atas kunjungan Anda!\n";
$pesan .= "Semoga lekas sembuh 🙏\n\n";

// Tambahkan link ke struk online
$host = $_SERVER['HTTP_HOST'];
$pesan .= "Lihat struk online:\n";
$pesan .= "http://$host/apotek-kasir/includes/transaksi/nota.php?id={$transaksi['id']}\n\n";
$pesan .= "> Sent via fonnte.com";

// Kirim via Fonnte
$apiKey = '73iTpmnvV9ntMhpLhE8t'; // Ganti dengan API Key kamu
$target = "62" . ltrim($nomorWA, '0'); // Format +62

$data = [
    'target' => $target,
    'message' => $pesan,
];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.fonnte.com/send",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $data,
    CURLOPT_HTTPHEADER => [
        "Authorization: $apiKey"
    ],
]);

$response = curl_exec($curl);
$error = curl_error($curl);
curl_close($curl);

if ($error) {
    echo "❌ Gagal kirim WA: $error";
} else {
    echo "✅ Pesan berhasil dikirim ke $target.<br><br>Response dari Fonnte:<br><pre>$response</pre>";
}
