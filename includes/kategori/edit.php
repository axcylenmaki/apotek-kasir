<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../../auth/login.php");
    exit;
}

include '../../config/config.php';
include '../header.php';
include '../navbar.php';
include 'functions.php';

$id = $_GET['id'];
$kategori = getKategoriById($conn, $id);
if (!$kategori) die("Data kategori tidak ditemukan.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kategori = trim($_POST['nama_kategori']);
    $keterangan = trim($_POST['keterangan']);
    $gambar = $kategori['gambar']; // gambar default lama
    $updated_by = $_SESSION['user_id'];

    // Jika gambar baru diupload
    if (!empty($_FILES['gambar']['name'])) {
        $new_gambar = basename($_FILES['gambar']['name']);
        $target_dir = "../../assets/img/kategori/";
        $target_file = $target_dir . $new_gambar;

        // Buat folder jika belum ada
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
            // Hapus gambar lama jika berbeda dan ada
            if (!empty($gambar) && file_exists($target_dir . $gambar) && $gambar !== $new_gambar) {
                unlink($target_dir . $gambar);
            }

            $gambar = $new_gambar;
        }
    }

    if (updateKategori($conn, $id, $nama_kategori, $keterangan, $gambar, 0, $updated_by)) {
        header("Location: ../../superadmin/kategori.php?edit=success");
        exit;
    } else {
        $error = "❌ Gagal mengupdate data.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Edit Kategori</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0d1b2a] min-h-screen text-white font-sans">
    <div class="max-w-lg mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6 text-white">✏️ Edit Kategori</h1>

        <?php if (isset($error)): ?>
            <div class="bg-red-600 bg-opacity-80 text-white p-3 mb-6 rounded shadow">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data"
            class="bg-white bg-opacity-10 p-6 rounded-lg shadow-lg backdrop-blur-md ring-1 ring-white/20 space-y-5">
            
            <label class="block">
                <span class="font-semibold mb-1 text-white block">Nama Kategori</span>
                <input type="text" name="nama_kategori" required
                    value="<?= htmlspecialchars($kategori['nama_kategori']) ?>"
                    class="w-full p-3 rounded border border-white/30 bg-transparent text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </label>

            <label class="block">
                <span class="font-semibold mb-1 text-white block">Keterangan</span>
                <textarea name="keterangan" rows="4"
                    class="w-full p-3 rounded border border-white/30 bg-transparent text-white placeholder:text-gray-400 resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($kategori['keterangan']) ?></textarea>
            </label>

            <label class="block">
                <span class="font-semibold mb-1 text-white block">Gambar (Opsional)</span>
                <input type="file" name="gambar"
                    class="w-full text-white file:bg-blue-700 file:px-4 file:py-2 file:rounded file:border-0 file:cursor-pointer" />
                <?php if ($kategori['gambar']): ?>
                    <p class="mt-2 text-sm text-gray-300">Gambar saat ini:</p>
                    <img src="../../assets/img/kategori/<?= htmlspecialchars($kategori['gambar']) ?>" alt="Gambar Kategori"
                        class="mt-1 w-32 h-32 object-cover rounded-md border border-white/20" />
                <?php else: ?>
                    <p class="mt-2 text-sm italic text-gray-400">Belum ada gambar</p>
                <?php endif; ?>
            </label>

            <div class="flex justify-between items-center">
                <a href="../../superadmin/kategori.php" class="text-blue-400 hover:underline">← Kembali</a>
                <button type="submit" class="bg-blue-700 hover:bg-blue-800 px-6 py-2 rounded text-white font-semibold shadow">
                    Update Kategori
                </button>
            </div>
        </form>
    </div>

<?php include '../footer.php'; ?>
</body>
</html>
