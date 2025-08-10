<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$role = $_SESSION['role'] ?? '';
?>

<!-- Sidebar -->
<aside id="sidebar"
       class="fixed top-0 left-0 h-full w-64 bg-gradient-to-b from-gray-900 to-gray-800 text-white shadow-lg flex flex-col transform -translate-x-full transition-transform duration-300 z-40">

    <!-- Brand -->
    <div class="flex items-center justify-center h-16 border-b border-gray-700">
        <span class="text-xl font-bold">Apotek Kasir</span>
    </div>

    <!-- Menu -->
    <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-2">
        <?php if ($role === 'superadmin'): ?>
            <a href="/apotek-kasir/superadmin/dashboard.php" class="block px-3 py-2 rounded-lg hover:bg-gray-700 transition">Dashboard</a>
            <a href="/apotek-kasir/superadmin/kategori.php" class="block px-3 py-2 rounded-lg hover:bg-gray-700 transition">Kategori</a>
            <a href="/apotek-kasir/superadmin/produk.php" class="block px-3 py-2 rounded-lg hover:bg-gray-700 transition">Produk</a>
            <a href="/apotek-kasir/superadmin/laporan.php" class="block px-3 py-2 rounded-lg hover:bg-gray-700 transition">Laporan</a>
            <a href="/apotek-kasir/superadmin/kasir.php" class="block px-3 py-2 rounded-lg hover:bg-gray-700 transition">Kelola Kasir</a>
            <a href="/apotek-kasir/superadmin/member.php" class="block px-3 py-2 rounded-lg hover:bg-gray-700 transition">Member</a>
        <?php elseif ($role === 'kasir'): ?>
            <a href="/apotek-kasir/kasir/transaksi.php" class="block px-3 py-2 rounded-lg hover:bg-gray-700 transition">Transaksi Scan QR</a>
            <a href="/apotek-kasir/kasir/laporan.php" class="block px-3 py-2 rounded-lg hover:bg-gray-700 transition">Laporan</a>
            <a href="/apotek-kasir/kasir/member.php" class="block px-3 py-2 rounded-lg hover:bg-gray-700 transition">Member</a>
        <?php endif; ?>
    </nav>

    <!-- User & Logout -->
    <div class="border-t border-gray-700 px-4 py-4">
        <div class="flex items-center space-x-3">
<?php
// Ambil nama, foto dari session
$namaLengkap = $_SESSION['nama'] ?? 'Admin';
$username = $_SESSION['username'] ?? 'admin';
$foto = $_SESSION['foto'] ?? null;
?>
<div class="w-10 h-10 rounded-full overflow-hidden bg-gray-600 flex items-center justify-center">
    <?php if (!empty($foto)): ?>
        <img src="/apotek-kasir/assets/img/poto_profile/<?php echo htmlspecialchars($foto); ?>" alt="Foto" class="w-full h-full object-cover">
    <?php else: ?>
        <span class="text-white text-lg font-semibold"><?php echo strtoupper(substr($username, 0, 1)); ?></span>
    <?php endif; ?>
</div>
            <div>
                <p class="font-semibold"><?php echo $_SESSION['username'] ?? 'Admin'; ?></p>
                <p class="text-sm text-gray-400"><?php echo $role; ?></p>
            </div>
        </div>
        <a href="/apotek-kasir/profile.php" class="block mt-4 w-full text-center bg-blue-600 hover:bg-blue-700 px-3 py-2 rounded-lg transition">
    Profile
</a>

        <a href="/apotek-kasir/auth/logout.php" class="block mt-4 w-full text-center bg-red-600 hover:bg-red-700 px-3 py-2 rounded-lg transition">
            Keluar
        </a>
    </div>
</aside>

<!-- Konten utama -->
<div class="p-4 transition-all duration-300" id="mainContent">
    <!-- Tombol toggle sidebar -->
    <button id="sidebarToggle"
            class="mb-4 bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700 transition">
        ☰ Toggle Menu
    </button>
