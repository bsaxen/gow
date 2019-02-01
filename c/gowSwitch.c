//=============================================
// File.......: gowSwitch.c
// Date.......: 2019-02-01
// Author.....: Benny Saxen
// Description:
//=============================================
// Configuration
//=============================================
#include "gowLib.c"

struct Configuration c1;
struct Data d1;
int wifi_ss = 0;


c1.conf_topic = "test/topic/here/0";
c1.conf_period  = 10;
c1.conf_wrap    = 999999;
c1.conf_action  = 1;

c1.conf_tags = "tag1,tag2,tag3";
c1.conf_desc = "your_description";
c1.conf_platform = "esp8266";
c1.ssid       = "my_ssid";
c1.password   = "my passw";
c1.host       = "192.168.1.242";
c1.streamId   = "....................";
c1.privateKey = "....................";
//=============================================

#define FAN_PIN 5  // D1 pin on NodeMCU 1.0
int g_status = 0;

//=============================================
void setup() {
//=============================================
  Serial.begin(9600);
  delay(100);

  wifiBegin(c1);

  pinMode(FAN_PIN, OUTPUT);
  digitalWrite(FAN_PIN,HIGH);
  delay(2000);
  digitalWrite(FAN_PIN,LOW);
  delay(2000);
  digitalWrite(FAN_PIN,HIGH);
  g_status = 1;
}

int counter = 0;

//=============================================
void loop()
//=============================================
{
  char msg[100];

  delay(c1.conf_period*1000);

  ++d1.counter;
  if (d1.counter > c1.conf_wrap) d1.counter = 1;

  lib_buildUrlStatic(c1, stat_url);
  lib_buildUrlDynamic(c1, d1, dyn_url);

  String cur_url = " ";
  if (d1.counter%100 == 0)
  {
    cur_url = stat_url;
  }
  else
  {
    cur_url = dyn_url;
  }

  msg = lib_wifiConnectandSend(c1, cur_url);

  int res = lib_decode_ON_OFF(msg);

  if (res == 1) digitalWrite(FAN_PIN,LOW);
  if (res == 2) digitalWrite(FAN_PIN,HIGH);
  g_status = res;
}
//=============================================
// End of File
//=============================================
