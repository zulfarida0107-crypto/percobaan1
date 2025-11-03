<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: login.php'); exit(); }
 $page_title = 'Desain Pesanan'; require_once 'config/database.php';
 $stmt = $pdo->query("SELECT dp.*, p.nama_pelanggan FROM desain_pesanan dp JOIN pesanan p ON dp.id_pesanan = p.id ORDER BY dp.tanggal_upload DESC");
include 'partials/header.php'; include 'partials/sidebar.php';
?>
<div class="content-wrapper">
    <div class="content-header"><div class="container-fluid"><h1 class="m-0"><?php echo $page_title; ?></h1></div></div>
    <section class="content"><div class="container-fluid">
        <div class="row"><div class="col-12">
            <div class="card"><div class="card-header"><h3 class="card-title">Daftar Desain Pesanan</h3></div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead><tr><th>ID</th><th>ID Pesanan</th><th>Pelanggan</th><th>File Desain</th><th>Keterangan</th><th>Tanggal Upload</th></tr></thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['id_pesanan']; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                                <td><?php echo ($row['file_desain_url']) ? "<a href='{$row['file_desain_url']}' target='_blank'>Lihat File</a>" : '-'; ?></td>
                                <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                                <td><?php echo date('d M Y, H:i', strtotime($row['tanggal_upload'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div></div>
    </div></section>
</div>
<?php include 'partials/footer.php'; ?>