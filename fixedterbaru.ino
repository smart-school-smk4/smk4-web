#include <WiFi.h>
#include <WiFiManager.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>
#include <Wire.h>
#include <DFRobotDFPlayerMini.h>
#include <RTClib.h>
#include <NTPClient.h>
#include <HTTPClient.h>
#include <PCF8575.h>
#include <driver/i2s.h>
#include <Preferences.h>

// ========== Debug Configuration ==========
#define SERIAL_DEBUG true
#define LOG(message) do { \
  if (SERIAL_DEBUG) { \
    Serial.flush(); \
    Serial.print("["); \
    Serial.print(millis()); \
    Serial.print("] "); \
    Serial.println(message); \
  } \
} while (0)

// ========== Server Configuration ==========
const char* VOICERSS_API_KEY = "90927de8275148d79080facd20fb486c";
const char* VOICERSS_URL = "http://api.voicerss.org/?key=%s&hl=%s&v=%s&c=WAV&f=22khz_16bit_mono&src=%s";

// ========== Configurable Parameters ==========
char mqtt_server[40] = "192.168.1.5";
char ip_server_laravel[40] = "192.168.110.10";

const char* IP_SERVER_LARAVEL = "192.168.110.10";
const int SERVER_PORT = 8000;
const char* API_ENDPOINT = "/api/bell-events";

// ========== MQTT Configuration ==========
const char* MQTT_SERVER = "192.168.1.5";
const int MQTT_PORT = 1883;
const char* MQTT_CLIENT_ID = "esp32_bel_sekolah";
const char* MQTT_USER = "";
const char* MQTT_PASSWORD = "";

// MQTT Topics
const char* TOPIC_COMMAND_STATUS = "bel/sekolah/command/status";
const char* TOPIC_COMMAND_RING = "bel/sekolah/command/ring";
const char* TOPIC_COMMAND_SYNC = "bel/sekolah/command/sync";
const char* TOPIC_RESPONSE_STATUS = "bel/sekolah/response/status";
const char* TOPIC_RESPONSE_ACK = "bel/sekolah/response/ack";
const char* TOPIC_EVENT_SCHEDULE = "bel/sekolah/events/schedule";
const char* TOPIC_EVENT_MANUAL = "bel/sekolah/events/manual";
const char* TOPIC_CONTROL_RELAY = "control/relay";
const char* TOPIC_TTS_PLAY = "tts/play";
const char* TOPIC_ANNOUNCEMENT_STATUS = "announcement/status";

// ========== Hardware Configuration ==========
#define RELAY_COUNT 64
#define I2C_SDA_PIN 8
#define I2C_SCL_PIN 9
#define DFPLAYER_RX_PIN 17
#define DFPLAYER_TX_PIN 18

// Konfigurasi I2S
#define I2S_BCK_PIN 12
#define I2S_WS_PIN 13
#define I2S_DOUT_PIN 14
#define SAMPLE_RATE 22050

// ========== Hardware Components ==========
WiFiClient espClient;
PubSubClient mqttClient(espClient);
DFRobotDFPlayerMini dfPlayer;
RTC_DS3231 rtc;
PCF8575 relayController;
PCF8575 relayController2;
PCF8575 relayController3;
PCF8575 relayController4;

void setupRTC(uint8_t i2cAddress);
void setupRelayController(uint8_t i2cAddress, int controllerNumber);

// ========== System State ==========
struct ActiveSchedule {
  int index = -1;
  unsigned long startTime = 0;
};

struct SystemState {
  bool wifiConnected = false;
  bool rtcConnected = false;
  bool dfPlayerConnected = false;
  bool mqttConnected = false;
  bool relayController1Connected = false;
  bool relayController2Connected = false;
  bool relayController3Connected = false;
  bool isPlaying = false;
  unsigned long lastCommunication = 0;
  unsigned long lastSync = 0;
  unsigned long lastNtpSync = 0;
  int scheduleCount = 0;
  bool isSchedulePlaying = false;
  unsigned long scheduleCooldownStart = 0;
  const unsigned long SCHEDULE_COOLDOWN = 60000; 
  int currentPlayingSchedule = -1;
  uint16_t relayStates1 = 0xFFFF; 
  uint16_t relayStates2 = 0xFFFF; 
  uint16_t relayStates3 = 0xFFFF;
  uint16_t relayStates4 = 0xFFFF;
  ActiveSchedule activeSchedules[3]; 
  unsigned long lastI2CCheck = 0;
  bool i2cStable = true;
  int i2cErrorCount = 0;
  unsigned long lastI2CRecovery = 0;
  bool relayController4Connected = false;
};

// ========== Global Variables ==========
bool isTTSPending = false;
String lastTTSPayload;
unsigned long streamStartTime = 0;
const unsigned long STREAM_TIMEOUT = 30000;
Preferences preferences;


// ========== Schedule Storage ==========
struct Schedule {
  String day;         // "Senin", "Selasa", etc.
  String time;        // "HH:MM"
  String fileNumber;  // "0001"
  int volume = 15;    // 0-30
  int repeat = 1;     // 1-5
  bool isActive = true;
};

SystemState state;
Schedule schedules[50]; // Max 50 schedules
int scheduleCount = 0;

// Konfigurasi I2S
i2s_config_t i2s_config = {
    .mode = (i2s_mode_t)(I2S_MODE_MASTER | I2S_MODE_TX),
    .sample_rate = 22050,
    .bits_per_sample = I2S_BITS_PER_SAMPLE_16BIT,
    .channel_format = I2S_CHANNEL_FMT_RIGHT_LEFT, // Stereo format, walau mono
    .communication_format = I2S_COMM_FORMAT_STAND_I2S,
    .intr_alloc_flags = ESP_INTR_FLAG_LEVEL1,
    .dma_buf_count = 16,           // 8 buffer
    .dma_buf_len = 512,          // 1024 samples per buffer
    .use_apll = false,            // Nonaktifkan APLL, gunakan PLL_D2_CLK
    .tx_desc_auto_clear = true,   // Auto clear TX descriptor
    .fixed_mclk = 0               // Biarkan kosong untuk clock default
};

i2s_pin_config_t pin_config = {
    .bck_io_num = I2S_BCK_PIN,
    .ws_io_num = I2S_WS_PIN,
    .data_out_num = I2S_DOUT_PIN,
    .data_in_num = I2S_PIN_NO_CHANGE
};

