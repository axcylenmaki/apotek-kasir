<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../config/config.php';
require_once '../header.php';
require_once '../navbar.php';
require_once 'functions.php';

// Proses jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (tambahProduk($_POST, $_FILES)) {
        echo "<script>alert('Produk berhasil ditambahkan'); window.location.href='../../superadmin/produk.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal menambahkan produk');</script>";
    }
}

// Ambil kategori untuk dropdown
$kategoriList = getAllKategori();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk | Apotek Kasir</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-gray-900 via-blue-900 to-black text-white min-h-screen font-sans">

<div class="container mx-auto px-4 py-10 max-w-2xl">
    <h1 class="text-3xl font-bold text-center mb-8 text-white">➕ Tambah Produk</h1>

    <form action="" method="POST" enctype="multipart/form-data" class="bg-white/10 p-6 rounded-lg shadow-lg backdrop-blur-md ring-1 ring-white/20">

        <div class="mb-4">
            <label for="nama_produk" class="block font-semibold mb-1">Nama Produk</label>
            <input type="text" name="nama_produk" id="nama_produk" required
                   class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded text-white focus:outline-none">
        </div>

        <div class="mb-4">
            <label for="id_kategori" class="block font-semibold mb-1">Kategori</label>
            <select name="id_kategori" id="id_kategori" required
                    class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded text-white">
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($kategoriList as $kategori): ?>
                    <option value="<?= $kategori['id'] ?>"><?= htmlspecialchars($kategori['nama_kategori']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-4">
            <label for="stok" class="block font-semibold mb-1">Stok</label>
            <input type="number" name="stok" id="stok" min="0" required
                   class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded text-white">
        </div>

        <div class="mb-4">
            <label for="harga_beli" class="block font-semibold mb-1">Harga Beli</label>
            <input type="number" name="harga_beli" id="harga_beli" min="0" required
                   class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded text-white">
        </div>

        <div class="mb-4">
            <label for="harga_jual" class="block font-semibold mb-1">Harga Jual</label>
            <input type="number" name="harga_jual" id="harga_jual" min="0" required
                   class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded text-white">
        </div>

        <div class="mb-4">
            <label for="expired_date" class="block font-semibold mb-1">Expired Date</label>
            <input type="date" name="expired_date" id="expired_date" required
                   class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded text-white">
        </div>

        <div class="mb-4">
            <label for="barcode" class="block font-semibold mb-1">Barcode (Opsional)</label>
            <input type="text" name="barcode" id="barcode"
                   class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded text-white"
                   placeholder="Kosongkan jika ingin dibuat otomatis">
        </div>

        <div class="mb-6">
            <label for="gambar" class="block font-semibold mb-1">Gambar Produk (Opsional)</label>
            <input type="file" name="gambar" id="gambar"
                   class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded text-white file:text-white file:bg-blue-600 file:border-0 file:px-3 file:py-1 file:rounded">
        </div>

        <div class="mb-4">
            <label for="deskripsi" class="block font-semibold mb-1">Deskripsi Produk</label>
            <textarea name="deskripsi" id="deskripsi" rows="4"
                      class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded text-white focus:outline-none"></textarea>
        </div>

        <div class="mb-4">
            <label for="izin_edar" class="block font-semibold mb-1">Nomor Izin Edar (BPOM)</label>
            <input type="text" name="izin_edar" id="izin_edar"
                   class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded text-white"
                   value="">
        </div>

        <div class="flex justify-between items-center">
            <a href="../../superadmin/produk.php" class="text-gray-300 hover:underline">← Kembali</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-5 py-2 rounded text-white font-semibold shadow">
                Simpan Produk
            </button>
        </div>

    </form>
</div>

<?php include '../footer.php'; ?>
</body>
</html>
