/*****************************************************************************************
 * FIRMWARE: HYBRID WEB-BASED IOT CONTROL SYSTEM - USV (UNMANNED SURFACE VEHICLE)
 * TRANSPORT: WebSocket Server native (port 81) - TIDAK pakai Blynk.
 * Browser (Laravel Blade) connect langsung ws://[ESP32_IP]:81 untuk kontrol
 * realtime, dan menerima broadcast telemetri baterai dalam format JSON.
 *
 * LIBRARY YANG WAJIB DIINSTALL (Library Manager Arduino IDE):
 *  - WebSockets by Markus Sattler (arduinoWebSockets)
 *  - ESP32Servo
 *  - ArduinoJson (untuk parsing perintah dari browser)
 *****************************************************************************************/

#include <WiFi.h>
#include <WebSocketsServer.h>
#include <ESP32Servo.h>
#include <ArduinoJson.h>

//========================================================================================
// KREDENSIAL WIFI LOKAL (SAMA JARINGAN DENGAN LAPTOP LARAGON)
//========================================================================================
const char* WIFI_SSID = "Nama_SSID_WiFi_Anda";
const char* WIFI_PASS = "Password_WiFi_Anda";

//========================================================================================
// PEMETAAN PIN PERANGKAT KERAS (SAMA DENGAN MODUL REFERENSI)
//========================================================================================
const int PIN_ADC_VOLTAGE  = 36; // ADC1_CH0 - sinyal voltage divider baterai
const int PIN_PWM_MOTOR    = 25; // PWM kecepatan motor DC (ENA / ESC signal)
const int PIN_DIR_MOTOR_1  = 26; // Logika arah 1 (L298N IN1)
const int PIN_DIR_MOTOR_2  = 27; // Logika arah 2 (L298N IN2)
const int PIN_PWM_SERVO    = 18; // Sinyal PWM servo kemudi
const int PIN_LED_BUILTIN_ = 2;  // LED indikator status

//========================================================================================
// KONFIGURASI PWM MOTOR (LEDC API - PENGGANTI analogWrite() YANG TIDAK NATIVE DI ESP32)
// Catatan: modul referensi PDF memakai analogWrite() untuk motor - itu TIDAK BERLAKU
// pada ESP32 Arduino Core resmi. Fungsi yang benar adalah ledcAttach()/ledcWrite().
//========================================================================================
const int PWM_FREQ_MOTOR = 2000;   // 2kHz, sesuai rekomendasi 1-5kHz modul referensi
const int PWM_RES_MOTOR  = 8;      // 8-bit -> rentang 0-255

//========================================================================================
// KALIBRASI SENSOR TEGANGAN BATERAI (VOLTAGE DIVIDER, LiPo 3S)
//========================================================================================
const float FAKTOR_KALIBRASI   = 4.4191;  // Sesuaikan dengan hasil kalibrasi multimeter
const float EMA_ALPHA          = 0.10;    // Smoothing filter (0-1, makin kecil makin halus)
const float TEGANGAN_BAT_MAKS  = 12.60;
const float TEGANGAN_BAT_MIN   = 11.10;
const float TEGANGAN_BAT_WARN  = 10.80;

//========================================================================================
// OBJEK GLOBAL
//========================================================================================
WebSocketsServer webSocket(81);
Servo rudderServo;

bool  statusSistemAktif    = false;
bool  statusBateraiLemah   = false;
int   inputSpeedTarget     = 0;
int   inputAngleTarget     = 90;
float nilaiTeganganFiltered = 12.0;
int   hitungPersentaseBaterai = 100;
String stringStatusPerahu  = "SIAGA";

unsigned long lastTelemetryMs = 0;
const unsigned long TELEMETRY_INTERVAL_MS = 1000;

// Fail-safe: kalau tidak ada client WS aktif, motor & servo wajib netral
bool adaClientAktif = false;

//========================================================================================
// FUNGSI KENDALI MOTOR (DIPANGGIL SETIAP ADA PERUBAHAN SPEED/START/STOP)
//========================================================================================
void eksekusiKendaliMotor() {
    if (!statusSistemAktif || !adaClientAktif) {
        ledcWrite(PIN_PWM_MOTOR, 0);
        digitalWrite(PIN_DIR_MOTOR_1, LOW);
        digitalWrite(PIN_DIR_MOTOR_2, LOW);
        return;
    }

    int pwmOut = inputSpeedTarget;

    // Proteksi: batasi daya ke 40% kalau baterai lemah
    if (statusBateraiLemah) {
        pwmOut = (pwmOut * 40) / 100;
    }

    ledcWrite(PIN_PWM_MOTOR, pwmOut);

    if (pwmOut > 5) {
        digitalWrite(PIN_DIR_MOTOR_1, HIGH);
        digitalWrite(PIN_DIR_MOTOR_2, LOW);
    } else {
        digitalWrite(PIN_DIR_MOTOR_1, LOW);
        digitalWrite(PIN_DIR_MOTOR_2, LOW);
    }
}

