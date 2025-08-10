<?php
session_start();
require_once '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cartData'])) {
    $cart = json_decode($_POST['cartData'], true);
    if (!$cart || empty($cart)) {
        die("Keranjang kosong!");
    }

    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        die("User tidak dikenali.");
    }

    // Ambil input
    $memberId = isset($_POST['id_member']) && $_POST['id_member'] !== '' ? intval($_POST['id_member']) : null;
    $pakaiPoin = isset($_POST['pakai_poin']) && $_POST['pakai_poin'] == '1';
    $metodeBayar = $_POST['metode_bayar'] ?? 'cash';
    $bayar = isset($_POST['bayar']) ? intval($_POST['bayar']) : 0;

    // Hitung total awal
    $totalAwal = 0;
    foreach ($cart as $item) {
        $totalAwal += $item['harga_jual'] * $item['qty'];
    }

    // Ambil poin member dari database & hitung diskon
    $diskon = 0;
    $memberPoin = 0;

    if ($pakaiPoin && $memberId) {
        $cek = $conn->query("SELECT poin FROM member WHERE id = $memberId");
        if ($cek->num_rows === 0) {
            die("Member tidak ditemukan.");
        }

        $data = $cek->fetch_assoc();
        $memberPoin = intval($data['poin']);
        $diskon = min($memberPoin, $totalAwal); // Diskon maksimal sebesar total belanja
    }

    $totalSetelahDiskon = max(0, $totalAwal - $diskon);

    // Validasi apakah bayar cukup
    if ($bayar < $totalSetelahDiskon) {
        die("Jumlah bayar kurang dari total belanja.");
    }

    // INSERT ke transaksi
    if ($memberId) {
        $stmt = $conn->prepare("INSERT INTO transaksi (id_kasir, id_member, total, tanggal, metode_bayar, diskon, bayar, pakai_poin) 
                                VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)");
        $stmt->bind_param("iiisiii", $userId, $memberId, $totalSetelahDiskon, $metodeBayar, $diskon, $bayar, $pakaiPoin);
    } else {
        $stmt = $conn->prepare("INSERT INTO transaksi (id_kasir, total, tanggal, metode_bayar, diskon, bayar, pakai_poin) 
                                VALUES (?, ?, NOW(), ?, ?, ?, ?)");
        $stmt->bind_param("iisiii", $userId, $totalSetelahDiskon, $metodeBayar, $diskon, $bayar, $pakaiPoin);
    }

    $stmt->execute();
    $transaksiId = $stmt->insert_id;
    $stmt->close();

    // Masukkan detail transaksi & kurangi stok
    foreach ($cart as $item) {
        $produkId = intval($item['id']);
        $qty = intval($item['qty']);
        $hargaJual = intval($item['harga_jual']);

        // Cek stok
        $stokRes = $conn->query("SELECT stok FROM produk WHERE id = $produkId");
        $stokRow = $stokRes->fetch_assoc();
        if ($stokRow['stok'] < $qty) {
            die("Stok produk {$item['nama_produk']} tidak cukup!");
        }

        // Simpan detail
        $stmtDetail = $conn->prepare("INSERT INTO detail_transaksi (id_transaksi, id_produk, jumlah, harga_satuan) 
                                      VALUES (?, ?, ?, ?)");
        $stmtDetail->bind_param("iiii", $transaksiId, $produkId, $qty, $hargaJual);
        $stmtDetail->execute();
        $stmtDetail->close();

        // Kurangi stok
        $conn->query("UPDATE produk SET stok = stok - $qty WHERE id = $produkId");
    }

    // Update poin member
    if ($memberId) {
        if ($pakaiPoin) {
            // Kurangi poin yang digunakan
            $conn->query("UPDATE member SET poin = poin - $diskon WHERE id = $memberId");
        } else {
            // Tambah poin (1 poin tiap 1000 rupiah)
            $poin_didapat = floor($totalSetelahDiskon / 1000);
            $conn->query("UPDATE member SET poin = poin + $poin_didapat WHERE id = $memberId");
        }
    }

    // Redirect ke nota
    header("Location: ../../includes/transaksi/nota.php?id=$transaksiId");
    exit;

} else {
    echo "Permintaan tidak valid.";
}
?>
