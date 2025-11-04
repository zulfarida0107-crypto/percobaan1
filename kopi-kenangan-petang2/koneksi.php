<?php

// 1. Definisikan Variabel Koneksi
$servername = "localhost"; // Nama server database, biasanya "localhost"
$username   = "root";       // Username database, default XAMPP/MAMP adalah "root"
$password   = "";           // Password database, default XAMPP/MAMP kosong
$dbname     = "db_kantin"; // Ganti dengan nama database Anda
$port       = 3307;

// 2. Buat Koneksi
// Menggunakan gaya Object-Oriented (OOP)
$koneksi = new mysqli($servername, $username, $password, $dbname, $port);

// 3. Cek Koneksi
if ($koneksi->connect_error) {
    // Jika koneksi gagal, hentikan skrip dan tampilkan pesan error
    die("Koneksi gagal: " . $koneksi->connect_error);}



/*
 * Jika skrip berlanjut sampai sini, artinya koneksi BERHASIL.
 * Variabel $koneksi (atau nama apa pun yang Anda berikan)
 * kini siap digunakan untuk query di file lain.
 *
 * Sebaiknya JANGAN ada output/echo/HTML lain di file ini.
 * Cukup skrip PHP murni untuk koneksi.
 */

?>