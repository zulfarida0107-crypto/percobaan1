<?php
session_start();
require_once 'config/database.php';
// --- LOGIKA PROSES (AJAX) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    
    // LOGIKA LOGIN AJAX (Harus berjalan meskipun belum ada sesi)
    if ($_POST['action'] == 'login') {
        // Logika Login ditambahkan
        $username = $_POST['username'];
        $password = md5($_POST['password']);
        
        try {
            $query = "SELECT * FROM user WHERE username = :username AND password = :password";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $_SESSION['user'] = $user;
                echo json_encode(['status' => 'success', 'message' => 'Login berhasil!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Username atau password salah!']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => "Error: " . $e->getMessage()]);
        }
        exit(); // Penting: Keluar setelah memproses AJAX Login
    }

    // Pengecekan sesi untuk semua aksi AJAX selain login
    if (!isset($_SESSION['user'])) { 
        echo json_encode(['status' => 'error', 'message' => 'Sesi berakhir.']); 
        exit(); 
    }

    $user_role = $_SESSION['user']['role'];

    // TAMBAH MENU (Admin Only)
    if ($_POST['action'] == 'add_menu' && $user_role == 'admin') {
        $gambar = '';
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['gambar']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $newname = uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['gambar']['tmp_name'], 'uploads/menu/' . $newname);
                $gambar = $newname;
            }
        }
        $stmt = $pdo->prepare("INSERT INTO menu_produk (nama_produk, harga, deskripsi, kategori, gambar) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['nama_produk'], $_POST['harga'], $_POST['deskripsi'], $_POST['kategori'], $gambar]);
        echo json_encode(['status' => 'success', 'message' => 'Menu berhasil ditambahkan!', 'data' => ['id' => $pdo->lastInsertId(), 'nama' => $_POST['nama_produk'], 'harga' => $_POST['harga'], 'kategori' => $_POST['kategori'], 'deskripsi' => $_POST['deskripsi'], 'gambar' => $gambar]]);
        exit();
    }

    // EDIT MENU (Admin Only)
    if ($_POST['action'] == 'edit_menu' && $user_role == 'admin') {
        $gambar = $_POST['gambar_lama']; // Keep old image by default
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['gambar']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                // Delete old image if exists
                if (!empty($_POST['gambar_lama']) && file_exists('uploads/menu/' . $_POST['gambar_lama'])) {
                    unlink('uploads/menu/' . $_POST['gambar_lama']);
                }
                $newname = uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['gambar']['tmp_name'], 'uploads/menu/' . $newname);
                $gambar = $newname;
            }
        }
        $stmt = $pdo->prepare("UPDATE menu_produk SET nama_produk=?, harga=?, deskripsi=?, kategori=?, gambar=? WHERE id=?");
        $stmt->execute([$_POST['nama_produk'], $_POST['harga'], $_POST['deskripsi'], $_POST['kategori'], $gambar, $_POST['id']]);
        echo json_encode(['status' => 'success', 'message' => 'Menu berhasil diperbarui!']);
        exit();
    }
    
    // HAPUS MENU (Admin Only)
    if ($_POST['action'] == 'delete_menu' && $user_role == 'admin') {
        // Get image name before deleting
        $stmt_img = $pdo->prepare("SELECT gambar FROM menu_produk WHERE id = ?");
        $stmt_img->execute([$_POST['id']]);
        $gambar = $stmt_img->fetchColumn();
        
        $stmt = $pdo->prepare("DELETE FROM menu_produk WHERE id = ?");
        $stmt->execute([$_POST['id']]);

        // Delete image file
        if (!empty($gambar) && file_exists('uploads/menu/' . $gambar)) {
            unlink('uploads/menu/' . $gambar);
        }

        echo json_encode(['status' => 'success', 'message' => 'Menu berhasil dihapus!']);
        exit();
    }

    // TAMBAH PESANAN (Admin & Karyawan)
    if ($_POST['action'] == 'add_order' && in_array($user_role, ['admin', 'karyawan'])) {
        $stmt_harga = $pdo->prepare("SELECT harga FROM menu_produk WHERE id = ?");
        $stmt_harga->execute([$_POST['id_produk']]);
        $harga = $stmt_harga->fetchColumn();
        $total_harga = $harga * $_POST['jumlah'];
        
        $stmt = $pdo->prepare("INSERT INTO pesanan (nama_pelanggan, id_produk, jumlah, total_harga) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['nama_pelanggan'], $_POST['id_produk'], $_POST['jumlah'], $total_harga]);
        echo json_encode(['status' => 'success', 'message' => 'Pesanan berhasil ditambahkan!']);
        exit();
    }

    // TAMBAH USER (Admin Only)
    if ($_POST['action'] == 'add_user' && $user_role == 'admin') {
        $stmt = $pdo->prepare("INSERT INTO user (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['username'], md5($_POST['password']), $_POST['nama_lengkap'], $_POST['role']]);
        echo json_encode(['status' => 'success', 'message' => 'User berhasil ditambahkan!']);
        exit();
    }
}

// LOGOUT
if (isset($_GET['page']) && $_GET['page'] == 'logout') {
    session_destroy();
    header('Location: login.php');
    exit();
}

// --- CEK LOGIN & TENTUKAN HALAMAN ---
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user_role = $_SESSION['user']['role'];
$page = $_GET['page'] ?? 'dashboard';

if ($page == 'manage_menu' && $user_role == 'admin') {
    $page_content = 'manage_menu_view';
} elseif ($page == 'manage_users' && $user_role == 'admin') {
    $page_content = 'manage_users_view';
} elseif ($page == 'data_pesanan') { // BARU: Logika untuk Data Pesanan terpisah
    $page_content = 'data_pesanan_view';
} elseif ($page == 'pesan_kontak') {
    $page_content = 'pesan_kontak_view';
} elseif ($page == 'desain_pesanan') {
    $page_content = 'desain_pesanan_view';
} else {
    $page_content = 'dashboard_view';
}

// --- AMBIL DATA DARI DATABASE ---
// Diperlukan $pdo = $GLOBALS['pdo'] di dalam functions untuk memastikan ketersediaan koneksi
$stmt_total_menu = $pdo->query("SELECT COUNT(*) as total FROM menu_produk");
$total_menu = $stmt_total_menu->fetch(PDO::FETCH_ASSOC)['total'];
$stmt_total_pesanan = $pdo->query("SELECT COUNT(*) as total FROM pesanan");
$total_pesanan = $stmt_total_pesanan->fetch(PDO::FETCH_ASSOC)['total'];
$stmt_total_user = $pdo->query("SELECT COUNT(*) as total FROM user");
$total_user = $stmt_total_user->fetch(PDO::FETCH_ASSOC)['total'];

$stmt_menu = $pdo->query("SELECT * FROM menu_produk ORDER BY kategori, nama_produk");
$stmt_pesanan = $pdo->query("SELECT p.*, mp.nama_produk FROM pesanan p JOIN menu_produk mp ON p.id_produk = mp.id ORDER BY p.tanggal_pesanan DESC");
$stmt_users = $pdo->query("SELECT id, username, nama_lengkap, role FROM user ORDER BY role, nama_lengkap");
$stmt_pesan_kontak = $pdo->query("SELECT * FROM pesan_kontak ORDER BY tanggal_dikirim DESC");
$stmt_desain_pesanan = $pdo->query("SELECT dp.*, p.nama_pelanggan FROM desain_pesanan dp JOIN pesanan p ON dp.id_pesanan = p.id ORDER BY dp.tanggal_upload DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel | Kopi Kenangan</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition dark-mode sidebar-mini layout-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-dark">
        <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li></ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                    <img src="https://i.pravatar.cc/150?u=<?php echo $_SESSION['user']['username']; ?>" class="user-image img-circle elevation-2" alt="User Image">
                    <span class="d-none d-md-inline"><?php echo $_SESSION['user']['nama_lengkap']; ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <li class="user-header bg-primary">
                        <img src="https://i.pravatar.cc/150?u=<?php echo $_SESSION['user']['username']; ?>" class="img-circle elevation-2" alt="User Image">
                        <p><?php echo $_SESSION['user']['nama_lengkap']; ?> - <small><?php echo ucfirst($_SESSION['user']['role']); ?></small></p>
                    </li>
                    <li class="user-footer"><a href="index.php?page=logout" class="btn btn-default btn-flat float-right">Sign out</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="index.php" class="brand-link"><span class="brand-text font-weight-light">Kopi Kenangan</span></a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image"><img src="https://i.pravatar.cc/150?u=<?php echo $_SESSION['user']['username']; ?>" class="img-circle elevation-2" alt="User Image"></div>
                <div class="info"><a href="#" class="d-block text-white"><?php echo $_SESSION['user']['nama_lengkap']; ?></a></div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item"><a href="index.php" class="nav-link <?php echo (!isset($_GET['page']) || $_GET['page'] == 'dashboard' || $_GET['page'] == '') ? 'active' : ''; ?>"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
                    
                    <?php if ($user_role == 'admin'): ?>
                    <li class="nav-header">MANAJEMEN</li>
                    <li class="nav-item"><a href="index.php?page=manage_menu" class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'manage_menu') ? 'active' : ''; ?>"><i class="nav-icon fas fa-utensils"></i><p>Menu Produk</p></a></li>
                    <li class="nav-item"><a href="index.php?page=manage_users" class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'manage_users') ? 'active' : ''; ?>"><i class="nav-icon fas fa-users"></i><p>Manajemen User</p></a></li>
                    <?php endif; ?>

                    <li class="nav-header">TRANSAKSI & LAINNYA</li>
                    <li class="nav-item">
                        <a href="index.php?page=data_pesanan" class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'data_pesanan') ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>Data Pelanggan</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?page=pesan_kontak" class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'pesan_kontak') ? 'active' : ''; ?>"><i class="nav-icon fas fa-envelope"></i><p>Pesan Kontak</p></a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?page=desain_pesanan" class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'desain_pesanan') ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-palette"></i>
                            <p>Data Pesanan</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <?php
        // Tambahkan pemanggilan view baru
        if ($page_content == 'dashboard_view') { dashboard_view(); }
        elseif ($page_content == 'data_pesanan_view') { data_pesanan_view(); } // Panggil view terpisah
        elseif ($page_content == 'manage_menu_view') { manage_menu_view(); }
        elseif ($page_content == 'manage_users_view') { manage_users_view(); }
        elseif ($page_content == 'pesan_kontak_view') { pesan_kontak_view(); }
        elseif ($page_content == 'desain_pesanan_view') { desain_pesanan_view(); }
        ?>
    </div>

    <footer class="main-footer"><strong>Copyright &copy; 2024 Kopi Kenangan.</strong> All rights reserved.</footer>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
