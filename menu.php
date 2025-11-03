<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}
 $page_title = 'Manajemen Menu';
require_once 'config/database.php';

if (isset($_GET['hapus_id'])) {
    $id = $_GET['hapus_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM menu_produk WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        header('Location: menu.php?status=hapus_sukses');
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

 $stmt = $pdo->query("SELECT * FROM menu_produk ORDER BY nama_produk ASC");

include 'partials/header.php';
include 'partials/sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0"><?php echo $page_title; ?></h1>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Daftar Menu</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-block btn-primary btn-sm" data-toggle="modal" data-target="#modalTambah">
                    <i class="fas fa-plus"></i> Tambah Menu Baru
                  </button>
                </div>
              </div>
              <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                  <thead><tr><th>ID</th><th>Nama Produk</th><th>Harga</th><th>Kategori</th><th>Aksi</th></tr></thead>
                  <tbody>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                      <td><?php echo $row['id']; ?></td>
                      <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                      <td>Rp <?php echo number_format($row['harga'], 2, ',', '.'); ?></td>
                      <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                      <td>
                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEdit" 
                                data-id="<?php echo $row['id']; ?>" data-nama="<?php echo $row['nama_produk']; ?>"
                                data-harga="<?php echo $row['harga']; ?>" data-kategori="<?php echo $row['kategori']; ?>"
                                data-deskripsi="<?php echo $row['deskripsi']; ?>">
                          <i class="fas fa-edit"></i> Edit
                        </button>
                        <a href="menu.php?hapus_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus menu ini?')">
                          <i class="fas fa-trash"></i> Hapus
                        </a>
                      </td>
                    </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah">
  <div class="modal-dialog"><div class="modal-content">
      <form action="process/proses_menu_tambah.php" method="post">
        <div class="modal-header"><h4 class="modal-title">Tambah Menu Baru</h4></div>
        <div class="modal-body">
          <div class="form-group"><label>Nama Produk</label><input type="text" name="nama_produk" class="form-control" required></div>
          <div class="form-group"><label>Harga</label><input type="number" name="harga" class="form-control" required></div>
          <div class="form-group"><label>Kategori</label><input type="text" name="kategori" class="form-control" required></div>
          <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" class="form-control"></textarea></div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div>
      </form>
  </div></div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit">
  <div class="modal-dialog"><div class="modal-content">
      <form action="process/proses_menu_edit.php" method="post"><input type="hidden" name="id" id="edit_id">
        <div class="modal-header"><h4 class="modal-title">Edit Menu</h4></div>
        <div class="modal-body">
          <div class="form-group"><label>Nama Produk</label><input type="text" name="nama_produk" id="edit_nama" class="form-control" required></div>
          <div class="form-group"><label>Harga</label><input type="number" name="harga" id="edit_harga" class="form-control" required></div>
          <div class="form-group"><label>Kategori</label><input type="text" name="kategori" id="edit_kategori" class="form-control" required></div>
          <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" id="edit_deskripsi" class="form-control"></textarea></div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan Perubahan</button></div>
      </form>
  </div></div>
</div>

<?php include 'partials/footer.php'; ?>
<script>
  $('#modalEdit').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); var modal = $(this);
    modal.find('#edit_id').val(button.data('id')); modal.find('#edit_nama').val(button.data('nama'));
    modal.find('#edit_harga').val(button.data('harga')); modal.find('#edit_kategori').val(button.data('kategori'));
    modal.find('#edit_deskripsi').val(button.data('deskripsi'));
  });
</script>