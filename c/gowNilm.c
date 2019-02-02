//=============================================
// File.......: gowNilm.c
// Date.......: 2019-02-01
// Author.....: Benny Saxen
// Description: nilm - electricity
//=============================================
// Configuration
//=============================================
//#include "gowLib.c"

struct Configuration c1;
struct Data d1;
//=============================================

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
    //digitalWrite(led_pin,HIGH);
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
    //digitalWrite(led_pin,LOW);
}
//===============================================================
void setup(){
//===============================================================
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
    
    bounce_value = 36000./electric_meter_pulses; // based on max power = 100 000 Watt

    pinMode(interrupt_pin, INPUT_PULLUP);
    pinMode(led_pin, OUTPUT);
    digitalWrite(led_pin,LOW);
    attachInterrupt(interrupt_pin, measure, FALLING);
    lib_wifiBegin(c1);
    d1.counter = 0;
}
//=============================================
void loop()
//=============================================
{
  ++d1.counter;
  if (d1.counter > c1.conf_wrap) d1.counter = 1;
  d1.rssi = WiFi.RSSI();
  String stat_url = lib_buildUrlStatic(c1);
  String dyn_url = lib_buildUrlDynamic(c1, d1);

  String cur_url = " ";
  if (d1.counter%100 == 0)
  {
    cur_url = stat_url;
  }
  else
  {
    cur_url = dyn_url;
    // Add payload
    cur_url += "&payload=";
    cur_url += "{";
        cur_url += "\"elpow";
        cur_url += "\":\"";
        cur_url += elpow;
        cur_url += "\"";
    cur_url += "}";
  }

  String msg = lib_wifiConnectandSend(c1, cur_url);
  delay(c1.conf_period*1000);
}
//=============================================
// End of File
//=============================================
