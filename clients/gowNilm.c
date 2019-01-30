//=============================================
// File.......: gowNilm.c
// Date.......: 2019-01-30
// Author.....: Benny Saxen
// Description: nilm - electricity 
//=============================================
// Configuration
//=============================================
char* conf_topic = "test/topic/here/0";
int conf_period  = 10;
int conf_wrap    = 999999;
int conf_action  = 1;
int wifi_ss      = 0;
char* conf_tags        = "tag1,tag2,tag3";
char* conf_desc        = "your_description";
char* conf_platform    = "esp8266";
const char* ssid       = "my_ssid";
const char* password   = "my passw";
const char* host       = "192.168.1.242";
const char* streamId   = "....................";
const char* privateKey = "....................";
//=============================================
#include <ESP8266WiFi.h>

const byte interrupt_pin = 5;
const byte led_pin       = 4;
int timeToCheckStatus    = 0;
unsigned long t1,t2,dt,ttemp;
float elpow               = 0.0;
int interrupt_counter     = 0;
int electric_meter_pulses = 1000;  //1000 pulses/kWh
int bounce_value          = 50; // minimum time between interrupts
//===============================================================
// Interrupt function for measuring the time between pulses and number of pulses
// Always stored in RAM
void ICACHE_RAM_ATTR measure(){
//===============================================================
    digitalWrite(led_pin,HIGH);
    ttemp = t1;
    t2 = t1;
    t1 = millis();
    dt = t1 - t2;
    if (dt < bounce_value)
    {
        t1 = ttemp;
        digitalWrite(led_pin,LOW);
        return;
    }
    elpow = 3600.*1000.*1000./(electric_meter_pulses*dt);
    interrupt_counter++;
    digitalWrite(led_pin,LOW);
}
//===============================================================
void setup(){
//===============================================================
    bounce_value = 36000./electric_meter_pulses; // based on max power = 100 000 Watt

    pinMode(interrupt_pin, INPUT_PULLUP);
    pinMode(led_pin, OUTPUT);
    digitalWrite(led_pin,LOW);
    attachInterrupt(interrupt_pin, measure, FALLING);

}
int counter = 0;
//===============================================================
void loop(void){
//===============================================================
  delay(conf_period*1000);
    
  ++counter;
  if (counter > conf_wrap) counter = 1;
    
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
  String url = "/gowServer.php";

  if (counter%10 == 0)
  {  
    url += "?do=stat";
    
    url += "&topic=";
    url += conf_topic;
    
    url += "&wrap=";
    url += conf_wrap;
    
    url += "&platfrom=";
    url += conf_platfrom;
    
    url += "&action=";
    url += conf_action;
      
    url += "&ssid=";
    url += ssid;
    
    url += "&tags=";
    url += conf_tags;
    
    url += "&desc=";
    url += conf_desc;
    
    url += "&period=";
    url += conf_period;
    
    url += "&url=";
    url += host;
  }
  else
  {
    url += "?do=dyn";
    
    url += "&topic=";
    url += conf_topic;
    
    url += "&no=";
    url += counter;
    
    url += "&wifi_ss=";
    url += wifi_ss;
    
    url += "&payload="; 
    url += "{"; 
        url += "\"temp";
        url += "\":\"";
        url += 123;
        url += "\"";
    }
    url += "}";
  }
    
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

