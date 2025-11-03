<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: ../login.php'); exit(); }
require_once '../config/database.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    extract($_POST);
    try {
        $sql = "UPDATE menu_produk SET nama_produk=:nama, harga=:harga, kategori=:kategori, deskripsi=:deskripsi WHERE id=:id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id); $stmt->bindParam(':nama', $nama_produk);
        $stmt->bindParam(':harga', $harga); $stmt->bindParam(':kategori', $kategori);
        $stmt->bindParam(':deskripsi', $deskripsi);
        $stmt->execute();
        header('Location: ../menu.php?status=edit_sukses');
    } catch (PDOException $e) { die("Error: " . $e->getMessage()); }
}
?>