$(function () {
    // Inisialisasi DataTables
    var menuTable = $('#menuTable').DataTable({ "responsive": true, "lengthChange": false, "autoWidth": false, "ordering": true, "info": true, "paging": true, "columnDefs": [ { "targets": -1, "orderable": false } ] });
    var pesananTable = $('#pesananTable').DataTable({ "responsive": true, "lengthChange": false, "autoWidth": false, "ordering": true, "info": true, "paging": true });
    var userTable = $('#userTable').DataTable({ "responsive": true, "lengthChange": false, "autoWidth": false, "ordering": true, "info": true, "paging": true });
    $('#pesanKontakTable, #desainPesananTable').DataTable({ "responsive": true, "lengthChange": false, "autoWidth": false, "ordering": true, "info": true, "paging": true });

    // Grafik Penjualan
    var salesChartCanvas = $('#salesChart').get(0).getContext('2d');
    var salesChartData = { labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'], datasets: [{ label: 'Kopi', backgroundColor: 'rgba(60,141,188,0.9)', data: [28, 48, 40, 59, 66, 77] }, { label: 'Pastry', backgroundColor: 'rgba(210, 214, 222, 1)', data: [15, 29, 30, 31, 42, 45] }] };
    new Chart(salesChartCanvas, { type: 'line', data: salesChartData, options: { responsive: true, maintainAspectRatio: false } });

    // --- AJAX HANDLERS ---
    function showMessage(type, message) {
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        var alertHtml = `<div class="alert ${alertClass} alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>${message}</div>`;
        $('.content-header').after(alertHtml);
        setTimeout(() => $('.alert').fadeOut(), 3000);
    }

// --- CRUD MENU (AJAX Handlers) ---

// 1. Handler untuk Menambahkan Menu Baru
$('#formAddMenuItem').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    formData.append('action', 'add_menu');
    $.ajax({ url: 'index.php', type: 'POST', data: formData, processData: false, contentType: false, dataType: 'json', success: function(response) {
        if (response.status === 'success') {
            $('#modalAddMenu').modal('hide');
            var imgHtml = response.data.gambar ? `<img src="uploads/menu/${response.data.gambar}" width="50">` : '';
            
            // Logika menambahkan baris baru ke DataTables (7 Kolom: ID, Nama, Harga, Kategori, Deskripsi, Gambar, Aksi)
            menuTable.row.add([
                response.data.id, 
                response.data.nama, 
                'Rp ' + Number(response.data.harga).toLocaleString(), 
                response.data.kategori, 
                response.data.deskripsi, 
                imgHtml, 
                // Tombol Aksi yang lengkap, termasuk data-toggle dan data-target untuk modal edit
                `<button class="btn btn-warning btn-sm btn-edit-menu" data-toggle="modal" data-target="#modalEditMenu" data-gambar="${response.data.gambar || ''}"><i class="fas fa-edit"></i></button> <button class="btn btn-danger btn-sm btn-delete-menu"><i class="fas fa-trash"></i></button>`
            ]).draw();
            
            showMessage('success', response.message);
        } else { showMessage('error', response.message); }
    }});
});

// 2. Handler untuk Mengedit Menu
$('#formEditMenu').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    formData.append('action', 'edit_menu');
    $.ajax({ url: 'index.php', type: 'POST', data: formData, processData: false, contentType: false, dataType: 'json', success: function(response) {
        if (response.status === 'success') {
            $('#modalEditMenu').modal('hide');
            // Reload DataTables setelah edit (atau gunakan row.update() yang lebih kompleks)
            menuTable.ajax.reload(null, false); // false: Jaga posisi paging saat ini
            showMessage('success', response.message);
        } else { showMessage('error', response.message); }
    }});
});

