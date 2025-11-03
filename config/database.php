<?php
// Konfigurasi Database
 $host = 'localhost';
 $db_name = 'db_kantin';
 $username = 'root'; // Ganti dengan username DB Anda
 $password = '';     // Ganti dengan password DB Anda

try {
    $pdo = new PDO("mysql:host={$host};dbname={$db_name}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    die("Error: Koneksi database gagal. " . $exception->getMessage());
}
?>