// ========== NTP Client ==========
WiFiUDP ntpUDP;
// Ganti di deklarasi NTPClient
NTPClient timeClient(ntpUDP, "id.pool.ntp.org", 7 * 3600, 60000); // UTC+7 dengan server Indonesia

// ========== Setup Functions ==========
void setup() {
  Serial.begin(115200);
  Serial.println("\n\n==== BOOT ====");
  LOG("Serial initialized");
  LOG("Starting School Bell System - ESP32-S3 N16R8");
  LOG("Firmware Version: 1.0.0");
  LOG("CPU Frequency: " + String(getCpuFrequencyMhz()) + "MHz");
  // Optimasi khusus ESP32-S3
  setupESP32S3();
  // Hardcode I2C address untuk PCF8575
  Wire.begin(I2C_SDA_PIN, I2C_SCL_PIN);
  Wire.setClock(100000);  
  Wire.setTimeOut(250);  
  setupRelayController(0x20, 1);  
  setupRelayController(0x21, 2);  
  setupRelayController(0x22, 3);  
  setupRelayController(0x23, 4);
  // Tetap coba setup RTC dengan scan
  setupRTC(0x68); // Coba address default DS3231 (0x68)
  scanI2CDevices();
  setupI2S();
  setupDFPlayer();
  setupWiFi();
  syncRTCWithNTP();
  setupMQTT();
  loadSchedulesFromPreferences();
  LOG("System initialization complete");
}


void setupESP32S3() {
  // Konfigurasi khusus untuk ESP32-S3
  
  WiFi.setTxPower(WIFI_POWER_19_5dBm); // Maksimal power untuk jangkauan lebih baik
  WiFi.setSleep(false); // Nonaktifkan sleep mode untuk koneksi lebih stabil
  
  // Atur clock CPU ke 240MHz untuk performa maksimal
  setCpuFrequencyMhz(240);
  LOG("CPU Frequency: " + String(getCpuFrequencyMhz()) + "MHz");
  
  // Enable brownout detector
  esp_sleep_enable_timer_wakeup(1);
}

void setupWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.setTxPower(WIFI_POWER_19_5dBm);
  WiFi.setAutoReconnect(true);
  WiFi.persistent(true);
  
  // Nonaktifkan power save mode
  esp_wifi_set_ps(WIFI_PS_NONE);
  WiFi.setSleep(false);
  
  // Baca konfigurasi yang tersimpan
  preferences.begin("wifi_config", true);
  String savedSSID = preferences.getString("ssid", "");
  String savedPass = preferences.getString("pass", "");
  String savedMqtt = preferences.getString("mqtt_server", "");
  String savedLaravel = preferences.getString("ip_server_laravel", "");
  preferences.end();

  if (savedMqtt.length() > 0) strcpy(mqtt_server, savedMqtt.c_str());
  if (savedLaravel.length() > 0) strcpy(ip_server_laravel, savedLaravel.c_str());

  // Jika sudah ada kredensial WiFi, coba konek langsung
  if (savedSSID.length() > 0) {
    WiFi.begin(savedSSID.c_str(), savedPass.c_str());
    
    unsigned long startAttemptTime = millis();
    while (WiFi.status() != WL_CONNECTED && millis() - startAttemptTime < 10000) {
      delay(100);
    }
    
    if (WiFi.status() == WL_CONNECTED) {
      state.wifiConnected = true;
      LOG("WiFi connected using saved credentials");
      LOG("IP Address: " + WiFi.localIP().toString());
      return;
    }
  }
  
  // Jika tidak berhasil, buka portal config
  startConfigPortal();
}

void startConfigPortal() {
  WiFiManager wifiManager;
  
  // Buat parameter custom
  WiFiManagerParameter custom_mqtt_server("mqtt", "MQTT Server", mqtt_server, 40);
  WiFiManagerParameter custom_laravel_server("laravel", "Laravel Server", ip_server_laravel, 40);
  
  wifiManager.addParameter(&custom_mqtt_server);
  wifiManager.addParameter(&custom_laravel_server);
  
  wifiManager.setConfigPortalTimeout(60);
  wifiManager.setConnectTimeout(30);
  
  if (!wifiManager.autoConnect("ESP32-S3-Bell-AP")) {
    LOG("Failed to connect and hit timeout");
    delay(3000);
    ESP.restart();
  }
  
  // Simpan parameter yang diinput
  strcpy(mqtt_server, custom_mqtt_server.getValue());
  strcpy(ip_server_laravel, custom_laravel_server.getValue());
  
  // Simpan ke preferences
  preferences.begin("wifi_config", false);
  preferences.putString("ssid", WiFi.SSID());
  preferences.putString("pass", WiFi.psk());
  preferences.putString("mqtt_server", mqtt_server);
  preferences.putString("ip_server_laravel", ip_server_laravel);
  preferences.end();
  
  state.wifiConnected = true;
  LOG("WiFi connected. IP: " + WiFi.localIP().toString());
  LOG("MQTT Server: " + String(mqtt_server));
  LOG("Laravel Server: " + String(ip_server_laravel));
}

void resetConfiguration() {
  preferences.begin("wifi_config", false);
  preferences.clear();
  preferences.end();
  
  LOG("Configuration reset. Restarting...");
  delay(1000);
  ESP.restart();
}

// Modified setupRTC to accept address
void setupRTC(uint8_t i2cAddress) {
  // Try DS3231 first
  rtc = RTC_DS3231();
  if (rtc.begin()) {
    state.rtcConnected = true;
    LOG("DS3231 RTC initialized at 0x" + String(i2cAddress, HEX));
    syncRTCWithNTP();
    return;
  }
  
  // If DS3231 fails, try other RTC types
  LOG("RTC not detected at 0x" + String(i2cAddress, HEX));
}

void scanI2CDevices() {
  byte error, address;
  int nDevices = 0;

  LOG("Scanning I2C bus...");
  for(address = 1; address < 127; address++) {
    Wire.beginTransmission(address);
    error = Wire.endTransmission();
    
    if (error == 0) {
      LOG("I2C device found at 0x" + String(address, HEX));
      nDevices++;
    } else if (error == 4) {
      LOG("Unknown error at 0x" + String(address, HEX));
    }
  }
  
  if (nDevices == 0) {
    LOG("No I2C devices found");
  } else {
    LOG("Found " + String(nDevices) + " I2C devices");
  }
}


