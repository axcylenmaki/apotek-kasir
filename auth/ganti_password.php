<?php
require '../config/config.php';

if (!isset($_GET['token'])) {
    die('Token tidak ditemukan.');
}

$token = $_GET['token'];

// Cek token dari DB
$stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Link reset tidak valid atau sudah kedaluwarsa.');
}

$data = $result->fetch_assoc();
if (strtotime($data['reset_expiry']) < time()) {
    die('Link reset sudah kedaluwarsa.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ganti Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold text-center text-blue-900 mb-6">Ganti Password</h2>
        <form action="proses_ganti_password.php" method="POST" class="space-y-4">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password Baru</label>
                <input type="password" name="password" id="password" required
                    class="mt-1 w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-900">
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required
                    class="mt-1 w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-900">
            </div>

            <button type="submit"
                class="w-full bg-blue-900 text-white py-2 rounded hover:bg-blue-950 transition">Simpan Password</button>
        </form>
    </div>
</body>
</html>
