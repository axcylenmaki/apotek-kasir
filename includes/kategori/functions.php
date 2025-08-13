<?php
// Tidak perlu include config.php lagi karena sudah diinclude di file utama

function getAllKategori($conn) {
    return $conn->query("SELECT * FROM kategori ORDER BY id DESC");
}

function getKategoriById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM kategori WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function createKategori($conn, $nama_kategori, $keterangan, $gambar = null, $created_by = null) {
    $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori, keterangan, gambar, jumlah_produk, created_by) VALUES (?, ?, ?, 0, ?)");
    $stmt->bind_param("sssi", $nama_kategori, $keterangan, $gambar, $created_by);
    return $stmt->execute();
}

function updateKategori($conn, $id, $nama_kategori, $keterangan, $gambar = null, $jumlah_produk = 0, $updated_by = null) {
    $stmt = $conn->prepare("UPDATE kategori 
        SET nama_kategori = ?, keterangan = ?, gambar = ?, jumlah_produk = ?, updated_by = ? 
        WHERE id = ?");
    $stmt->bind_param("sssiii", $nama_kategori, $keterangan, $gambar, $jumlah_produk, $updated_by, $id);
    return $stmt->execute();
}

function deleteKategori($conn, $id) {
    // Cek apakah kategori sedang digunakan oleh produk
    $stmt = $conn->prepare("SELECT COUNT(*) FROM produk WHERE id_kategori = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($jumlah);
    $stmt->fetch();
    $stmt->close();

    if ($jumlah > 0) {
        // Tidak boleh hapus, karena masih dipakai
        throw new Exception("Kategori tidak dapat dihapus karena masih digunakan oleh $jumlah produk.");
    }

    // Lanjut hapus kategori
    $stmt = $conn->prepare("DELETE FROM kategori WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}


function updateJumlahProdukPerKategori($conn) {
    $kategoriResult = $conn->query("SELECT id FROM kategori");

    while ($kategori = $kategoriResult->fetch_assoc()) {
        $kategoriId = $kategori['id'];

        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM produk WHERE id_kategori = ?");
        $stmt->bind_param("i", $kategoriId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $jumlah = $result['total'];

        $update = $conn->prepare("UPDATE kategori SET jumlah_produk = ? WHERE id = ?");
        $update->bind_param("ii", $jumlah, $kategoriId);
        $update->execute();
    }
}

function hapusGambarKategori($namaFile) {
    $lokasi = "../../assets/img/kategori/" . $namaFile;
    if (file_exists($lokasi) && is_file($lokasi)) {
        unlink($lokasi);
    }
}
