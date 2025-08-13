<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit;
}


include '../config/config.php';
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/kategori/functions.php';
updateJumlahProdukPerKategori($conn);

$kategori = getAllKategori($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Kategori</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0d1b2a] text-white min-h-screen font-sans">

    <div class="max-w-7xl mx-auto px-6 py-10">
        <div class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight">📦 Manajemen Kategori Produk</h1>
            <p class="text-gray-300 mt-1">Kelola kategori produk toko Anda dengan mudah dan cepat.</p>
        </div>
        <?php if (isset($_GET['error'])): ?>
    <div id="alertBox" class="bg-red-500 text-white px-4 py-3 rounded-md mb-4 shadow animate-fade-in relative">
        <strong class="font-bold">Gagal!</strong>
        <span class="block sm:inline"><?= htmlspecialchars($_GET['error']) ?></span>
        <button onclick="document.getElementById('alertBox').remove();" class="absolute top-1 right-3 text-xl">&times;</button>
    </div>
<?php elseif (isset($_GET['delete']) && $_GET['delete'] === 'success'): ?>
    <div id="alertBox" class="bg-green-500 text-white px-4 py-3 rounded-md mb-4 shadow animate-fade-in relative">
        <strong class="font-bold">Sukses!</strong>
        <span class="block sm:inline">Kategori berhasil dihapus.</span>
        <button onclick="document.getElementById('alertBox').remove();" class="absolute top-1 right-3 text-xl">&times;</button>
    </div>
<?php endif; ?>


        <div class="mb-6">
            <a href="../includes/kategori/tambah.php" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg shadow transition duration-200">
                ➕ Tambah Kategori
            </a>
        </div>

        <div class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="min-w-full divide-y divide-gray-200 text-[#0d1b2a]">
                <thead class="bg-gray-100 text-sm font-semibold uppercase tracking-wide">
                    <tr>
                        <th class="px-6 py-4 text-left">#</th>
                        <th class="px-6 py-4 text-left">Gambar</th>
                        <th class="px-6 py-4 text-left">Nama Kategori</th>
                        <th class="px-6 py-4 text-left">Deskripsi</th>
                        <th class="px-6 py-4 text-left">Jumlah Produk</th>
                        <th class="px-6 py-4 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php $no = 1; while ($row = $kategori->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-6 py-4"><?= $no++ ?></td>
                        <td class="px-6 py-4">
                            <?php if (!empty($row['gambar'])): ?>
                                <img src="../assets/img/kategori/<?= $row['gambar'] ?>" alt="Gambar Kategori" class="w-14 h-14 object-cover rounded-md border border-gray-200">
                            <?php else: ?>
                                <span class="text-sm italic text-gray-500">Tidak ada gambar</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 font-medium"><?= htmlspecialchars($row['nama_kategori']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-700"><?= nl2br(htmlspecialchars($row['keterangan'])) ?></td>
                        <td class="px-6 py-4 text-center"><?= (int)$row['jumlah_produk'] ?></td>
                        <td class="px-6 py-4 space-x-2">
                            <a href="../includes/kategori/edit.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:underline text-sm">Edit</a>
                            <span class="text-gray-400">|</span>
                            <a href="../includes/kategori/hapus.php?id=<?= $row['id'] ?>" class="text-red-600 hover:underline text-sm" onclick="return confirm('Hapus kategori ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
<script>
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const toggleBtn = document.getElementById('sidebarToggle');

    toggleBtn?.addEventListener('click', () => {
        sidebar?.classList.toggle('-translate-x-full');

        // Optional: shift konten utama (kalau sidebar muncul)
        if (!sidebar.classList.contains('-translate-x-full')) {
            mainContent.classList.add('md:ml-64');
        } else {
            mainContent.classList.remove('md:ml-64');
        }
    });
</script>

</html>
