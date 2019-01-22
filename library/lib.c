//===============================================
// File.......: lib.c
// Date.......: 2019-01-22
// Author.....: Benny Saxen
// Description: GOW C library
//===============================================
#include <ESP8266WiFi.h>
//===============================================
initWifi()
//===============================================
{
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
//===============================================
void publish()
//===============================================
{
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
  delay(2000);
  // Read all the lines of the reply from server and print them to Serial
  while (client.available()) {
    String action = client.readStringUntil('\r');
      if (action.indexOf(':') == 1) 
      {
        int x = action.lastIndexOf(':');
        Serial.print("Order received");
        Serial.println(x);
        x = x+2;
        action.toCharArray(buf,x);
        Serial.println(buf);  
 
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

  Serial.println();
  Serial.println("closing connection");
  }
