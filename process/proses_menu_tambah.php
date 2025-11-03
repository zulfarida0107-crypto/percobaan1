<?php
session_start();
// Cek sesi login
if (!isset($_SESSION['user'])) { 
    header('Location: ../login.php'); 
    exit(); 
}

require_once '../config/database.php';
// Ambil koneksi PDO dari global scope
$pdo = $GLOBALS['pdo']; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Ambil data POST
    $nama_produk = $_POST['nama_produk'];
    $harga = $_POST['harga'];
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];
    
    // --- PENANGANAN GAMBAR (Wajib Ditambahkan) ---
    $gambar = NULL; // Default nilai NULL jika tidak ada gambar
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['gambar']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Cek ekstensi file
        if (in_array($ext, $allowed)) {
            $newname = uniqid() . '.' . $ext;
            // PATH PERBAIKAN: Gunakan '../uploads/menu/' (Relatif dari folder 'process/')
            $upload_path = '../uploads/menu/' . $newname;
            
            // Pindahkan file yang diupload
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_path)) {
                $gambar = $newname;
            }
        }
    }
    // --- AKHIR PENANGANAN GAMBAR ---

    try {
        // PERBAIKAN SQL: Tambahkan kolom `gambar` ke INSERT dan VALUES
        $sql = "INSERT INTO menu_produk (nama_produk, harga, kategori, deskripsi, gambar) 
                VALUES (:nama, :harga, :kategori, :deskripsi, :gambar)";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':nama', $nama_produk); 
        $stmt->bindParam(':harga', $harga);
        $stmt->bindParam(':kategori', $kategori); 
        $stmt->bindParam(':deskripsi', $deskripsi);
        $stmt->bindParam(':gambar', $gambar); // Bind parameter gambar yang sudah diproses

        $stmt->execute();
        
        // Redirect sukses
        header('Location: ../menu.php?status=tambah_sukses');
        exit();
        
    } catch (PDOException $e) { 
        die("Error: " . $e->getMessage()); 
    }
} else {
    header('Location: ../menu.php');
    exit();
}
?>