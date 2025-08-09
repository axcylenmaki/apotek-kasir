<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/config.php';
require_once '../../vendor/autoload.php'; // pastikan composer autoload tersedia

use Picqer\Barcode\BarcodeGeneratorPNG;

// Ambil semua produk
function getAllProduk() {
    global $conn;
    $query = "SELECT produk.*, kategori.nama_kategori FROM produk 
              LEFT JOIN kategori ON produk.id_kategori = kategori.id";
    $result = mysqli_query($conn, $query);

    $produk = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Buat barcode manual jika belum ada (tampilan)
        if (empty($row['barcode'])) {
            $row['barcode'] = str_pad($row['id'], 13, '0', STR_PAD_LEFT);
        }

        $produk[] = $row;
    }

    return $produk;
}

// Ambil semua kategori
function getAllKategori() {
    global $conn;
    $query = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
    $result = mysqli_query($conn, $query);

    $kategori = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $kategori[] = $row;
    }

    return $kategori;
}

// Tambah produk
function tambahProduk($data, $file) {
    global $conn;

    $nama_produk = mysqli_real_escape_string($conn, $data['nama_produk']);
    $id_kategori = intval($data['id_kategori']);
    $stok = intval($data['stok']);
    $harga_beli = intval($data['harga_beli']);
    $harga_jual = intval($data['harga_jual']);
    $expired_date = mysqli_real_escape_string($conn, $data['expired_date']);
    $barcode = !empty($data['barcode']) ? mysqli_real_escape_string($conn, $data['barcode']) : null;
    $izin_edar = mysqli_real_escape_string($conn, $data['izin_edar']);
    $deskripsi = mysqli_real_escape_string($conn, $data['deskripsi']);

    $created_by = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

    // Validasi duplikat nama produk
    $cekNama = mysqli_query($conn, "SELECT id FROM produk WHERE nama_produk = '$nama_produk'");
    if (mysqli_num_rows($cekNama) > 0) {
        echo "<script>alert('Nama produk sudah digunakan, silakan gunakan nama lain.');</script>";
        return false;
    }

    // Validasi duplikat barcode jika barcode diinput
    if (!empty($barcode)) {
        $cekBarcode = mysqli_query($conn, "SELECT id FROM produk WHERE barcode = '$barcode'");
        if (mysqli_num_rows($cekBarcode) > 0) {
            echo "<script>alert('Nomor barcode sudah digunakan, silakan gunakan nomor lain.');</script>";
            return false;
        }
    }

    // Upload gambar produk
    $gambar = '';
    if (!empty($file['gambar']['name'])) {
        $uploadDir = '../../assets/img/produk/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $gambar = uniqid() . '_' . basename($file['gambar']['name']);
        $targetPath = $uploadDir . $gambar;
        move_uploaded_file($file['gambar']['tmp_name'], $targetPath);
    }

    // Insert produk
    $query = "INSERT INTO produk (
        nama_produk, id_kategori, stok, harga_beli, harga_jual, expired_date, gambar, barcode, izin_edar, deskripsi, created_by
    ) VALUES (
        '$nama_produk', $id_kategori, $stok, $harga_beli, $harga_jual, '$expired_date', '$gambar', '$barcode', '$izin_edar', '$deskripsi', $created_by
    )";
    mysqli_query($conn, $query);

    $last_id = mysqli_insert_id($conn);

    // Buat barcode otomatis jika belum ada
    if (empty($barcode)) {
        $barcode = str_pad($last_id, 13, '0', STR_PAD_LEFT);
        mysqli_query($conn, "UPDATE produk SET barcode = '$barcode' WHERE id = $last_id");
    }

    // Buat gambar barcode
    generateBarcodeImage($barcode, $last_id);

    return true;
}

