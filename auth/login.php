<?php
session_start();
include '../config/config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Verifikasi password
        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["nama"] = $user["nama"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["foto"] = $user["foto"];

            if ($user["role"] == "superadmin") {
                header("Location: ../superadmin/dashboard.php");
            } else {
                header("Location: ../kasir/dashboard.php");
            }
            exit;
        } else {
            $error = "Password salah. Silakan coba lagi.";
        }
    } else {
        $error = "Email tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Apotek</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0d1b2a] flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-2xl rounded-xl w-full max-w-md p-8">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-[#0d1b2a]">Selamat Datang</h2>
            <p class="text-sm text-gray-500">Silakan login ke sistem kasir apotek</p>
        </div>

        <?php if (!empty($error)) : ?>
            <div class="mb-4 text-sm text-red-600 bg-red-100 p-2 rounded">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm text-gray-700 mb-1">Email</label>
                <input type="email" name="email" required class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-800" />
            </div>
            <div>
                <label class="block text-sm text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-800" />
            </div>
            <div class="flex justify-between items-center text-sm">
                <a href="lupa_password.php" class="text-blue-800 hover:underline">Lupa Password?</a>
            </div>
            <button type="submit" class="w-full bg-[#0d1b2a] text-white p-2 rounded hover:bg-[#1e2e4a] transition">Login</button>
        </form>
    </div>

   

</body>
</html>
