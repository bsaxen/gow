
int DIR   = 4;   // D2
int STEP  = 5;   // D1
int SLEEP = 12;  // D6
int MS1   = 13;  // D7
int MS2   = 14;  // D5

int SW    = 15;  // D8


// Stepper Motor Characteristics
float stepper_resolution = 1.5;
int stepper_gearbox = 5;  // factor of scaling down rotation
int stepper_mode = 1; // full step (0,0), half step (1,0), 1/4 step (0,1), and 1/8 step (1,1 : default).

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

int current_pos = 0;

void setup(void){
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
    reset_pos();
}

void loop(void){
 Serial.println(digitalRead(SW) );
 delay(2000);
 go_to_pos(current_pos, 100);
 delay(1000);
 go_to_pos(current_pos, 200);
 
}

int FULL_STEP = 1;
int HALF_STEP = 2;
int QUARTER_STEP = 3;
int CLOCKWISE = 1;
int COUNTER_CLOCKWISE = 2;



//Find current potted plant
void go_to_pos(int cur, int pos)
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

void reset_pos()
{
   Serial.println( "Reset position...");
  move_stepper(2,1,300,100);
   move_stepper(1,1,10, 100);
   current_pos = 10;
    Serial.println( "Reset finnished!");
}

// Function for handling received MQTT messages
void move_stepper(int dir, int step_size, int number_of_step, int delay_between_steps){
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