void setupDFPlayer() {
  LOG("Initializing DFPlayer...");
  Serial2.begin(9600, SERIAL_8N1, DFPLAYER_RX_PIN, DFPLAYER_TX_PIN);
  
  LOG("Checking DFPlayer connection...");
  int retry = 0;
  while (!dfPlayer.begin(Serial2) && retry < 5) {
    LOG("DFPlayer not responding, retrying... (" + String(retry+1) + "/5)");
    retry++;
  }
  
  if (retry >= 5) {
    LOG("DFPlayer initialization FAILED!");
    LOG("Possible causes:");
    LOG("1. Wrong RX/TX wiring (harus cross: TX->RX, RX->TX)");
    LOG("2. Power insufficient (butuhkan 3.3V-5V stabil)");
    LOG("3. SD card tidak terdeteksi (format FAT32)");
    state.dfPlayerConnected = false;
    return;
  }
  
  state.dfPlayerConnected = true;
  dfPlayer.enableDAC();        // Aktifkan DAC output
  dfPlayer.volume(25);
  dfPlayer.outputDevice(DFPLAYER_DEVICE_SD);
  LOG("DFPlayer initialized successfully");
  LOG("Current volume: " + String(dfPlayer.readVolume()));
}

// setupRelayController to accept address
void setupRelayController(uint8_t i2cAddress, int controllerNumber) {
  int retryCount = 0;
  const int maxRetries = 3;
  while (retryCount < maxRetries) {
    if (controllerNumber == 1) {
      relayController = PCF8575(i2cAddress);
      if (relayController.begin()) {
        state.relayController1Connected = true;
        relayController.write16(0xFFFF);
        LOG("Relay controller 1 initialized at 0x" + String(i2cAddress, HEX));
        return;
      }
    } else if (controllerNumber == 2) {
      relayController2 = PCF8575(i2cAddress);
      if (relayController2.begin()) {
        state.relayController2Connected = true;
        relayController2.write16(0xFFFF);
        LOG("Relay controller 2 initialized at 0x" + String(i2cAddress, HEX));
        return;
      }
    } else if (controllerNumber == 3) {
      relayController3 = PCF8575(i2cAddress);
      if (relayController3.begin()) {
        state.relayController3Connected = true;
        relayController3.write16(0xFFFF);
        LOG("Relay controller 3 initialized at 0x" + String(i2cAddress, HEX));
        return;
      }
    } else if (controllerNumber == 4) {
      relayController4 = PCF8575(i2cAddress);
      if (relayController4.begin()) {
        state.relayController4Connected = true;
        relayController4.write16(0xFFFF);
        LOG("Relay controller 4 initialized at 0x" + String(i2cAddress, HEX));
        return;
      }
    }
    LOG("PCF8575 " + String(controllerNumber) + " tidak terdeteksi di 0x" + String(i2cAddress, HEX) + 
        ", retrying (" + String(retryCount+1) + "/" + String(maxRetries) + ")");
    retryCount++;
    delay(100);
  }
  // Jika sampai sini berarti gagal
  if (controllerNumber == 1) {
    state.relayController1Connected = false;
  } else if (controllerNumber == 2) {
    state.relayController2Connected = false;
  } else if (controllerNumber == 3) {
    state.relayController3Connected = false;
  } else if (controllerNumber == 4) {
    state.relayController4Connected = false;
  }
  LOG("Gagal menginisialisasi PCF8575 " + String(controllerNumber) + " setelah " + String(maxRetries) + " percobaan");
}

void setupI2S() {
    esp_err_t err;
    
    // 1. Uninstall driver hanya jika diperlukan (dengan penanganan error)
    err = i2s_driver_uninstall(I2S_NUM_0);
    if (err != ESP_OK && err != ESP_ERR_INVALID_STATE) {
        LOG("Gagal uninstall driver I2S. Error: " + String(err));
        // Lanjutkan saja karena mungkin belum terinstall
    }
    
    // 2. Set konfigurasi buffer baru
    i2s_config.dma_buf_count = 16;    
    i2s_config.dma_buf_len = 512;    
    
    // 3. Install driver
    err = i2s_driver_install(I2S_NUM_0, &i2s_config, 0, NULL);
    if (err != ESP_OK) {
        LOG("Gagal install driver I2S. Error: " + String(err));
        if (err == ESP_ERR_INVALID_ARG) {
            LOG("Invalid configuration");
        } 
        else if (err == ESP_ERR_NO_MEM) {
            LOG("Out of memory");
        }
        return;
    }

    // 4. Set pin configuration
    err = i2s_set_pin(I2S_NUM_0, &pin_config);
    if (err != ESP_OK) {
        LOG("Gagal set pin I2S. Error: " + String(err));
        i2s_driver_uninstall(I2S_NUM_0);
        return;
    }

    // 5. Set clock rate
    err = i2s_set_clk(I2S_NUM_0, SAMPLE_RATE, I2S_BITS_PER_SAMPLE_16BIT, I2S_CHANNEL_MONO);
    if (err != ESP_OK) {
        LOG("Gagal set clock I2S. Error: " + String(err));
        i2s_driver_uninstall(I2S_NUM_0);
        return;
    }

    // 6. Clear buffer
    i2s_zero_dma_buffer(I2S_NUM_0);

    // 7. Log informasi
    LOG("I2S driver berhasil diinisialisasi");
    LOG("Konfigurasi I2S Terbaru:");
    LOG("- Sample Rate: " + String(SAMPLE_RATE) + " Hz");
    LOG("- Bit Depth: 16-bit");
    LOG("- Channel: Mono");
    LOG("- DMA Buffers: " + String(i2s_config.dma_buf_count) + " buffers");
    LOG("- Samples per Buffer: " + String(i2s_config.dma_buf_len));
    LOG("- Total Buffer Size: " + String(i2s_config.dma_buf_count * i2s_config.dma_buf_len * 2) + " bytes");
}

