<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../config/config.php';

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal koneksi database']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['status' => 'error', 'message' => 'Metode request salah']);
    exit;
}

if (!isset($_GET['telp']) || empty(trim($_GET['telp']))) {
    echo json_encode(['status' => 'error', 'message' => 'Nomor telepon harus diisi']);
    exit;
}

$telp = $conn->real_escape_string(trim($_GET['telp']));

$query = "SELECT id, nama, poin FROM member WHERE no_hp = '$telp' LIMIT 1";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $member = $result->fetch_assoc();
echo json_encode([
    'status' => 'found',
    'id' => $member['id'],
    'nama' => $member['nama'],
    'poin' => $member['poin']
]);
} else {
    echo json_encode(['status' => 'not_found', 'message' => 'Member tidak ditemukan']);
}
