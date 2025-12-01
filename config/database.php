<?php
// Ambil konfigurasi dari environment variable Docker
$DB_HOST = getenv('DB_HOST') ?: 'mysql';      // nama service mysql di docker-compose
$DB_USER = getenv('DB_USER') ?: 'appuser';
$DB_PASS = getenv('DB_PASSWORD') ?: 'password';
$DB_NAME = getenv('DB_NAME') ?: 'db_kantin';
$DB_PORT = getenv('DB_PORT') ?: 3306;

try {
    // Format DSN untuk PDO MySQL
    $dsn = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME}";

    // Koneksi PDO
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS);

    // Set error mode (optional tapi sangat disarankan)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $exception) {
    die("Error: Koneksi database gagal. " . $exception->getMessage());
}
?>