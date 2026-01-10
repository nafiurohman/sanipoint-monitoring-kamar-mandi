#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <HTTPClient.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <SPI.h>
#include <MFRC522.h>
#include <ESP32Servo.h>
#include <WiFiManager.h>
#include <ArduinoJson.h>

// --- DAFTAR NADA (FREKUENSI) ---
#define NOTE_C4  262
#define NOTE_D4  294
#define NOTE_E4  330
#define NOTE_F4  349
#define NOTE_G4  392
#define NOTE_A4  440
#define NOTE_B4  494
#define NOTE_C5  523
#define NOTE_E5  659
#define NOTE_G5  784
#define NOTE_C6  1047

// --- KONFIGURASI ENDPOINT HTTPS ---
const char* updateStatusURL = "https://storage-241204-250305.beznlabs.web.id/sanipoint-api/update_status.php";
const char* rfidTapURL = "https://storage-241204-250305.beznlabs.web.id/sanipoint-api/rfid_tap.php";
const char* getStatusURL = "https://storage-241204-250305.beznlabs.web.id/sanipoint-api/get_status.php";

// --- PIN HARDWARE ---
#define IR_T1 16   
#define IR_T2 26   
#define SERVO_1 13 
#define SERVO_2 27 
#define MQ_PIN 32    
#define BUZZER 5   
#define SS_PIN 15  
#define RST_PIN 4  

// --- VARIABEL SISTEM ---
int count1 = 0, count2 = 0;
const int GAS_LIMIT = 1800;
bool alarmActive = false;

unsigned long lastWebUpdate = 0;
unsigned long lastScreenToggle = 0;
unsigned long lastDetected1 = 0;
unsigned long lastDetected2 = 0;
unsigned long lastAlarmBeep = 0;
unsigned long lastStatusSync = 0;
int screenState = 0; 

Adafruit_SSD1306 display(128, 64, &Wire, -1);
MFRC522 rfid(SS_PIN, RST_PIN);
Servo s1, s2;

// Fungsi pembantu untuk kirim data
String sendDataToWeb(const char* url, String postData);
void syncWithServer();

void setup() {
  Serial.begin(115200);
  
  pinMode(BUZZER, OUTPUT); 
  digitalWrite(BUZZER, HIGH); 
  
  Wire.begin(21, 22);
  display.begin(SSD1306_SWITCHCAPVCC, 0x3C);
  
  SPI.begin(18, 19, 23, 15);
  rfid.PCD_Init();
  
  pinMode(IR_T1, INPUT_PULLUP); 
  pinMode(IR_T2, INPUT_PULLUP);
  
  s1.attach(SERVO_1); 
  s2.attach(SERVO_2);
  s1.write(90); 
  s2.write(90);

  // Melodi Startup (C-E-G)
  tone(BUZZER, NOTE_C5, 100); delay(100);
  tone(BUZZER, NOTE_E5, 100); delay(100);
  tone(BUZZER, NOTE_G5, 100); delay(100);
  noTone(BUZZER);

  WiFiManager wm;
  if (!wm.autoConnect("Sanipoint_Setup")) {
    ESP.restart();
  }

  // Melodi WiFi Connect (G-C tinggi)
  tone(BUZZER, NOTE_G5, 100); delay(100);
  tone(BUZZER, NOTE_C6, 200); delay(200);
  noTone(BUZZER);
  
  Serial.println("WiFi connected!");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
  
  // Initial sync with server
  syncWithServer();
}

void loop() {
  unsigned long currentMillis = millis();
  int gasValue = analogRead(MQ_PIN);

  // Update status ke server setiap 10 detik
  if (currentMillis - lastWebUpdate > 10000) {
    updateToiletStatus(1, count1, gasValue);
    updateToiletStatus(2, count2, gasValue);
    lastWebUpdate = currentMillis;
  }
  
  // Sync dengan server setiap 30 detik
  if (currentMillis - lastStatusSync > 30000) {
    syncWithServer();
    lastStatusSync = currentMillis;
  }

  alarmActive = (count1 >= 5 || count2 >= 5 || gasValue > GAS_LIMIT);
  handleHardware(gasValue);
  handleOLED(gasValue);
  yield();
}

