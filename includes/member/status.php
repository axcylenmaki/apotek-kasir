<?php
include __DIR__ . '/../../config/config.php';

// Ambil semua member
$members = $conn->query("SELECT id FROM member");

// Jangan tampilkan output langsung dari sini!
// Fungsi ini hanya untuk menampilkan status member
function getStatusMember($row) {
    $status = strtolower($row['status'] ?? 'unknown');

    switch ($status) {
        case 'aktif':
            return '<span class="text-green-400 font-semibold">Aktif</span>';
        case 'tidak aktif':
            return '<span class="text-red-500 font-semibold">Tidak Aktif</span>';
        default:
            return '<span class="text-gray-400 italic">Tidak Diketahui</span>';
    }
}

while ($member = $members->fetch_assoc()) {
    $id = $member['id'];

    // Ambil transaksi terakhir member ini
    $lastTrans = $conn->query("SELECT tanggal FROM transaksi WHERE id_member = $id ORDER BY tanggal DESC LIMIT 1");
    
    if ($lastTrans->num_rows > 0) {
        // Jika pernah transaksi, ambil tanggal transaksi terakhir
        $lastDate = new DateTime($lastTrans->fetch_assoc()['tanggal']);
        $now = new DateTime();

        // Hitung selisih hari antara hari ini dengan transaksi terakhir
        $diff = $now->diff($lastDate)->days;

        if ($diff > 14) {
            // <<< UBAH DI SINI
            // Jika tidak transaksi selama lebih dari 14 hari, hapus member otomatis
            $conn->query("DELETE FROM member WHERE id = $id");
        } elseif ($diff > 7) {
            // <<< UBAH DI SINI
            // Jika transaksi terakhir lebih dari 7 hari tapi kurang atau sama dengan 14 hari,
            // update status member jadi 'tidak aktif'
            $conn->query("UPDATE member SET status = 'tidak aktif' WHERE id = $id");
        } else {
            // <<< UBAH DI SINI
            // Jika transaksi terakhir kurang dari atau sama dengan 7 hari,
            // update status member jadi 'aktif'
            $conn->query("UPDATE member SET status = 'aktif' WHERE id = $id");
        }
    } else {
        // <<< UBAH DI SINI
        // Jika member tidak pernah melakukan transaksi,
        // maka status langsung di-set jadi 'tidak aktif'
        $conn->query("UPDATE member SET status = 'tidak aktif' WHERE id = $id");
    }
}

?>
