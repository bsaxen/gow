//=============================================
// File.......: httpClient.c
// Date.......: 2019-02-02
// Author.....: Benny Saxen
// Description: Basic http client
//=============================================
// Configuration
//=============================================
//#include "gowLib.c"

struct Configuration c1;
struct Data d1;
int wifi_ss = 0;



//=============================================
String stat_url = " ";
String dyn_url = " ";
//=============================================
void setup() {
//=============================================  
  c1.conf_topic = "test/topic/here/0";
  c1.conf_period  = 10;
  c1.conf_wrap    = 999999;
  c1.conf_action  = 1;

  c1.conf_tags = "tag1,tag2,tag3";
  c1.conf_desc = "your_description";
  c1.conf_platform = "esp8266";
  c1.conf_ssid       = "my_ssid";
  c1.conf_password   = "my passw";
  c1.conf_host       = "192.168.1.242";
  c1.conf_streamId   = "....................";
  c1.conf_privateKey = "....................";
  Serial.begin(9600);
  delay(100);
  lib_wifiBegin(c1);
  d1.counter = 0;
  stat_url = lib_buildUrlStatic(c1);
  String dont_care = lib_wifiConnectandSend(c1, stat_url);
}


//=============================================
void loop() {
//=============================================
  delay(c1.conf_period*1000);
  ++d1.counter;
  if (d1.counter > c1.conf_wrap) d1.counter = 1;
  dyn_url = lib_buildUrlDynamic(c1, d1);
  String msg = lib_wifiConnectandSend(c1, dyn_url);
}
//=============================================
// End of File
//=============================================
