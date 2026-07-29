<?php
$conn = new mysqli("localhost","root","","server_room");
if ($conn->connect_error) {
  http_response_code(500);
  exit;
}

$q = $conn->query("SELECT * FROM data_sensor ORDER BY id DESC LIMIT 30");

$data = [];
while($r = $q->fetch_assoc()){
  $data[] = [
    "suhu" => (float)$r['suhu'],
    "hum"  => (float)$r['kelembaban'],
    "time" => date('H:i:s', strtotime($r['waktu'])),
    "alarm"=> (int)$r['alarm']
  ];
}

echo json_encode(array_reverse($data));