// Hapus produk
function hapusProduk($id) {
    global $conn;
    $id = intval($id);

    // Hapus gambar produk
    $queryGambar = "SELECT gambar FROM produk WHERE id = $id";
    $result = mysqli_query($conn, $queryGambar);
    $row = mysqli_fetch_assoc($result);

    if (!empty($row['gambar']) && file_exists('../../assets/img/produk/' . $row['gambar'])) {
        unlink('../../assets/img/produk/' . $row['gambar']);
    }

    // Hapus gambar barcode
    $barcodeImage = '../../assets/img/barcode/' . $id . '.png';
    if (file_exists($barcodeImage)) {
        unlink($barcodeImage);
    }

    // Hapus dari database
    $query = "DELETE FROM produk WHERE id = $id";
    return mysqli_query($conn, $query);
}

// Buat gambar barcode dari kode
function generateBarcodeImage($barcode, $idProduk) {
    $path = '../../assets/img/barcode/';
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }

    $generator = new BarcodeGeneratorPNG();
    $barcodeImage = $generator->getBarcode($barcode, $generator::TYPE_CODE_128);

    file_put_contents($path . $idProduk . '.png', $barcodeImage);
}

// Edit produk
function editProduk($id, $data, $file) {
    global $conn;

    $id = intval($id);
    $nama_produk = mysqli_real_escape_string($conn, $data['nama_produk']);
    $id_kategori = intval($data['id_kategori']);
    $stok = intval($data['stok']);
    $harga_beli = intval($data['harga_beli']);
    $harga_jual = intval($data['harga_jual']);
    $expired_date = mysqli_real_escape_string($conn, $data['expired_date']);
    $barcode = !empty($data['barcode']) ? mysqli_real_escape_string($conn, $data['barcode']) : null;
    $izin_edar = mysqli_real_escape_string($conn, $data['izin_edar']);
    $deskripsi = mysqli_real_escape_string($conn, $data['deskripsi']);

    $updated_by = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

    // Validasi nama (kecuali dirinya sendiri)
    $cekNama = mysqli_query($conn, "SELECT id FROM produk WHERE nama_produk = '$nama_produk' AND id != $id");
    if (mysqli_num_rows($cekNama) > 0) {
        echo "<script>alert('Nama produk sudah digunakan.');</script>";
        return false;
    }

    // Validasi barcode (kecuali dirinya sendiri)
    if (!empty($barcode)) {
        $cekBarcode = mysqli_query($conn, "SELECT id FROM produk WHERE barcode = '$barcode' AND id != $id");
        if (mysqli_num_rows($cekBarcode) > 0) {
            echo "<script>alert('Barcode sudah digunakan.');</script>";
            return false;
        }
    }

    // Handle gambar jika diubah
    $gambarBaru = '';
    if (!empty($file['gambar']['name'])) {
        $uploadDir = '../../assets/img/produk/';
        $gambarBaru = uniqid() . '_' . basename($file['gambar']['name']);
        $targetPath = $uploadDir . $gambarBaru;
        move_uploaded_file($file['gambar']['tmp_name'], $targetPath);

        // Hapus gambar lama
        $result = mysqli_query($conn, "SELECT gambar FROM produk WHERE id = $id");
        $row = mysqli_fetch_assoc($result);
        if (!empty($row['gambar']) && file_exists($uploadDir . $row['gambar'])) {
            unlink($uploadDir . $row['gambar']);
        }
    }

    $query = "UPDATE produk SET 
                nama_produk = '$nama_produk',
                id_kategori = $id_kategori,
                stok = $stok,
                harga_beli = $harga_beli,
                harga_jual = $harga_jual,
                expired_date = '$expired_date',
                barcode = '$barcode',
                izin_edar = '$izin_edar',
                deskripsi = '$deskripsi',
                updated_by = $updated_by";

    if (!empty($gambarBaru)) {
        $query .= ", gambar = '$gambarBaru'";
    }

    $query .= " WHERE id = $id";

    mysqli_query($conn, $query);

    // Perbarui barcode image kalau barcode berubah
    generateBarcodeImage($barcode, $id);

    return true;
}
