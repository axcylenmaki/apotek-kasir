<?php
session_start();
require_once '../../config/config.php';
require_once '../header.php';
require_once '../navbar.php';
require_once 'functions.php';

if (!in_array($_SESSION['role'], ['superadmin', 'kasir'])) {
    header('Location: ../../auth/login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../../superadmin/kasir.php');
    exit;
}

$id = intval($_GET['id']);
$kasir = getKasirById($id);

if (!$kasir) {
    header('Location: ../../superadmin/kasir.php');
    exit;
}

// Tangani submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email'] ?? '');
    $fotoName = $kasir['foto'] ?? null;
    $errors = [];

    // Validasi username tidak boleh kosong
    if (!$username) {
        $errors[] = 'Username wajib diisi.';
    }

    // Cek duplikat username selain diri sendiri
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
    $stmt->bind_param("si", $username, $id);
    $stmt->execute();
    $stmt->bind_result($countUsername);
    $stmt->fetch();
    $stmt->close();

    if ($countUsername > 0) {
        $errors[] = "Username sudah digunakan oleh kasir lain.";
    }

    // Cek duplikat email selain diri sendiri (kalau email diisi)
    if ($email !== '') {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $id);
        $stmt->execute();
        $stmt->bind_result($countEmail);
        $stmt->fetch();
        $stmt->close();

        if ($countEmail > 0) {
            $errors[] = "Email sudah digunakan oleh kasir lain.";
        }
    }

    // Handle upload foto jika ada dan valid
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['foto']['tmp_name'];
        $originalName = basename($_FILES['foto']['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($extension, $allowed)) {
            $errors[] = "Format file foto tidak didukung.";
        } else {
            $newName = uniqid('foto_') . '.' . $extension;
            $uploadPath = __DIR__ . '/../../assets/img/poto_profile/' . $newName;
            if (move_uploaded_file($tmpName, $uploadPath)) {
                $fotoName = $newName;
            } else {
                $errors[] = "Gagal mengunggah foto.";
            }
        }
    }

    if (empty($errors)) {
        $result = updateKasirFotoEmailUsername($id, $username, $email, $fotoName);
        if ($result) {
            header('Location: ../../superadmin/kasir.php?update=success');
            exit;
        } else {
            $error = "Gagal memperbarui data kasir.";
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Kasir</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen font-sans">
    <div class="max-w-xl mx-auto mt-10 px-6">
        <h1 class="text-3xl font-bold mb-6 text-center">Edit Kasir</h1>

        <?php if (isset($error)): ?>
            <div class="bg-red-600 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="bg-gray-800 p-6 rounded shadow-md space-y-4">
            <div>
                <label for="username" class="block text-sm mb-1">Username</label>
                <input type="text" name="username" id="username" value="<?= htmlspecialchars($kasir['username']) ?>" required class="w-full px-4 py-2 rounded bg-gray-700 text-white">
            </div>

            <div>
                <label for="email" class="block text-sm mb-1">Email</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($kasir['email'] ?? '') ?>" class="w-full px-4 py-2 rounded bg-gray-700 text-white">
            </div>

            <div>
                <label for="foto" class="block text-sm mb-1">Foto (Opsional)</label>
                <input type="file" name="foto" id="foto" accept=".jpg,.jpeg,.png,.gif" class="w-full text-sm text-white">
                <?php if (!empty($kasir['foto'])): ?>
                    <div class="mt-2">
                        <img src="../../assets/img/poto_profile/<?= htmlspecialchars($kasir['foto']) ?>" alt="Foto Kasir"
                             class="h-20 rounded shadow border border-gray-600">
                    </div>
                <?php endif; ?>
            </div>

            <div class="flex justify-between items-center mt-6">
                <a href="../../superadmin/kasir.php" class="text-blue-400 hover:underline">← Kembali</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-6 py-2 rounded text-white font-semibold">Simpan</button>
            </div>
        </form>
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
