<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: login.php'); exit(); }
 $page_title = 'Manajemen Pesanan'; require_once 'config/database.php';
 $stmt = $pdo->query("SELECT p.*, mp.nama_produk FROM pesanan p JOIN menu_produk mp ON p.id_produk = mp.id ORDER BY p.tanggal_pesanan DESC");
 $stmt_produk = $pdo->query("SELECT id, nama_produk FROM menu_produk ORDER BY nama_produk ASC");
include 'partials/header.php'; include 'partials/sidebar.php';
?>
<div class="content-wrapper">
    <div class="content-header"><div class="container-fluid"><h1 class="m-0"><?php echo $page_title; ?></h1></div></div>
    <section class="content"><div class="container-fluid">
        <div class="row"><div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Pesanan</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-block btn-success btn-sm" data-toggle="modal" data-target="#modalTambah"><i class="fas fa-plus"></i> Tambah Pesanan Baru</button>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead><tr><th>ID</th><th>Pelanggan</th><th>Produk</th><th>Jumlah</th><th>Total Harga</th><th>Status</th><th>Tanggal</th></tr></thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                <td><?php echo $row['jumlah']; ?></td>
                                <td>Rp <?php echo number_format($row['total_harga'], 2, ',', '.'); ?></td>
                                <td><span class="badge badge-info"><?php echo $row['status_pesanan']; ?></span></td>
                                <td><?php echo date('d M Y, H:i', strtotime($row['tanggal_pesanan'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div></div>
    </div></section>
</div>
<div class="modal fade" id="modalTambah">
    <div class="modal-dialog"><div class="modal-content">
        <form action="process/proses_pesanan_tambah.php" method="post">
            <div class="modal-header"><h4 class="modal-title">Tambah Pesanan Baru</h4></div>
            <div class="modal-body">
                <div class="form-group"><label>Nama Pelanggan</label><input type="text" name="nama_pelanggan" class="form-control" required></div>
                <div class="form-group">
                    <label>Produk</label>
                    <select name="id_produk" class="form-control" required>
                        <option value="">-- Pilih Produk --</option>
                        <?php while ($produk = $stmt_produk->fetch(PDO::FETCH_ASSOC)): ?>
                            <option value="<?php echo $produk['id']; ?>"><?php echo htmlspecialchars($produk['nama_produk']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group"><label>Jumlah</label><input type="number" name="jumlah" class="form-control" required></div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div>
        </form>
    </div></div>
</div>
<?php include 'partials/footer.php'; ?>