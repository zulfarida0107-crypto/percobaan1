<?php
session_start();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
            header('Location: ../index.php');
        } else {
            $_SESSION['error'] = 'Username atau password salah!';
            header('Location: ../login.php');
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header('Location: ../login.php');
}
?>