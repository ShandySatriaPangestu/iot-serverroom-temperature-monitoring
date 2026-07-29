<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "serveruser", "Server123!", "server_room");

if ($conn->connect_error) {
    die(json_encode(["error" => "Koneksi Gagal"]));
}

$sql = "SELECT * FROM summary_suhu_harian ORDER BY created_at DESC LIMIT 1";
$result = $conn->query($sql);

if (!$result) {
    die(json_encode(["error" => "Query Gagal"]));
}

header('Content-Type: application/json');

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    $suhu_rata = round((float)$row['suhu_rata_rata'], 1);
    $suhu_maks = round((float)$row['suhu_maksimum'], 1);
    $suhu_min = round((float)$row['suhu_minimum'], 1);
    $kel_rata  = round((float)$row['kelembapan_rata_rata'], 1);
    $alarm     = (int)$row['total_alarm'];

    echo json_encode([
        "tanggal" => $row['tanggal'],
        "suhu_rata" => $suhu_rata,
        "suhu_maks" => $suhu_maks,
        "suhu_min" => $suhu_min,
        "kelembapan_rata" => $kel_rata,
        "total_alarm" => $alarm,
        "suhu_rata_teks" => $suhu_rata . "°C",
        "suhu_maks_teks" => $suhu_maks . "°C",
        "suhu_min_teks" => $suhu_min . "°C",
        "kelembapan_teks" => $kel_rata . "%",
        "alarm_teks" => $alarm . " Kali"
    ]);
} else {
    echo json_encode([
        "tanggal" => date('Y-m-d'),
        "suhu_rata" => 0,
        "suhu_maks" => 0,
        "suhu_min" => 0,
        "kelembapan_rata" => 0,
        "total_alarm" => 0,
        "suhu_rata_teks" => "-",
        "suhu_maks_teks" => "-",
        "suhu_min_teks" => "-",
        "kelembapan_teks" => "-",
        "alarm_teks" => "0 Kali"
    ]);
}

$conn->close();
?>