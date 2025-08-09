<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';

/**
 * Ambil semua kasir (role kasir + superadmin)
 */
function getAllKasir() {
    global $conn;
    $sql = "SELECT id, nama, username, email, role, foto FROM users ORDER BY id DESC";
    $result = mysqli_query($conn, $sql);

    $list = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $list[] = $row;
        }
    }
    return $list;
}

/**
 * Ambil data kasir berdasarkan ID
 */
function getKasirById($id) {
    global $conn;
    $id = intval($id);
    $sql = "SELECT id, nama, username, email, role, foto FROM users WHERE id = $id LIMIT 1";
    $result = mysqli_query($conn, $sql);
    return $result ? mysqli_fetch_assoc($result) : null;
}

/**
 * Hapus kasir berdasarkan ID (tidak boleh hapus superadmin)
 */
function hapusKasir($id) {
    global $conn;
    $id = intval($id);

    $sql = "SELECT role FROM users WHERE id = $id LIMIT 1";
    $res = mysqli_query($conn, $sql);
    if (!$res) return false;

    $user = mysqli_fetch_assoc($res);
    if (!$user || $user['role'] === 'superadmin') {
        return false; // Tidak boleh hapus superadmin
    }

    return mysqli_query($conn, "DELETE FROM users WHERE id = $id");
}

/**
 * Update data lengkap kasir (nama, username, email, password opsional)
 */
function updateKasir($id, $nama, $username, $email = null, $password = null) {
    global $conn;
    $id = intval($id);
    $nama = mysqli_real_escape_string($conn, $nama);
    $username = mysqli_real_escape_string($conn, $username);
    $email = !empty($email) ? mysqli_real_escape_string($conn, $email) : null;

    // Cek username unik selain diri sendiri
    $checkSql = "SELECT id FROM users WHERE username = '$username' AND id != $id LIMIT 1";
    $checkRes = mysqli_query($conn, $checkSql);
    if (mysqli_num_rows($checkRes) > 0) return false;

    $emailValue = $email ? "'$email'" : "NULL";

    if ($password) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users 
                SET nama = '$nama', username = '$username', email = $emailValue, password = '$passwordHash'
                WHERE id = $id";
    } else {
        $sql = "UPDATE users 
                SET nama = '$nama', username = '$username', email = $emailValue
                WHERE id = $id";
    }

    return mysqli_query($conn, $sql);
}

/**
 * Update hanya username dan email kasir
 */
function updateKasirEmailUsername($id, $username, $email = null) {
    global $conn;
    $id = intval($id);
    $username = mysqli_real_escape_string($conn, $username);
    $emailEscaped = $email ? mysqli_real_escape_string($conn, $email) : null;

    // Cek apakah username sudah digunakan user lain
    $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username' AND id != $id LIMIT 1");
    if (mysqli_num_rows($check) > 0) return false;

    $sql = "UPDATE users SET username = '$username'";
    $sql .= $emailEscaped !== null ? ", email = '$emailEscaped'" : ", email = NULL";
    $sql .= " WHERE id = $id";

    return mysqli_query($conn, $sql);
}

/**
 * Update username, email, dan foto kasir
 */
function updateKasirFotoEmailUsername($id, $username, $email, $foto = null) {
    global $conn;
    $id = intval($id);
    $username = mysqli_real_escape_string($conn, $username);
    $email = mysqli_real_escape_string($conn, $email ?? '');
    $fotoSet = $foto ? ", foto = '" . mysqli_real_escape_string($conn, $foto) . "'" : '';

    // Cek username unik selain dirinya sendiri
    $cek = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username' AND id != $id");
    if (mysqli_num_rows($cek) > 0) return false;

    $query = "UPDATE users SET username = '$username', email = '$email' $fotoSet WHERE id = $id";
    return mysqli_query($conn, $query);
}

/**
 * Tambah kasir baru
 */
function tambahKasir($nama, $username, $password, $email = null, $foto = null) {
    global $conn;

    $nama = mysqli_real_escape_string($conn, $nama);
    $username = mysqli_real_escape_string($conn, $username);
    $email = mysqli_real_escape_string($conn, $email ?? '');
    $foto = mysqli_real_escape_string($conn, $foto ?? '');

    // Cek username unik
    $sql = "SELECT id FROM users WHERE username = '$username' LIMIT 1";
    $res = mysqli_query($conn, $sql);
    if (mysqli_num_rows($res) > 0) return false;

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $role = 'kasir';

    $sql = "INSERT INTO users (nama, username, password, role, email, foto)
            VALUES ('$nama', '$username', '$passwordHash', '$role', '$email', '$foto')";

    return mysqli_query($conn, $sql);
}
