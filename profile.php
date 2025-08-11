<?php
session_start();
include 'config/config.php'; // Sesuaikan path-nya jika beda

// Cek login
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['superadmin', 'kasir'])) {
    header("Location: /apotek-kasir/auth/login.php");
    exit;
}


$user_id = $_SESSION['user_id'];

// Ambil data user dari DB
$query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    echo "User tidak ditemukan.";
    exit();
}

$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-900 text-white">
    <?php include 'includes/navbar.php'; ?> <!-- Sidebar tetap tampil -->

    <div class="p-4 transition-all duration-300 ml-0 lg:ml-64" id="mainContent">
        <div class="max-w-3xl mx-auto mt-8 bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="flex items-center space-x-6 mb-6">
                <div class="w-24 h-24 rounded-full bg-gray-600 overflow-hidden border-2 border-gray-500">
                    <?php if (!empty($user['foto'])): ?>
<img src="assets/img/poto_profile/<?php echo htmlspecialchars($user['foto']); ?>" alt="Foto Profil" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="flex items-center justify-center h-full text-gray-300">No Photo</div>
                    <?php endif; ?>
                </div>
                <div>
                    <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($user['nama']); ?></h2>
                    <p class="text-gray-400 capitalize"><?php echo htmlspecialchars($user['role']); ?></p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="text-gray-400">Username</label>
                    <p class="font-medium"><?php echo htmlspecialchars($user['username']); ?></p>
                </div>
                <div>
                    <label class="text-gray-400">Email</label>
                    <p class="font-medium"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <div>
                    <label class="text-gray-400">Role</label>
                    <p class="font-medium capitalize"><?php echo htmlspecialchars($user['role']); ?></p>
                </div>
            </div>

            <div class="mt-6">
<a href="/apotek-kasir/includes/kasir/edit.php?id=<?= $user['id'] ?>" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
    Edit Profile
</a>
            </div>
        </div>
    </div>

    <!-- JS toggle sidebar (kalau pakai toggle) -->
    <script>
        const toggleBtn = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                mainContent.classList.toggle('ml-64');
            });
        }
    </script>
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
