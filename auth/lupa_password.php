<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../config/config.php';
require '../vendor/autoload.php';
session_start();

function generateToken() {
    return bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $check = $conn->query("SELECT * FROM users WHERE email = '$email'");

    if ($check->num_rows > 0) {
        $user = $check->fetch_assoc();
        $token = generateToken();
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Buat tabel reset_password jika belum ada
        $conn->query("CREATE TABLE IF NOT EXISTS reset_password (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) NOT NULL,
            token VARCHAR(100) NOT NULL,
            expiry DATETIME NOT NULL
        )");

        // Hapus token lama jika ada
        $conn->query("DELETE FROM reset_password WHERE email = '$email'");

        // Simpan token baru
// Simpan token langsung ke tabel users
$update = $conn->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE email = ?");
$update->bind_param("sss", $token, $expiry, $email);
$update->execute();

        // Kirim email menggunakan PHPMailer
        $resetLink = "http://localhost/apotek-kasir/auth/ganti_password.php?token=$token";

        $mail = new PHPMailer(true);

        try {
            // Konfigurasi SMTP (ubah sesuai pengaturan SMTP kamu)
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';         // contoh pakai Gmail
            $mail->SMTPAuth = true;
            $mail->Username = 'ayusyafira3003@gmail.com'; // ganti dengan email pengirim
            $mail->Password = 'bzcg oxhl ocip dqzl';   // gunakan app password jika Gmail
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('youremail@gmail.com', 'Admin Apotek');
            $mail->addAddress($email, $user['username']);  // kirim ke email user

            $mail->isHTML(true);
            $mail->Subject = 'Reset Password Anda';
            $mail->Body = "
                <p>Halo <strong>{$user['username']}</strong>,</p>
                <p>Kami menerima permintaan untuk reset password Anda.</p>
                <p>Silakan klik link berikut untuk mengatur ulang password Anda:</p>
                <p><a href='$resetLink'>$resetLink</a></p>
                <p>Link ini hanya berlaku selama 1 jam.</p>
                <br><p>Hormat kami,<br>Admin Apotek</p>
            ";

            $mail->send();
            $message = "Link reset password telah dikirim ke email Anda.";
        } catch (Exception $e) {
            $error = "Gagal mengirim email. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "Email tidak ditemukan dalam sistem.";
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-6 rounded shadow w-full max-w-md">
        <h1 class="text-2xl font-bold mb-4 text-center text-blue-800">Reset Password</h1>

        <?php if (isset($message)): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4"> <?= $message ?> </div>
        <?php elseif (isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4"> <?= $error ?> </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium">Email</label>
                <input type="email" name="email" id="email" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-500">
            </div>
            <button type="submit" class="w-full bg-blue-800 text-white py-2 rounded hover:bg-blue-900">Kirim Link Reset</button>
        </form>

        <p class="text-sm text-center mt-4">
            <a href="login.php" class="text-blue-700 hover:underline">Kembali ke login</a>
        </p>
    </div>
</body>
</html>