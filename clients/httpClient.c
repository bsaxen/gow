//=============================================
// File.......: httpClient.c
// Date.......: 2018-11-20
// Author.....: Benny Saxen
// Description: Basic http client
//=============================================
// Configuration
//=============================================
char* publishTopic = "test/topic/here/0";
int period = 10;
const char* ssid       = "my_ssid";
const char* password   = "my passw";
const char* host       = "192.168.1.242";
const char* streamId   = "....................";
const char* privateKey = "....................";
//=============================================
#include <ESP8266WiFi.h>




void setup() {
  Serial.begin(115200);
  delay(10);

  // We start by connecting to a WiFi network

  Serial.println();
  Serial.println();
  Serial.print("Connecting to ");
  Serial.println(ssid);

  /* Explicitly set the ESP8266 to be a WiFi-client, otherwise, it by default,
     would try to act as both a client and an access-point and could cause
     network-issues with your other WiFi-devices on your WiFi-network. */
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
  delay(period*1000);
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

  // We now create a URI for the request
  String url = "/git/gow/gowServer.php";
  url += "?do=data";
  url += "&topic=";
  url += publishTopic;
  url += "&no=";
  url += value;
  url += "&type=";
  url += "TEMPERATURE";
  url += "&value=";
  url += value;
  url += "&ts=";
  url += "2018-01-01%2012:12:31";
  url += "&unit=";
  url += "celcius";
  url += "&period=";
  url += 10;
  url += "&url=";
  url += "http://192.168.1.242/git/gow/";
  url += "&hw=";
  url += "esp8266";
      
  Serial.print("Requesting URL: ");
  Serial.println(url);

  // This will send the request to the server
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
