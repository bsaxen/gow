//=============================================
// File.......: gpwStepperMotor.c
// Date.......: 2018-12-20
// Author.....: Benny Saxen
// Description: 
//=============================================
// Configuration
//=============================================
#include <ESP8266WiFi.h>
char* publishTopic = "huskvarna/carl/stepper/0";
int conf_period = 20;
int conf_wrap   = 999999;
const char* ssid       = "bridge";
const char* password   = "mypwsd";
const char* host       = "some.com";
const char* streamId   = "....................";
const char* privateKey = "....................";

//================================================
// Globals
//================================================
int current_pos = 0;
int loop_counter = 0;
int FULL_STEP = 1;
int HALF_STEP = 2;
int QUARTER_STEP = 3;
int CLOCKWISE = 1;
int COUNTER_CLOCKWISE = 2;

int DIR   = 4;   // D2
int STEP  = 5;   // D1
int SLEEP = 12;  // D6
int MS1   = 13;  // D7
int MS2   = 14;  // D5
int SW    = 15;  // D8

//================================================
int stepCW(int steps,int dd)
//================================================
{
  int i;
  digitalWrite(DIR, LOW);
  digitalWrite(SLEEP, HIGH); // Set the Sleep mode to AWAKE.
  for(i=0;i<=steps;i++)
    {
      delayMicroseconds(200);
      digitalWrite(STEP, HIGH);
      delay(dd);
      digitalWrite(STEP, LOW);
      delay(dd);
      if (digitalRead(SW) == HIGH) return 2;
    }
  digitalWrite(DIR, LOW);
  digitalWrite(SLEEP, LOW); // Set the Sleep mode to SLEEP.Serial.println
  return 1;
}

//================================================
int stepCCW(int steps,int dd)
//================================================  current_pos +=  number_of_step;

{
  int i;
  digitalWrite(DIR, HIGH);
  digitalWrite(SLEEP, HIGH); // Set the Sleep mode to AWAKE.
  for(i=0;i<=steps;i++)
    {
      delayMicroseconds(200);
      digitalWrite(STEP, HIGH);
      delay(dd);
      digitalWrite(STEP, LOW);
      delay(dd);
      if (digitalRead(SW) == HIGH) return 2;
        
    }
  digitalWrite(DIR, LOW);
  digitalWrite(SLEEP, LOW); // Set the Sleep mode to SLEEP.
  return 1;
}
//================================================
void go_to_pos(int cur, int pos)
//================================================
{
  int delta = pos -cur;
    Serial.print( "delta = ");
      Serial.println(delta);
  if (delta > 0)
  {
    move_stepper(1, 1, delta, 100);
  }
  else 
  {
    move_stepper(2, 1, abs(delta), 100);
  }
  current_pos += delta;
}
//================================================
void reset_pos()
//================================================
{
   Serial.println( "Reset position...");
  move_stepper(2,1,300,100);
   move_stepper(1,1,10, 100);
   current_pos = 10;
    Serial.println( "Reset finnished!");
}

//================================================
void move_stepper(int dir, int step_size, int number_of_step, int delay_between_steps){
//================================================
        int sw = 0;
      
        
        Serial.println( "Stepper awake");

        if(step_size == FULL_STEP)
        {
            Serial.println( "Sstepstepper FULL STEP");
            digitalWrite(MS1,LOW);
            digitalWrite(MS2,LOW);
        }
        else if (step_size == HALF_STEP)
        {
            Serial.println( "Stepper HALF STEP");
            digitalWrite(MS1,HIGH);
            digitalWrite(MS2,LOW);
        }
        else if (step_size == QUARTER_STEP)
        {
            Serial.println( "Stepper QUARTER STEP");
            digitalWrite(MS1,LOW);
            digitalWrite(MS2,HIGH);
        }
        else // default fullstep
        {
            Serial.println( "Steppernumber_of_step DEFAULT FULL STEP");
            digitalWrite(MS1,LOW);
            digitalWrite(MS2,LOW);
        }

        if(dir == CLOCKWISE)
        {
            current_pos +=  number_of_step;

            Serial.println( "Stepper motor CW -->");
            sw = stepCW(number_of_step, delay_between_steps);
        }
        else if(dir == COUNTER_CLOCKWISE)
        {
            current_pos -=  number_of_step;

            Serial.println( "Stepper motor CCW  <--");
            sw = stepCCW(number_of_step, delay_between_steps);
        }
        else
            Serial.println( "ERROR: Unknown direction for stepper motor");

        digitalWrite(MS1,LOW);
        digitalWrite(MS2,LOW);
        Serial.println( "Stepper sleeping");

        if(sw == 2)
        {
          current_pos = 0;
          Serial.println("RESET");
        }
       
       Serial.print( "current position: ");
       Serial.println(current_pos);  
}

//================================================
void gow_publish()
{
  WiFiClient client;
  const int httpPort = 80;
  if (!client.connect(host, httpPort)) {
    Serial.println("connection failed");
    return;
  }
  
  String url = "/gowServer.php";
  url += "?do=data";
  url += "&topic=";
  url += publishTopic;
  url += "&no=";
  url += loop_counter;
  url += "&wrap=";
  url += conf_wrap;
  url += "&type=";
  url += "STEPPER";
  url += "&p1=";
  url += "value";
  url += "&v1=";
  url += current_pos;
  url += "&period=";
  url += conf_period;
  url += "&message=";
  url += '2';
  url += "&hw=";
  url += "esp8266";
      
  //Serial.print("Requesting URL: ");
  //Serial.println(url);

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
  int n = 0;
  char buf[100];
  while (client.available()) {
    n++;
    String action = client.readStringUntil('\r');
    //Serial.print(action);
    //Serial.print(n);
    // Do something based upon the action string
    //move_stepper(2,1,300,100);
    //Serial.println(action.indexOf(':'));
    if (action.indexOf(':') == 1) 
    {
      int x = action.lastIndexOf(':');
      Serial.print("Stepper order received");
      Serial.println(x);
      x = x+2;
      action.toCharArray(buf,x);
      Serial.println(buf);
    }

    
  }

  Serial.println();
  Serial.println("closing connection");  
}
//================================================

//================================================
void setup(void){
//================================================
   Serial.begin(9600);
    //Initialize
    pinMode(DIR,OUTPUT);
    pinMode(STEP,OUTPUT);
    pinMode(SLEEP,OUTPUT);
    pinMode(MS1,OUTPUT);
    pinMode(MS2,OUTPUT);
    pinMode(SW, INPUT);

    digitalWrite(MS1,LOW);
    digitalWrite(MS2,LOW);
    digitalWrite(SLEEP,LOW);
    digitalWrite(DIR,LOW);
    digitalWrite(STEP,LOW);
    //Possible settings are (MS1/MS2) : full step (0,0), half step (1,0), 1/4 step (0,1), and 1/8 step (1,1)
    //reset_pos();
  
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
  loop_counter = 0;
}
//================================================
void loop(void){
//================================================

  loop_counter += 1;
  
  gow_publish();

  delay(5000);
}


