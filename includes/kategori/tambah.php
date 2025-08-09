<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../../auth/login.php");
    exit;
}

include '../../config/config.php';
include 'functions.php';
include '../header.php';
include '../navbar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kategori = trim($_POST['nama_kategori']);
    $keterangan = trim($_POST['keterangan']);
    $created_by = $_SESSION['user_id'];

    $gambar = '';
    if (!empty($_FILES['gambar']['name'])) {
        $gambar = basename($_FILES['gambar']['name']);
        $target_dir = "../../assets/img/kategori/";
        $target_file = $target_dir . $gambar;

        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file);
    }

    if (createKategori($conn, $nama_kategori, $keterangan, $gambar, $created_by)) {
        header("Location: ../../superadmin/kategori.php?add=success");
        exit;
    } else {
        $error = "❌ Gagal menambahkan kategori. Silakan coba lagi.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Kategori</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0d1b2a] text-white min-h-screen font-sans flex items-center justify-center px-4 py-10">

    <div class="w-full max-w-2xl bg-white text-[#0d1b2a] rounded-xl shadow-lg p-8">
        <h2 class="text-2xl font-bold mb-6">➕ Tambah Kategori Produk</h2>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 text-red-700 border border-red-300 px-4 py-3 rounded mb-4">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label for="nama_kategori" class="block font-medium mb-1">Nama Kategori</label>
                <input type="text" name="nama_kategori" id="nama_kategori" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div>
                <label for="keterangan" class="block font-medium mb-1">Keterangan</label>
                <textarea name="keterangan" id="keterangan" rows="4"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
            </div>

            <div>
                <label for="gambar" class="block font-medium mb-1">Gambar Kategori</label>
                <input type="file" name="gambar" id="gambar"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg file:bg-blue-600 file:text-white file:border-0 file:px-3 file:py-1 file:rounded-md file:cursor-pointer">
                <p class="text-sm text-gray-500 mt-1">Opsional. Format JPG/PNG. Ukuran ideal 300x300px.</p>
            </div>

            <div class="flex justify-between items-center">
                <a href="../../superadmin/kategori.php" class="text-sm text-blue-700 hover:underline">← Kembali</a>
                <button type="submit"
                    class="bg-blue-700 hover:bg-blue-800 transition duration-200 px-5 py-2 rounded-lg text-white font-semibold">
                    Simpan Kategori
                </button>
            </div>
        </form>
    </div>

<?php include '../footer.php'; ?>
</body>
</html>
