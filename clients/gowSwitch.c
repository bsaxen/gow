//=============================================
// File.......: gowSwitch.c
// Date.......: 2019-01-05
// Author.....: Benny Saxen
// Description: 
//=============================================
// Configuration
//=============================================
char* publishTopic = "test/topic/here/0";
int conf_period = 10;
int conf_wrap   = 999999;
const char* ssid       = "my_ssid";
const char* password   = "my passw";
const char* host       = "192.168.1.242";
const char* streamId   = "....................";
const char* privateKey = "....................";
//=============================================
#include <ESP8266WiFi.h>

void setup() {
  Serial.begin(9600;
  delay(10);

  Serial.println();
  Serial.println();
  Serial.print("Connecting to ");
  Serial.println(ssid);

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("");
  Serial.println("WiFi connected");
  Serial.println("IP address: ");
  Serial.println(WiFi.localIP());
}

int value = 0;

void loop() {
  delay(conf_period*1000);
  ++value;

  Serial.print("connecting to ");
  Serial.println(host);

  // Use WiFiClient class to create TCP connections
  WiFiClient client;
  const int httpPort = 80;
  if (!client.connect(host, httpPort)) {
    Serial.println("connection failed");
    return;
  }

  String url = "/gowServer.php";
  url += "?do=data";
  url += "&topic=";
  url += publishTopic;
  url += "&no=";
  url += value;
  url += "&wrap=";
  url += conf_wrap;
  url += "&period=";
  url += conf_period;
  url += "&hw=";
  url += "esp8266";
      
  Serial.print("Requesting URL: ");
  Serial.println(url);

  client.print(String("GET ") + url + " HTTP/1.1\r\n" +
               "Host: " + host + "\r\n" +
               "Connection: close\r\n\r\n");
  unsigned long timeout = millis();
  while (client.available() == 0) {
    if (millis() - timeout > 5000) {
      Serial.println(">>> Client Timeout !");
      client.stop();
      return;
    }
  }

  // Read all the lines of the reply from server and print them to Serial
  while (client.available()) {
    String action = client.readStringUntil('\r');
    Serial.print(action);
    // Do something based upon the action string
  }

  Serial.println();
  Serial.println("closing connection");
}
