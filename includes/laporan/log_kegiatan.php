<?php
require '../../config/config.php';
require '../header.php';
require '../navbar.php';

// Query ambil log kegiatan dan join ke nama kasir
$query = $conn->query("
    SELECT l.*, u.nama AS nama_kasir 
    FROM log_kegiatan l
    LEFT JOIN users u ON l.id_user = u.id
    ORDER BY l.waktu DESC
");
?>

<div class="container mt-5">
    <h3 class="mb-4 fw-bold">Log Aktivitas Kasir</h3>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Nama Kasir</th>
                    <th scope="col">Aksi</th>
                    <th scope="col">Waktu</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                if ($query->num_rows > 0):
                    while($row = $query->fetch_assoc()):
                ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nama_kasir'] ?? 'Tidak diketahui') ?></td>
                        <td><?= htmlspecialchars($row['aksi']) ?></td>
                        <td><?= date('d M Y H:i:s', strtotime($row['waktu'])) ?></td>
                    </tr>
                <?php 
                    endwhile;
                else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">Tidak ada aktivitas tercatat.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Script Toggle Sidebar -->
<script>
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const toggleBtn = document.getElementById('sidebarToggle');

    toggleBtn?.addEventListener('click', () => {
        sidebar?.classList.toggle('-translate-x-full');
        mainContent?.classList.toggle('md:ml-64');
    });
</script>

<?php require '../footer.php'; ?>