void updateToiletStatus(int toiletId, int count, int gasLevel) {
  String postData = "toilet_id=" + String(toiletId) + 
                   "&count=" + String(count) + 
                   "&gas_level=" + String(gasLevel) + 
                   "&is_locked=" + String(alarmActive ? 1 : 0);
  
  String response = sendDataToWeb(updateStatusURL, postData);
  
  if (response != "ERR" && response != "OFFLINE") {
    Serial.println("Status updated for Toilet " + String(toiletId));
  } else {
    Serial.println("Failed to update status for Toilet " + String(toiletId));
  }
}

void handleRFIDTap(String uid) {
  String postData = "uid=" + uid + "&toilet_id=1"; // Default toilet 1
  String response = sendDataToWeb(rfidTapURL, postData);
  
  Serial.println("RFID Response: " + response);
  
  // Parse JSON response
  DynamicJsonDocument doc(1024);
  DeserializationError error = deserializeJson(doc, response);
  
  if (!error && doc["success"]) {
    String action = doc["action"];
    if (action == "reset" || uid == "B490FBB0" || uid == "C6861BFF") {
      // Admin reset
      count1 = 0; 
      count2 = 0; 
      alarmActive = false;
      // Nada Sukses (C5 - G5)
      tone(BUZZER, NOTE_C5, 150); delay(150);
      tone(BUZZER, NOTE_G5, 150);
      Serial.println("System reset by admin");
    } else if (action == "start_cleaning" || action == "finish_cleaning") {
      // Employee cleaning
      tone(BUZZER, NOTE_E5, 100); delay(100);
      tone(BUZZER, NOTE_C6, 100);
      Serial.println("Cleaning action: " + action);
    }
  } else {
    // Nada Ditolak (G4 - D4 rendah)
    tone(BUZZER, NOTE_G4, 200); delay(200);
    tone(BUZZER, NOTE_D4, 400);
    Serial.println("RFID access denied");
  }
}

void syncWithServer() {
  String response = sendDataToWeb(getStatusURL, "");
  
  if (response != "ERR" && response != "OFFLINE") {
    // Parse server response untuk sinkronisasi status
    DynamicJsonDocument doc(2048);
    DeserializationError error = deserializeJson(doc, response);
    
    if (!error && doc["success"]) {
      Serial.println("Server sync successful");
      // Bisa tambahkan logic untuk sync status dari server
    }
  }
}

void handleHardware(int gas) {
  // Toilet 1
  if (digitalRead(IR_T1) == LOW && !alarmActive && count1 < 5) {
    if (millis() - lastDetected1 > 3000) {
      count1++; 
      lastDetected1 = millis();
      tone(BUZZER, NOTE_C6, 50); // Blip pendek saat deteksi
      s1.write(170); 
      delay(2000); 
      s1.write(90);
      Serial.println("Toilet 1 visitor detected. Count: " + String(count1));
    }
  }

  // Toilet 2
  if (digitalRead(IR_T2) == LOW && !alarmActive && count2 < 5) {
    if (millis() - lastDetected2 > 3000) {
      count2++; 
      lastDetected2 = millis();
      tone(BUZZER, NOTE_C6, 50); // Blip pendek saat deteksi
      s2.write(10); 
      delay(2000); 
      s2.write(90);
      Serial.println("Toilet 2 visitor detected. Count: " + String(count2));
    }
  }

  // RFID
  if (rfid.PICC_IsNewCardPresent() && rfid.PICC_ReadCardSerial()) {
    String uid = "";
    for (byte i = 0; i < rfid.uid.size; i++) {
      uid += String(rfid.uid.uidByte[i] < 0x10 ? "0" : "");
      uid += String(rfid.uid.uidByte[i], HEX);
    }
    uid.toUpperCase();
    
    Serial.println("RFID detected: " + uid);
    handleRFIDTap(uid);
    
    delay(400); 
    noTone(BUZZER);
    rfid.PICC_HaltA(); 
    rfid.PCD_StopCrypto1();
  }

  // Alarm (Nada Siren Dua Ton)
  if (alarmActive) {
    if (millis() - lastAlarmBeep > 400) {
      static bool bState = false;
      // Berganti antara nada A4 dan E5 (seperti sirine ambulance profesional)
      if (bState) tone(BUZZER, 880); // A5
      else tone(BUZZER, 659);        // E5
      bState = !bState;
      lastAlarmBeep = millis();
    }
    // Lock servos when alarm active
    s1.write(90);
    s2.write(90);
  } else {
    noTone(BUZZER);
  }
}

