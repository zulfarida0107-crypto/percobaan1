<?php
include "koneksi.php";

$name = $_POST['name'];
$items = $_POST['items']; 
$total = $_POST['total'];
$status = 'baru';

$itemsArray = json_decode($items, true); // Decode jadi array asosiatif

$id = $itemsArray[0]['id']; // Ambil 'id' dari item pertama
$jumlah = $itemsArray[0]['quantity'];

// echo $id, ' ', $jumlah, ' ', $status;

$sql = "INSERT INTO pesanan (nama_pelanggan, id_produk, jumlah, total_harga, status_pesanan) VALUES (?, ?, ?, ?, ?)";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("siids", 
  $name, 
  $id, 
  $jumlah, 
  $total, 
  $status);

if ($stmt->execute()) {
    echo "Pesanan berhasil disimpan!";
} else {
    echo "Gagal menyimpan: " . $stmt->error;
}

$stmt->close();
$koneksi->close();
?>