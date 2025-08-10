function logKegiatan($conn, $id_user, $aksi) {
    $stmt = $conn->prepare("INSERT INTO log_kegiatan (id_user, aksi) VALUES (?, ?)");
    $stmt->bind_param("is", $id_user, $aksi);
    $stmt->execute();
}
