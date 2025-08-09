<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['superadmin', 'kasir'])) {
    header("Location: ../auth/login.php");
    exit;
}

include '../config/config.php';
include '../includes/header.php';
include '../includes/navbar.php';

$result = $conn->query("SELECT * FROM member ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kelola Member - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        ::-webkit-scrollbar { height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background-color: #2563eb; border-radius: 9999px; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-blue-900 to-black text-gray-100 min-h-screen font-sans">

<main class="max-w-6xl mx-auto px-6 py-8">
    <!-- Header -->
    <header class="flex flex-col md:flex-row justify-between items-center mb-10 gap-4">
        <h1 class="text-4xl font-extrabold tracking-tight drop-shadow-lg">Kelola Member</h1>
        <a href="../includes/member/tambah.php"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-400 text-white rounded-md px-5 py-3 shadow-md transition duration-300 select-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Member
        </a>
    </header>

    <!-- Notifikasi SweetAlert -->
    <?php if (isset($_GET['success'])): ?>
        <div class="mb-6 rounded-md bg-green-600 px-6 py-4 shadow-lg text-white font-semibold animate-fadeIn">
            Member berhasil ditambahkan!
        </div>
    <?php elseif (isset($_GET['updated'])): ?>
        <div class="mb-6 rounded-md bg-yellow-600 px-6 py-4 shadow-lg text-white font-semibold animate-fadeIn">
            Member berhasil diperbarui!
        </div>
    <?php elseif (isset($_GET['deleted'])): ?>
        <div class="mb-6 rounded-md bg-red-600 px-6 py-4 shadow-lg text-white font-semibold animate-fadeIn">
            Member berhasil dihapus!
        </div>
    <?php endif; ?>

    <!-- Tabel -->
    <div class="overflow-x-auto rounded-lg shadow-lg border border-gray-700 bg-gray-800">
        <table class="min-w-full table-auto border-collapse">
            <thead class="bg-gray-700 text-gray-300 uppercase text-sm tracking-wide select-none">
                <tr>
                    <th class="px-6 py-4 text-left font-medium">#</th>
                    <th class="px-6 py-4 text-left font-medium">Nama</th>
                    <th class="px-6 py-4 text-left font-medium">Kontak</th>
                    <th class="px-6 py-4 text-left font-medium">Point</th>
                    <th class="px-6 py-4 text-center font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                <?php $no = 1; while($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-700 transition duration-200 ease-in-out cursor-default">
                    <td class="px-6 py-3 font-mono text-sm text-gray-400"><?= $no++ ?></td>
                    <td class="px-6 py-3 font-semibold"><?= htmlspecialchars($row['nama']) ?></td>
                    <td class="px-6 py-3 text-sm text-blue-300"><?= htmlspecialchars($row['no_hp']) ?></td>
                    <td class="px-6 py-3 text-sm"><?= htmlspecialchars($row['poin']) ?></td>
                    <td class="px-6 py-3 text-center flex justify-center gap-3">
                        <a href="../includes/member/edit.php?id=<?= $row['id'] ?>"
                           class="group p-2 rounded-md bg-yellow-500 hover:bg-yellow-600 active:bg-yellow-700 shadow-md transition"
                           title="Edit Member">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-black group-hover:text-white transition"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M11 17h2m-6 0h.01M7 13h10l1-4H6l1 4zm1-4h8"/>
                            </svg>
                        </a>
                        <a href="javascript:void(0);" onclick="hapusMember(<?= $row['id'] ?>)"
                           class="group p-2 rounded-md bg-red-600 hover:bg-red-700 active:bg-red-800 shadow-md transition"
                           title="Hapus Member">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white group-hover:text-gray-100 transition"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M19 7L5 21M5 7l14 14"/>
                            </svg>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if ($result->num_rows === 0): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-6 text-center text-gray-400 italic">Belum ada data member.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
function hapusMember(id) {
    Swal.fire({
        title: 'Yakin ingin menghapus?',
        text: 'Data tidak bisa dikembalikan setelah dihapus!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../includes/member/hapus.php?id=' + id;
        }
    });
}

// Fade in animation
document.querySelectorAll('.animate-fadeIn').forEach(el => {
    el.style.opacity = 0;
    el.style.transition = 'opacity 0.5s ease-in-out';
    setTimeout(() => el.style.opacity = 1, 50);
});
</script>

<script>
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');
const toggleBtn = document.getElementById('sidebarToggle');

toggleBtn?.addEventListener('click', () => {
    sidebar?.classList.toggle('-translate-x-full');
    if (!sidebar.classList.contains('-translate-x-full')) {
        mainContent?.classList.add('md:ml-64');
    } else {
        mainContent?.classList.remove('md:ml-64');
    }
});
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>
