<?php
// Aktifkan pelaporan error agar ketahuan letak salahnya
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = "localhost";
$user = "serveruser";
$password = "Server123!";
$database = "server_room";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}
echo "Koneksi Aman.<br>";

$sql = "SELECT * FROM summary_suhu_harian ORDER BY created_at DESC LIMIT 1";
$result = $conn->query($sql);

if (!$result) {
    die("Query Salah / Nama Kolom Salah: " . $conn->error);
}

echo "Query Berhasil! Jumlah baris: " . $result->num_rows . "<br><br>";

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "Isi baris data terakhir:<br>";
    echo "<pre>";
    print_r($row);
    echo "</pre>";
} else {
    echo "Tabel summary_suhu_harian kosong.";
}
$conn->close();
?>