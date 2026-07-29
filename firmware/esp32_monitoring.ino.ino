#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <DHT.h>
#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <HTTPClient.h>
#include <UniversalTelegramBot.h>

// ================= PIN & CONFIG =================
#define DHT_PIN     14
#define RAIN_PIN    26
#define BUZZER_PIN  25
#define LED_R       32
#define LED_G       33
#define LED_B       4
#define DHTTYPE     DHT22

DHT dht(DHT_PIN, DHTTYPE);
LiquidCrystal_I2C lcd(0x27, 16, 2);

// NOTE: Jika ingin di-upload ke GitHub, disarankan mengganti kredensial ini 
// dengan placeholder (misal: "GANTI_WIFI") agar data aman.
const char* ssid     = "ganti nama wifi";
const char* password = "ganti password wifi";

const char* botToken = "ganti token telegram";
const char* chatID   = "ganti chatid telegram";

WiFiClientSecure tgClient;
UniversalTelegramBot bot(botToken, tgClient);

const char* serverURL = "http://gantiserverURLanda";

unsigned long lastWebsite = 0;
unsigned long lastLCD     = 0;
const unsigned long WEBSITE_INTERVAL = 60000; // 1 Menit
const unsigned long LCD_INTERVAL     = 2000;  // 2 Detik

bool alarmNotified = false;

void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println("\n--- [STARTUP] ---");

  pinMode(RAIN_PIN, INPUT_PULLUP);
  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(LED_R, OUTPUT);
  pinMode(LED_G, OUTPUT);
  pinMode(LED_B, OUTPUT);

  dht.begin();
  lcd.init();
  lcd.backlight();
  lcd.print("Connecting WiFi...");
  
  Serial.print("Menghubungkan ke WiFi: ");
  Serial.println(ssid);

  WiFi.begin(ssid, password);
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  
  Serial.println("\n[INFO] WiFi Terhubung!");
  Serial.print("[INFO] IP Address: ");
  Serial.println(WiFi.localIP());

  lcd.clear();
  lcd.print("WiFi Connected!");
  delay(1000);
  
  tgClient.setInsecure();
  Serial.println("[INFO] Mengirim notifikasi Online ke Telegram...");
  bot.sendMessage(chatID, "✅ ESP32 SERVER ROOM ONLINE", "");
}

void loop() {
  unsigned long now = millis();

  // 1. BACA SENSOR
  float temp = dht.readTemperature();
  float hum  = dht.readHumidity();
  bool rainAlarm = (digitalRead(RAIN_PIN) == LOW);
  bool dhtError = isnan(temp) || isnan(hum);
  bool alarm = (!dhtError && (temp > 25.5 || hum > 70)) || rainAlarm;

  // DEBUG KE SERIAL (Tiap 5 detik)
  if (now % 5000 < 50) {
    Serial.println("--- STATUS ---");
    Serial.printf("Temp: %.2f C | Hum: %.2f %% | Rain: %s | Alarm: %s\n", 
                  temp, hum, rainAlarm ? "AIR" : "KERING", alarm ? "AKTIF" : "AMAN");
  }

  // 2. KIRIM KE WEB SERVER
  if (now - lastWebsite > WEBSITE_INTERVAL) {
    Serial.println("[WEB] Mencoba mengirim data ke server...");
    if (WiFi.status() == WL_CONNECTED) {
      HTTPClient http;
      String url = String(serverURL) + "?temp=" + String(temp, 1) + 
                   "&hum=" + String(hum, 0) + 
                   "&rain=" + String(rainAlarm ? 0 : 1) + 
                   "&alarm=" + String(alarm ? 1 : 0);
      
      http.begin(url);
      int httpCode = http.GET();
      
      if (httpCode > 0) {
        Serial.print("[WEB] Server Response: ");
        Serial.println(httpCode);
      } else {
        Serial.print("[WEB] ERROR: ");
        Serial.println(http.errorToString(httpCode).c_str());
      }
      http.end();
    } else {
      Serial.println("[WEB] Gagal: WiFi Terputus");
    }
    lastWebsite = now;
  }

  // 3. TELEGRAM NOTIFICATION (Alarm logic)
  if (alarm && !alarmNotified) {
    Serial.println("[TELEGRAM] Mengirim alert...");
    bot.sendMessage(chatID, "🚨 ALERT! Suhu/Hujan terdeteksi di Server Room!", "");
    alarmNotified = true;
  } else if (!alarm && alarmNotified) {
    Serial.println("[TELEGRAM] Mengirim notifikasi aman...");
    bot.sendMessage(chatID, "✅ Kondisi Server Room Kembali Normal", "");
    alarmNotified = false;
  }

  // 4. LCD & LED UPDATE
  if (now - lastLCD > LCD_INTERVAL) {
    lcd.setCursor(0,0);
    lcd.print("T:" + String(temp,1) + "C H:" + String(hum,0) + "%  ");
    lcd.setCursor(0,1);
    lcd.print(alarm ? "STATUS: ALARM   " : "STATUS: AMAN    ");
    
    digitalWrite(LED_R, alarm);
    digitalWrite(LED_G, !alarm);
    digitalWrite(BUZZER_PIN, alarm);
    
    lastLCD = now;
  }

  delay(20);
}