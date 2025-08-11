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

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM produk WHERE id = $id"));

if (!$produk) {
    echo "<script>alert('Produk tidak ditemukan'); window.location.href='../../superadmin/produk.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = trim($_POST['nama_produk']);

    // Cek duplikat nama produk, kecuali id produk yang sedang diedit
    $cekStmt = $conn->prepare("SELECT id FROM produk WHERE nama_produk = ? AND id != ?");
    $cekStmt->bind_param("si", $nama_produk, $id);
    $cekStmt->execute();
    $cekResult = $cekStmt->get_result();

    if ($cekResult->num_rows > 0) {
        echo "<script>alert('Nama produk sudah digunakan oleh produk lain, silakan gunakan nama lain');</script>";
    } else {
        if (editProduk($id, $_POST, $_FILES)) {
            echo "<script>alert('Produk berhasil diperbarui'); window.location.href='../../superadmin/produk.php';</script>";
            exit;
        } else {
            echo "<script>alert('Gagal memperbarui produk');</script>";
        }
    }
}


$kategoriList = getAllKategori();
?>

<div class="container mx-auto px-4 py-10 max-w-2xl">
    <h1 class="text-3xl font-bold text-center mb-8 text-white">✏️ Edit Produk</h1>

    <form action="" method="POST" enctype="multipart/form-data" class="bg-white/10 p-6 rounded-lg shadow-lg backdrop-blur-md ring-1 ring-white/20">
        <div class="mb-4">
            <label for="nama_produk" class="block font-semibold mb-1">Nama Produk</label>
            <input type="text" name="nama_produk" id="nama_produk" required
                class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded text-white"
                value="<?= htmlspecialchars($produk['nama_produk']) ?>">
        </div>

        <div class="mb-4">
            <label for="id_kategori" class="block font-semibold mb-1">Kategori</label>
            <select name="id_kategori" id="id_kategori" required
                class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded text-white">
                <?php foreach ($kategoriList as $kategori): ?>
                    <option value="<?= $kategori['id'] ?>" <?= $produk['id_kategori'] == $kategori['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kategori['nama_kategori']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-4">
            <label for="stok" class="block font-semibold mb-1">Stok</label>
            <input type="number" name="stok" id="stok" min="0" required
                class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded text-white"
                value="<?= $produk['stok'] ?>">
        </div>

        <div class="mb-4">
            <label for="harga_beli" class="block font-semibold mb-1">Harga Beli</label>
            <input type="number" name="harga_beli" id="harga_beli" min="0" required
                class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded text-white"
                value="<?= $produk['harga_beli'] ?>">
        </div>

        <div class="mb-4">
            <label for="harga_jual" class="block font-semibold mb-1">Harga Jual</label>
            <input type="number" name="harga_jual" id="harga_jual" min="0" required
                class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded text-white"
                value="<?= $produk['harga_jual'] ?>">
        </div>

        <div class="mb-4">
            <label for="expired_date" class="block font-semibold mb-1">Expired Date</label>
            <input type="date" name="expired_date" id="expired_date" required
                class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded text-white"
                value="<?= $produk['expired_date'] ?>">
        </div>

        <div class="mb-4">
            <label for="barcode" class="block font-semibold mb-1">Barcode</label>
            <input type="text" name="barcode" id="barcode"
                class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded text-white"
                value="<?= $produk['barcode'] ?>">
        </div>

        <div class="mb-4">
            <label for="izin_edar" class="block font-semibold mb-1">Nomor Izin Edar</label>
            <input type="text" name="izin_edar" id="izin_edar"
                class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded text-white"
                value="<?= htmlspecialchars($produk['izin_edar']) ?>">
        </div>

        <div class="mb-4">
            <label for="deskripsi" class="block font-semibold mb-1">Deskripsi</label>
            <textarea name="deskripsi" id="deskripsi" rows="4"
                class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded text-white"><?= htmlspecialchars($produk['deskripsi']) ?></textarea>
        </div>

        <div class="mb-6">
            <label for="gambar" class="block font-semibold mb-1">Ganti Gambar Produk (Opsional)</label>
            <input type="file" name="gambar" id="gambar"
                class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded text-white file:text-white file:bg-blue-600 file:border-0 file:px-3 file:py-1 file:rounded">
        </div>

        <div class="flex justify-between items-center">
            <a href="../../superadmin/produk.php" class="text-gray-300 hover:underline">← Batal</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-5 py-2 rounded text-white font-semibold shadow">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<?php include '../footer.php'; ?>
