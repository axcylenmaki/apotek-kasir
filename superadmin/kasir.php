<?php
session_start();
include '../config/config.php';
include '../includes/kasir/functions.php';

if ($_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit;
}

$kasirList = getAllKasir();
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kelola Kasir - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        ::-webkit-scrollbar { height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background-color: #2563eb; border-radius: 9999px; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-blue-900 to-black text-gray-100 min-h-screen font-sans">

    <?php include '../includes/navbar.php'; ?>

    <main class="max-w-6xl mx-auto px-6 py-8">
        <header class="flex flex-col md:flex-row justify-between items-center mb-10 gap-4">
            <h1 class="text-4xl font-extrabold tracking-tight drop-shadow-lg">Kelola Kasir</h1>
            <a href="../includes/kasir/tambah.php"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-400 text-white rounded-md px-5 py-3 shadow-md transition duration-300 select-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Kasir
            </a>
        </header>

        <?php if (isset($_GET['delete']) && $_GET['delete'] === 'success'): ?>
            <div class="mb-6 rounded-md bg-green-600 px-6 py-4 shadow-lg text-white font-semibold animate-fadeIn">
                Kasir berhasil dihapus!
            </div>
        <?php elseif (isset($_GET['delete']) && $_GET['delete'] === 'forbidden'): ?>
            <div class="mb-6 rounded-md bg-red-600 px-6 py-4 shadow-lg text-white font-semibold animate-fadeIn">
                Kasir superadmin tidak bisa dihapus!
            </div>
        <?php endif; ?>

        <div class="overflow-x-auto rounded-lg shadow-lg border border-gray-700 bg-gray-800">
            <table class="min-w-full table-auto border-collapse">
                <thead class="bg-gray-700 text-gray-300 uppercase text-sm tracking-wide select-none">
                    <tr>
                        <th class="px-6 py-4 text-left font-medium">ID</th>
                        <th class="px-6 py-4 text-left font-medium">Nama</th>
                        <th class="px-6 py-4 text-left font-medium">Username</th>
                        <th class="px-6 py-4 text-left font-medium">Email</th>
                        <th class="px-6 py-4 text-left font-medium">Foto</th> <!-- Tambahkan ini -->
                        <th class="px-6 py-4 text-left font-medium">Role</th>
                        <th class="px-6 py-4 text-left font-medium">Status</th>
                        <th class="px-6 py-4 text-center font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php foreach ($kasirList as $kasir): ?>
                        <tr class="hover:bg-gray-700 transition duration-200 ease-in-out cursor-default">
                            <td class="px-6 py-3 font-mono text-sm text-gray-400"><?= $kasir['id'] ?></td>
                            <td class="px-6 py-3 font-semibold"><?= htmlspecialchars($kasir['nama']) ?></td>
                            <td class="px-6 py-3 lowercase tracking-wide text-blue-300 font-medium"><?= htmlspecialchars($kasir['username']) ?></td>
                            <td class="px-6 py-3 text-sm text-gray-200">
                                <?= htmlspecialchars($kasir['email'] ?? '') ?: '<span class="italic text-gray-400">Tidak ada</span>' ?>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <?php if (!empty($kasir['foto'])): ?>
                                    <img src="../assets/img/poto_profile/<?= htmlspecialchars($kasir['foto']) ?>" alt="Foto Kasir" class="h-10 w-10 rounded-full object-cover mx-auto border border-gray-600">
                                <?php else: ?>
                                    <span class="inline-block h-10 w-10 rounded-full bg-gray-600 text-gray-300 flex items-center justify-center mx-auto text-xs">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3 capitalize">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                                    <?= $kasir['role'] === 'superadmin' ? 'bg-green-600 text-green-100' : 'bg-indigo-600 text-indigo-100' ?>">
                                    <?= htmlspecialchars($kasir['role']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3">
    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold 
        <?= $kasir['aktif'] ? 'bg-green-600 text-green-100' : 'bg-red-600 text-red-100' ?>">
        <?= $kasir['aktif'] ? 'Aktif' : 'Tidak Aktif' ?>
    </span>
</td>

                            <td class="px-6 py-3 text-center flex justify-center gap-3">
                                <a href="../includes/kasir/edit.php?id=<?= $kasir['id'] ?>"
                                   class="group p-2 rounded-md bg-yellow-500 hover:bg-yellow-600 active:bg-yellow-700 shadow-md transition"
                                   title="Edit Kasir">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-black group-hover:text-white transition"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M11 17h2m-6 0h.01M7 13h10l1-4H6l1 4zm1-4h8"/>
                                    </svg>
                                </a>

                              <?php if ($kasir['role'] !== 'superadmin' && !$kasir['aktif']): ?>
    <a href="../includes/kasir/hapus.php?id=<?= $kasir['id'] ?>"
       onclick="return confirm('Yakin ingin menghapus kasir ini?')"
       class="group p-2 rounded-md bg-red-600 hover:bg-red-700 active:bg-red-800 shadow-md transition"
       title="Hapus Kasir">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white group-hover:text-gray-100 transition"
             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M19 7L5 21M5 7l14 14"/>
        </svg>
    </a>
<?php else: ?>
    <span class="text-gray-400 italic text-sm select-none" title="Tidak bisa dihapus">
        Tidak bisa dihapus
    </span>
<?php endif; ?>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(empty($kasirList)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-6 text-center text-gray-400 italic">Belum ada data kasir.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        // Fade in animation
        document.querySelectorAll('.animate-fadeIn').forEach(el => {
            el.style.opacity = 0;
            el.style.transition = 'opacity 0.5s ease-in-out';
            setTimeout(() => el.style.opacity = 1, 50);
        });
    </script>
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
