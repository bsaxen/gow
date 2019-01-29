//=============================================
// File.......: httpClient.c
// Date.......: 2019-01-29
// Author.....: Benny Saxen
// Description: Basic http client
//=============================================
// Configuration
//=============================================
char* publishTopic = "test/topic/here/0";
int conf_period = 10;
int conf_wrap   = 999999;
int action = 1;
int wifi_ss = 0;
char* payload = "{};
char* tags = "tag1,tag2,tag3";
char* desc = "your description";
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

int counter = 0;

void loop() {
  delay(conf_period*1000);
  ++counter;

  Serial.print("connecting to ");
  Serial.println(host);

  // Use WiFiClient class to create TCP connections
  WiFiClient client;
  const int httpPort = 80;
  if (!client.connect(host, httpPort)) {
    Serial.println("connection failed");
    //return;
  }
  
  //===================================
  String stat_url = "/gowServer.php";
  //===================================
  stat_url += "?do=stat";
  
  stat_url += "&topic=";
  stat_url += publishTopic;
  
  stat_url += "&ssid=";
  stat_url += ssid;
  
  stat_url += "&wrap=";
  stat_url += conf_wrap;
  
  stat_url += "&period=";
  stat_url += conf_period;
  
  stat_url += "&url=";
  stat_url += "gow.asd.com";
  
  stat_url += "&platform=";
  stat_url += "esp8266";

  stat_url += "&tags=";
  stat_url += tags;

  stat_url += "&desc=";
  stat_url += desc;

  stat_url += "&action=";
  stat_url += action;
  
  //===================================
  String dyn_url = "/gowServer.php";
  //=================================== 
  dyn_url += "?do=dyn";
  
  dyn_url += "&topic=";
  dyn_url += publishTopic;
  
  dyn_url += "&no=";
  dyn_url += counter;
  
  dyn_url += "&wifi_ss=";
  dyn_url += wifi_ss;
    
  dyn_url += "&payload=";
  dyn_url += payload;
  
  if (counter%100 == 0)
  {
    cur_url = stat_url;
  }
  else
  {
    cur_url = dyn_url; 
  }
  
  Serial.print("Requesting URL: ");
  Serial.println(cur_url);

  // This will send the request to the server
  client.print(String("GET ") + cur_url + " HTTP/1.1\r\n" +
               "Host: " + host + "\r\n" +
               "Connection: close\r\n\r\n");
  unsigned long timeout = millis();
  while (client.available() == 0) {
    if (millis() - timeout > 5000) {
      Serial.println(">>> Client Timeout !");
      client.stop();
      //return;
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