void setupMQTT() {
  mqttClient.setServer(mqtt_server, MQTT_PORT); // Gunakan variabel yang bisa dikonfig
  mqttClient.setCallback([](char* topic, uint8_t* payload, unsigned int length) {
    mqttCallback(topic, payload, length);
  });
  mqttClient.setKeepAlive(60);
  mqttClient.setSocketTimeout(30);
  mqttClient.setBufferSize(4096);
}

// ========== Main Loop ==========
void loop() {
  maintainMQTTConnection();
  mqttClient.loop();

  static unsigned long lastSecondTick = 0;
  if (millis() - lastSecondTick >= 1000) {
    lastSecondTick = millis();
    checkSchedules();
  }

    // Periksa koneksi I2C setiap 5 detik
  if (millis() - state.lastI2CCheck > 30000) {
    state.lastI2CCheck = millis();
    
    bool controller1OK = checkI2CConnection(0x20);
    bool controller2OK = checkI2CConnection(0x21);
    bool controller3OK = checkI2CConnection(0x22);
    
    if (!controller1OK || !controller2OK) {
      state.i2cErrorCount++;
      state.i2cStable = false;
      
      // Jika error mencapai 3 kali, coba reset I2C
      if (state.i2cErrorCount >= 3) {
        recoverI2CBus();
        state.i2cErrorCount = 0;
      }
    } else {
      state.i2cStable = true;
    }
    
    logI2CStatus();
  }

  for (int i = 0; i < 3; i++) {
    if (state.activeSchedules[i].index != -1 && 
        millis() - state.activeSchedules[i].startTime >= 60000) {
      LOG("Resetting active schedule index: " + String(i));
      state.activeSchedules[i].index = -1;
    }
  }

  if (millis() - state.lastNtpSync > 24 * 60 * 60 * 1000) {
    LOG("Syncing RTC with NTP");
    syncRTCWithNTP();
  }

  state.lastCommunication = millis() / 1000;
}

// ========== Time Functions ==========
void syncRTCWithNTP() {
  if (WiFi.status() != WL_CONNECTED) {
    LOG("WiFi not connected for NTP sync");
    return;
  }
  
  // Perbaiki dengan menambahkan pengecekan waktu yang valid
  timeClient.forceUpdate();
  unsigned long epochTime = timeClient.getEpochTime();
  
  // Validasi waktu (harus antara tahun 2020-2030)
  if (epochTime > 1577836800 && epochTime < 1893456000) { // 2020-2030
    rtc.adjust(DateTime(epochTime));
    state.lastNtpSync = millis();
    LOG("RTC synced with NTP: " + getCurrentDateTime());
  } else {
    LOG("Invalid NTP time received: " + String(epochTime));
    LOG("Skipping RTC sync to prevent year 2036 problem");
  }
}

String getCurrentDateTime() {
  DateTime now = rtc.now();
  return String(now.year()) + "-" + 
         String(now.month()) + "-" + 
         String(now.day()) + " " + 
         formatTime(now.hour(), now.minute(), now.second());
}

String formatTime(int h, int m, int s) {
  char buf[9]; // Format HH:MM:SS
  sprintf(buf, "%02d:%02d:%02d", h, m, s);
  return String(buf);
}

String getDayName(int dayIndex) {
  const char* days[] = {"Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"};
  return String(days[dayIndex]);
}

// ========== MQTT Functions ==========
void maintainMQTTConnection() {
  if (!mqttClient.connected()) {
    reconnectMQTT();
  }
}

void reconnectMQTT() {
  static unsigned long lastAttempt = 0;
  const unsigned long retryInterval = 5000; // 5 seconds
  
  if (millis() - lastAttempt < retryInterval) {
    return;
  }
  
  lastAttempt = millis();
  LOG("Attempting MQTT connection...");
  
  if (mqttClient.connect(MQTT_CLIENT_ID, MQTT_USER, MQTT_PASSWORD)) {
    state.mqttConnected = true;
    subscribeToMQTTTopics();
    sendSystemStatus();
    LOG("MQTT connected and subscribed");
  } else {
    state.mqttConnected = false;
    LOG("MQTT connection failed, rc=" + String(mqttClient.state()));
  }
}

void subscribeToMQTTTopics() {
  mqttClient.subscribe(TOPIC_COMMAND_STATUS, 1);    // QoS 1
  mqttClient.subscribe(TOPIC_COMMAND_RING, 1);      // QoS 1
  mqttClient.subscribe("bel/sekolah/command/sync", 1); // QoS=1
  mqttClient.subscribe(TOPIC_RESPONSE_ACK, 1);      // QoS 1 - Critical for sync confirmation
  mqttClient.subscribe(TOPIC_RESPONSE_STATUS, 1);   // QoS 1
  mqttClient.subscribe(TOPIC_CONTROL_RELAY, 1);
  mqttClient.subscribe(TOPIC_TTS_PLAY, 1);
  LOG("Subscribed to all MQTT topics");
}

void mqttCallback(char* topic, uint8_t* payload, unsigned int length) {
  String message;
  for (int i = 0; i < length; i++) {
    message += (char)payload[i];
  }

  LOG("MQTT [" + String(topic) + "]: " + message);

  if (strcmp(topic, TOPIC_COMMAND_STATUS) == 0) {
    handleStatusRequest();
  } 
  else if (strcmp(topic, TOPIC_COMMAND_RING) == 0) {
    handleRingCommand(message);
  } 
  else if (strcmp(topic, TOPIC_COMMAND_SYNC) == 0) {
    handleScheduleSync(message);
  }
  else  if (strcmp(topic, TOPIC_CONTROL_RELAY) == 0) {
    handleRelayControl(message);
  } 
  else if (strcmp(topic, TOPIC_TTS_PLAY) == 0) {
    handleTTSPlay(message);
  }
}

// ========== Command Handlers ==========
void handleStatusRequest() {
  sendSystemStatus();
}

void handleRelayControl(String payload) {
  DynamicJsonDocument doc(256);
  DeserializationError error = deserializeJson(doc, payload);
  if (error) {
    LOG("Error parsing relay control: " + String(error.c_str()));
    return;
  }

  String action = doc["action"] | "";
  String mode = doc["mode"] | "manual";
  JsonArray ruangans = doc["ruang"].as<JsonArray>();

  if (action == "activate") {
    for (int room : ruangans) {
      if (room >= 0 && room < RELAY_COUNT) {
        setRelayState(room, true);
      }
    }
  } 
  else if (action == "deactivate") {
    for (int room : ruangans) {
      if (room >= 0 && room < RELAY_COUNT) {
        setRelayState(room, false);
      }
    }
  }

  if (mode != "tts") {
    sendRelayStatusUpdate();
  }
}

