

const byte interrupt_pin = 5;
const byte led_pin = 4;
int timeToCheckStatus = 0;
unsigned long t1,t2,dt,ttemp;
float elpow = 0.0;
int interrupt_counter = 0;
int electric_meter_pulses = 1000;  //1000 pulses/kWh
int bounce_value = 50; // minimum time between interrupts
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
void setup(void){

    electric_meter_pulses =  loaded_configuration.app_generic_a;
    bounce_value = 36000./electric_meter_pulses; // based on max power = 100 000 Watt

    pinMode(interrupt_pin, INPUT_PULLUP);
    pinMode(led_pin, OUTPUT);
    digitalWrite(led_pin,LOW);
    attachInterrupt(interrupt_pin, measure, FALLING);

}

void loop(void){

}

