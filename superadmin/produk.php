<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ../auth/login.php");
    exit;
}
include '../config/config.php';
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/kategori/functions.php';

// Ambil data produk dari database, tambahkan izin_edar dan deskripsi
$query = "SELECT produk.*, kategori.nama_kategori 
          FROM produk 
          LEFT JOIN kategori ON produk.id_kategori = kategori.id";
$result = mysqli_query($conn, $query);

// Siapkan array produk
$produk = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Buat barcode manual jika belum ada
    if (empty($row['barcode'])) {
        $barcode = str_pad($row['id'], 13, '0', STR_PAD_LEFT);
        $row['barcode'] = $barcode;

        // Update ke database (hanya pertama kali)
        $updateQuery = "UPDATE produk SET barcode = '$barcode' WHERE id = {$row['id']}";
        mysqli_query($conn, $updateQuery);
    }

    $produk[] = [
        'id' => $row['id'],
        'barcode' => $row['barcode'],
        'nama' => $row['nama_produk'],
        'kategori' => $row['nama_kategori'] ?? 'Tidak diketahui',
        'stok' => $row['stok'],
        'harga_beli' => $row['harga_beli'],
        'harga_jual' => $row['harga_jual'],
        'expired' => $row['expired_date'],
        'gambar' => $row['gambar'],
        'izin_edar' => $row['izin_edar'] ?? '',
        'deskripsi' => $row['deskripsi'] ?? '',
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Data Produk | Apotek Kasir</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-b from-gray-900 via-blue-900 to-black text-white font-sans">

    <div class="container mx-auto px-4 py-10">
        <h1 class="text-3xl font-extrabold text-center mb-10 text-white drop-shadow-lg">
            📦 Data Produk Apotek
        </h1>

        <div class="mb-6 text-right">
            <a href="../includes/produk/tambah.php" 
               class="inline-block px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded shadow">
                + Tambah Produk
            </a>
        </div>

        <div class="overflow-x-auto rounded-xl shadow-2xl backdrop-blur-sm bg-white/10 ring-1 ring-white/20">
            <table class="min-w-full divide-y divide-white/20 text-sm">
                <thead class="bg-white/10 backdrop-blur-sm text-blue-200">
                    <tr>
                        <?php
                        $headers = ['ID Produk', 'Barcode (Gambar)', 'Nama Produk', 'Kategori', 'Stok', 'Harga Beli', 'Harga Jual', 'Untung', 'Expired Date', 'Gambar', 'Aksi'];
                        foreach ($headers as $header) {
                            echo "<th class='px-4 py-3 text-left uppercase tracking-wider font-semibold'>$header</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 text-gray-200">
                    <?php foreach ($produk as $row): ?>
                        <tr class="hover:bg-blue-800/30 transition duration-200">
                            <td class="px-4 py-3"><?= $row['id'] ?></td>
                            <td class="px-4 py-3">
                                <?php 
                                $barcodeImgPath = "../assets/img/barcode/" . $row['id'] . ".png"; 
                                ?>
                                <?php if (file_exists($barcodeImgPath)): ?>
                                    <img src="<?= $barcodeImgPath ?>" alt="Barcode <?= $row['id'] ?>" style="height:50px; width:auto;">
                                    <br>
                                    <a href="<?= $barcodeImgPath ?>" download class="text-blue-300 hover:underline text-xs">Unduh</a>
                                <?php else: ?>
                                    <span class="italic text-gray-400">Belum tersedia</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-4 py-3"><?= htmlspecialchars($row['nama']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($row['kategori']) ?></td>
                            <td class="px-4 py-3"><?= $row['stok'] ?></td>
                            <td class="px-4 py-3 text-green-400">Rp <?= number_format($row['harga_beli'], 0, ',', '.') ?></td>
                            <td class="px-4 py-3 text-blue-400">Rp <?= number_format($row['harga_jual'], 0, ',', '.') ?></td>
                            <td class="px-4 py-3 text-yellow-300 font-semibold">
                                Rp <?= number_format($row['harga_jual'] - $row['harga_beli'], 0, ',', '.') ?>
                            </td>
                            <td class="px-4 py-3"><?= date('d-m-Y', strtotime($row['expired'])) ?></td>
                            <td class="px-4 py-3">
                                <?php if (!empty($row['gambar']) && file_exists("../assets/img/produk/" . $row['gambar'])): ?>
                                    <img src="../assets/img/produk/<?= htmlspecialchars($row['gambar']) ?>" 
                                         alt="<?= htmlspecialchars($row['nama']) ?>" 
                                         class="w-16 h-16 object-cover rounded-lg shadow-md border border-white/20">
                                <?php else: ?>
                                    <span class="italic text-gray-400">Tidak ada gambar</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <a href="#" 
                                   class="text-blue-400 hover:underline mr-3"
                                   onclick="showDetail(this); return false;"
data-nama="<?= htmlspecialchars($row['nama'])?>"
                                   data-kategori="<?= htmlspecialchars($row['kategori']) ?>"
                                   data-stok="<?= $row['stok'] ?>"
                                   data-harga_beli="<?= $row['harga_beli'] ?>"
                                   data-harga_jual="<?= $row['harga_jual'] ?>"
                                   data-expired="<?= $row['expired'] ?>"
                                   data-gambar="<?= htmlspecialchars($row['gambar']) ?>"
                                   data-izin_edar="<?= htmlspecialchars($row['izin_edar']) ?>"
                                   data-deskripsi="<?= htmlspecialchars($row['deskripsi']) ?>"
                                >
                                  Detail
                                </a>
                                <a href="../includes/produk/edit.php?id=<?= $row['id'] ?>" class="text-yellow-400 hover:underline mr-3">Edit</a>
                                <a href="../includes/produk/hapus.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus produk ini?')" class="text-red-500 hover:underline">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

<!-- Modal -->
<div id="detailModal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center hidden z-50">
<div class="bg-white text-gray-900 rounded-lg shadow-lg max-w-lg w-full p-6 relative overflow-y-auto max-h-[90vh]">
        <button onclick="closeModal()" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 font-bold text-xl">&times;</button>
        <div id="modalContent">
            <!-- Detail produk akan dimunculkan di sini via JS -->
        </div>
    </div>
</div>

<script>
function showDetail(el) {
    const data = {
        nama: el.getAttribute('data-nama'),
        kategori: el.getAttribute('data-kategori'),
        stok: el.getAttribute('data-stok'),
        harga_beli: el.getAttribute('data-harga_beli'),
        harga_jual: el.getAttribute('data-harga_jual'),
        expired: el.getAttribute('data-expired'),
        gambar: el.getAttribute('data-gambar'),
        izin_edar: el.getAttribute('data-izin_edar'),
        deskripsi: el.getAttribute('data-deskripsi'),
    };

    console.log('Data produk dari atribut:', data); // <- cek di console browser

    const modal = document.getElementById('detailModal');
    const content = document.getElementById('modalContent');

    content.innerHTML = `
        <h2 class="text-2xl font-bold mb-4">${data.nama || '-'}</h2>
        ${data.gambar ? `<img src="../assets/img/produk/${data.gambar}" alt="${data.nama}" class="mb-4 max-h-48 object-contain mx-auto rounded shadow" />` : ''}
        <p><strong>Kategori:</strong> ${data.kategori || '-'}</p>
        <p><strong>Stok:</strong> ${data.stok || '-'}</p>
        <p><strong>Harga Beli:</strong> Rp ${data.harga_beli ? Number(data.harga_beli).toLocaleString('id-ID') : '-'}</p>
        <p><strong>Harga Jual:</strong> Rp ${data.harga_jual ? Number(data.harga_jual).toLocaleString('id-ID') : '-'}</p>
        <p><strong>Expired:</strong> ${data.expired ? new Date(data.expired).toLocaleDateString('id-ID') : '-'}</p>
        <p><strong>Izin Edar:</strong> ${data.izin_edar || '-'}</p>
        <p><strong>Deskripsi:</strong> ${data.deskripsi || 'Tidak ada deskripsi.'}</p>
    `;

    modal.classList.remove('hidden');
}


function closeModal() {
    document.getElementById('detailModal').classList.add('hidden');
}
</script>
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
