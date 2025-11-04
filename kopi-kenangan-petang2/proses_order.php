<?php
// 1. Panggil file koneksi
// require 'koneksi.php';
// "require" akan menghentikan skrip jika file koneksi.php tidak ditemukan.
require 'koneksi.php';

echo "Koneksi ke database berhasil!";
echo "<br>";

// 2. Buat query SQL (contoh)
$sql = "SELECT nama_produk, harga FROM tabel_produk LIMIT 5";

// 3. Eksekusi query menggunakan variabel $koneksi dari file koneksi.php
$result = $koneksi->query($sql);

// 4. Tampilkan hasil (contoh)
if ($result->num_rows > 0) {
    // Looping untuk mengambil setiap baris data
    while($row = $result->fetch_assoc()) {
        echo "Nama Produk: " . $row["nama_produk"]. " - Harga: " . $row["harga"]. "<br>";
    }
} else {
    echo "Tidak ada data produk.";
}

// 5. Tutup koneksi (opsional di akhir skrip, tapi praktik yang baik)
$koneksi->close();

?>