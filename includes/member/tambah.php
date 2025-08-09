<?php
include '../../config/config.php';
require_once 'functions.php';
include '../header.php';
include '../navbar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama  = htmlspecialchars($_POST['nama']);
    $no_hp = htmlspecialchars($_POST['no_hp']);

    if (isPhoneExist($conn, $no_hp)) {
        $error = "Nomor HP sudah terdaftar.";
    } else {
        if (createMember($conn, $nama, $no_hp)) {
            header("Location: ../../superadmin/member.php?success=1");
            exit;
        } else {
            $error = "Gagal menambahkan member.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Member</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-thumb {
            background-color: #2563eb;
            border-radius: 9999px;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-900 via-gray-900 to-black text-white min-h-screen flex items-center justify-center px-4 py-12">

    <div class="bg-gray-800 w-full max-w-lg p-8 rounded-lg shadow-2xl border border-gray-700">
        <h1 class="text-3xl font-extrabold text-center text-blue-400 mb-6">Tambah Member</h1>

        <?php if (isset($error)): ?>
            <div class="bg-red-600 text-white px-4 py-3 rounded mb-4 shadow animate-pulse">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label for="nama" class="block text-sm font-semibold mb-1 text-gray-300">Nama</label>
                <input type="text" name="nama" id="nama" required
                       class="w-full px-4 py-2 rounded-md bg-gray-900 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
            </div>

            <div>
                <label for="no_hp" class="block text-sm font-semibold mb-1 text-gray-300">Nomor HP</label>
                <input type="text" name="no_hp" id="no_hp" required
                       class="w-full px-4 py-2 rounded-md bg-gray-900 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
            </div>

            <div class="flex items-center justify-between mt-6">
                <a href="../../superadmin/member.php"
                   class="text-sm text-blue-400 hover:underline hover:text-blue-300 transition">
                   ← Kembali
                </a>

                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 active:bg-blue-800 px-6 py-2 rounded-md text-white font-semibold shadow-md transition">
                    Simpan
                </button>
            </div>
        </form>
    </div>

    <?php include '../footer.php'; ?>
</body>
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
</html>