void failsafeBerhenti() {
    statusSistemAktif = false;
    ledcWrite(PIN_PWM_MOTOR, 0);
    digitalWrite(PIN_DIR_MOTOR_1, LOW);
    digitalWrite(PIN_DIR_MOTOR_2, LOW);
    rudderServo.write(90);
    digitalWrite(PIN_LED_BUILTIN_, LOW);
}

//========================================================================================
// PARSING PERINTAH DARI BROWSER (JSON MASUK LEWAT WEBSOCKET)
// Format yang didukung:
//   {"cmd":"start"}
//   {"cmd":"stop"}
//   {"cmd":"speed","value":170}
//   {"cmd":"steer","value":45}
//========================================================================================
void handleIncomingCommand(uint8_t num, uint8_t* payload, size_t length) {
    StaticJsonDocument<200> doc;
    DeserializationError err = deserializeJson(doc, payload, length);
    if (err) {
        Serial.print("[WS] JSON parse error: ");
        Serial.println(err.c_str());
        return;
    }

    const char* cmd = doc["cmd"];
    if (cmd == nullptr) return;

    if (strcmp(cmd, "start") == 0) {
        statusSistemAktif = true;
        stringStatusPerahu = "RUNNING";
        digitalWrite(PIN_LED_BUILTIN_, HIGH);
        rudderServo.write(inputAngleTarget);
        eksekusiKendaliMotor();
        Serial.println("[CMD] START diterima.");

    } else if (strcmp(cmd, "stop") == 0) {
        failsafeBerhenti();
        stringStatusPerahu = "EMERGENCY STOP";
        Serial.println("[CMD] EMERGENCY STOP diterima.");

    } else if (strcmp(cmd, "speed") == 0) {
        int v = doc["value"] | 0;
        v = constrain(v, 0, 255);
        inputSpeedTarget = v;
        eksekusiKendaliMotor();

    } else if (strcmp(cmd, "steer") == 0) {
        int v = doc["value"] | 90;
        v = constrain(v, 0, 180);
        inputAngleTarget = v;
        if (statusSistemAktif && !statusBateraiLemah) {
            rudderServo.write(inputAngleTarget);
        }
    }
}

//========================================================================================
// EVENT HANDLER WEBSOCKET (KONEKSI/PUTUS/PESAN MASUK)
//========================================================================================
void onWebSocketEvent(uint8_t num, WStype_t type, uint8_t* payload, size_t length) {
    switch (type) {
        case WStype_DISCONNECTED:
            Serial.printf("[WS] Client #%u disconnected\n", num);
            if (webSocket.connectedClients() == 0) {
                adaClientAktif = false;
                // FAIL-SAFE UTAMA: begitu tidak ada browser yang terhubung,
                // motor dan servo WAJIB berhenti seketika.
                failsafeBerhenti();
                Serial.println("[FAILSAFE] Semua client terputus. Motor & servo dinetralkan.");
            }
            break;

        case WStype_CONNECTED:
            adaClientAktif = true;
            Serial.printf("[WS] Client #%u connected\n", num);
            break;

        case WStype_TEXT:
            handleIncomingCommand(num, payload, length);
            break;

        default:
            break;
    }
}

