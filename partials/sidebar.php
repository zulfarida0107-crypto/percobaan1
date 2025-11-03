<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index.php" class="brand-link">
      <span class="brand-text font-weight-light">Kopi Kenangan</span> </a>

    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="https://i.pravatar.cc/150?u=<?php echo $_SESSION['user']['username']; ?>" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block text-white"><?php echo $_SESSION['user']['nama_lengkap']; ?></a>
        </div>
      </div>

      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item">
            <a href="index.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>

          <li class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'menu.php') ? 'menu-open' : ''; ?>">
            <a href="#" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'menu.php') ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-box"></i>
              <p>
                Manajemen Produk
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="menu.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'menu.php') ? 'active' : ''; ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Menu Produk</p>
                </a>
              </li>
            </ul>
          </li>

          <li class="nav-item <?php echo in_array(basename($_SERVER['PHP_SELF']), ['pesanan.php', 'desain_pesanan.php']) ? 'menu-open' : ''; ?>">
            <a href="#" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['pesanan.php', 'desain_pesanan.php']) ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-shopping-bag"></i>
              <p>
                Transaksi
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pesanan.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'pesanan.php') ? 'active' : ''; ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Data Pesanan</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="desain_pesanan.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'desain_pesanan.php') ? 'active' : ''; ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Desain Pesanan</p>
                </a>
              </li>
            </ul>
          </li>

          <li class="nav-item">
            <a href="pesan_kontak.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'pesan_kontak.php') ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-envelope"></i>
              <p>Pesan Kontak</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
</aside>