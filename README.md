IoT Server Room Temperature & Monitoring System

Sistem pemantauan lingkungan ruang server berbasis IoT (Internet of Things) menggunakan ESP32. Proyek ini dirancang untuk memantau suhu, kelembapan, dan potensi kebocoran/cairan secara real-time, lengkap dengan indikator lokal, web dashboard, serta otomatisasi pengiriman peringatan darurat (alert) ke Telegram.
🚀 Fitur Utama

    Monitoring Lingkungan: Membaca suhu dan kelembapan secara akurat menggunakan sensor DHT22.

    Deteksi Dini Kebocoran: Dilengkapi modul Sensor Hujan untuk mendeteksi rembesan air di area kritis ruang server.

    Notifikasi Telegram Otomatis: Mengirimkan pesan instan ketika suhu atau kelembapan melewati ambang batas aman, atau saat terdeteksi cairan.

    Indikator Lokal: Menampilkan informasi secara langsung via LCD I2C 16x2, LED Indikator, dan Buzzer.

    Web Dashboard & API: Menyediakan antarmuka berbasis web untuk memantau data sensor secara real-time dari jarak jauh.

🛠️ Komponen & Perangkat Keras (Hardware)

    Mikrokontroler: ESP32 Dev Module

    Sensor Suhu & Kelembapan: DHT22

    Sensor Hujan / Air: Rain Sensor Module

    Display: LCD 16x2 with I2C Interface

    Indikator & Alarm: LED RGB (Merah, Hijau, Biru) & Active Buzzer

📂 Struktur Repository
Plaintext

├── firmware/
│   └── esp32_monitoring.ino   # Kode program utama untuk mikrokontroler ESP32
├── serverroom/
│   ├── dashboard.php          # Antarmuka web dashboard pemantauan
│   ├── api_realtime.php       # Endpoint API untuk data sensor
│   └── ...                    # File pendukung web lainnya
└── README.md                  # Dokumentasi proyek

⚙️ Cara Konfigurasi & Menjalankan
1. Bagian Firmware (ESP32)

    Buka file firmware/esp32_monitoring.ino menggunakan Arduino IDE.

    Pastikan Anda sudah menginstal library: LiquidCrystal_I2C, DHT sensor library, dan UniversalTelegramBot.

    Sesuaikan kredensial jaringan WiFi, Token Telegram, Chat ID, serta URL server Anda pada baris kode berikut:
    C++

    const char* ssid     = "NAMA_WIFI_ANDA";
    const char* password = "PASSWORD_WIFI_ANDA";

    const char* botToken = "TOKEN_TELEGRAM_ANDA";
    const char* chatID   = "CHAT_ID_ANDA";

    const char* serverURL = "http://IP_SERVER_ANDA/monitoring/serverroom/dashboard.php";

    Hubungkan ESP32 ke komputer, lalu klik Upload.

2. Bagian Web Dashboard

    Letakkan seluruh file yang ada di dalam folder serverroom/ ke direktori server lokal Anda (misalnya htdocs/monitoring/serverroom/ pada XAMPP).

    Akses halaman dashboard melalui peramban web (browser) sesuai dengan alamat IP lokal server Anda.
