<?php
session_start();
include '../../config/config.php';
include 'functions.php';

if ($_SESSION['role'] !== 'superadmin') {
    header("Location: ../../login.php");
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';
    $fotoName = null;

    // Validasi dasar
    if (!$nama) $errors[] = 'Nama kasir wajib diisi.';
    if (!$username) $errors[] = 'Username wajib diisi.';
    if (!$password) $errors[] = 'Password wajib diisi.';

    // Validasi file gambar jika diupload
    if (!empty($_FILES['foto']['name'])) {
        $allowedExt = ['jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt)) {
            $errors[] = 'Hanya file JPG, JPEG, dan PNG yang diperbolehkan.';
        }

        if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Ukuran gambar maksimal 2MB.';
        }

        if (!$errors) {
            $fotoName = 'kasir_' . time() . '.' . $ext;
            $uploadPath = '../../assets/img/poto_profile/' . $fotoName;
            move_uploaded_file($_FILES['foto']['tmp_name'], $uploadPath);
        }
    }

    // Simpan jika valid
    if (!$errors) {
        if (tambahKasir($nama, $username, $password, $email, $fotoName)) {
            $success = true;
        } else {
            $errors[] = "Gagal menyimpan data kasir. Username mungkin sudah digunakan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Tambah Kasir - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-gray-900 via-blue-900 to-black text-gray-100 min-h-screen font-sans">

    <?php include '../../includes/navbar.php'; ?>

    <main class="max-w-3xl mx-auto px-6 py-10">
        <header class="mb-10">
            <h1 class="text-4xl font-extrabold tracking-tight drop-shadow-lg">Tambah Kasir Baru</h1>
            <p class="mt-1 text-gray-400">Isi form berikut untuk menambahkan kasir baru.</p>
        </header>

        <?php if ($success): ?>
            <div class="mb-6 rounded-md bg-green-600 px-6 py-4 shadow-lg text-white font-semibold animate-fadeIn">
                Kasir berhasil ditambahkan! <a href="../kasir.php" class="underline hover:text-blue-200">Kembali ke daftar</a>
            </div>
        <?php elseif ($errors): ?>
            <div class="mb-6 rounded-md bg-red-700 px-6 py-4 shadow-lg text-white font-semibold animate-fadeIn">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="bg-gray-800 p-8 rounded-lg shadow-md" novalidate>
            <div class="mb-6">
                <label for="nama" class="block mb-2 font-semibold text-gray-300">Nama Kasir</label>
                <input id="nama" name="nama" type="text" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required class="w-full p-3 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="Masukkan nama lengkap kasir" />
            </div>

            <div class="mb-6">
                <label for="username" class="block mb-2 font-semibold text-gray-300">Username</label>
                <input id="username" name="username" type="text" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required class="w-full p-3 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="Username unik untuk login" />
            </div>

            <div class="mb-6">
                <label for="email" class="block mb-2 font-semibold text-gray-300">Email (Opsional)</label>
                <input id="email" name="email" type="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" class="w-full p-3 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="Email kasir jika ada" />
            </div>

            <div class="mb-6">
                <label for="password" class="block mb-2 font-semibold text-gray-300">Password</label>
                <input id="password" name="password" type="password" required class="w-full p-3 rounded bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" placeholder="Password minimal 6 karakter" />
            </div>

            <div class="mb-8">
                <label for="foto" class="block mb-2 font-semibold text-gray-300">Foto Profil (Opsional)</label>
                <input id="foto" name="foto" type="file" accept=".jpg,.jpeg,.png" class="block w-full text-sm text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-blue-600 file:text-white hover:file:bg-blue-700 transition" />
            </div>

            <div class="flex justify-between items-center">
                <a href="../../superadmin/kasir.php" class="text-blue-400 hover:text-blue-600 transition font-medium">← Kembali ke daftar kasir</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 active:bg-blue-800 px-6 py-3 rounded shadow-md text-white font-semibold transition select-none">Tambah Kasir</button>
            </div>
        </form>
    </main>

    <script>
        document.querySelectorAll('.animate-fadeIn').forEach(el => {
            el.style.opacity = 0;
            el.style.transition = 'opacity 0.5s ease-in-out';
            setTimeout(() => el.style.opacity = 1, 50);
        });
    </script>

</body>
</html>
