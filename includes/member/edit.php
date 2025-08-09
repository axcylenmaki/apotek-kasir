<?php
include '../../config/config.php';
include 'functions.php';

include '../../config/config.php';
include '../header.php';
include '../navbar.php';
require_once 'functions.php';

$id = $_GET['id'];
$member = getMemberById($conn, $id);

if (!$member) {
    die("Member tidak ditemukan.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = htmlspecialchars($_POST['nama']);
    $no_hp = htmlspecialchars($_POST['no_hp']);

    // Validasi: Nomor HP tidak boleh digunakan member lain
    if (isPhoneExist($conn, $no_hp, $id)) {
        $error = "Nomor HP sudah digunakan oleh member lain.";
    } else {
        if (updateMember($conn, $id, $nama, $no_hp)) {
            header("Location: ../../superadmin/member.php?updated=1");
            exit;
        } else {
            $error = "Gagal mengubah data member.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Member</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gradient-to-br from-blue-900 via-gray-900 to-black text-white min-h-screen flex items-center justify-center p-4">

    <div class="bg-white text-black p-6 rounded shadow-lg w-full max-w-lg">
        <h1 class="text-2xl font-bold mb-4 text-center text-blue-800">Edit Member</h1>

        <?php if (isset($error)): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '<?= $error ?>'
            });
        </script>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nama</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($member['nama']) ?>" required class="w-full px-3 py-2 border rounded">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Nomor HP</label>
                <input type="text" name="no_hp" value="<?= htmlspecialchars($member['no_hp']) ?>" required class="w-full px-3 py-2 border rounded">
            </div>
            <div class="flex justify-between">
                <a href="../../superadmin/member.php" class="text-sm text-blue-700 hover:underline">← Kembali</a>
                <button type="submit" class="bg-blue-800 hover:bg-blue-900 text-white px-4 py-2 rounded">Update</button>
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