// 3. Listener untuk Tombol Edit (Memuat data ke modal)
$(document).on('click', '.btn-edit-menu', function(){
    // Dapatkan data baris dari DataTables
    var data = menuTable.row($(this).parents('tr')).data();
    
    // Isi field pada modal edit
    $('#editMenuId').val(data[0]); 
    $('#editNama').val(data[1]); 
    // Bersihkan format harga sebelum diisi ke input number
    $('#editHarga').val(data[2].replace(/[^0-9.-]+/g,"")); 
    $('#editKategori').val(data[3]); 
    $('#editDeskripsi').val(data[4]); 
    // Ambil nama file gambar dari data-attribute tombol edit
    $('#gambarPreview').attr('src', 'uploads/menu/' + $(this).data('gambar'));
    
    // Tampilkan modal edit
    $('#modalEditMenu').modal('show');
});

// 4. Listener untuk Tombol Hapus
$(document).on('click', '.btn-delete-menu', function(){
    if (confirm('Yakin ingin menghapus menu ini?')) {
        var row = $(this).parents('tr');
        var id = menuTable.row(row).data()[0];
        $.post('index.php', { id: id, action: 'delete_menu' }, function(response) {
            if (response.status === 'success') { 
                // Hapus baris dari DataTables secara langsung
                menuTable.row(row).remove().draw(); 
                showMessage('success', response.message); 
            }
            else { showMessage('error', response.message); }
        }, 'json');
    }
});

    // --- CRUD PESANAN ---
    $('#formAddOrder').on('submit', function(e) {
        e.preventDefault();
        $.post('index.php', $(this).serialize() + '&action=add_order', function(response) {
            if (response.status === 'success') {
                $('#modalAddOrder').modal('hide'); pesananTable.ajax.reload(); showMessage('success', response.message);
            } else { showMessage('error', response.message); }
        }, 'json');
    });

    // --- CRUD USER ---
    $('#formAddUser').on('submit', function(e) {
        e.preventDefault();
        $.post('index.php', $(this).serialize() + '&action=add_user', function(response) {
            if (response.status === 'success') {
                $('#modalAddUser').modal('hide'); userTable.ajax.reload(); showMessage('success', response.message);
            } else { showMessage('error', response.message); }
        }, 'json');
    });
});
</script>

