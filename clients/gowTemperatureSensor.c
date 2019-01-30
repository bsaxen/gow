//=============================================
// File.......: gowTemperatureSensor.c
// Date.......: 2019-01-30
// Author.....: Benny Saxen
// Description: Signal from D1 pin. 
// 4.7kOhm between signal and Vcc
// Problem access port: sudo chmod 666 /dev/ttyUSB0
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
  Serial.begin(9600);
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


int counter = 0;
//=============================================
void loop()
//=============================================
{
  float temps[10];
  int i;
  char order[10];
  char buf[100];
    
  ++counter;
  if (counter > conf_wrap) counter = 1;
    
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
    for (int i=1;i<=nsensors;i++)
    {
        url += "\"temp";
        url += i;
        url += "\":\"";
        url += temps[i-1];
        url += "\"";
        if(i < nsensors)url += ",";
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
    while (client.available()) 
    {
      String action = client.readStringUntil('\r');
      if (action.indexOf(':') == 1) 
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
    delay(conf_period*1000);
}