//========================================================================================
// TELEMETRI: BACA ADC, FILTER EMA, BROADCAST JSON KE SEMUA CLIENT WS
//========================================================================================
void rutinTelemetriBaterai() {
    int nilaiMentahADC = analogRead(PIN_ADC_VOLTAGE);
    float voltPin = (nilaiMentahADC * 3.30f) / 4095.0f;
    float voltAktual = voltPin * FAKTOR_KALIBRASI;

    nilaiTeganganFiltered = (EMA_ALPHA * voltAktual) + ((1.0f - EMA_ALPHA) * nilaiTeganganFiltered);

    if (nilaiTeganganFiltered < 1.0f) {
        nilaiTeganganFiltered = 0.0f;
        stringStatusPerahu = "ERROR SENSOR";
    }

    float rentangSisa = nilaiTeganganFiltered - TEGANGAN_BAT_MIN;
    float rentangTotal = TEGANGAN_BAT_MAKS - TEGANGAN_BAT_MIN;
    hitungPersentaseBaterai = (int)((rentangSisa / rentangTotal) * 100);
    hitungPersentaseBaterai = constrain(hitungPersentaseBaterai, 0, 100);

    if (nilaiTeganganFiltered >= TEGANGAN_BAT_MIN) {
        statusBateraiLemah = false;
        if (statusSistemAktif) stringStatusPerahu = "NORMAL";
    } else if (nilaiTeganganFiltered >= TEGANGAN_BAT_WARN) {
        statusBateraiLemah = true;
        stringStatusPerahu = "LOW BATTERY";
        eksekusiKendaliMotor();
    } else if (nilaiTeganganFiltered > 1.0f) {
        statusBateraiLemah = true;
        stringStatusPerahu = "BATTERY CRITICAL";
        failsafeBerhenti();
    }

    // Susun payload JSON ringan untuk dikirim ke semua browser terhubung
    StaticJsonDocument<200> doc;
    doc["v"] = round(nilaiTeganganFiltered * 100) / 100.0;
    doc["p"] = hitungPersentaseBaterai;
    doc["status"] = stringStatusPerahu;
    doc["rssi"] = WiFi.RSSI();

    String out;
    serializeJson(doc, out);
    webSocket.broadcastTXT(out);

    Serial.print("[TELEMETRI] "); Serial.println(out);
}

//========================================================================================
// SETUP
//========================================================================================
void setup() {
    Serial.begin(115200);
    delay(300);
    Serial.println("\n=== BOOTING FIRMWARE USV WEBSOCKET CONTROLLER ===");

    pinMode(PIN_ADC_VOLTAGE, INPUT);
    pinMode(PIN_DIR_MOTOR_1, OUTPUT);
    pinMode(PIN_DIR_MOTOR_2, OUTPUT);
    pinMode(PIN_LED_BUILTIN_, OUTPUT);

    // Amankan kondisi awal driver motor sebelum WiFi/WS aktif
    digitalWrite(PIN_DIR_MOTOR_1, LOW);
    digitalWrite(PIN_DIR_MOTOR_2, LOW);

    // Setup PWM motor pakai LEDC (API resmi ESP32, BUKAN analogWrite)
    ledcAttach(PIN_PWM_MOTOR, PWM_FREQ_MOTOR, PWM_RES_MOTOR);
    ledcWrite(PIN_PWM_MOTOR, 0);

    // Setup servo kemudi
    ESP32PWM::allocateTimer(0);
    rudderServo.setPeriodHertz(50);
    rudderServo.attach(PIN_PWM_SERVO, 500, 2400);
    rudderServo.write(90);

    // Koneksi WiFi lokal
    WiFi.mode(WIFI_STA);
    WiFi.begin(WIFI_SSID, WIFI_PASS);
    Serial.print("[NET] Menghubungkan ke WiFi");
    while (WiFi.status() != WL_CONNECTED) {
        delay(400);
        Serial.print(".");
    }
    Serial.println();
    Serial.print("[NET] Terhubung. IP Address: ");
    Serial.println(WiFi.localIP());
    Serial.println("[NET] Gunakan IP ini di kolom 'ESP32 IP' pada dashboard Laravel.");

    // Mulai WebSocket server di port 81
    webSocket.begin();
    webSocket.onEvent(onWebSocketEvent);

    Serial.println("[SYSTEM] Inisialisasi selesai. Menunggu koneksi client...");
}

//========================================================================================
// LOOP UTAMA
//========================================================================================
void loop() {
    webSocket.loop();

    unsigned long now = millis();
    if (now - lastTelemetryMs >= TELEMETRY_INTERVAL_MS) {
        lastTelemetryMs = now;
        rutinTelemetriBaterai();
    }

    // Fail-safe tambahan: kalau WiFi putus total di tengah operasi, paksa berhenti.
    // WebSocket tidak akan bisa broadcast tanpa WiFi, jadi ini lapisan proteksi kedua
    // selain fail-safe "client disconnect" di atas.
    if (WiFi.status() != WL_CONNECTED && statusSistemAktif) {
        failsafeBerhenti();
        Serial.println("[FAILSAFE] WiFi terputus! Motor & servo dinetralkan.");
    }
}
