```php
<?php
date_default_timezone_set('Asia/Jakarta');

/* ======================================
   SHANDY LAB SERVER
====================================== */

$hostname = gethostname();
$ip = $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname());

$php = phpversion();

$apache = $_SERVER['SERVER_SOFTWARE'];

$kernel = php_uname('r');

$os = php_uname('s');

$date = date("l, d F Y");

$time = date("H:i:s");
?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Shandy Lab Server</title>

<link rel="preconnect" href="https://fonts.googleapis.com">

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<div class="background"></div>

<div class="background2"></div>

<div class="container">

<header>

<div class="logo">

SLS

</div>

<h1>

Shandy Lab Server

</h1>

<p>

Ubuntu 22.04 Infrastructure Dashboard

</p>

<div class="status">

<span class="dot"></span>

ONLINE

</div>

</header>

<section class="dashboard">

<div class="card">

<h3>🖥 Hostname</h3>

<h2>

<?= $hostname ?>

</h2>

</div>

<div class="card">

<h3>🌐 Server IP</h3>

<h2>

<?= $ip ?>

</h2>

</div>

<div class="card">

<h3>🐘 PHP Version</h3>

<h2>

<?= $php ?>

</h2>

</div>

<div class="card">

<h3>⚙ Apache</h3>

<h2>

Running

</h2>

</div>

<div class="card">

<h3>🗄 Database</h3>

<h2>

MariaDB

</h2>

</div>

<div class="card">

<h3>💻 Operating System</h3>

<h2>

Ubuntu 22.04 LTS

</h2>

</div>

<div class="card">

<h3>🧠 Kernel</h3>

<h2>

<?= $kernel ?>

</h2>

</div>

<div class="card">

<h3>🕒 Server Time</h3>

<h2 id="clock">

<?= $time ?>

</h2>

</div>

</section>

<h2 class="title">

Applications

</h2>

<section class="applications">

<a href="/monitoring/serverroom/" class="app">

<div>

<h3>

📊 Monitoring Server Room

</h3>

<p>

Realtime monitoring dashboard

</p>

</div>

<span>

→

</span>

</a>

<a href="/inventory/" class="app">

<div>

<h3>

💻 Inventory IT

</h3>

<p>

Manage IT Assets

</p>

</div>

<span>

→

</span>

</a>

<a href="/backup/" class="app">

<div>

<h3>

💾 Backup Center

</h3>

<p>

Backup Management

</p>

</div>

<span>

→

</span>

</a>

<a href="/dokumentasi/" class="app">

<div>

<h3>

📄 Documentation

</h3>

<p>

Internal Documentation

</p>

</div>

<span>

→

</span>

</a>

<a href="/project/" class="app">

<div>

<h3>

📁 Project Lab

</h3>

<p>

Development Project

</p>

</div>

<span>

→

</span>

</a>

<a href="/about/" class="app">

<div>

<h3>

👨 About Server

</h3>

<p>

Server Information

</p>

</div>

<span>

→

</span>

</a>

</section>

<section class="about">

<h2>

About This Server

</h2>

<p>

Shandy Lab Server merupakan server internal berbasis Ubuntu Server 22.04 yang digunakan untuk pembelajaran Linux Server, Apache, PHP, MariaDB, Monitoring System, serta pengembangan aplikasi internal.

</p>

<div class="info">

<div>

<strong>Hostname</strong>

<span><?= $hostname ?></span>

</div>

<div>

<strong>IP Address</strong>

<span><?= $ip ?></span>

</div>

<div>

<strong>Operating System</strong>

<span><?= $os ?></span>

</div>

<div>

<strong>Apache</strong>

<span><?= $apache ?></span>

</div>

<div>

<strong>Date</strong>

<span><?= $date ?></span>

</div>

</div>

</section>

<footer>

<p>

Designed & Managed by

</p>

<h2>

Shandy

</h2>

<span>

IT Infrastructure • System Administrator

</span>

<p>

© <?= date("Y") ?> Shandy Lab Server

</p>

</footer>

</div>

<script src="assets/js/script.js"></script>

</body>

</html>
```
