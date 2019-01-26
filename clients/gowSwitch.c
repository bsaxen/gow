//=============================================
// File.......: gowSwitch.c
// Date.......: 2019-01-26
// Author.....: Benny Saxen
// Description: 
//=============================================
// Configuration
//=============================================
char* publishTopic = "nytomta/gow/fan1/0";
int conf_period = 20;
int conf_wrap   = 999999;
const char* ssid       = "ASUS-hus";
const char* password   = "some";
const char* host       = "gow.simuino.com";
const char* streamId   = "....................";
const char* privateKey = "....................";
//=============================================
#include <ESP8266WiFi.h>
#define FAN_PIN 5  // D1 pin on NodeMCU 1.0
int g_status = 1;

void setup() {
  Serial.begin(9600);
  delay(100);

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

  pinMode(FAN_PIN, OUTPUT);
  digitalWrite(FAN_PIN,HIGH);
  delay(2000);
  digitalWrite(FAN_PIN,LOW);
  delay(2000);
  digitalWrite(FAN_PIN,HIGH);
  g_status = 1;
}

int value = 0;

//=============================================
void loop() 
//=============================================
{
  char order[10];
  char buf[100];
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
  url += "&message=2";
  url += "&hw=";
  url += "esp8266";
  url += "&ssid=";
  url += ssid;
  url += "&payload=";
  url += "{\"status\":\"";
  url += g_status;
  url += "\"}"; 

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
  delay(500);
  // Read all the lines of the reply from server and print them to Serial
  while (client.available()) {
    String action = client.readStringUntil('\r');
      //Serial.println(action);
      Serial.println(action.indexOf(':'));
      if (action.indexOf('[') == 1) 
      {
        int b = action.indexOf(':')+1;
        int x = action.lastIndexOf(':');
        String sub = action.substring(b,x);
        //Serial.print("Order received <");
        //Serial.print(sub);
        //Serial.println(">");
        x = x+2;
        action.toCharArray(buf,x);
        //Serial.println(buf);  
 
        if( strstr(buf,"OFF") != NULL)
        {
            g_status = 1;
            digitalWrite(FAN_PIN,HIGH);
        }
           
        if( strstr(buf,"ONN") != NULL)
        {
            g_status = 2;
            digitalWrite(FAN_PIN,LOW);
        }
        if(strstr(buf,"period") != NULL)// set new period
        {
           for (char* p = buf; p = strchr(p, ','); ++p) 
           {
              *p = ' ';
           }
           for (char* p = buf; p = strchr(p, ':'); ++p) 
           {
              *p = ' ';
           }
           //sscanf(buf,"%s %d",order, &conf_period);
        }
      }
  }

  Serial.println("closing connection");
}