</body>
</html>

<?php
// --- DEFINISI SEMUA VIEW ---

// BARU: Function untuk Data Pesanan (terpisah dari Dashboard)
function data_pesanan_view() { 
    $pdo = $GLOBALS['pdo']; // Ambil koneksi PDO dari scope global
?>
    <div class="content-header"><div class="container-fluid"><h1 class="m-0">Data Pelanggan</h1></div></div>
    <section class="content"><div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Daftar Pelanggan</h3></div>
                    <div class="card-body table-responsive p-0">
                        <table id="pesananTable" class="table table-hover text-nowrap">
                            <thead><tr><th>ID</th><th>Pelanggan</th><th>Produk</th><th>Jumlah</th><th>Total</th><th>Status</th><th>Tanggal</th></tr></thead>
                            <tbody>
                                <?php 
                                $stmt_pesanan_view = $pdo->query("SELECT p.*, mp.nama_produk FROM pesanan p JOIN menu_produk mp ON p.id_produk = mp.id ORDER BY p.tanggal_pesanan DESC");
                                while ($row = $stmt_pesanan_view->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                        <td><?php echo $row['jumlah']; ?></td>
                                        <td>Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                        <td><span class="badge badge-info"><?php echo $row['status_pesanan']; ?></span></td>
                                        <td><?php echo date('d M Y, H:i', strtotime($row['tanggal_pesanan'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div></section>
<?php }

function dashboard_view() { 
    global $total_menu, $total_pesanan, $total_user, $user_role; 
    $pdo = $GLOBALS['pdo']; // Ambil koneksi PDO dari scope global
    
    // Query yang hanya diperlukan di dashboard
    $stmt_menu_dash = $pdo->query("SELECT * FROM menu_produk ORDER BY kategori, nama_produk");
    $stmt_pesanan_dash = $pdo->query("SELECT p.*, mp.nama_produk FROM pesanan p JOIN menu_produk mp ON p.id_produk = mp.id ORDER BY p.tanggal_pesanan DESC");

?>
    <div class="content-header"><div class="container-fluid"><h1 class="m-0">Dashboard</h1></div></div>
    <section class="content"><div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h3><?php echo $total_menu; ?></h3><p>Total Menu</p></div><div class="icon"><i class="fas fa-utensils"></i></div></div></div>
            <div class="col-lg-3 col-6"><div class="small-box bg-success"><div class="inner"><h3><?php echo $total_pesanan; ?></h3><p>Total Pesanan</p></div><div class="icon"><i class="fas fa-shopping-cart"></i></div></div></div>
            <div class="col-lg-3 col-6"><div class="small-box bg-warning"><div class="inner"><h3><?php echo $total_user; ?></h3><p>Total User</p></div><div class="icon"><i class="fas fa-users"></i></div></div></div>
            <div class="col-lg-3 col-6"><div class="small-box bg-danger"><div class="inner"><h3>65</h3><p>Pesanan Hari Ini</p></div><div class="icon"><i class="fas fa-chart-pie"></i></div></div></div>
        </div>
        <div class="row">
            <div class="col-md-8"><div class="card"><div class="card-header"><h3 class="card-title">Grafik Penjualan Bulanan</h3></div><div class="card-body"><div class="chart"><canvas id="salesChart" style="height: 250px;"></canvas></div></div></div></div>
            <div class="col-md-4"><div class="card"><div class="card-header"><h3 class="card-title">Kategori Terlaris</h3></div><div class="card-body"><div class="chart-pie pt-4"><canvas id="salesChartPie" style="height: 250px;"></canvas></div></div></div></div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Data Menu Produk</h3>
                        <?php if (in_array($user_role, ['admin', 'karyawan'])): ?>
                        <div class="card-tools"><button type="button" class="btn btn-block btn-success btn-sm" data-toggle="modal" data-target="#modalAddOrder"><i class="fas fa-plus"></i> Tambah Daftar Menu</button></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table id="menuTable" class="table table-hover text-nowrap">
                            <thead><tr><th>ID</th><th>Nama</th><th>Harga</th><th>Kategori</th><th>Deskripsi</th><th>Gambar</th></tr></thead>
                            <tbody>
                                <?php while ($row = $stmt_menu_dash->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                    <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                                    <td><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                                    <td><img src="uploads/menu/<?php echo $row['gambar'] ?: 'placeholder.png'; ?>" width="50" onerror="this.src='https://via.placeholder.com/50';"></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card"><div class="card-header"><h3 class="card-title">Data Pesanan</h3></div>
                    <div class="card-body table-responsive p-0"><table id="pesananTable" class="table table-hover text-nowrap"><thead><tr><th>ID</th><th>Pelanggan</th><th>Produk</th><th>Jumlah</th><th>Total</th><th>Status</th><th>Tanggal</th></tr></thead><tbody><?php while ($row = $stmt_pesanan_dash->fetch(PDO::FETCH_ASSOC)): ?><tr><td><?php echo $row['id']; ?></td><td><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td><td><?php echo htmlspecialchars($row['nama_produk']); ?></td><td><?php echo $row['jumlah']; ?></td><td>Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td><td><span class="badge badge-info"><?php echo $row['status_pesanan']; ?></span></td><td><?php echo date('d M Y, H:i', strtotime($row['tanggal_pesanan'])); ?></td></tr><?php endwhile; ?></tbody></table></div>
                </div>
            </div>
        </div>
    </div></section>

    <div class="modal fade" id="modalAddOrder"><div class="modal-dialog"><div class="modal-content">
        <form enctype="multipart/form-data">
            <div class="modal-header"><h4 class="modal-title">Tambah Menu Baru</h4></div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Menu</label>
                    <input type="text" name="nama_produk" class="form-control" required> </div>
                <div class="form-group">
                    <label>Harga</label>
                    <input type="number" name="harga" class="form-control" required> </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori" class="form-control" required> <option value="Kopi">Kopi</option>
                        <option value="Non-Kopi">Non-Kopi</option>
                        <option value="Pastry">Pastry</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control"></textarea> </div>
                <div class="form-group">
                    <label>Gambar</label>
                    <input type="file" name="gambar" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div>
        </form>
    </div></div></div>
<?php }

function manage_menu_view() { 
    $pdo = $GLOBALS['pdo']; // Ambil koneksi PDO dari scope global
?>
    <div class="content-header"><div class="container-fluid"><h1 class="m-0">Manajemen Menu</h1></div></div>
    <section class="content"><div class="container-fluid">
        <div class="row"><div class="col-12">
            <div class="card"><div class="card-header"><h3 class="card-title">Daftar Menu</h3><div class="card-tools"><button type="button" class="btn btn-block btn-primary btn-sm" data-toggle="modal" data-target="#modalAddMenu"><i class="fas fa-plus"></i> Tambah Menu</button></div></div>
                <div class="card-body table-responsive p-0">
                    <table id="menuTable" class="table table-hover text-nowrap">
                        <thead><tr><th>ID</th><th>Nama</th><th>Harga</th><th>Kategori</th><th>Deskripsi</th><th>Gambar</th><th>Aksi</th></tr></thead>
                        <tbody>
                            <?php 
                            // Ulangi query untuk view ini
                            $stmt_menu_view = $pdo->query("SELECT * FROM menu_produk ORDER BY kategori, nama_produk");
                            while ($row = $stmt_menu_view->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                                <td><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                                <td><img src="uploads/menu/<?php echo $row['gambar'] ?: 'placeholder.png'; ?>" width="50" onerror="this.src='https://via.placeholder.com/50';"></td>
                                <td>
                                    <button class="btn btn-warning btn-sm btn-edit-menu" data-gambar="<?php echo $row['gambar']; ?>" data-toggle="modal" data-target="#modalEditMenu"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-danger btn-sm btn-delete-menu"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div></div>
    </div></section>
    <div class="modal fade" id="modalAddMenu"><div class="modal-dialog"><div class="modal-content">
        <form id="formAddMenuItem" enctype="multipart/form-data"><div class="modal-header"><h4 class="modal-title">Tambah Menu Baru</h4></div>
        <div class="modal-body">
            <div class="form-group"><label>Nama Produk</label><input type="text" name="nama_produk" class="form-control" required></div>
            <div class="form-group"><label>Harga</label><input type="number" name="harga" class="form-control" required></div>
            <div class="form-group"><label>Kategori</label><select name="kategori" class="form-control" required><option value="Kopi">Kopi</option><option value="Non-Kopi">Non-Kopi</option><option value="Pastry">Pastry</option></select></div>
            <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" class="form-control"></textarea></div>
            <div class="form-group"><label>Gambar</label><input type="file" name="gambar" class="form-control" accept="image/*"></div>
        </div><div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div></form>
    </div></div></div>
    <div class="modal fade" id="modalEditMenu"><div class="modal-dialog"><div class="modal-content">
        <form id="formEditMenu" enctype="multipart/form-data"><input type="hidden" name="id" id="editMenuId"><input type="hidden" name="gambar_lama" id="editGambarLama">
        <div class="modal-header"><h4 class="modal-title">Edit Menu</h4></div>
        <div class="modal-body">
            <div class="form-group"><label>Nama Produk</label><input type="text" name="nama_produk" id="editNama" class="form-control" required></div>
            <div class="form-group"><label>Harga</label><input type="number" name="harga" id="editHarga" class="form-control" required></div>
            <div class="form-group"><label>Kategori</label><select name="kategori" id="editKategori" class="form-control" required><option value="Kopi">Kopi</option><option value="Non-Kopi">Non-Kopi</option><option value="Pastry">Pastry</option></select></div>
            <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" id="editDeskripsi" class="form-control"></textarea></div>
            <div class="form-group"><label>Gambar Baru (kosongkan jika tidak ingin mengubah)</label><input type="file" name="gambar" class="form-control" accept="image/*"></div>
            <div class="form-group"><label>Gambar Saat Ini:</label><br><img id="gambarPreview" src="" width="100"></div>
        </div><div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan Perubahan</button></div></form>
    </div></div></div>
<?php } 

function manage_users_view() { 
    $pdo = $GLOBALS['pdo']; // Ambil koneksi PDO dari scope global
?>
    <div class="content-header"><div class="container-fluid"><h1 class="m-0">Manajemen User</h1></div></div>
    <section class="content"><div class="container-fluid">
        <div class="row"><div class="col-12">
            <div class="card"><div class="card-header"><h3 class="card-title">Daftar User</h3><div class="card-tools"><button type="button" class="btn btn-block btn-primary btn-sm" data-toggle="modal" data-target="#modalAddUser"><i class="fas fa-plus"></i> Tambah User</button></div></div>
                <div class="card-body table-responsive p-0"><table id="userTable" class="table table-hover text-nowrap"><thead><tr><th>ID</th><th>Username</th><th>Nama Lengkap</th><th>Role</th></tr></thead><tbody><?php 
                // Ulangi query untuk view ini
                $stmt_users_view = $pdo->query("SELECT id, username, nama_lengkap, role FROM user ORDER BY role, nama_lengkap");
                while ($row = $stmt_users_view->fetch(PDO::FETCH_ASSOC)): ?><tr><td><?php echo $row['id']; ?></td><td><?php echo htmlspecialchars($row['username']); ?></td><td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td><td><span class="badge badge-primary"><?php echo ucfirst($row['role']); ?></span></td></tr><?php endwhile; ?></tbody></table></div>
            </div>
        </div></div>
    </div></section>
    <div class="modal fade" id="modalAddUser"><div class="modal-dialog"><div class="modal-content"><form id="formAddUser"><div class="modal-header"><h4 class="modal-title">Tambah User Baru</h4></div><div class="modal-body"><div class="form-group"><label>Username</label><input type="text" name="username" class="form-control" required></div><div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" required></div><div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama_lengkap" class="form-control" required></div><div class="form-group"><label>Role</label><select name="role" class="form-control" required><option value="admin">Admin</option><option value="karyawan">Karyawan</option><option value="user">User</option></select></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div></form></div></div></div>
<?php }

function pesan_kontak_view() { 
    $pdo = $GLOBALS['pdo']; // Ambil koneksi PDO dari scope global
?>
    <div class="content-header"><div class="container-fluid"><h1 class="m-0">Pesan Masuk</h1></div></div>
    <section class="content"><div class="container-fluid">
        <div class="row"><div class="col-12">
            <div class="card"><div class="card-header"><h3 class="card-title">Daftar Pesan dari Kontak</h3></div>
                <div class="card-body table-responsive p-0"><table id="pesanKontakTable" class="table table-hover text-nowrap"><thead><tr><th>Tanggal</th><th>Nama</th><th>Email</th><th>Subjek</th><th>Pesan</th></tr></thead><tbody><?php 
                // Ulangi query untuk view ini
                $stmt_pesan_kontak_view = $pdo->query("SELECT * FROM pesan_kontak ORDER BY tanggal_dikirim DESC");
                while ($row = $stmt_pesan_kontak_view->fetch(PDO::FETCH_ASSOC)): ?><tr><td><?php echo date('d M Y', strtotime($row['tanggal_dikirim'])); ?></td><td><?php echo htmlspecialchars($row['nama']); ?></td><td><?php echo htmlspecialchars($row['email']); ?></td><td><?php echo htmlspecialchars($row['subjek']); ?></td><td><?php echo htmlspecialchars(substr($row['pesan'], 0, 50)) . '...'; ?></td></tr><?php endwhile; ?></tbody></table></div>
            </div>
        </div></div>
    </div></section>
<?php }

function desain_pesanan_view() { 
    $pdo = $GLOBALS['pdo']; // Ambil koneksi PDO dari scope global
?>
    <div class="content-header"><div class="container-fluid"><h1 class="m-0">Data Pesanan</h1></div></div>
    <section class="content"><div class="container-fluid">
        <div class="row"><div class="col-12">
            <div class="card"><div class="card-header"><h3 class="card-title">Daftar Data Pesanan</h3></div>
                <div class="card-body table-responsive p-0"><table id="desainPesananTable" class="table table-hover text-nowrap"><thead><tr><th>ID</th><th>ID Pesanan</th><th>Pelanggan</th><th>File Desain</th><th>Keterangan</th><th>Tanggal Upload</th></tr></thead><tbody><?php 
                // Ulangi query untuk view ini
                $stmt_desain_view = $pdo->query("SELECT dp.*, p.nama_pelanggan FROM desain_pesanan dp JOIN pesanan p ON dp.id_pesanan = p.id ORDER BY dp.tanggal_upload DESC");
                while ($row = $stmt_desain_view->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['id_pesanan']; ?></td>
                    <td><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                    <td>
                        <?php 
                        if ($row['file_desain_url']) {
                            $file_path = $row['file_desain_url'];
                            
                            if (strpos($file_path, 'uploads/') === false && $file_path[0] !== '/') {
                                $file_path = 'uploads/' . $file_path;
                            } elseif ($file_path[0] === '/') {
                                $file_path = substr($file_path, 1);
                            }
                            
                            // UBAH WIDTH DAN TAMBAHKAN HEIGHT UNTUK UKURAN 100x100
                            echo "<a href='{$file_path}' target='_blank'>";
                            echo "<img src='{$file_path}' width='100' height='100' style='object-fit: cover;' onerror=\"this.onerror=null; this.src='https://via.placeholder.com/100';\">";
                            echo "</a>";
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                    <td><?php echo date('d M Y, H:i', strtotime($row['tanggal_upload'])); ?></td>
                </tr>
                <?php endwhile; ?></tbody></table></div>
            </div>
        </div></div>
    </div></section>
<?php } ?>