void handleTTSPlay(String payload) {
  lastTTSPayload = payload;
  DynamicJsonDocument doc(512);
  DeserializationError error = deserializeJson(doc, payload);
  if (error) {
    LOG("Error parsing TTS play: " + String(error.c_str()));
    return;
  }
  String text = doc["teks"] | "";
  if (text.isEmpty()) {
    LOG("Empty TTS text");
    return;
  }

  String lang = doc["hl"] | "id-id";
  String voice = doc["v"] | "intan";

  JsonArray ruangans = doc["ruang"].as<JsonArray>();

  // Nyalakan relay terlebih dahulu
  for (int room : ruangans) {
    if (room >= 0 && room < RELAY_COUNT) {
      setRelayState(room, true);
    }
  }
  sendRelayStatusUpdate();

  // Mulai streaming TTS
  playTTS(text, lang, voice);

  // Tunggu sampai pemutaran selesai
  unsigned long startTime = millis();
  while (state.isPlaying && millis() - startTime < STREAM_TIMEOUT) {
    delay(10); // Jangan boros CPU
  }

  // Setelah selesai, matikan relay
  for (int room : ruangans) {
    if (room >= 0 && room < RELAY_COUNT) {
      setRelayState(room, false);
    }
  }
  sendRelayStatusUpdate();

  lastTTSPayload = ""; // Kosongkan payload
}

void handleRingCommand(String payload) {
  static String lastPayload;
  static unsigned long lastTime = 0;
  
  // Cek duplikat dalam 5 detik terakhir
  if (payload == lastPayload && millis() - lastTime < 5000) {
    LOG("Ignoring duplicate ring command");
    return;
  }
  
  lastPayload = payload;
  lastTime = millis();
  DynamicJsonDocument doc(256);
  DeserializationError error = deserializeJson(doc, payload);
  
  if (error) {
    LOG("Ring command JSON error: " + String(error.c_str()));
    sendAckResponse("error", "Invalid ring command format");
    return;
  }
  
  if (!doc.containsKey("file_number")) {
    sendAckResponse("error", "Missing file_number");
    return;
  }

  playBellSound(
    doc["file_number"].as<String>(),
    doc["volume"] | 15,
    doc["repeat"] | 1,
    "manual"
  );
}

// ========== Relay Control Functions ==========
void setRelayState(uint8_t relayNum, bool active) {
  if (relayNum >= RELAY_COUNT) return;

  // Jika I2C tidak stabil, coba recovery dulu
  if (!state.i2cStable && millis() - state.lastI2CRecovery > 5000) {
    recoverI2CBus();
  }

  // Coba operasi hingga 3 kali
  for (int attempt = 0; attempt < 3; attempt++) {
    bool success = false;
    if (relayNum < 16) {
      if (!state.relayController1Connected) break;
      if (active) {
        state.relayStates1 &= ~(1 << relayNum);
      } else {
        state.relayStates1 |= (1 << relayNum);
      }
      Wire.beginTransmission(0x20);
      Wire.write(lowByte(state.relayStates1));
      Wire.write(highByte(state.relayStates1));
      byte error = Wire.endTransmission();
      success = (error == 0);
    } 
    else if (relayNum < 32) {
      if (!state.relayController2Connected) break;
      uint8_t pin = relayNum - 16;
      if (active) {
        state.relayStates2 &= ~(1 << pin);
      } else {
        state.relayStates2 |= (1 << pin);
      }
      Wire.beginTransmission(0x21);
      Wire.write(lowByte(state.relayStates2));
      Wire.write(highByte(state.relayStates2));
      byte error = Wire.endTransmission();
      success = (error == 0);
    } 
    else if (relayNum < 48) {
      if (!state.relayController3Connected) break;
      uint8_t pin = relayNum - 32;
      if (active) {
        state.relayStates3 &= ~(1 << pin);
      } else {
        state.relayStates3 |= (1 << pin);
      }
      Wire.beginTransmission(0x22);
      Wire.write(lowByte(state.relayStates3));
      Wire.write(highByte(state.relayStates3));
      byte error = Wire.endTransmission();
      success = (error == 0);
    }
    else {
      if (!state.relayController4Connected) break;
      uint8_t pin = relayNum - 48;
      if (active) {
        state.relayStates4 &= ~(1 << pin);
      } else {
        state.relayStates4 |= (1 << pin);
      }
      Wire.beginTransmission(0x23);
      Wire.write(lowByte(state.relayStates4));
      Wire.write(highByte(state.relayStates4));
      byte error = Wire.endTransmission();
      success = (error == 0);
    }

    if (success) {
      LOG("Relay " + String(relayNum) + " set to " + (active ? "ON" : "OFF"));
      return;
    }
    LOG("Attempt " + String(attempt+1) + " failed for relay " + String(relayNum));
    delay(10);
  }
  LOG("Failed to set relay state after 3 attempts");
  state.i2cStable = false;
}

void setAllRelays(bool active) {
  if (active) {
    state.relayStates1 = 0x0000; // Semua relay controller 1 ON
    state.relayStates2 = 0x0000; // Semua relay controller 2 ON 
    state.relayStates3 = 0x0000; // Semua relay controller 3 ON
    state.relayStates4 = 0x0000; // Semua relay controller 4 ON
  } else {
    state.relayStates1 = 0xFFFF; // Semua relay controller 1 OFF
    state.relayStates2 = 0xFFFF; // Semua relay controller 2 OFF
    state.relayStates3 = 0xFFFF; // Semua relay controller 3 OFF
    state.relayStates4 = 0xFFFF; // Semua relay controller 4 OFF
  }

  // Kirim ke semua controller yang terhubung
  if (state.relayController1Connected) {
    relayController.write16(state.relayStates1);
    delay(10); // Jeda singkat antara operasi I2C
  }
  if (state.relayController2Connected) {
    relayController2.write16(state.relayStates2);
    delay(10);
  }
  if (state.relayController3Connected) {
    relayController3.write16(state.relayStates3);
    delay(10);
  }
  if (state.relayController4Connected) {
    relayController4.write16(state.relayStates4);
    delay(10);
  }
}

