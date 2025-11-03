<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: login.php'); exit(); }
 $page_title = 'Pesan Masuk'; require_once 'config/database.php';
 $stmt = $pdo->query("SELECT * FROM pesan_kontak ORDER BY tanggal_dikirim DESC");
include 'partials/header.php'; include 'partials/sidebar.php';
?>
<div class="content-wrapper">
    <div class="content-header"><div class="container-fluid"><h1 class="m-0"><?php echo $page_title; ?></h1></div></div>
    <section class="content"><div class="container-fluid">
        <div class="row"><div class="col-12">
            <div class="card"><div class="card-header"><h3 class="card-title">Daftar Pesan dari Kontak</h3></div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead><tr><th>Tanggal</th><th>Nama</th><th>Email</th><th>Subjek</th><th>Pesan</th></tr></thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($row['tanggal_dikirim'])); ?></td>
                                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['subjek']); ?></td>
                                <td><?php echo htmlspecialchars(substr($row['pesan'], 0, 50)) . '...'; ?></td>
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