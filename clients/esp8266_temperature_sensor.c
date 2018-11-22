///
/// @file   esp8266_temperature_sensor.c
/// @Author Benny Saxen
/// @date   2017-01-13
/// @brief  Boiler plate application for onewire temperature sensor(DS18B20)
///

#include <OneWire.h>
#include <DallasTemperature.h>
#define ONE_WIRE_BUS 5 // Pin for connecting one wire sensor
#define TEMPERATURE_PRECISION 12

///. CUSTOM variables
OneWire oneWire(ONE_WIRE_BUS);
DallasTemperature sensor(&oneWire);
DeviceAddress device[10];
int nsensors = 0;
/// END OF - CUSTOM variables

/// Custom function definitions
void SetUpTemperatureSensors(){

    pinMode(ONE_WIRE_BUS, INPUT);
    sensor.begin();
    nsensors = sensor.getDeviceCount();
    WLOG_INFO << "nsensors " << nsensors;
    ULOG_INFO << "nsensors " << nsensors;
    if(nsensors > 0)
    {
        for(int i=0;i<nsensors;i++)
        {
            sensor.getAddress(device[i], i);
            sensor.setResolution(device[i], TEMPERATURE_PRECISION);
        }
    }
}

SetUpTemperatureSensors();

while(1)
{
    //Retrieve one or more temperature values
    sensor.requestTemperatures();
    //Loop through results and publish
    for(int i=0;i<nsensors;i++){
        float temperature = sensor.getTempCByIndex(i);
        if (temperature > -100) // filter out bad values , i.e. -127
        {
            TemperatureMessage msg;
            msg.data.unit = Temperature_Unit_CELSIUS;
            msg.data.value = temperature;

            Ioant::Topic topic = IOANT->GetConfiguredTopic();
            topic.stream_index = i;
            bool result = IOANT->Publish(msg, topic);
        }
    }
}