void sendRelayStatusUpdate() {
  DynamicJsonDocument doc(256);
  doc["status"] = "relay_update";
  
  JsonArray activeRelays = doc.createNestedArray("active_relays");
  for (int i = 0; i < RELAY_COUNT; i++) {
    bool isActive = false;
    if (i < 16) {
      isActive = !(state.relayStates1 & (1 << i));
    } else if (i < 32) {
      isActive = !(state.relayStates2 & (1 << (i - 16)));
    } else if (i < 48) {
      isActive = !(state.relayStates3 & (1 << (i - 32)));
    } else {
      isActive = !(state.relayStates4 & (1 << (i - 48)));
    }
    if (isActive) activeRelays.add(i);
  }
  
  String payload;
  serializeJson(doc, payload);
  mqttClient.publish(TOPIC_ANNOUNCEMENT_STATUS, payload.c_str());
}

void handleScheduleSync(String payload) {
  LOG("Received sync payload: " + payload);
  
  DynamicJsonDocument doc(2048);
  DeserializationError error = deserializeJson(doc, payload);
  
  if (error) {
    LOG("JSON error: " + String(error.c_str()));
    sendAckResponse("error", "Invalid schedule format");
    return;
  }

  if (!doc.containsKey("schedules")) {
    LOG("Missing schedules array");
    sendAckResponse("error", "No schedules provided");
    return;
  }

  JsonArray schedulesArray = doc["schedules"];
  LOG("Found " + String(schedulesArray.size()) + " schedules");
  
  // Simpan ke Preferences
  saveSchedulesToPreferences(schedulesArray);
  
  // Update schedule di memory
  updateSchedules(schedulesArray);
}

void saveSchedulesToPreferences(JsonArray schedulesArray) {
  // Buka namespace preferences
  preferences.begin("bell_schedules", false);
  
  // Hapus data lama
  preferences.clear();
  
  // Simpan jumlah schedule
  preferences.putUInt("count", schedulesArray.size());
  
  // Simpan setiap schedule
  for (int i = 0; i < schedulesArray.size(); i++) {
    JsonObject s = schedulesArray[i];
    String prefix = "s" + String(i) + "_";
    
    preferences.putString((prefix + "day").c_str(), s["hari"].as<String>());
    preferences.putString((prefix + "time").c_str(), s["waktu"].as<String>().substring(0, 5));
    preferences.putString((prefix + "file").c_str(), s["file_number"].as<String>());
    preferences.putInt((prefix + "vol").c_str(), s["volume"] | 15);
    preferences.putInt((prefix + "rep").c_str(), s["repeat"] | 1);
    preferences.putBool((prefix + "active").c_str(), s["is_active"] | true);
  }
  
  preferences.end();
  LOG("Schedules saved to flash");
}

void loadSchedulesFromPreferences() {
  preferences.begin("bell_schedules", true);
  
  scheduleCount = preferences.getUInt("count", 0);
  LOG("Loading " + String(scheduleCount) + " schedules from flash");
  
  for (int i = 0; i < scheduleCount; i++) {
    String prefix = "s" + String(i) + "_";
    
    schedules[i].day = preferences.getString((prefix + "day").c_str(), "");
    schedules[i].time = preferences.getString((prefix + "time").c_str(), "");
    schedules[i].fileNumber = preferences.getString((prefix + "file").c_str(), "");
    schedules[i].volume = preferences.getInt((prefix + "vol").c_str(), 15);
    schedules[i].repeat = preferences.getInt((prefix + "rep").c_str(), 1);
    schedules[i].isActive = preferences.getBool((prefix + "active").c_str(), true);
    
    LOG("Loaded schedule: " + schedules[i].day + " " + 
        schedules[i].time + " File:" + schedules[i].fileNumber);
  }
  
  preferences.end();
  state.scheduleCount = scheduleCount;
}

void updateSchedules(JsonArray schedulesArray) {
  scheduleCount = 0;
  
  for (int i = 0; i < schedulesArray.size() && scheduleCount < 50; i++) {
    JsonObject s = schedulesArray[i];
    
    if (!s.containsKey("hari") || !s.containsKey("waktu") || !s.containsKey("file_number")) {
      LOG("Skipping invalid schedule - missing required fields");
      continue;
    }

    schedules[scheduleCount] = {
        s["hari"].as<String>(),       // day
        s["waktu"].as<String>().substring(0, 5),  // time
        s["file_number"].as<String>(),// fileNumber
        s["volume"] | 15,            // volume
        s["repeat"] | 1,             // repeat
        s["is_active"] | true        // isActive
    };
    
    LOG("Added schedule: " + schedules[scheduleCount].day + " " + 
        schedules[scheduleCount].time + " File:" + schedules[scheduleCount].fileNumber);
    
    scheduleCount++;
  }

  state.scheduleCount = scheduleCount;
  state.lastSync = millis() / 1000;
  LOG("Total schedules: " + String(scheduleCount));
}

