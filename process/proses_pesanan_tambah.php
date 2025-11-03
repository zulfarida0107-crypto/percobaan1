<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../login.php'); exit(); }
require_once '../config/database.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_pelanggan = $_POST['nama_pelanggan']; $id_produk = $_POST['id_produk']; $jumlah = $_POST['jumlah'];
    $stmt_harga = $pdo->prepare("SELECT harga FROM menu_produk WHERE id = :id");
    $stmt_harga->bindParam(':id', $id_produk); $stmt_harga->execute();
    $harga_produk = $stmt_harga->fetch(PDO::FETCH_ASSOC)['harga']; $total_harga = $harga_produk * $jumlah;
    try {
        $sql = "INSERT INTO pesanan (nama_pelanggan, id_produk, jumlah, total_harga) VALUES (:nama_pelanggan, :id_produk, :jumlah, :total_harga)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nama_pelanggan', $nama_pelanggan); $stmt->bindParam(':id_produk', $id_produk);
        $stmt->bindParam(':jumlah', $jumlah); $stmt->bindParam(':total_harga', $total_harga);
        $stmt->execute();
        header('Location: ../pesanan.php?status=tambah_sukses');
    } catch (PDOException $e) { die("Error: " . $e->getMessage()); }
}
?>