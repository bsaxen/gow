//=============================================
// File.......: gowTemperatureSensor.c
// Date.......: 2018-12-22
// Author.....: Benny Saxen
// Description: Signal from D1 pin. 
// 4.7kOhm between signal and Vcc
//=============================================
// Configuration
//=============================================
char* publishTopic = "kvv32/test/temperature/0";
int conf_period = 10;
int conf_wrap   = 999999;
const char* ssid       = "my ssid";
const char* password   = "my pswd";
const char* host       = "gow.simuino.com";
const char* streamId   = "....................";
const char* privateKey = "....................";
//=============================================
#include <ESP8266WiFi.h>
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
  Serial.begin(115200);
  delay(10);

  // We start by connecting to a WiFi network
  SetUpTemperatureSensors();
  Serial.println(nsensors);
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



//=============================================
void loop()
//=============================================
{
  float temps[10];
  int i;
  char order[10];
  char buf[100];
    
  delay(conf_period*1000);
    
  ++counter;
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
    // Use WiFiClient class to create TCP connections
    WiFiClient client;
    const int httpPort = 80;
    if (!client.connect(host, httpPort)) {
      Serial.println("connection failed");
      return;
    }

    // We now create a URI for the request
    String url = "/gowServer.php";
    url += "?do=data";
    url += "&topic=";
    url += publishTopic;
    url += "&no=";
    url += counter;
    url += "&wrap=";
    url += conf_wrap;
    url += "&type=";
    url += "TEMPERATURE";
    for (i=0;i<nsensors,i++)
    {
        url += "&p";
        url += i;
        url += "=";
        url += temps[i];
        url += "&v";
        url += i;
        url += "=";
        url += "celcius";
    }

    url += "&ts=";
    url += "void;
        
    url += "&period=";
    url += conf_period;
    url += "&url=";
    url += "gow.simuino.com";
    url += "&hw=";
    url += "esp8266";

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
    while (client.available()) 
    {
      String action = client.readStringUntil('\r');
      if (action.indexOf(':') == 1) 
      {
        int x = action.lastIndexOf(':');
        Serial.print("Order received");
        Serial.println(x);
        x = x+2;
        action.toCharArray(buf,x);
        Serial.println(buf);  
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
           Serial.println(buf);
           sscanf(buf,"%s %d",order, &conf_period);
        }
      }
    }

    Serial.println();
    Serial.println("closing connection");
}