void handleOLED(int gas) {
  if (millis() - lastScreenToggle > 5000) {
    screenState = !screenState;
    lastScreenToggle = millis();
  }
  
  display.clearDisplay();
  display.setTextColor(WHITE);
  
  if (screenState == 0) {
    // Screen 1: System Monitor
    display.setCursor(0, 0); 
    display.println("--- SANIPOINT IoT ---");
    display.drawLine(0, 10, 128, 10, WHITE);
    
    display.setCursor(0, 15); 
    display.print("T1: "); 
    display.print(count1); 
    display.println(count1 >= 5 ? " [MAINT]" : " [OK]");
    
    display.setCursor(0, 25); 
    display.print("T2: "); 
    display.print(count2); 
    display.println(count2 >= 5 ? " [MAINT]" : " [OK]");
    
    display.setCursor(0, 35); 
    display.print("Gas: "); 
    display.print(gas);
    display.println(gas > GAS_LIMIT ? " [HIGH]" : " ppm");
    
    display.setCursor(0, 45);
    if (WiFi.status() == WL_CONNECTED) {
      display.println("WiFi: Connected");
    } else {
      display.println("WiFi: Disconnected");
    }
    
  } else {
    // Screen 2: Network Info
    display.setCursor(0, 0); 
    display.println("--- NETWORK INFO ---");
    display.drawLine(0, 10, 128, 10, WHITE);
    
    display.setCursor(0, 15); 
    display.print("IP: ");
    display.println(WiFi.localIP());
    
    display.setCursor(0, 25); 
    display.print("SSID: ");
    display.println(WiFi.SSID());
    
    display.setCursor(0, 35);
    display.print("RSSI: ");
    display.print(WiFi.RSSI());
    display.println(" dBm");
    
    // Progress bar animation
    int bar = (millis() % 2000) / 16;
    display.drawRect(0, 50, 128, 6, WHITE);
    display.fillRect(2, 52, bar, 2, WHITE);
  }
  
  // Alarm overlay
  if (alarmActive) {
    display.fillRect(0, 56, 128, 8, WHITE);
    display.setTextColor(BLACK);
    display.setCursor(10, 57); 
    display.print("TAP ADMIN CARD!");
  }
  
  display.display();
}

String sendDataToWeb(const char* url, String postData) {
  if (WiFi.status() != WL_CONNECTED) {
    return "OFFLINE";
  }
  
  WiFiClientSecure client;
  client.setInsecure(); // Skip SSL verification for simplicity
  
  HTTPClient http;
  http.setTimeout(10000); // 10 second timeout
  http.begin(client, url);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  http.addHeader("User-Agent", "ESP32-Sanipoint-IoT/1.0");
  
  int httpResponseCode;
  if (postData.length() > 0) {
    httpResponseCode = http.POST(postData);
  } else {
    httpResponseCode = http.GET();
  }
  
  String payload = "ERR";
  if (httpResponseCode > 0) {
    payload = http.getString();
    Serial.println("HTTP Response: " + String(httpResponseCode));
    Serial.println("Payload: " + payload);
  } else {
    Serial.println("HTTP Error: " + String(httpResponseCode));
  }
  
  http.end();
  return payload;
}