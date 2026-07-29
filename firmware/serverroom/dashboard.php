
<?php
date_default_timezone_set('Asia/Jakarta');
// === 1. KONEKSI DATABASE ===
$conn = new mysqli("localhost", "serveruser", "Server123!", "server_room");
if ($conn->connect_errno) {
    die("Koneksi Database Gagal : " . $conn->connect_error);
}

// Tambahan: API untuk AJAX (Mengambil data JSON saja)
if (isset($_GET['ajax'])) {
    $q = $conn->query("SELECT * FROM data_sensor ORDER BY id DESC LIMIT 50");
    $data = [];
    while($r = $q->fetch_assoc()){
        $data[] = ["time"=>date("H:i:s", strtotime($r['waktu'])), "suhu"=>(float)$r['suhu'], "hum"=>(float)$r['kelembaban'], "pir"=>(int)$r['pir'], "sound"=>(int)$r['sound'], "rain"=>(int)$r['rain'], "alarm"=>(int)$r['alarm']];
    }
    echo json_encode(array_reverse($data));
    exit;
}

// === 2. BLOK API PENERIMA DATA (ESP32) ===
if (isset($_GET['temp'])) {
    $stmt = $conn->prepare("INSERT INTO data_sensor (suhu, kelembaban, pir, sound, rain, alarm, waktu) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    
    // Pastikan variabel mengambil data dari $_GET. 
    // Jika tidak dikirim dari ESP32, berikan nilai default 0
    $pir = isset($_GET['pir']) ? $_GET['pir'] : 0;
    $sound = isset($_GET['sound']) ? $_GET['sound'] : 0;
    $rain = isset($_GET['rain']) ? $_GET['rain'] : 0;
    $alarm = isset($_GET['alarm']) ? $_GET['alarm'] : 0;

    $stmt->bind_param("ssssss", $_GET['temp'], $_GET['hum'], $pir, $sound, $rain, $alarm);
    $stmt->execute();
    echo "Data Saved";
    exit;
}

// === 3. BLOK DATA DASHBOARD ===
$tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : date('Y-m-d 00:00:00');
$tgl_selesai = isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : date('Y-m-d 23:59:59');

if (isset($_GET['filter'])) {
    $stmt = $conn->prepare("SELECT * FROM data_sensor WHERE waktu BETWEEN ? AND ? ORDER BY id ASC");
    $stmt->bind_param("ss", $tgl_mulai, $tgl_selesai);
    $stmt->execute();
    $q = $stmt->get_result();
} else {
    $q = $conn->query("SELECT * FROM data_sensor ORDER BY id DESC LIMIT 50");
}

$data = [];
while($r = $q->fetch_assoc()){
    $data[] = [
        "date_full" => date("d-m-Y H:i:s", strtotime($r['waktu'])),
        "time"  => date("H:i:s", strtotime($r['waktu'])),
        "suhu"  => (float)$r['suhu'],
        "hum"   => (float)$r['kelembaban'],
        "pir"   => (int)$r['pir'],
        "sound" => (int)$r['sound'],
        "rain"  => (int)$r['rain'],
        "alarm" => (int)$r['alarm']
    ];
}

if (!isset($_GET['filter'])) { $data = array_reverse($data); }
$last = !empty($data) ? end($data) : ["suhu"=>0,"hum"=>0,"pir"=>0,"sound"=>0,"rain"=>1,"alarm"=>0,"time"=>"-","date_full"=>"-"];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Server Room Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<style>
:root {
    --bg-main: #edf0f4;       
    --bg-navbar: #ffffff;     
    --bg-card: #ffffff;       
    --bg-inner-card: #f5f7fa;  
    --text-main: #334155;        
    --text-muted: #64748b;     
    --border-color: #e2e8f0;    
    --color-ok: #0d9488;        
    --color-alarm: #e11d48;     
    --color-warning: #d97706;   
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Inter', sans-serif; background: var(--bg-main); color: var(--text-main); height: 100vh; overflow: hidden; display: flex; flex-direction: column; }
.navbar { background: var(--bg-navbar); border-bottom: 1px solid var(--border-color); padding: 0 24px; height: 55px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.02); flex-shrink: 0; }
.nav-brand { display: flex; flex-direction: column; }
.nav-title { font-size: 14px; font-weight: 700; color: var(--text-main); letter-spacing: 0.3px; }
.nav-subtitle { font-size: 10px; color: var(--text-muted); }
.nav-links { display: flex; gap: 8px; align-items: center; }
.nav-links a { color: var(--text-muted); text-decoration: none; font-size: 12px; font-weight: 500; padding: 6px 12px; border-radius: 6px; transition: all 0.2s; display: flex; align-items: center; gap: 4px; }
.nav-links a:hover, .nav-links a.active { background: var(--bg-main); color: var(--text-main); }
.btn-back { background: #334155 !important; color: #ffffff !important; }
.btn-back:hover { background: #1e293b !important; }
.split-wrapper { display: table; width: 100%; table-layout: fixed; height: calc(100vh - 55px); padding: 16px; }
.left-column { display: table-cell; width: 280px; vertical-align: top; padding-right: 16px; }
.right-column { display: table-cell; vertical-align: top; height: 100%; }
.panel { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.01); }
.panel-title { font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
.status-box { text-align: center; padding: 4px 0; }
.status-badge { display: inline-block; font-size: 18px; font-weight: 700; padding: 5px 16px; border-radius: 6px; }
.status-ok { color: var(--color-ok); background: rgba(13, 148, 136, 0.08); }
.status-alarm { color: var(--color-alarm); background: rgba(225, 29, 72, 0.08); animation: blink 1.5s infinite alternate; }
@keyframes blink { 0% { opacity: 0.8; } 100% { opacity: 1; } }
.desc { margin-top: 8px; color: var(--color-warning); font-size: 11px; font-weight: 600; }
.vertical-node-list { display: flex; flex-direction: column; gap: 8px; }
.node-card { background: var(--bg-inner-card); border: 1px solid var(--border-color); padding: 12px 14px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
.node-info { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; }
.node-info i { width: 16px; text-align: center; }
.node-value { font-size: 13px; font-weight: 700; color: var(--text-main); }
.node-value.badge-status { padding: 2px 8px; border-radius: 4px; font-size: 11px; }
.ok { color: var(--color-ok); background: rgba(13, 148, 136, 0.08); }
.alarm { color: var(--color-alarm); background: rgba(225, 29, 72, 0.08); }
.chart-panel-flex { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px; height: 100%; box-shadow: 0 4px 12px rgba(0,0,0,0.01); display: table; width: 100%; }
.chart-header-row { display: table-row; height: 1px; }
.chart-header-bar { display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid var(--border-color); gap: 10px; }
.filter-inline-form { display: flex; gap: 6px; align-items: center; }
.input-box { display: flex; align-items: center; gap: 4px; }
.input-box span { font-size: 10px; font-weight: 600; color: var(--text-muted); }
.input-box input { padding: 5px 8px; border: 1px solid var(--border-color); border-radius: 6px; font-family: 'Inter', sans-serif; font-size: 10px; color: var(--text-main); background: var(--bg-inner-card); }
.btn-action { background: #475569; color: #fff; border: none; padding: 5px 10px; font-size: 10px; font-weight: 600; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: 0.2s; }
.btn-action:hover { background: #334155; }
.btn-reset { background: #e2e8f0; color: var(--text-main); text-decoration: none; padding: 5px 10px; font-size: 10px; font-weight: 500; border-radius: 6px; display: flex; align-items: center; gap: 4px; }
.btn-reset:hover { background: #cbd5e1; }
.export-buttons { display: flex; gap: 4px; }
.btn-export { border: 1px solid var(--border-color); background: #ffffff; color: var(--text-main); padding: 5px 10px; font-size: 10px; font-weight: 600; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: 0.2s; }
.btn-export:hover { background: var(--bg-inner-card); }
.btn-excel-color i { color: #16a34a; }
.btn-pdf-color i { color: #dc2626; }
.chart-body-row { display: table-row; height: 100%; }
.chart-content-wrapper { position: relative; height: 100%; width: 100%; padding-top: 15px; }

</style>
</head>

<body>

<nav class="navbar">
    <div class="nav-brand">
        <span class="nav-title">SHANDY LAB</span>
        <span class="nav-subtitle">Server Room Control Center</span>
    </div>
    <div class="nav-links">
        <a href="http://172.16.50.250/index.php" class="btn-back"><i class="fa fa-arrow-left"></i> Main Dashboard</a>
        <a href="dashboard.php" class="active"><i class="fa fa-chart-line"></i> Realtime Monitor</a>
    </div>
<ul class="nav-menu">
    <li class="nav-item">
        <a href="../../logout.php" style="color: #e11d48;">
            <i class="fa fa-sign-out-alt"></i> 
            <span>Logout</span>
        </a>
    </li>
</ul>


</nav>

<div class="split-wrapper">
    <div class="left-column">
        <div class="panel" style="margin-bottom: 12px;">
            <div class="status-box">
                <div id="status-badge" class="status-badge <?= $last['alarm'] ? 'status-alarm' : 'status-ok' ?>">
                    <i id="status-icon" class="fa <?= $last['alarm'] ? 'fa-triangle-exclamation' : 'fa-circle-check' ?>"></i>
                    <span id="status-text"><?= $last['alarm'] ? 'ALARM' : 'AMAN' ?></span>
                </div>
                <div id="desc-container" class="desc">
                    <?php
                    $desc=[];
                    if($last['pir']) $desc[]="Motion";
                    if($last['sound']) $desc[]="Noise";
                    if($last['rain']==0) $desc[]="Water Leak";
                    echo !empty($desc) ? '<i class="fa fa-triangle-exclamation"></i> Terdeteksi: '.implode(", ",$desc) : '<i class="fa fa-shield-halved"></i> Normal';
                    ?>
                </div>
            </div>
        </div>

        <div class="panel" style="height: calc(100% - 105px); margin-bottom: 0;">
            <div class="panel-title"><i class="fa fa-microchip"></i> Telemetry Node</div>
            <div class="vertical-node-list">
                <div class="node-card"><span class="node-info">Suhu</span><div class="node-value" id="val-suhu"><?= $last['suhu'] ?> &deg;C</div></div>
                <div class="node-card"><span class="node-info">Kelembaban</span><div class="node-value" id="val-hum"><?= $last['hum'] ?> %</div></div>
                <div class="node-card"><span class="node-info"><i class="fa fa-person-walking"></i> PIR Motion</span><div class="node-value badge-status <?= $last['pir']?'alarm':'ok' ?>" id="val-pir"><?= $last['pir']?'ALERT':'OK' ?></div></div>
                <div class="node-card"><span class="node-info"><i class="fa fa-volume-high"></i> Sound Node</span><div class="node-value badge-status <?= $last['sound']?'alarm':'ok' ?>" id="val-sound"><?= $last['sound']?'ALERT':'OK' ?></div></div>
                <div class="node-card"><span class="node-info"><i class="fa fa-cloud-showers-water"></i> Rain Status</span><div class="node-value badge-status <?= $last['rain']==0?'alarm':'ok' ?>" id="val-rain"><?= $last['rain']==0?'WET':'DRY' ?></div></div>
                <div class="node-card" style="margin-top:10px; background:transparent; border-style:dashed;">
                    <span class="node-info" style="font-size:10px;"><i class="fa fa-clock"></i> Last Sync</span>
                    <div class="node-value" id="val-time" style="font-size:11px; color:var(--text-muted); font-weight:500;"><?= $last['time'] ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="right-column">
        <div class="chart-panel-flex">
            <div class="chart-header-row">
                <div class="chart-header-bar">
                    <div class="panel-title" style="margin-bottom:0;"><i class="fa fa-chart-area"></i> <?= isset($_GET['filter']) ? "Logs Terfilter" : "Analytics (Last 50)" ?></div>
                    <form method="GET" action="" class="filter-inline-form">
                        <div class="input-box"><span>Dari:</span><input type="datetime-local" name="tgl_mulai" value="<?= date('Y-m-d\TH:i', strtotime($tgl_mulai)) ?>" required></div>
                        <div class="input-box"><span>Sampai:</span><input type="datetime-local" name="tgl_selesai" value="<?= date('Y-m-d\TH:i', strtotime($tgl_selesai)) ?>" required></div>
                        <button type="submit" name="filter" class="btn-action"><i class="fa fa-magnifying-glass"></i> Cari</button>
                        <?php if(isset($_GET['filter'])): ?><a href="dashboard.php" class="btn-reset"><i class="fa fa-arrows-rotate"></i> Reset</a><?php endif; ?>
                    </form>
                    <div class="export-buttons">
                        <button onclick="exportToExcel()" class="btn-export btn-excel-color"><i class="fa fa-file-excel"></i> Excel</button>
                        <button onclick="exportToPDF()" class="btn-export btn-pdf-color"><i class="fa fa-file-pdf"></i> PDF</button>
                    </div>
                </div>
            </div>
            <div class="chart-body-row">
                <div class="chart-content-wrapper">
                    <?php if(empty($data)): ?>
                        <div style="text-align:center; color: var(--text-muted); padding-top: 100px; font-size:12px;">Tidak ada rekaman data sensor pada rentang tanggal tersebut.</div>
                    <?php else: ?>
                        <canvas id="chart"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let data = <?= json_encode($data) ?>;
let myChart;

if (data.length > 0) {
    const ctx = document.getElementById('chart').getContext('2d');
    myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.time),
            datasets: [
                {label:'Suhu (°C)', data:data.map(d=>d.suhu), borderColor:'#475569', backgroundColor:'rgba(71, 85, 105, 0.01)', fill:true, tension:.2, borderWidth: 2, radius: 2},
                {label:'Kelembaban (%)', data:data.map(d=>d.hum), borderColor:'#64748b', backgroundColor:'rgba(100, 116, 139, 0.01)', fill:true, tension:.2, borderWidth: 2, radius: 2},
                {label:'PIR', data:data.map(d=>d.pir), borderColor:'#94a3b8', stepped:true, borderWidth: 1.5, radius: 0},
                {label:'Sound', data:data.map(d=>d.sound), borderColor:'#cbd5e1', stepped:true, borderWidth: 1.5, radius: 0},
                {label:'Rain', data:data.map(d=>d.rain), borderColor:'#94a3b8', borderDash: [3, 3], stepped:true, borderWidth: 1.5, radius: 0}
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, animation: false, plugins: { legend: { position: 'top', labels: { color:'#475569', boxWidth: 10, padding: 8, font: { family: "'Inter', sans-serif", size: 10 } } } }, scales: { x: { grid: { color: '#f1f5f9' }, ticks: { color: '#64748b', font: { family: "'Inter', sans-serif", size: 9 }, maxRotation: 0 } }, y: { grid: { color: '#e2e8f0' }, ticks: { color: '#64748b', font: { family: "'Inter', sans-serif", size: 9 } } } } }
    });
}

function autoUpdate() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('filter')) return;

    // Tambahkan timestamp agar browser tidak mengambil data dari cache
    fetch('dashboard.php?ajax=1&t=' + new Date().getTime())
        .then(response => response.json())
        .then(newData => {
            data = newData;
            // DEFINISI LATEST HARUS DI SINI
            const latest = newData[newData.length - 1];
            
            // Update Teks Dasar
            document.getElementById('val-suhu').innerText = latest.suhu + ' °C';
            document.getElementById('val-hum').innerText = latest.hum + ' %';
            document.getElementById('val-time').innerText = latest.time;
            
            // Update Sensor PIR, Sound, Rain
            document.getElementById('val-pir').innerText = latest.pir ? 'ALERT' : 'OK';
            document.getElementById('val-pir').className = 'node-value badge-status ' + (latest.pir ? 'alarm' : 'ok');

            document.getElementById('val-sound').innerText = latest.sound ? 'ALERT' : 'OK';
            document.getElementById('val-sound').className = 'node-value badge-status ' + (latest.sound ? 'alarm' : 'ok');

            document.getElementById('val-rain').innerText = (latest.rain == 0 ? 'WET' : 'DRY');
            document.getElementById('val-rain').className = 'node-value badge-status ' + (latest.rain == 0 ? 'alarm' : 'ok');
            
            // Update Status Badge (Alarm Utama)
            const badge = document.getElementById('status-badge');
            badge.className = 'status-badge ' + (latest.alarm ? 'status-alarm' : 'status-ok');
            document.getElementById('status-text').innerText = latest.alarm ? 'ALARM' : 'AMAN';
            document.getElementById('status-icon').className = 'fa ' + (latest.alarm ? 'fa-triangle-exclamation' : 'fa-circle-check');
            
            // Update Chart
            if (myChart) {
                myChart.data.labels = newData.map(d => d.time);
                myChart.data.datasets[0].data = newData.map(d => d.suhu);
                myChart.data.datasets[1].data = newData.map(d => d.hum);
                myChart.data.datasets[2].data = newData.map(d => d.pir);
                myChart.data.datasets[3].data = newData.map(d => d.sound);
                myChart.data.datasets[4].data = newData.map(d => d.rain);
                myChart.update('none');
            }
        })
        .catch(error => console.error('Error fetching data:', error));
}

setInterval(autoUpdate, 5000);

function exportToExcel() {
    if(data.length === 0) return alert('Tidak ada data.');
    let csvContent = "data:text/csv;charset=utf-8,\uFEFFTanggal,Suhu,Hum,PIR,Sound,Rain,Alarm\n";
    data.forEach(r => csvContent += `${r.time},${r.suhu},${r.hum},${r.pir},${r.sound},${r.rain},${r.alarm}\n`);
    const link = document.createElement("a");
    link.href = encodeURI(csvContent);
    link.download = "Log_ServerRoom.csv";
    link.click();
}

function exportToPDF() {
    if(data.length === 0) return alert('Tidak ada data.');
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.text("SHANDY LAB REPORT", 14, 15);
    let y = 30;
    data.forEach(r => { doc.text(`${r.time} | Suhu: ${r.suhu}C | Hum: ${r.hum}%`, 14, y); y += 7; });
    doc.save("Log_ServerRoom.pdf");
}
</script>
</body>
</html>