// ========== Fungsi Audio ==========
void playTTS(String text, String language, String voice) {
    LOG("playTTS() started");

    // [1] Persiapan Awal
    esp_wifi_set_ps(WIFI_PS_NONE);
    WiFi.setSleep(false);

    LOG("WiFi power settings adjusted");

    // [2] Setup I2S
    i2s_zero_dma_buffer(I2S_NUM_0);
    i2s_stop(I2S_NUM_0);
    LOG("I2S driver reset");

    i2s_set_clk(I2S_NUM_0, SAMPLE_RATE, I2S_BITS_PER_SAMPLE_16BIT, I2S_CHANNEL_MONO);
    i2s_start(I2S_NUM_0);
    LOG("I2S clock started");

    // [3] Download Audio
    String url = "http://api.voicerss.org/?key=" + String(VOICERSS_API_KEY) + 
                "&hl=" + language + "&v=" + voice + 
                "&c=WAV&f=22khz_16bit_mono&src=" + urlEncode(text);
    LOG("Fetching TTS URL: " + url);

    HTTPClient http;
    http.setReuse(true);
    http.setTimeout(60000);
    
    if (!http.begin(url)) {
        LOG("HTTP Begin failed");
        return;
    }
    LOG("HTTP client started");

    int httpCode = http.GET();
    if (httpCode != HTTP_CODE_OK) {
        LOG("HTTP Error: " + String(httpCode));
        http.end();
        return;
    }
    LOG("HTTP Response OK");

    // [4] Baca Data Sekaligus
    int contentLength = http.getSize();
    LOG("Content length: " + String(contentLength));

    if (contentLength <= 44) {
        LOG("Invalid content length");
        http.end();
        return;
    }

    uint8_t* audioBuffer = (uint8_t*)ps_malloc(contentLength);
    if (!audioBuffer) {
        LOG("Memory allocation failed");
        http.end();
        return;
    }
    LOG("Memory allocated: " + String(contentLength));

    int bytesRead = http.getStreamPtr()->readBytes(audioBuffer, contentLength);
    http.end();

    if (bytesRead != contentLength) {
        LOG("Download incomplete (" + String(bytesRead) + "/" + String(contentLength) + ")");
        free(audioBuffer);
        return;
    }
    LOG("Audio data downloaded");

    // [5] Proses Audio Data
    uint8_t* audioData = audioBuffer + 44; // Skip header WAV
    size_t audioSize = contentLength - 44;
    
    // [6] Pre-fill DMA Buffer (SOLUSI UTAMA)
    size_t prefillSize = 4096; // Isi sebagian buffer DMA terlebih dahulu
    if (audioSize > prefillSize) {
        size_t bytesWritten;
        i2s_write(I2S_NUM_0, audioData, prefillSize, &bytesWritten, portMAX_DELAY);
        audioData += prefillSize;
        audioSize -= prefillSize;
    }

    // [7] Mainkan Sisa Audio
    state.isPlaying = true;
    isTTSPending = true;
    
    size_t totalWritten = 0;
    while (audioSize > 0 && state.isPlaying) {
        size_t bytesWritten;
        esp_err_t err = i2s_write(I2S_NUM_0, audioData, audioSize, &bytesWritten, portMAX_DELAY);
        
        if (err != ESP_OK) {
            LOG("I2S Write Error: " + String(err));
            break;
        }
        
        audioData += bytesWritten;
        audioSize -= bytesWritten;
        totalWritten += bytesWritten;
        
        // Beri jeda kecil untuk memastikan DAC tidak overflow
        if (bytesWritten > 2048) {
            delay(1);
        }
    }
  
    // [9] Cleanup
    free(audioBuffer);
    state.isPlaying = false; // Audio sudah dikirim
    LOG("Audio stream selesai");
}

// ========== Fungsi Pendukung ==========
String urlEncode(String str) {
  String encodedString = "";
  char c;
  char code0;
  char code1;
  
  for (unsigned int i = 0; i < str.length(); i++) {
    c = str.charAt(i);
    
    if (c == ' ') {
      encodedString += '+';
    } else if (isalnum(c)) {
      encodedString += c;
    } else {
      code1 = (c & 0xf) + '0';
      if ((c & 0xf) > 9) {
        code1 = (c & 0xf) - 10 + 'A';
      }
      c = (c >> 4) & 0xf;
      code0 = c + '0';
      if (c > 9) {
        code0 = c - 10 + 'A';
      }
      encodedString += '%';
      encodedString += code0;
      encodedString += code1;
    }
  }
  
  return encodedString;
}

bool checkI2CConnection(uint8_t address) {
  Wire.beginTransmission(address);
  byte error = Wire.endTransmission();
  if (error == 0) {
    return true;
  } else {
    LOG("I2C error at 0x" + String(address, HEX) + ": " + getI2CErrorString(error));
    return false;
  }
}

String getI2CErrorString(byte error) {
  switch(error) {
    case 0: return "Success";
    case 1: return "Data too long";
    case 2: return "NACK on address";
    case 3: return "NACK on data";
    case 4: return "Other error";
    default: return "Unknown error";
  }
}

void recoverI2CBus() {
  LOG("Attempting I2C bus recovery...");
  
  // 1. Stop I2C
  Wire.end();
  delay(100);
  
  // 2. Kembalikan pin ke default
  pinMode(I2C_SDA_PIN, INPUT_PULLUP);
  pinMode(I2C_SCL_PIN, INPUT_PULLUP);
  delay(100);
  
  // 3. Coba clock out any stuck bits
  for (int i = 0; i < 10; i++) {
    pinMode(I2C_SCL_PIN, OUTPUT);
    digitalWrite(I2C_SCL_PIN, LOW);
    delayMicroseconds(10);
    digitalWrite(I2C_SCL_PIN, HIGH);
    pinMode(I2C_SCL_PIN, INPUT_PULLUP);
    delayMicroseconds(10);
  }
  
  // 4. Re-init I2C
  Wire.begin(I2C_SDA_PIN, I2C_SCL_PIN);
  Wire.setClock(100000);
  Wire.setTimeOut(250);
  
  LOG("I2C bus recovery completed");
  state.lastI2CRecovery = millis();
  
  // Set ulang status relay controller
  if (state.relayController1Connected) {
    relayController.write16(state.relayStates1);
  }
  if (state.relayController2Connected) {
    relayController2.write16(state.relayStates2);
  }
  if (state.relayController3Connected) {
  relayController3.write16(state.relayStates3);
  }
}

void logI2CStatus() {
  DynamicJsonDocument doc(512); // Perbesar ukuran buffer
  doc["i2c_stable"] = state.i2cStable;
  doc["i2c_error_count"] = state.i2cErrorCount;
  doc["controller1_connected"] = state.relayController1Connected;
  doc["controller2_connected"] = state.relayController2Connected;
  doc["controller3_connected"] = state.relayController3Connected;
  doc["last_recovery"] = state.lastI2CRecovery;
  String payload;
  serializeJson(doc, payload);
  LOG("I2C Status: " + payload);
}

