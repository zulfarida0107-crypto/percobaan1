<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
require 'config/database.php'; // path sesuai project

$result = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM pesanan");
if (!$result) { echo "Query error: ".mysqli_error($conn); exit; }
$row = mysqli_fetch_assoc($result);
echo "Count pesanan: ".$row['cnt']."<br>";

$res = mysqli_query($conn, "SELECT * FROM pesanan ORDER BY id DESC LIMIT 5");
while ($r = mysqli_fetch_assoc($res)) {
    echo "<pre>"; print_r($r); echo "</pre>";
}
?>
