<?php
include '../../config/config.php';

// Ambil semua member
$members = $conn->query("SELECT id FROM member");

while ($member = $members->fetch_assoc()) {
    $id = $member['id'];

    // Ambil transaksi terakhir member ini
    $lastTrans = $conn->query("SELECT tanggal FROM transaksi WHERE id_member = $id ORDER BY tanggal DESC LIMIT 1");
    
    if ($lastTrans->num_rows > 0) {
        $lastDate = new DateTime($lastTrans->fetch_assoc()['tanggal']);
        $now = new DateTime();

        $diff = $now->diff($lastDate)->days;

        if ($diff > 14) {
            // Delete member jika tidak aktif lebih dari 14 hari
            $conn->query("DELETE FROM member WHERE id = $id");
        } elseif ($diff > 7) {
            // Update status jadi tidak aktif
            $conn->query("UPDATE member SET status = 'tidak aktif' WHERE id = $id");
        } else {
            // Masih aktif
            $conn->query("UPDATE member SET status = 'aktif' WHERE id = $id");
        }
    } else {
        // Tidak pernah transaksi, cek sejak kapan member dibuat (opsional)
        $conn->query("UPDATE member SET status = 'tidak aktif' WHERE id = $id");
    }
}

echo "Status member berhasil diperbarui.";
?>
