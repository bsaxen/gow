//=============================================
// File.......: gowSwitch.c
// Date.......: 2019-02-02
// Author.....: Benny Saxen
// Description:
//=============================================
// Configuration
//=============================================
//#include "gowLib.c"

struct Configuration c1;
struct Data d1;
//=============================================

#define FAN_PIN 5  // D1 pin on NodeMCU 1.0
int g_status = 0;

//=============================================
void setup() {
//=============================================
  c1.conf_topic = "benny/saxen/2";
  c1.conf_period  = 10;
  c1.conf_wrap    = 999999;
  c1.conf_action  = 1;

  c1.conf_tags = "tag1,tag2,tag3";
  c1.conf_desc = "your_description";
  c1.conf_platform = "esp8266";
  c1.conf_ssid       = "bridge";
  c1.conf_password   = "qweqwe";
  c1.conf_host       = "gow.qwe.com";
  c1.conf_streamId   = "....................";
  c1.conf_privateKey = "....................";
  Serial.begin(9600);
  delay(100);

  lib_wifiBegin(c1);

  pinMode(FAN_PIN, OUTPUT);
  digitalWrite(FAN_PIN,HIGH);
  delay(2000);
  digitalWrite(FAN_PIN,LOW);
  delay(2000);
  digitalWrite(FAN_PIN,HIGH);
  g_status = 1;
}

//=============================================
void loop()
//=============================================
{
  String msg;
  char buf[100];

  Serial.println("Start...");

  ++d1.counter;
  if (d1.counter > c1.conf_wrap) d1.counter = 1;
  d1.rssi = WiFi.RSSI();
  
  Serial.println(d1.counter);
  String stat_url = lib_buildUrlStatic(c1);
  String dyn_url = lib_buildUrlDynamic(c1, d1);
  Serial.println(stat_url);
  Serial.println(dyn_url);
  String cur_url = " ";
  if (d1.counter%100 == 0)
  {
    cur_url = stat_url;
  }
  else
  {
    cur_url = dyn_url;
  }
  Serial.println(cur_url);
  msg = lib_wifiConnectandSend(c1, cur_url);
  Serial.println(msg);
  int res = lib_decode_ON_OFF(msg);

  if (res == 1) digitalWrite(FAN_PIN,LOW);
  if (res == 2) digitalWrite(FAN_PIN,HIGH);
  g_status = res;

  delay(c1.conf_period*1000);
}
//=============================================
// End of File
//=============================================
