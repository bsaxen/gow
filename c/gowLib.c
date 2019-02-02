//=============================================
// File.......: gowLib.c
// Date.......: 2019-02-02
// Author.....: Benny Saxen
// Description:
//=============================================
#include <ESP8266WiFi.h>


struct Configuration
{
  char* conf_topic = "test/topic/here/0";
  int conf_period  = 10;
  int conf_wrap    = 999999;
  int conf_action  = 1;
  char* conf_tags = "tag1,tag2,tag3";
  char* conf_desc = "your_description";
  char* conf_platform = "esp8266";
  char* conf_ssid       = "my_ssid";
  char* conf_password   = "my passw";
  char* conf_host       = "192.168.1.242";
  char* conf_streamId   = "....................";
  char* conf_privateKey = "....................";
}

struct data
{
  int counter;
  int wifi_ss;
}

//=============================================
int lib_decode_ON_OFF(String* msg)
//=============================================
{
  char buf[100];
  int result = 0;

  msg.toCharArray(buf);

  if( strstr(buf,"OFF") != NULL)
  {
      result = 1;
      digitalWrite(FAN_PIN,HIGH);
  }

  if( strstr(buf,"ONN") != NULL)
  {
      result = 2;
      digitalWrite(FAN_PIN,LOW);
  }
  return result;
}
/=============================================
int lib_decode_STEPPER(String* msg)
//=============================================
{
  char buf[100];
  int result = 0;

  msg.toCharArray(buf);

  action.toCharArray(buf,x);
  Serial.println(buf);

  // RESET
  if(strstr(buf,"reboot") != NULL)
  {
    software_Reset();
  }
  else if(strstr(buf,"reset") != NULL)
  {
    reset_pos();
  }
  else if(strstr(buf,"move") != NULL)// move stepper
  {
       for (char* p = buf; p = strchr(p, ','); ++p)
       {
          *p = ' ';
       }
       for (char* p = buf; p = strchr(p, ':'); ++p)
       {
          *p = ' ';
       }
       Serial.println(buf);
       sscanf(buf,"%s %d %d %d %d",order, &dir,&step_size,&steps,&step_delay);
       move_stepper(dir,step_size,steps,step_delay);
  }
  else if(strstr(buf,"period") != NULL)// set new period
  {
       for (char* p = buf; p = strchr(p, ','); ++p)
       {
          *p = ' ';
       }
       for (char* p = buf; p = strchr(p, ':'); ++p)
       {
          *p = ' ';
       }
       Serial.println(buf);
       sscanf(buf,"%s %d",order, &conf_period);
  }

  return result;
}
//=============================================
void lib_buildUrlStatic(struct Configuration c2, String* stat_url)
//=============================================
{
  //===================================
  stat_url = "/gowServer.php";
  //===================================
  stat_url += "?do=stat";

  stat_url += "&topic=";
  stat_url += c2.conf_topic;

  stat_url += "&ssid=";
  stat_url += c2.conf_ssid;

  stat_url += "&wrap=";
  stat_url += c2.conf_wrap;

  stat_url += "&period=";
  stat_url += c2.conf_period;

  stat_url += "&url=";
  stat_url += c2.conf_host;

  stat_url += "&platform=";
  stat_url += c2.conf_platform;

  stat_url += "&tags=";
  stat_url += c2.conf_tags;

  stat_url += "&desc=";
  stat_url += c2.conf_desc;

  stat_url += "&action=";
  stat_url += c2.conf_action;
}
//=============================================
void lib_buildUrlDynamic(struct Configuration c2,struct Data d2, String* dyn_url)
//=============================================
{
  //===================================
  String dyn_url = "/gowServer.php";
  //===================================
  dyn_url += "?do=dyn";

  dyn_url += "&topic=";
  dyn_url += c2.conf_topic;

  dyn_url += "&no=";
  dyn_url += d2.counter;

  dyn_url += "&wifi_ss=";
  dyn_url += d2.wifi_ss;

  /*dyn_url += "&payload=";
  dyn_url += "{";
  dyn_url += "\"temp";
  dyn_url += "\":\"";
  dyn_url += 123;
  dyn_url += "\"";
  dyn_url += "}";*/
}
//=============================================
void lib_wifiBegin(struct Configuration c2)
//=============================================
{
  Serial.print("Connecting to ");
  Serial.println(c2.conf_ssid);

  /* Explicitly set the ESP8266 to be a WiFi-client, otherwise, it by default,
   would try to act as both a client and an access-point and could cause
   network-issues with your other WiFi-devices on your WiFi-network. */
   WiFi.mode(WIFI_STA);
   WiFi.begin(c2.conf_ssid, c2.conf_password);

   while (WiFi.status() != WL_CONNECTED) {
     delay(500);
     Serial.print(".");
   }

   Serial.println("WiFi connected");
   Serial.println("IP address: ");
   Serial.println(WiFi.localIP());
}

//=============================================
void lib_wireBegin(struct Configuration c2)
//=============================================
{
  Serial.print("Connecting to router via wire");

   Serial.println("Wire connected");
   Serial.println("IP address: ");
   Serial.println("benny");
}

//=============================================
String lib_wifiConnectandSend(struct Configuration c2, char* cur_url)
//=============================================
{
  String sub = "-";
  Serial.print("Requesting URL: ");
  Serial.println(cur_url);
  // Use WiFiClient class to create TCP connections
  WiFiClient client;
  const int httpPort = 80;
  if (!client.connect(c2.conf_host, c2.conf_httpPort)) {
    Serial.println("connection failed");
  //return;
  }

  // This will send the request to the server
  client.print(String("GET ") + cur_url + " HTTP/1.1\r\n" +
             "Host: " + c2.conf_host + "\r\n" +
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
    if (action.indexOf('[') == 1)
    {
      int b = action.indexOf(':')+1;
      int x = action.lastIndexOf(':');
      sub = action.substring(b,x);
    }
    // Do something based upon the action string
  }

  Serial.println("closing connection");
  return sub;
}
