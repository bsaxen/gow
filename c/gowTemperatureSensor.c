//=============================================
// File.......: gowTemperatureSensor.c
// Date.......: 2019-02-02
// Author.....: Benny Saxen
// Description: Signal from D1 pin.
// 4.7kOhm between signal and Vcc
// Problem access port: sudo chmod 666 /dev/ttyUSB0
//=============================================
// Configuration
//=============================================
//#include "gowLib.c"

struct Configuration c1;
struct Data d1;
int wifi_ss = 0;



//=============================================

#include <OneWire.h>
#include <DallasTemperature.h>
#define ONE_WIRE_BUS 5 // Pin for connecting one wire sensor
#define TEMPERATURE_PRECISION 12

///. CUSTOM variables
OneWire oneWire(ONE_WIRE_BUS);
DallasTemperature sensor(&oneWire);
DeviceAddress device[10];
int nsensors = 0;
int counter = 0;
/// END OF - CUSTOM variables

//=============================================
void SetUpTemperatureSensors()
//=============================================
{

    pinMode(ONE_WIRE_BUS, INPUT);
    sensor.begin();
    nsensors = sensor.getDeviceCount();
    if(nsensors > 0)
    {
        for(int i=0;i<nsensors;i++)
        {
            sensor.getAddress(device[i], i);
            sensor.setResolution(device[i], TEMPERATURE_PRECISION);
        }
    }
}


//=============================================
void setup()
//=============================================
{
 
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
  delay(10);

  // We start by connecting to a WiFi network
  SetUpTemperatureSensors();
  Serial.println(nsensors);

  lib_wifiBegin(c1);
  d1.counter = 0;
}

//=============================================
void loop()
//=============================================
{
  float temps[10];
  int i;
  char order[10];
  char buf[100];

  delay(c1.conf_period*1000);
  ++d1.counter;

  if (d1.counter > c1.conf_wrap) d1.counter = 1;

  //Retrieve one or more temperature values
  sensor.requestTemperatures();
  //Loop through results and publish
  for(int i=0;i<nsensors;i++)
  {
      float temperature = sensor.getTempCByIndex(i);
      if (temperature > -100) // filter out bad values , i.e. -127
      {
        temps[i] = temperature;
        Serial.println(temperature);
      }
  }

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
    for (int i=1;i<=nsensors;i++)
    {
        cur_url += "\"temp";
        cur_url += i;
        cur_url += "\":\"";
        cur_url += temps[i-1];
        cur_url += "\"";
        if(i < nsensors)cur_url += ",";
    }
    cur_url += "}";
  }

  String msg = lib_wifiConnectandSend(c1, cur_url);

}
//=============================================
// End of File
//=============================================