// ========== Bell Functions ==========
void playBellSound(String fileNumber, int volume, int repeat, const char* triggerType) {
  if (!state.dfPlayerConnected) {
    LOG("DFPlayer not connected - cannot play sound");
    sendAckResponse("error", "DFPlayer not connected");
    return;
  }
  
  // Set volume ke maksimal (30)
  int finalVolume = 25; // Ubah ini dari variabel parameter ke nilai tetap 30
  
  LOG("Playing bell: " + fileNumber + " Vol:" + String(finalVolume) + " Repeat:" + String(repeat));
  
  dfPlayer.volume(finalVolume); // Gunakan finalVolume yang sudah di-set ke 30
  int repeatCount = constrain(repeat, 1, 5);

  // Nyalakan semua relay
  setAllRelays(true);
  sendRelayStatusUpdate();

  for (int i = 0; i < repeatCount; i++) {
    dfPlayer.play(fileNumber.toInt());
    LOG("Putar ke-" + String(i+1) + " dimulai");

    // Tunggu selama 30 detik untuk setiap pemutaran
    unsigned long startTime = millis();
    while (millis() - startTime < 30000) {
      delay(10);
    }

    LOG("Putar ke-" + String(i+1) + " selesai (setelah 30 detik)");
  }

  // Matikan semua relay
  setAllRelays(false);
  sendRelayStatusUpdate();

  logBellEvent(fileNumber, finalVolume, repeatCount, triggerType);
  sendAckResponse("success", "Bel berbunyi");
}

void logBellEvent(String fileNumber, int volume, int repeat, const char* triggerType) {
  DateTime now = rtc.now();
  
  DynamicJsonDocument doc(256);
  doc["hari"] = getDayName(now.dayOfTheWeek());
  doc["waktu"] = formatTime(now.hour(), now.minute(), now.second());
  doc["file_number"] = fileNumber;
  doc["trigger_type"] = triggerType;
  doc["volume"] = volume;
  doc["repeat"] = repeat;
  
  String payload;
  serializeJson(doc, payload);
  
  // Tentukan topik berdasarkan trigger type
  const char* topic = (strcmp(triggerType, "schedule") == 0) ? 
                      TOPIC_EVENT_SCHEDULE : TOPIC_EVENT_MANUAL;
  
  // Kirim MQTT
  bool mqttSuccess = mqttClient.publish(topic, payload.c_str());
  
  // Kirim HTTP
  sendBellEventViaHTTP(payload, triggerType);
  
  LOG(mqttSuccess ? "MQTT publish success" : "MQTT publish failed");
}

// Fungsi HTTP async sederhana
void sendBellEventViaHTTP(String jsonPayload, const char* triggerType) {
  static WiFiClient client;
  static HTTPClient http;
  
  if (http.connected()) {
    http.end();
  }
  
  // Gunakan endpoint berbeda untuk manual dan schedule
  String endpoint = (strcmp(triggerType, "schedule") == 0) ? 
                   "/api/bell-events/schedule" : "/api/bell-events/manual";
  
  String url = "http://" + String(ip_server_laravel) + ":" + String(SERVER_PORT) + endpoint;
  http.begin(client, url);
  http.addHeader("Content-Type", "application/json");
  
  LOG("Sending HTTP to: " + url);
  int httpCode = http.POST(jsonPayload);
  
  if (httpCode > 0) {
    LOG("HTTP response: " + String(httpCode));
  } else {
    LOG("HTTP failed: " + String(httpCode));
  }
}

// ========== Response Functions ==========
void sendSystemStatus() {
  DynamicJsonDocument doc(256);
  doc["wifi"] = state.wifiConnected;
  doc["rtc"] = state.rtcConnected;
  doc["dfplayer"] = state.dfPlayerConnected;
  doc["mqtt"] = state.mqttConnected;
  
  if (state.rtcConnected) {
    DateTime now = rtc.now();
    doc["rtc_time"] = now.unixtime();
    doc["rtc_formatted"] = getCurrentDateTime();
  }
  
  doc["last_communication"] = state.lastCommunication;
  doc["last_sync"] = state.lastSync;
  doc["schedule_count"] = state.scheduleCount;
  doc["ip_address"] = WiFi.localIP().toString();

  // Tambahkan status I2C
  doc["i2c_stable"] = state.i2cStable;
  doc["i2c_error_count"] = state.i2cErrorCount;
  doc["last_i2c_check"] = state.lastI2CCheck;
  doc["last_i2c_recovery"] = state.lastI2CRecovery;

  String payload;
  serializeJson(doc, payload);
  
  if (!mqttClient.publish(TOPIC_RESPONSE_STATUS, payload.c_str())) {
    LOG("Failed to publish status");
  }
}

void sendAckResponse(const char* status, const char* message) {
  DynamicJsonDocument doc(128);
  doc["status"] = status;
  doc["message"] = message;
  doc["timestamp"] = state.lastCommunication;
  
  String payload;
  serializeJson(doc, payload);
  
  if (!mqttClient.publish(TOPIC_RESPONSE_ACK, payload.c_str())) {
    LOG("Failed to publish ack");
  }
}

// ========== Schedule Checking ==========
// Di checkSchedules():
void checkSchedules() {
  static unsigned long lastCheck = 0;
  if (millis() - lastCheck < 1000) return;
  lastCheck = millis();

  if (!state.rtcConnected || scheduleCount == 0) return;

  DateTime now = rtc.now();
  int currentHour = now.hour();
  int currentMinute = now.minute();
  String currentDay = getDayName(now.dayOfTheWeek());

  for (int i = 0; i < scheduleCount; i++) {
    if (!schedules[i].isActive) continue;
    
    // Cek jika schedule sudah aktif
    bool alreadyActive = false;
    for (int j = 0; j < 3; j++) {
      if (state.activeSchedules[j].index == i) {
        alreadyActive = true;
        break;
      }
    }
    if (alreadyActive) continue;

    // Parse waktu schedule
    int schedHour = schedules[i].time.substring(0,2).toInt();
    int schedMinute = schedules[i].time.substring(3,5).toInt();

    if (schedules[i].day == currentDay && 
        currentHour == schedHour && 
        currentMinute == schedMinute) {
      
      // Cari slot kosong
      for (int j = 0; j < 3; j++) {
        if (state.activeSchedules[j].index == -1) {
          state.activeSchedules[j].index = i;
          state.activeSchedules[j].startTime = millis();
          
          playBellSound(
            schedules[i].fileNumber,
            schedules[i].volume,
            schedules[i].repeat,
            "schedule"
          );
          break;
        }
      }
    }
  }
}